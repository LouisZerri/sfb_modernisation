<?php

declare(strict_types=1);

namespace App\Service\Search;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;

/**
 * Recherche d'adhérents via Elasticsearch (entreprise, contact, ville, SIRET…).
 */
final readonly class MemberSearchService
{
    private const MAX_RESULTS = 50;

    public function __construct(private Client $client)
    {
    }

    /**
     * @return list<array{id: int, company: string, representativeFullName: string, representativeEmail: string, city: string, postalCode: string, siret: string, returned: bool, returnedAt: ?string}>
     */
    public function search(string $query, int $limit = self::MAX_RESULTS): array
    {
        $query = trim($query);

        try {
            $response = $this->client->search([
                'index' => MemberIndexer::INDEX,
                'body' => [
                    'size' => $limit,
                    'query' => $this->buildQuery($query),
                    'sort' => '' === $query ? [['company.keyword' => 'asc']] : ['_score'],
                ],
            ]);
        } catch (ClientResponseException | ServerResponseException) {
            return [];
        }

        if (!$response instanceof Elasticsearch) {
            return [];
        }

        return $this->hydrate($this->extractHits($response->asArray()));
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return list<mixed>
     */
    private function extractHits(array $body): array
    {
        $hits = $body['hits'] ?? null;
        if (!\is_array($hits)) {
            return [];
        }

        $rows = $hits['hits'] ?? null;

        return \is_array($rows) ? array_values($rows) : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQuery(string $query): array
    {
        if ('' === $query) {
            return ['match_all' => (object) []];
        }

        return [
            'multi_match' => [
                'query' => $query,
                'operator' => 'and',
                'fields' => ['company^3', 'representativeFullName^2', 'city', 'siret', 'postalCode'],
            ],
        ];
    }

    /**
     * @param list<mixed> $hits
     *
     * @return list<array{id: int, company: string, representativeFullName: string, representativeEmail: string, city: string, postalCode: string, siret: string, returned: bool, returnedAt: ?string}>
     */
    private function hydrate(array $hits): array
    {
        $results = [];

        foreach ($hits as $hit) {
            if (!\is_array($hit) || !\is_array($hit['_source'] ?? null)) {
                continue;
            }

            $source = $hit['_source'];
            $returnedAt = $source['returnedAt'] ?? null;

            $results[] = [
                'id' => (int) ($hit['_id'] ?? 0),
                'company' => (string) ($source['company'] ?? ''),
                'representativeFullName' => (string) ($source['representativeFullName'] ?? ''),
                'representativeEmail' => (string) ($source['representativeEmail'] ?? ''),
                'city' => (string) ($source['city'] ?? ''),
                'postalCode' => (string) ($source['postalCode'] ?? ''),
                'siret' => (string) ($source['siret'] ?? ''),
                'returned' => (bool) ($source['returned'] ?? false),
                'returnedAt' => null !== $returnedAt ? (string) $returnedAt : null,
            ];
        }

        return $results;
    }
}
