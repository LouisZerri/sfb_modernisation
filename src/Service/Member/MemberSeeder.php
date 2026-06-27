<?php

declare(strict_types=1);

namespace App\Service\Member;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Génère des adhérents fictifs en masse (insertion par lots),
 * réutilisé par la commande de seed et par le reset de la démo.
 */
final readonly class MemberSeeder
{
    private const BATCH_SIZE = 500;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MemberGenerator $generator,
        private CacheInterface $cacheMembers,
    ) {
    }

    /**
     * @param int           $count   Nombre d'adhérents à générer
     * @param bool          $fresh   Vide les tables adhérents au préalable
     * @param callable|null $onBatch Appelé après chaque lot avec sa taille (progression)
     */
    public function seed(int $count, bool $fresh = false, ?callable $onBatch = null): void
    {
        if ($fresh) {
            $this->truncate();
        }

        for ($i = 1; $i <= $count; ++$i) {
            $this->entityManager->persist($this->generator->generate());

            if (0 === $i % self::BATCH_SIZE) {
                $this->entityManager->flush();
                $this->entityManager->clear();

                if (null !== $onBatch) {
                    $onBatch(self::BATCH_SIZE);
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->cacheMembers->delete(MemberManager::COUNT_CACHE_KEY);
    }

    private function truncate(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $connection->executeStatement('TRUNCATE TABLE member');
        $connection->executeStatement('TRUNCATE TABLE representative');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
