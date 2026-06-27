<?php

declare(strict_types=1);

namespace App\Service\Search;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

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

        return $this->hydrate($response['hits']['hits'] ?? []);
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
     * @param list<array<string, mixed>> $hits
     *
     * @return list<array{id: int, company: string, representativeFullName: string, representativeEmail: string, city: string, postalCode: string, siret: string, returned: bool, returnedAt: ?string}>
     */
    private function hydrate(array $hits): array
    {
        $results = [];

        foreach ($hits as $hit) {
            $source = $hit['_source'];
            $results[] = [
                'id' => (int) $hit['_id'],
                'company' => $source['company'] ?? '',
                'representativeFullName' => $source['representativeFullName'] ?? '',
                'representativeEmail' => $source['representativeEmail'] ?? '',
                'city' => $source['city'] ?? '',
                'postalCode' => $source['postalCode'] ?? '',
                'siret' => $source['siret'] ?? '',
                'returned' => (bool) ($source['returned'] ?? false),
                'returnedAt' => $source['returnedAt'] ?? null,
            ];
        }

        return $results;
    }
}
