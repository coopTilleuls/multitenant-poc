<?php

namespace App\Listener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: User::class)]
class UserPostPersistListener
{
    public function postPersist(User $user, PostPersistEventArgs $event): void
    {
        $sql_id = str_replace('-', '_', $user->getOwner() ?: $user->getId());
        $user->setSqlUserName("user_{$sql_id}");
        $user->setSqlDbName("client_{$sql_id}");

        $event->getObjectManager()->flush();
    }
}
