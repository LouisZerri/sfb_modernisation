<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEMBRE')]
final class SearchController extends AbstractController
{
    #[Route('/filtrer-les-adherents', name: 'app_member_filter', methods: ['GET'])]
    public function filter(): Response
    {
        // La recherche Elasticsearch sera branchée à l'étape 7.
        return $this->render('search/filter.html.twig');
    }
}
