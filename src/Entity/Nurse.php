<?php

namespace App\Entity;

use App\Repository\NurseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NurseRepository::class)]
#[UniqueEntity(fields: ['internal_id'], message: 'This internal_id already exists')]
#[UniqueEntity(fields: ['username'], message: 'This username already exists')]
#[UniqueEntity(fields: ['license_number'], message: 'This license_number already exists')]
#[UniqueEntity(fields: ['email'], message: 'This email already exists')]
class Nurse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 25)]
    #[Assert\Regex('/^ENF-\d{4}$/', message: 'Format must be ENF-0000')]
    private ?string $internal_id = null;

    #[ORM\Column(length: 25)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 25)]
    private ?string $first_name = null;

    #[ORM\Column(length: 25, nullable: true)]
    #[Assert\Length(max: 25)]
    private ?string $last_name = null;

    #[ORM\Column(length: 25, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex('/^\d{8}$/', message: 'License number must have 8 digits')]
    private ?string $license_number = null;

    #[ORM\Column(length: 25, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 25)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 50, nullable: true, unique: true)]
    #[Assert\Email]
    #[Assert\Length(max: 50)]
    private ?string $email = null;

    #[ORM\Column(length: 25, nullable: true)]
    #[Assert\Choice(choices: ['morning', 'afternoon', 'night'])]
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
