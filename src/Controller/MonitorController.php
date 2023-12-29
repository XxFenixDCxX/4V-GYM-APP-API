<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Monitor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MonitorController extends AbstractController
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    #[Route('/monitors', name: 'get_monitors', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $monitors = $entityManager->getRepository(Monitor::class)->findAll();

        $monitorsArray = array_map(fn ($monitor) => $monitor->toArray(), $monitors);

        return $this->json($monitorsArray);
    }

    #[Route('/monitors', name: 'create_monitor', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $monitor = new Monitor();
        $monitor->setName($data['name']);
        $monitor->setEmail($data['email']);
        $monitor->setPhone($data['phone']);
        $monitor->setPhoto($data['photo']);

        $errors = $this->validator->validate($monitor);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $entityManager->persist($monitor);
        $entityManager->flush();

        return $this->json($monitor->toArray());
    }
    
    #[Route('/monitors/{id}', name: 'update_monitor', methods: ['PUT'])]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $monitor = $entityManager->getRepository(Monitor::class)->find($id);

        if (!$monitor) {
            throw $this->createNotFoundException('Monitor not found');
        }

        $data = json_decode($request->getContent(), true);

        $monitor->setName($data['name']);
        $monitor->setEmail($data['email']);
        $monitor->setPhone($data['phone']);
        $monitor->setPhoto($data['photo']);

        $errors = $this->validator->validate($monitor);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $entityManager->flush();

        return $this->getAll($entityManager);
    }
    

    #[Route('/monitors/{id}', name: 'delete_monitor', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $monitor = $entityManager->getRepository(Monitor::class)->find($id);

        if (!$monitor) {
            throw $this->createNotFoundException('Monitor not found');
        }

        $entityManager->remove($monitor);
        $entityManager->flush();

        return $this->getAll($entityManager);
    }
}
