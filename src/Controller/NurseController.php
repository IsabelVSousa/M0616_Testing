<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NurseController extends AbstractController
{
    #[Route('/nurse', name: 'app_nurse')]
    public function index(): Response
    {
        return $this->render('nurse/index.html.twig', [
            'controller_name' => 'NurseController',
        ]);
    }
}
