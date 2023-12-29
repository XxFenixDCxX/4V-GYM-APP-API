<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Monitor;

class MonitorController extends AbstractController
{
    #[Route('/monitor', name: 'get_monitors', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $monitors = $entityManager->getRepository(Monitor::class)->findAll();

        // Convertir la colección a un array
        $monitorsArray = array_map(fn($monitor) => $monitor->toArray(), $monitors);

        return $this->json($monitorsArray);
    }
}
