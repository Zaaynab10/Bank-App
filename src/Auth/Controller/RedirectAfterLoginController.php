<?php


namespace App\Auth\Controller;

use App\Auth\Service\LoginRedirectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectAfterLoginController extends AbstractController
{
    private $loginRedirectService;

    public function __construct(LoginRedirectService $loginRedirectService)
    {
        $this->loginRedirectService = $loginRedirectService;
    }

    #[Route('/post-login', name: 'post_login')]
    public function redirectAfterLogin(): Response
    {
        $redirectUrl = $this->loginRedirectService->redirectUserBasedOnRole();
        return $this->redirect($redirectUrl);
    }
}
