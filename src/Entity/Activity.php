<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    private ?ActivityType $activity_type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivityType(): ?ActivityType
    {
        return $this->activity_type;
    }

    public function setActivityType(?ActivityType $activity_type): static
    {
        $this->activity_type = $activity_type;

        return $this;
    }
}
