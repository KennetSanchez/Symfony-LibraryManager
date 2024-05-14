<?php

namespace App\Controller;

use App\Entity\Library;
use App\Form\LibraryType;
use App\Repository\LibraryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LibraryController extends AbstractController
{
    public function index(LibraryRepository $libraryRepository): Response
    {
        return $this->render('library/index.html.twig', [
            'libraries' => $libraryRepository->findAll(),
        ]);
    }

    public function showLibraryById(Library $library): Response
    {
        return $this->render('library/show.html.twig', [
            'library' => $library,
        ]);
    }

    public function indexAdminLibraries(LibraryRepository $libraryRepository): Response
    {
        return $this->render('admin/admin-library/index.html.twig', [
            'libraries' => $libraryRepository->findAll(),
        ]);
    }

    public function showAdminLibraryById(Library $library): Response
    {
        return $this->render('admin/admin-library/show.html.twig', [
            'library' => $library,
        ]);
    }

    public function editLibrary(Request $request, Library $library, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LibraryType::class, $library);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('indexAdminLibraries', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin-library/edit.html.twig', [
            'library' => $library,
            'form' => $form,
        ]);
    }


    public function newLibrary(Request $request, EntityManagerInterface $entityManager): Response
    {
        $library = new Library();
        $form = $this->createForm(LibraryType::class, $library);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($library);
            $entityManager->flush();

            return $this->redirectToRoute('indexAdminLibraries', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin-library/new.html.twig', [
            'library' => $library,
            'form' => $form,
        ]);
    }

    public function deleteLibrary(Request $request, Library $library, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$library->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($library);
            $entityManager->flush();
        }

        return $this->redirectToRoute('indexAdminLibraries', [], Response::HTTP_SEE_OTHER);
    }

}
