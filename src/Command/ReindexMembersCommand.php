<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\MemberRepository;
use App\Service\Search\MemberIndexer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:search:reindex',
    description: 'Recrée l\'index Elasticsearch et y indexe tous les adhérents.',
)]
final class ReindexMembersCommand extends Command
{
    public function __construct(
        private readonly MemberRepository $members,
        private readonly MemberIndexer $indexer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Recréation de l\'index');
        $this->indexer->reset();

        $members = $this->members->findAllForIndexing();
        $io->section(\sprintf('Indexation de %d adhérents', \count($members)));

        $this->indexer->bulkIndex($members);

        $io->success('Index Elasticsearch à jour.');

        return Command::SUCCESS;
    }
}
