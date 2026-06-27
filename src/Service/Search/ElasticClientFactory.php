<?php

declare(strict_types=1);

namespace App\Service\Search;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

/**
 * Construit le client Elasticsearch à partir de l'URL configurée,
 * pour pouvoir l'injecter comme un service standard.
 */
final readonly class ElasticClientFactory
{
    public function __construct(private string $url)
    {
    }

    public function create(): Client
    {
        return ClientBuilder::create()
            ->setHosts([$this->url])
            ->build();
    }
}
