<?php

declare(strict_types=1);

namespace App\Service\Search;

use App\Entity\Member;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;

/**
 * Gère l'index Elasticsearch des adhérents : création du schéma,
 * indexation unitaire (sync CRUD) et réindexation en masse.
 */
final readonly class MemberIndexer
{
    public const INDEX = 'members';

    public function __construct(private Client $client)
    {
    }

    /**
     * Supprime puis recrée l'index avec ses analyseurs (recherche par préfixe).
     */
    public function reset(): void
    {
        try {
            $this->client->indices()->delete(['index' => self::INDEX]);
        } catch (ClientResponseException) {
            // L'index n'existait pas encore : rien à supprimer.
        }

        $this->client->indices()->create([
            'index' => self::INDEX,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'tokenizer' => [
                            'edge_tokenizer' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 2,
                                'max_gram' => 20,
                                'token_chars' => ['letter', 'digit'],
                            ],
                        ],
                        'analyzer' => [
                            'edge_index' => [
                                'tokenizer' => 'edge_tokenizer',
                                'filter' => ['lowercase', 'asciifolding'],
                            ],
                            'edge_search' => [
                                'tokenizer' => 'standard',
                                'filter' => ['lowercase', 'asciifolding'],
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'company' => $this->edgeTextField(),
                        'city' => $this->edgeTextField(),
                        'postalCode' => ['type' => 'keyword'],
                        'siret' => ['type' => 'keyword'],
                        'representativeFullName' => $this->edgeTextField(),
                        'representativeEmail' => ['type' => 'keyword'],
                        'returned' => ['type' => 'boolean'],
                        'returnedAt' => ['type' => 'date', 'format' => 'yyyy-MM-dd'],
                    ],
                ],
            ],
        ]);
    }

    public function index(Member $member): void
    {
        $this->client->index([
            'index' => self::INDEX,
            'id' => (string) $member->getId(),
            'body' => $this->toDocument($member),
        ]);
    }

    public function remove(int $id): void
    {
        try {
            $this->client->delete(['index' => self::INDEX, 'id' => (string) $id]);
        } catch (ClientResponseException) {
            // Déjà absent de l'index : rien à faire.
        }
    }

    /**
     * @param iterable<Member> $members
     */
    public function bulkIndex(iterable $members): void
    {
        $operations = [];
        $count = 0;

        foreach ($members as $member) {
            $operations[] = ['index' => ['_index' => self::INDEX, '_id' => (string) $member->getId()]];
            $operations[] = $this->toDocument($member);

            if (0 === ++$count % 1000) {
                $this->client->bulk(['body' => $operations]);
                $operations = [];
            }
        }

        if ([] !== $operations) {
            $this->client->bulk(['body' => $operations]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function toDocument(Member $member): array
    {
        $representative = $member->getRepresentative();

        return [
            'company' => $member->getCompany(),
            'city' => $member->getCity(),
            'postalCode' => $member->getPostalCode(),
            'siret' => $member->getSiret(),
            'representativeFullName' => $representative->getFullName(),
            'representativeEmail' => $representative->getEmail(),
            'returned' => $member->isReturned(),
            'returnedAt' => $member->getReturnedAt()?->format('Y-m-d'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function edgeTextField(): array
    {
        return [
            'type' => 'text',
            'analyzer' => 'edge_index',
            'search_analyzer' => 'edge_search',
            'fields' => [
                'keyword' => ['type' => 'keyword', 'ignore_above' => 256],
            ],
        ];
    }
}
