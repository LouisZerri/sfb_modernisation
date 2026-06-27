<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEMBRE')]
final class WebServiceController extends AbstractController
{
    #[Route('/webservice', name: 'app_webservice', methods: ['GET'])]
    public function query(): Response
    {
        // La recherche SIRET + réponse XML seront branchées à l'étape 8.
        return $this->render('webservice/query.html.twig');
    }
}
