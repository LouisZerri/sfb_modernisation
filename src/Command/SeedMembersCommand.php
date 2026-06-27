<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Member\MemberGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:members:seed',
    description: 'Génère des adhérents fictifs mais réalistes (par lots).',
)]
final class SeedMembersCommand extends Command
{
    private const BATCH_SIZE = 500;
    private const DEFAULT_TARGET = 5103;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MemberGenerator $generator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'Nombre total d\'adhérents visé', self::DEFAULT_TARGET)
            ->addOption('fresh', null, InputOption::VALUE_NONE, 'Vide les tables adhérents avant génération');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $target = max(0, (int) $input->getOption('count'));

        if ($input->getOption('fresh')) {
            $this->truncate();
            $io->note('Tables adhérents vidées.');
        }

        $io->progressStart($target);

        for ($i = 1; $i <= $target; ++$i) {
            $this->entityManager->persist($this->generator->generate());

            if (0 === $i % self::BATCH_SIZE) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $io->progressAdvance(self::BATCH_SIZE);
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
        $io->progressFinish();

        $io->success(\sprintf('%d adhérents générés.', $target));

        return Command::SUCCESS;
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
