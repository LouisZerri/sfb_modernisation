<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\WebService\SiretWebService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MEMBRE')]
final class WebServiceController extends AbstractController
{
    public function __construct(private readonly SiretWebService $webService)
    {
    }

    #[Route('/webservice', name: 'app_webservice', methods: ['GET'])]
    public function query(): Response
    {
        return $this->render('webservice/query.html.twig');
    }

    #[Route('/webservice/lookup', name: 'app_webservice_lookup', methods: ['GET'])]
    public function lookup(Request $request): Response
    {
        $xml = $this->webService->buildResponse((string) $request->query->get('siret', ''));

        return new Response($xml, Response::HTTP_OK, ['Content-Type' => 'text/xml; charset=UTF-8']);
    }
}
