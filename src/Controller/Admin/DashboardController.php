<?php

namespace App\Controller\Admin;

use App\Entity\Books;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    #[\Override]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('POC')
            ->renderSidebarMinimized(true);
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Books', 'fa fa-book', Books::class);
    }
}
