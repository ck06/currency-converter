<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(Request $request): Response
    {
        return new JsonResponse();
    }

    #[Route('/login/forgot-password', name: 'forgot-password')]
    public function passwordReset(Request $request): Response
    {
        return new JsonResponse();
    }
}
