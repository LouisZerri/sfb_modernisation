<?php

declare(strict_types=1);

namespace App\Service\Member;

use App\Dto\MemberDto;
use App\Entity\Member;
use App\Entity\Representative;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Orchestre la création, la mise à jour et la suppression d'un adhérent
 * (entreprise + représentant), à partir des données validées du formulaire.
 */
final readonly class MemberManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function create(MemberDto $dto): Member
    {
        $member = new Member();
        $member->setRepresentative(new Representative());

        $this->fill($member, $dto);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return $member;
    }

    public function update(Member $member, MemberDto $dto): void
    {
        $this->fill($member, $dto);
        $this->entityManager->flush();
    }

    public function delete(Member $member): void
    {
        // La cascade 'remove' supprime aussi le représentant associé.
        $this->entityManager->remove($member);
        $this->entityManager->flush();
    }

    private function fill(Member $member, MemberDto $dto): void
    {
        $representative = $member->getRepresentative();
        $representative->setLastName((string) $dto->lastName);
        $representative->setFirstName((string) $dto->firstName);
        $representative->setEmail((string) $dto->email);

        $member->setCompany((string) $dto->company);
        $member->setAddress((string) $dto->address);
        $member->setPostalCode((string) $dto->postalCode);
        $member->setCity((string) $dto->city);
        $member->setPhone((string) $dto->phone);
        $member->setSiret((string) $dto->siret);
        $member->setReturnedAt($dto->received ? $dto->returnedAt : null);
    }
}
