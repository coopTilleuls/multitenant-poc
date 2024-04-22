<?php

namespace App\Command;

use App\Entity\User;
use App\Exceptions\TenantUserDatabaseNotCreatedException;
use App\Repository\UserRepository;
use App\Services\MultiTenantDatabaseHandler;
use BadMethodCallException;
use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:database:create',
    description: 'Creates a new database',
    aliases: ['app:create-database'],
    hidden: false
)]
class CreateDatabaseCommand extends Command
{
    public const string NAME_ARGUMENT = 'id';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MultiTenantDatabaseHandler $tenancyHandler,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::NAME_ARGUMENT, InputArgument::REQUIRED, 'ID of the user');
    }

    /**
     * @throws BadMethodCallException
     */
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument(self::NAME_ARGUMENT);

        /* @var User $user */
        $user = $this->userRepository->find($id);

        if (!$user) {
            $output->writeln("Can't find any user with id {$id}");

            return Command::FAILURE;
        }

        if ($this->tenancyHandler->createCredentialsForUser($user)) {
            try {
                if($this->tenancyHandler->createForeignTablesForUser($user)) {
                    return Command::SUCCESS;
                }
            } catch (TenantUserDatabaseNotCreatedException $_) {
                return Command::FAILURE;
            }
        }

        return Command::FAILURE;
    }
}
