<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Member\MemberSeeder;
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
    private const DEFAULT_TARGET = 5103;

    public function __construct(private readonly MemberSeeder $seeder)
    {
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

        $io->progressStart($target);
        $this->seeder->seed(
            $target,
            (bool) $input->getOption('fresh'),
            static fn (int $batch) => $io->progressAdvance($batch),
        );
        $io->progressFinish();

        $io->success(\sprintf('%d adhérents générés.', $target));

        return Command::SUCCESS;
    }
}
