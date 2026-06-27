<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\MemberRepository;
use App\Repository\UserRepository;
use App\Service\Member\MemberSeeder;
use App\Service\Search\MemberIndexer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:demo:reset',
    description: 'Réinitialise la base de démonstration (adhérents fictifs, compte démo, index ES).',
)]
final class DemoResetCommand extends Command
{
    private const MEMBER_COUNT = 5103;
    private const DEMO_EMAIL = 'demo@sfbois.com';
    private const DEMO_PASSWORD = 'demo';

    public function __construct(
        private readonly MemberSeeder $seeder,
        private readonly MemberIndexer $indexer,
        private readonly MemberRepository $members,
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Régénération des adhérents');
        $this->seeder->seed(self::MEMBER_COUNT, fresh: true);

        $io->section('Compte de démonstration');
        $this->ensureDemoUser();

        $io->section('Réindexation Elasticsearch');
        $this->indexer->reset();
        $this->indexer->bulkIndex($this->members->findAllForIndexing());

        $io->success('Base de démonstration réinitialisée.');

        return Command::SUCCESS;
    }

    private function ensureDemoUser(): void
    {
        if (null !== $this->users->findOneBy(['email' => self::DEMO_EMAIL])) {
            return;
        }

        $user = new User();
        $user->setEmail(self::DEMO_EMAIL);
        $user->setRoles(['ROLE_MEMBRE']);
        $user->setPassword($this->passwordHasher->hashPassword($user, self::DEMO_PASSWORD));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
