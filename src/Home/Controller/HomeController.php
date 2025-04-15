<?php

namespace App\Home\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController {
    #[Route('/', name: 'homepage')]
    public function index(SecurityBundleSecurity $security): Response {
        if ($security->getUser()) {
            return $this->redirectToRoute('post_login');
        }

        return $this->render('@Home/index.html.twig');
    }
}