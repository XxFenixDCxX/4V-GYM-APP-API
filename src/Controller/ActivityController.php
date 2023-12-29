<?php

namespace App\Controller;

use App\Entity\Activity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ActivityController extends AbstractController
{
    #[Route('/activitys', name: 'get_activity', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $activitys = $entityManager->getRepository(Activity::class)->findAll();

        $activitysArray = array_map(fn ($activity) => $activity->toArray(), $activitys);

        return $this->json($activitysArray);
    }
}
