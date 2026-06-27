<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\Table(name: '`member`')]
#[ORM\Index(name: 'idx_member_company', columns: ['company'])]
#[ORM\Index(name: 'idx_member_postal_code', columns: ['postal_code'])]
#[ORM\Index(name: 'idx_member_city', columns: ['city'])]
#[ORM\Index(name: 'idx_member_siret', columns: ['siret'])]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $company;

    #[ORM\Column(length: 255)]
    private string $address;

    #[ORM\Column(length: 10)]
    private string $postalCode;

    #[ORM\Column(length: 100)]
    private string $city;

    #[ORM\Column(length: 100)]
    private string $phone;

    #[ORM\Column(length: 255)]
    private string $siret;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $returnedAt = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Representative $representative;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getSiret(): string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getReturnedAt(): ?\DateTimeImmutable
    {
        return $this->returnedAt;
    }

    public function setReturnedAt(?\DateTimeImmutable $returnedAt): static
    {
        $this->returnedAt = $returnedAt;

        return $this;
    }

    public function isReturned(): bool
    {
        return null !== $this->returnedAt;
    }

    public function getRepresentative(): Representative
    {
        return $this->representative;
    }

    public function setRepresentative(Representative $representative): static
    {
        $this->representative = $representative;

        return $this;
    }
}
