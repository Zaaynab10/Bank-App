<?php

namespace App\Auth\Service;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;

class LoginRedirectService
{
    private $security;
    private $router;

    public function __construct(Security $security, RouterInterface $router)
    {
        $this->security = $security;
        $this->router = $router;
    }

    public function redirectUserBasedOnRole(): string
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->router->generate('admin_home');
        }

        if ($this->security->isGranted('ROLE_CUSTOMER')) {
            return $this->router->generate('accounts');
        }

        return $this->router->generate('homepage');
    }
}
