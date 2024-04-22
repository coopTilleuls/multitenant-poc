<?php

namespace App\Listener;

use App\Connection\DoctrineMultidatabaseConnection;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest')]
class KernelRequestEvent
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly ManagerRegistry $registry)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($token = $this->tokenStorage->getToken()) {
            /* @var User $user */
            $user = $token->getUser();
            if (array_search('ROLE_SUPER_ADMIN', $user->getRoles())) {
                return;
            }
            /* @var DoctrineMultidatabaseConnection $doctrineConnection */
            $doctrineConnection = $this->registry->getConnection('default');
            $doctrineConnection->changeDatabase([
                'dbname' => $user->getSqlDbName(),
                'user' => $user->getSqlUserName(),
                'password' => md5($user->getSqlUserName()),
            ]);
        }
    }
}
