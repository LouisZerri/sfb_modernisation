<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    /**
     * Page d'adhérents triée par entreprise, avec le représentant joint
     * dans la même requête (évite le N+1 à l'affichage).
     *
     * @return Paginator<Member>
     */
    public function paginate(int $page, int $perPage): Paginator
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('m')
            ->addSelect('r')
            ->join('m.representative', 'r')
            ->orderBy('m.company', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery();

        return new Paginator($query, fetchJoinCollection: false);
    }

    /**
     * Tous les adhérents avec leur représentant joint, pour l'indexation Elasticsearch.
     *
     * @return list<Member>
     */
    public function findAllForIndexing(): array
    {
        return $this->createQueryBuilder('m')
            ->addSelect('r')
            ->join('m.representative', 'r')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySiret(string $siret): ?Member
    {
        return $this->createQueryBuilder('m')
            ->addSelect('r')
            ->join('m.representative', 'r')
            ->andWhere('m.siret = :siret')
            ->setParameter('siret', $siret)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
