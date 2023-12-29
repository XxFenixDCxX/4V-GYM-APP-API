<?php

namespace App\Controller;

use App\Entity\ActivityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ActivityTypeController extends AbstractController
{
    #[Route('/activityTypes', name: 'get_activityTypes', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $activityTypes = $entityManager->getRepository(ActivityType::class)->findAll();

        // Convertir la colección a un array
        $activityTypesArray = array_map(fn($activityTypes) => $activityTypes->toArray(), $activityTypes);

        return $this->json($activityTypesArray);
    }
}
