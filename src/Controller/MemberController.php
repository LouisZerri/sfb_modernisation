<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\MemberDto;
use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\MemberRepository;
use App\Service\Member\MemberManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class MemberController extends AbstractController
{
    private const PER_PAGE = 15;

    public function __construct(
        private readonly MemberRepository $members,
        private readonly MemberManager $memberManager,
        private readonly CacheInterface $cacheMembers,
    ) {
    }

    #[Route('/', name: 'app_root')]
    public function root(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/accueil', name: 'app_home', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Reproduit le droit « membre » du legacy : seuls les membres voient la base.
        if (!$this->isGranted('ROLE_MEMBRE')) {
            return $this->render('member/index.html.twig', ['canView' => false]);
        }

        $page = $request->query->getInt('page', 1);
        $total = $this->cachedTotal();

        return $this->render('member/index.html.twig', [
            'canView' => true,
            'members' => $this->members->paginate($page, self::PER_PAGE),
            'currentPage' => max(1, $page),
            'pageCount' => max(1, (int) ceil($total / self::PER_PAGE)),
            'total' => $total,
        ]);
    }

    private function cachedTotal(): int
    {
        return $this->cacheMembers->get(MemberManager::COUNT_CACHE_KEY, function (ItemInterface $item): int {
            $item->expiresAfter(3600);

            return $this->members->countAll();
        });
    }

    #[Route('/register', name: 'app_member_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createForm(MemberType::class, new MemberDto());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->memberManager->create($form->getData());
            $this->addFlash('success', 'L\'adhérent a été ajouté avec succès.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('member/new.html.twig', ['form' => $form]);
    }

    #[Route('/adherents/{id}/modifier', name: 'app_member_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MEMBRE')]
    public function edit(Request $request, Member $member): Response
    {
        $form = $this->createForm(MemberType::class, MemberDto::fromMember($member));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->memberManager->update($member, $form->getData());
            $this->addFlash('success', 'L\'adhérent a été modifié avec succès.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('member/edit.html.twig', [
            'form' => $form,
            'member' => $member,
        ]);
    }

    #[Route('/adherents/{id}/supprimer', name: 'app_member_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MEMBRE')]
    public function delete(Request $request, Member $member): Response
    {
        if ($this->isCsrfTokenValid('delete'.$member->getId(), (string) $request->request->get('_token'))) {
            $this->memberManager->delete($member);
            $this->addFlash('success', 'L\'adhérent a été supprimé.');
        }

        return $this->redirectToRoute('app_home');
    }
}
