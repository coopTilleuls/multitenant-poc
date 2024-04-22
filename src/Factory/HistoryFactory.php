<?php

namespace App\Factory;

use App\Entity\History;
use App\Entity\User;

class HistoryFactory
{
    public static function createHistory(User $user, string $comment): History
    {
        $history = new History();
        $history->setOwner($user->getOwner() ?: $user);
        $history->setComment($comment);

        return $history;
    }

    public static function createHistoryFromParams(User $user, string $entityClass, string $type, string $subject): History
    {
        $date = new \DateTimeImmutable();
        $comment = sprintf('[%s] (%s %s) %s by %s', $date->format('Y-m-d H:i:s'), $entityClass, $type, $subject, $user);

        return HistoryFactory::createHistory($user, $comment);
    }
}
