<?php

namespace App\Controller\Admin;

use App\Entity\Books;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BooksCrudController extends AbstractCrudController
{
    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Books::class;
    }
}
