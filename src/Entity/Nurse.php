<?php

namespace App\Entity;

use App\Repository\NurseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NurseRepository::class)]
class Nurse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25)]
    private ?string $internal_id = null;

    #[ORM\Column(length: 25)]
    private ?string $first_name = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $last_name = null;

    #[ORM\Column(length: 25)]
    private ?string $license_number = null;

    #[ORM\Column(length: 25)]
    private ?string $username = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $shift = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInternalId(): ?string
    {
        return $this->internal_id;
    }

    public function setInternalId(string $internal_id): static
    {
        $this->internal_id = $internal_id;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->license_number;
    }

    public function setLicenseNumber(string $license_number): static
    {
        $this->license_number = $license_number;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getShift(): ?string
    {
        return $this->shift;
    }

    public function setShift(?string $shift): static
    {
        $this->shift = $shift;

        return $this;
    }
}
