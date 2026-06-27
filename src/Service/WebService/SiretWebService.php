<?php

declare(strict_types=1);

namespace App\Service\WebService;

use App\Repository\MemberRepository;

/**
 * Reproduit le web service legacy : à partir d'un SIRET, renvoie en XML
 * les informations de l'adhérent (entreprise + représentant) s'il existe.
 */
final readonly class SiretWebService
{
    public function __construct(private MemberRepository $members)
    {
    }

    public function buildResponse(string $rawSiret): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $root = $document->createElement('WebService');
        $document->appendChild($root);

        $siret = preg_replace('/\s+/', '', $rawSiret) ?? '';

        if ('' === $siret) {
            $this->appendResponse($document, $root, 'Aucune donnée à afficher');

            return (string) $document->saveXML();
        }

        $member = $this->members->findOneBySiret($siret);

        if (null === $member) {
            $this->appendResponse($document, $root, 'False');

            return (string) $document->saveXML();
        }

        $representative = $member->getRepresentative();
        $entity = $document->createElement('Entity');
        $root->appendChild($entity);

        $this->appendText($document, $entity, 'Entreprise', (string) $member->getCompany());
        $this->appendText($document, $entity, 'RepresentantNom', (string) $representative->getLastName());
        $this->appendText($document, $entity, 'RepresentantPrenom', (string) $representative->getFirstName());
        $this->appendResponse($document, $entity, 'True');

        return (string) $document->saveXML();
    }

    private function appendText(\DOMDocument $document, \DOMElement $parent, string $tag, string $value): void
    {
        $parent->appendChild($document->createElement($tag))->appendChild($document->createTextNode($value));
    }

    private function appendResponse(\DOMDocument $document, \DOMElement $parent, string $value): void
    {
        $this->appendText($document, $parent, 'Response', $value);
    }
}
