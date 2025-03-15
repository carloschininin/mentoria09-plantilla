<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $data = [
            ['country' => 'USA', "value" => rand(500, 2000)],
            ['country' => 'CHINA', "value" => rand(500, 2000)],
            ['country' => 'ALEMANIA', "value" => rand(500, 2000)],
            ['country' => 'FRANCIA', "value" => rand(500, 2000)],
            ['country' => 'RUSIA', "value" => rand(500, 2000)],
            ['country' => 'CANADA', "value" => rand(500, 2000)],
            ['country' => 'MEXICO', "value" => rand(500, 2000)],
            ['country' => 'PERU', "value" => rand(500, 2000)],
        ];

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'dataPaises' => $data
        ]);
    }
}
