<?php

declare(strict_types=1);

namespace App\Service\Member;

use App\Dto\MemberDto;
use App\Entity\Member;
use App\Entity\Representative;
use App\Service\Search\MemberIndexer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Orchestre la création, la mise à jour et la suppression d'un adhérent
 * (entreprise + représentant), à partir des données validées du formulaire,
 * et maintient l'index Elasticsearch à jour.
 */
final readonly class MemberManager
{
    /** Clé du compteur d'adhérents mis en cache (partagée avec le contrôleur). */
    public const COUNT_CACHE_KEY = 'members_count';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MemberIndexer $indexer,
        private LoggerInterface $logger,
        private CacheInterface $cacheMembers,
    ) {
    }

    public function create(MemberDto $dto): Member
    {
        $member = new Member();
        $member->setRepresentative(new Representative());

        $this->fill($member, $dto);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        $this->indexSafely($member);
        $this->cacheMembers->delete(self::COUNT_CACHE_KEY);

        return $member;
    }

    public function update(Member $member, MemberDto $dto): void
    {
        $this->fill($member, $dto);
        $this->entityManager->flush();

        $this->indexSafely($member);
    }

    public function delete(Member $member): void
    {
        $id = $member->getId();

        // La cascade 'remove' supprime aussi le représentant associé.
        $this->entityManager->remove($member);
        $this->entityManager->flush();

        if (null !== $id) {
            $this->removeFromIndexSafely($id);
        }

        $this->cacheMembers->delete(self::COUNT_CACHE_KEY);
    }

    /**
     * L'indisponibilité d'Elasticsearch ne doit jamais faire échouer une écriture en base.
     */
    private function indexSafely(Member $member): void
    {
        try {
            $this->indexer->index($member);
        } catch (\Throwable $e) {
            $this->logger->error('Échec de l\'indexation Elasticsearch d\'un adhérent.', ['exception' => $e]);
        }
    }

    private function removeFromIndexSafely(int $id): void
    {
        try {
            $this->indexer->remove($id);
        } catch (\Throwable $e) {
            $this->logger->error('Échec de la suppression d\'un adhérent dans l\'index Elasticsearch.', ['exception' => $e]);
        }
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
