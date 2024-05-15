<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use PhpParser\Builder\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/books')]
class BookController extends AbstractController
{
    private function decodeRequest(Request $request){
        $content = $request->getContent();
        return json_decode($content);
    }

    #[Route('/', name: 'app_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_book_find_with_id', methods: ['GET'])]
    public function findById(BookRepository $bookRepository, string $id): Response
    {
        return $this->render('book/show.html.twig', [
            'searchType' => 'Libro con id: '.$id,
            'books' => $bookRepository->findBy([
                'id' => $id
            ])
        ]);
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_book_edit', methods: ['GET', 'PUT'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookType::class, $book, [
            'method' => 'PUT',
        ]);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/library', name: 'app_book_find_with_library', methods: ['GET'])]
    public function findByLibrary(BookRepository $bookRepository, Request $request) : Response {
        $decoded = $this->decodeRequest($request);

        try{
            $libraryName = $decoded->libraryName;
            $books = $bookRepository->findByLibrary($libraryName);
            return $this->render('book/show.html.twig',[
                'books' => $books,
                'searchType' => sprintf('Libros de la biblioteca %s', $libraryName)
            ]);
        }catch (ErrorException $exception){
            throw new HttpException(statusCode: Response::HTTP_BAD_REQUEST, message: "Parece haber un problema con los parámetros de la petición. ".$exception->getMessage());
        }
    }


    #[Route('/title', name: 'app_book_find_with_title', methods: ['GET'], format: 'json')]
    public function findByTitle(BookRepository $bookRepository, Request $request): Response
    {
        
        $decoded = $this->decodeRequest($request);

        try{
            $title = $decoded->title;
            $criteria = ['title'=>$title];
            $books = $bookRepository->findBy($criteria);

            return $this->render('book/show.html.twig', [
                'books' => $books,
                'searchType' => sprintf('Libros con título %s', $title),
            ]);

        }catch(ErrorException $exception){
            throw new HttpException(statusCode: Response::HTTP_BAD_REQUEST, message: "Parece haber un error con los parámetros de la petición. ".$exception->getMessage());
        }
    }

    #[Route('/titleAndAvailability', name: 'app_book_find_with_name_and_copies', methods: ['GET'])]
    public function findByTitleAndAvailability(BookRepository $bookRepository, Request $request): Response
    {
        $decoded = $this->decodeRequest($request);


        try{
            $title = $decoded->title;
            $query = $bookRepository->findByTitleAndAvailabilityJoinedToLibrary($title);

            return $this->render('book/show_with_library.html.twig', [
                'queryResult' => $query,
                'searchTitle' => sprintf('Libros disponibles con título %s', $title),
            ]);

        }catch(ErrorException $exception){
            throw new HttpException(statusCode: Response::HTTP_BAD_REQUEST, message: "Parece haber un error con los parámetros de la petición. ".$exception->getMessage());
        }
    }

    #[Route('/titleAndLibrary', name: 'app_book_find_with_title_and_library', methods: ['GET'])]
    public function findByTitleAndLibrary(BookRepository $bookRepository, Request $request) : Response {
        $decoded = $this->decodeRequest($request);

        try {
            $title = $decoded->title;
            $libraryName = $decoded->libraryName;
            $results = $bookRepository->findByTitleAndLibraryJoinedToLibrary(title: $title, libraryName: $libraryName);
        }catch (ErrorException $exception){
            throw new HttpException(
                statusCode: Response::HTTP_BAD_REQUEST,
                message: "Parece haber un problema con los parámetros de la petición. ".$exception->getMessage()
            );
        }

        return $this->render('book/show_with_library.html.twig', [
            'queryResult' => $results,
            'searchTitle' => sprintf('Libros con título %s y biblioteca %s', $title, $libraryName),
        ]);
    }

    #[Route('/publisherAndLibrary', name: 'app_book_find_with_publisher_and_library', methods: ['GET'])]
    public function findByPublisherAndLibrary(BookRepository $bookRepository, Request $request) : Response {
        $decoded = $this->decodeRequest($request);

        try {
            $publisher = $decoded->publisher;
            $libraryName = $decoded->libraryName;
            $results = $bookRepository->findByPublisherAndLibraryJoinedToLibrary(publisher: $publisher, libraryName: $libraryName);
        }catch (ErrorException $exception){
            throw new HttpException(
                statusCode: Response::HTTP_BAD_REQUEST,
                message: "Parece haber un problema con los parámetros de la petición. ".$exception->getMessage()
            );
        }

        return $this->render('book/show_with_library.html.twig', [
            'queryResult' => $results,
            'searchTitle' => sprintf('Libros con editorial %s y biblioteca %s', $publisher, $libraryName),
        ]);
    }

    #[Route('/authorAndLibrary', name: 'app_book_find_with_author_and_library', methods: ['GET'])]
    public function findByAuthorAndLibrary(BookRepository $bookRepository, Request $request) : Response {
        $decoded = $this->decodeRequest($request);

        try {
            $author = $decoded->author;
            $libraryName = $decoded->libraryName;
            $results = $bookRepository->findByAuthorAndLibraryJoinedToLibrary(author: $author, libraryName: $libraryName);
        }catch (ErrorException $exception){
            throw new HttpException(
                statusCode: Response::HTTP_BAD_REQUEST,
                message: "Parece haber un problema con los parámetros de la petición. ".$exception->getMessage()
            );
        }

        return $this->render('book/show_with_library.html.twig', [
            'queryResult' => $results,
            'searchTitle' => sprintf('Libros del autor %s y biblioteca %s', $author, $libraryName),
        ]);
    }




}
