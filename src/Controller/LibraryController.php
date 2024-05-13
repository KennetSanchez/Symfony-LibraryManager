<?php

namespace App\Controller;

use App\Entity\Library;
use App\Repository\LibraryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LibraryController extends AbstractController
{
    #[Route('/', name: 'showLibraries')]
    public function index(LibraryRepository $libraryRepository): Response
    {
        return $this->render('library/index.html.twig', [
            'libraries' => $libraryRepository->findAll(),
        ]);
    }

    #[Route('/library/{id}', name: 'showLibraryById', methods: ['GET'])]
    public function showLibraryById(Library $library): Response
    {
        return $this->render('library/show.html.twig', [
            'library' => $library,
        ]);
    }

    #[Route('/library/{name}', name: 'showLibraryByName', methods: ['GET'])]
    public function showLibraryByName(Library $library): Response
    {
        return $this->render('library/show.html.twig', [
            'library' => $library,
        ]);
    }
}
