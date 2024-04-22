<?php

namespace App\Controller\Admin;

use App\Entity\Nimbus;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class NimbusCrudController extends AbstractCrudController
{
    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Nimbus::class;
    }
}
