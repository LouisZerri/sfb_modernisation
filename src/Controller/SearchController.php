<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Search\MemberSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEMBRE')]
final class SearchController extends AbstractController
{
    public function __construct(private readonly MemberSearchService $search)
    {
    }

    #[Route('/filtrer-les-adherents', name: 'app_member_filter', methods: ['GET'])]
    public function filter(): Response
    {
        return $this->render('search/filter.html.twig');
    }

    #[Route('/filtrer-les-adherents/recherche', name: 'app_member_search', methods: ['GET'])]
    public function results(Request $request): Response
    {
        $term = (string) $request->query->get('q', '');

        return $this->render('search/_results.html.twig', [
            'results' => $this->search->search($term),
            'term' => $term,
        ]);
    }
}
