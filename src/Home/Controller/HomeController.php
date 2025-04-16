<?php

namespace App\Home\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(Security $security): Response
    {
        if ($security->getUser()) {
            return $this->redirectToRoute('post_login');
        }

        return $this->render('@Home/index.html.twig');
    }
}
