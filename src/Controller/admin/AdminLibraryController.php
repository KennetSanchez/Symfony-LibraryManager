<?php

namespace App\Controller\admin;

use App\Entity\Library;
use App\Form\LibraryType;
use App\Repository\LibraryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/library')]
class AdminLibraryController extends AbstractController
{
    #[Route('/', name: 'app_library_index', methods: ['GET'])]
    public function index(LibraryRepository $libraryRepository): Response
    {
        return $this->render('admin/admin-library/index.html.twig', [
            'libraries' => $libraryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_library_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $library = new Library();
        $form = $this->createForm(LibraryType::class, $library);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($library);
            $entityManager->flush();

            return $this->redirectToRoute('app_library_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin-library/new.html.twig', [
            'library' => $library,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_library_show', methods: ['GET'])]
    public function show(Library $library): Response
    {
        return $this->render('admin/admin-library/show.html.twig', [
            'library' => $library,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_library_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Library $library, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LibraryType::class, $library);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_library_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin-library/edit.html.twig', [
            'library' => $library,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_library_delete', methods: ['POST'])]
    public function delete(Request $request, Library $library, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$library->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($library);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_library_index', [], Response::HTTP_SEE_OTHER);
    }
}