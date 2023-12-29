<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\ActivityType;
use App\Entity\Monitor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActivityController extends AbstractController
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

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

    #[Route('/activities', name: 'create_activity', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $activity = new Activity();

        // Validar que se proporciona el tipo de actividad y que existe en la base de datos
        if (empty($data['activity_type_id'])) {
            return $this->json(['error' => 'El tipo de actividad es obligatorio.'], 400);
        }

        $activityType = $entityManager->getRepository(ActivityType::class)->find($data['activity_type_id']);

        if (!$activityType) {
            return $this->json(['error' => 'Tipo de actividad no encontrado.'], 400);
        }

        // Validar que la fecha es válida y se ajusta a los requisitos
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $data['date_start']);

        if (!$date || $date->format('H:i') != '09:00' && $date->format('H:i') != '13:30' && $date->format('H:i') != '17:30') {
            return $this->json(['error' => 'La fecha de inicio no es válida. Debe ser a las 09:00, 13:30 o 17:30.'], 400);
        }

        // Validar la duración (90 minutos)
        $dateEnd = clone $date;
        $dateEnd->modify('+90 minutes');

        if ($dateEnd->format('H:i') != '10:30' && $dateEnd->format('H:i') != '15:00' && $dateEnd->format('H:i') != '18:30') {
            return $this->json(['error' => 'La duración no es válida. Debe ser de 90 minutos.'], 400);
        }

        $activity->setActivityType($activityType);
        $activity->setDateStart($date);
        $activity->setDateEnd($dateEnd);

        // Validar monitores
        if (empty($data['monitors'])) {
            return $this->json(['error' => 'Se requiere al menos un monitor para la actividad.'], 400);
        }

        foreach ($data['monitors'] as $monitorId) {
            $monitor = $entityManager->getRepository(Monitor::class)->find($monitorId);

            if (!$monitor) {
                return $this->json(['error' => 'Monitor no encontrado.'], 400);
            }

            // Validar que el monitor cumple con los requisitos del tipo de actividad
            if (!$monitor->getActivities()->isEmpty()) {
                foreach ($monitor->getActivities() as $monitorActivity) {
                    if ($monitorActivity->getDateStart() < $dateEnd && $monitorActivity->getDateEnd() > $date) {
                        return $this->json(['error' => 'El monitor ya está asignado en ese horario.'], 400);
                    }
                }
            }

            $activity->addMonitor($monitor);
        }

        // Validar entidad con el ValidatorInterface
        $errors = $this->validator->validate($activity);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $entityManager->persist($activity);
        $entityManager->flush();

        return $this->json($activity->toArray());
    }

    #[Route('/activities/{id}', name: 'update_activity', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $activity = $entityManager->getRepository(Activity::class)->find($id);

        if (!$activity) {
            return $this->json(['error' => 'Actividad no encontrada.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Validar que se proporciona el tipo de actividad y que existe en la base de datos
        if (!empty($data['activity_type_id'])) {
            $activityType = $entityManager->getRepository(ActivityType::class)->find($data['activity_type_id']);

            if (!$activityType) {
                return $this->json(['error' => 'Tipo de actividad no encontrado.'], 400);
            }

            $activity->setActivityType($activityType);
        }

        // Validar que la fecha es válida y se ajusta a los requisitos
        if (!empty($data['date_start'])) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $data['date_start']);

            if (!$date || $date->format('H:i') != '09:00' && $date->format('H:i') != '13:30' && $date->format('H:i') != '17:30') {
                return $this->json(['error' => 'La fecha de inicio no es válida. Debe ser a las 09:00, 13:30 o 17:30.'], 400);
            }

            // Validar la duración (90 minutos)
            $dateEnd = clone $date;
            $dateEnd->modify('+90 minutes');

            if ($dateEnd->format('H:i') != '10:30' && $dateEnd->format('H:i') != '15:00' && $dateEnd->format('H:i') != '18:30') {
                return $this->json(['error' => 'La duración no es válida. Debe ser de 90 minutos.'], 400);
            }

            $activity->setDateStart($date);
            $activity->setDateEnd($dateEnd);
        }

        // Validar monitores
        if (!empty($data['monitors'])) {
            foreach ($activity->getMonitors() as $monitor) {
                $activity->removeMonitor($monitor);
            }

            foreach ($data['monitors'] as $monitorId) {
                $monitor = $entityManager->getRepository(Monitor::class)->find($monitorId);

                if (!$monitor) {
                    return $this->json(['error' => 'Monitor no encontrado.'], 400);
                }

                // Validar que el monitor cumple con los requisitos del tipo de actividad
                if (!$monitor->getActivities()->isEmpty()) {
                    foreach ($monitor->getActivities() as $monitorActivity) {
                        if ($monitorActivity->getDateStart() < $dateEnd && $monitorActivity->getDateEnd() > $date) {
                            return $this->json(['error' => 'El monitor ya está asignado en ese horario.'], 400);
                        }
                    }
                }

                $activity->addMonitor($monitor);
            }
        }

        // Validar entidad con el ValidatorInterface
        $errors = $this->validator->validate($activity);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $entityManager->flush();

        return $this->json($activity->toArray());
    }
}
