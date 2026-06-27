<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Member;
use App\Validator\Siret as SiretConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Données saisies dans le formulaire adhérent (représentant + entreprise),
 * validées avant tout passage vers les entités.
 */
final class MemberDto
{
    #[Assert\NotBlank(message: 'Le nom du représentant est obligatoire.')]
    #[Assert\Length(max: 100)]
    public ?string $lastName = null;

    #[Assert\NotBlank(message: 'Le prénom du représentant est obligatoire.')]
    #[Assert\Length(max: 100)]
    public ?string $firstName = null;

    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'Cet email n\'est pas valide.')]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Le nom de l\'entreprise est obligatoire.')]
    #[Assert\Length(max: 255)]
    public ?string $company = null;

    #[Assert\NotBlank(message: 'L\'adresse est obligatoire.')]
    #[Assert\Length(max: 255)]
    public ?string $address = null;

    #[Assert\NotBlank(message: 'Le code postal est obligatoire.')]
    #[Assert\Regex(pattern: '/^[0-9]{5}$/', message: 'Le code postal doit comporter 5 chiffres.')]
    public ?string $postalCode = null;

    #[Assert\NotBlank(message: 'La ville est obligatoire.')]
    #[Assert\Length(max: 100)]
    public ?string $city = null;

    #[Assert\NotBlank(message: 'Le téléphone est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^0[1-9]([-. ]?[0-9]{2}){4}$/',
        message: 'Le numéro de téléphone n\'est pas valide (format français attendu).'
    )]
    public ?string $phone = null;

    #[Assert\NotBlank(message: 'Le SIRET est obligatoire.')]
    #[SiretConstraint]
    public ?string $siret = null;

    public bool $received = false;

    #[Assert\When(
        expression: 'this.received === true',
        constraints: [
            new Assert\NotNull(message: 'Indiquez la date de réception du bulletin.'),
            new Assert\LessThanOrEqual(value: 'today', message: 'La date de réception ne peut pas être dans le futur.'),
        ],
    )]
    public ?\DateTimeImmutable $returnedAt = null;

    public static function fromMember(Member $member): self
    {
        $dto = new self();
        $representative = $member->getRepresentative();

        $dto->lastName = $representative?->getLastName();
        $dto->firstName = $representative?->getFirstName();
        $dto->email = $representative?->getEmail();
        $dto->company = $member->getCompany();
        $dto->address = $member->getAddress();
        $dto->postalCode = $member->getPostalCode();
        $dto->city = $member->getCity();
        $dto->phone = $member->getPhone();
        $dto->siret = $member->getSiret();
        $dto->returnedAt = $member->getReturnedAt();
        $dto->received = $member->isReturned();

        return $dto;
    }
}
