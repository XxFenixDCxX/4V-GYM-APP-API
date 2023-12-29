<?php

namespace App\Controller;

use App\Entity\Activity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ActivityController extends AbstractController
{
    #[Route('/activities', name: 'get_activities', methods: ['GET'])]
    public function getAll(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $dateParameter = $request->query->get('date');

        $activityRepository = $entityManager->getRepository(Activity::class);

        if ($dateParameter) {
            // Si se proporciona el parámetro de fecha, intenta buscar actividades por esa fecha y hora
            $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $dateParameter . ' 00:00:00');

            if (!$dateTime) {
                return $this->json(['error' => 'Formato de fecha y hora no válido. Use Y-m-d H:i:s.'], 400);
            }

            $activities = $activityRepository->findBy(['date_start' => $dateTime]);
        } else {
            // Si no se proporciona el parámetro de fecha, obtén todas las actividades
            $activities = $activityRepository->findAll();
        }

        $activitiesArray = [];

        foreach ($activities as $activity) {
            $activitiesArray[] = $activity->toArray();
        }

        return $this->json($activitiesArray);
    }
}
