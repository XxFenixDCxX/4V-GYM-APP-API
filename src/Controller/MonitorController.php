<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Monitor;

class MonitorController extends AbstractController
{
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

        $entityManager->persist($monitor);
        $entityManager->flush();

        return $this->getAll($entityManager);
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
