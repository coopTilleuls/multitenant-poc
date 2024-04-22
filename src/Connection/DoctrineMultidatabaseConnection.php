<?php

namespace App\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class DoctrineMultidatabaseConnection extends Connection
{
    /**
     * @param array<string, string> $params
     * @return bool
     * @throws Exception
     */
    public function changeDatabase(array $params): bool
    {
        $currentParams = $this->getParams();

        if (array_key_exists('url', $params)) {
            $this->parseUrl($params['url']);
        } else {
            if ($this->isConnected()) {
                $this->close();
            }

            foreach ($params as $key => $param) {
                $currentParams[$key] = $param;
            }

            $currentParams['url'] = 'postgresql://'.$currentParams['user'].':'.$currentParams['password'].'@'.$currentParams['host'].':'.$currentParams['port'].'/'.$currentParams['dbname'];

            parent::__construct(
                $currentParams,
                $this->_driver,
                $this->_config,
                $this->_eventManager
            );

            return true;
        }

        return false;
    }

    private function parseUrl(string $url): void
    {
        $params = parse_url($url);
        $this->changeDatabase([
            'user' => $params['user'],
            'password' => $params['pass'],
            'host' => $params['host'],
            'port' => $params['port'],
            'dbname' => str_replace('/', '', $params['path']),
        ]);
    }
}
