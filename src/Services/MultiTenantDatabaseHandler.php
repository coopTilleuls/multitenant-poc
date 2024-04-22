<?php

namespace App\Services;

use App\Connection\DoctrineMultidatabaseConnection;
use App\Entity\User;
use App\Exceptions\TenantUserDatabaseNotCreatedException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ManyToManyOwningSideMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class MultiTenantDatabaseHandler
{
    private DoctrineMultidatabaseConnection $connection;
    private array $tablesNames;
    private array $manyTablesNames;

    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly KernelInterface $kernel,
        private readonly EntityManagerInterface $entityManager,
    ) {
        /* @var DoctrineMultidatabaseConnection $doctrineConnection */
        $doctrineConnection = $this->registry->getConnection('default');
        $this->connection = $doctrineConnection;
        $this->generateTablesArrays();
    }

    private function generateTablesArrays(): void
    {
        $this->tablesNames = [];
        $this->manyTablesNames = [];

        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            $tableName = $metadata->getTableName();
            $this->tablesNames[$tableName] = [];

            $mappings = $metadata->getAssociationMappings();
            foreach ($mappings as $mapping) {
                if ($mapping instanceof ManyToManyOwningSideMapping) {
                    $joinTableName = $mapping->joinTable->name;
                    $this->manyTablesNames[$joinTableName] = $mapping->joinTableColumns;
                    $this->manyTablesNames[$joinTableName][] = $this->entityManager->getClassMetadata($mapping->sourceEntity)->getTableName();
                    $this->manyTablesNames[$joinTableName][] = $this->entityManager->getClassMetadata($mapping->targetEntity)->getTableName();
                }
            }
        }
    }

    public function createCredentialsForUser(User $user): bool
    {
        $this->connection->changeDatabase([
            'dbname' => $user->getSqlDbName(),
        ]);

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $arguments = [
            'command' => 'doctrine:database:create',
            '--if-not-exists' => null,
            '--no-interaction' => null,
            '--connection' => 'default',
        ];

        $commandInput = new ArrayInput($arguments);
        try {
            $username = $user->getSqlUserName();
            $password = md5($username);

            $application->run($commandInput, new NullOutput());

            $this->connection->executeStatement("
                DROP USER IF EXISTS {$username};
                CREATE USER {$username} WITH PASSWORD '{$password}';
            ");

            unset($application);
            unset($kernel);
        } catch (\Exception $_) {
            return false;
        }

        $this->connection->changeDatabase([
            'dbname' => 'app',
        ]);

        $user->setDbCreated(true);
        $this->entityManager->flush();

        return $user->isDbCreated();
    }

    /**
     * @throws TenantUserDatabaseNotCreatedException
     */
    public function createForeignTablesForUser(User $user): bool
    {
        if (!$user->isDbCreated()) {
            throw new TenantUserDatabaseNotCreatedException();
        }

        $username = $user->getSqlUserName();
        $id = $user->getId();
        $psqlId = $this->generatePsqlIdForUser($user);
        $dbname = $user->getSqlDbName();

        try {
            $this->connection->changeDatabase([
                'dbname' => 'app',
            ]);

            if ($this->createViewsForUser($user)) {
                $this->connection->changeDatabase([
                    'dbname' => $user->getSqlDbName(),
                ]);

                $this->connection->executeStatement("
                    CREATE EXTENSION IF NOT EXISTS postgres_fdw;
                    CREATE SERVER IF NOT EXISTS app_fdw FOREIGN DATA WRAPPER postgres_fdw OPTIONS (host '127.0.0.1', port '5432', dbname 'app');
                    CREATE USER MAPPING IF NOT EXISTS FOR app SERVER app_fdw OPTIONS (user 'app');
                    CREATE USER MAPPING IF NOT EXISTS FOR {$username} SERVER app_fdw OPTIONS (user '{$username}', password_required 'false');

                    CREATE OR REPLACE FUNCTION override_id() RETURNS trigger as \$override_id\$
                        BEGIN
                            NEW.owner_id := '{$id}';
                            RETURN NEW;
                        END;
                    \$override_id\$
                    LANGUAGE plpgsql;
                ");

                foreach ($this->tablesNames as $name => $options) {
                    $view_name = "{$name}_{$psqlId}";
                    $function_name = "{$name}_id";

                    $this->connection->executeStatement("
                        DROP FOREIGN TABLE IF EXISTS {$name};
                        IMPORT FOREIGN SCHEMA public
                        LIMIT TO ({$view_name})
                        FROM SERVER app_fdw INTO public;
                        ALTER TABLE {$view_name} RENAME TO {$name};

                        CREATE OR REPLACE TRIGGER {$function_name}
                        BEFORE INSERT
                        ON \"{$name}\"
                        FOR EACH ROW
                        EXECUTE PROCEDURE override_id();
                    ");
                }

                foreach ($this->manyTablesNames as $name => $options) {
                    $view_name = "{$name}_{$psqlId}";

                    $this->connection->executeStatement("
                        DROP FOREIGN TABLE IF EXISTS {$name};
                        IMPORT FOREIGN SCHEMA public
                        LIMIT TO ({$view_name})
                        FROM SERVER app_fdw INTO public;
                        ALTER TABLE {$view_name} RENAME TO {$name};
                    ");
                }

                // Grant privileges to user on database
                $this->connection->executeStatement("
                    GRANT ALL ON SCHEMA public TO {$username};
                    GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO {$username};
                    GRANT ALL PRIVILEGES ON DATABASE {$dbname} TO {$username};
                ");

                return true;
            }
        } catch (\Exception $_) {
            return false;
        }

        return false;
    }

    private function generatePsqlIdForUser(User $user): string
    {
        return str_replace('-', '_', $user->getId());
    }

    /**
     * @throws Exception
     */
    private function createViewsForUser(User $user): bool
    {
        $this->connection->changeDatabase([
            'dbname' => 'app',
        ]);

        $username = $user->getSqlUserName();
        $id = $user->getId();
        $psqlId = $this->generatePsqlIdForUser($user);

        foreach ($this->tablesNames as $name => $options) {
            $view_name = "{$name}_{$psqlId}";

            $this->connection->executeStatement("
                DROP VIEW IF EXISTS {$view_name};
                CREATE VIEW {$view_name} AS SELECT * FROM {$name} WHERE id = '{$id}' OR owner_id = '{$id}';
                GRANT SELECT, INSERT, UPDATE, DELETE on {$view_name} TO {$username};
            ");
        }

        foreach ($this->manyTablesNames as $name => $options) {
            $view_name = "{$name}_{$psqlId}";

            $this->connection->executeStatement("
                DROP VIEW IF EXISTS {$view_name};
                CREATE VIEW {$view_name} AS SELECT * FROM {$name}
                WHERE {$options[0]} IN (SELECT id FROM {$options[2]} WHERE id = '{$id}' OR owner_id = '{$id}')
                    OR {$options[1]} IN (SELECT id FROM {$options[3]} WHERE id = '{$id}' OR owner_id = '{$id}');
                GRANT SELECT, INSERT, UPDATE, DELETE on {$view_name} TO {$username};
            ");
        }

        return true;
    }
}
