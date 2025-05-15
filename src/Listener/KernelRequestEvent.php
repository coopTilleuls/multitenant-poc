<?php

namespace App\Listener;

use App\Connection\DoctrineMultidatabaseConnection;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest')]
class KernelRequestEvent
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly UserRepository $userRepository)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $uri = $event->getRequest()->getRequestUri();
        if (in_array($uri, ['/', '/register', '/login'])) {
            return;
        }
        /* @var DoctrineMultidatabaseConnection $doctrineConnection */
        $doctrineConnection = $this->registry->getConnection('default');
        $subDomain = explode('.', $_SERVER['HTTP_HOST'])[0];
        $subDomain = 'localhost' === $subDomain ? 'admin' : $subDomain; // only to dev with localhost
        $tenant = $this->userRepository->findOneByTenant($subDomain);

        if (!$tenant) {
            throw new NotFoundHttpException();
        }

        if (array_search('ROLE_SUPER_ADMIN', $tenant->getRoles())) {
            return;
        }

        $doctrineConnection->changeDatabase([
            'dbname' => $tenant->getSqlDbName(),
            'user' => $tenant->getSqlUserName(),
            'password' => md5($tenant->getSqlUserName()),
        ]);
    }
}
