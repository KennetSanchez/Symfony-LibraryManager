<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Exception;
use http\Exception\InvalidArgumentException;
use http\Exception\UnexpectedValueException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{
    private function decodeRequest(Request $request)
    {
        $content = $request->getContent();
        return json_decode($content);
    }

    public function booksIndex(Request $request, BookRepository $bookRepository): Response
    {

        $form = $this->createFormBuilder(null)
            ->add('param1', TextType::class, ['label' => 'Parámetro 1', 'required' => true])
            ->add('param2', TextType::class, ['label' => 'Parámetro 2 (separado por "y")', 'required' => false])
            ->add('choice', ChoiceType::class, ['label'=> 'Buscar por: ', 'choices' => [
                'Nombre biblioteca' => 'library',
                'Título libro' => 'title',
                'Título libro con disponibilidad' => 'titleAndAvailability',
                'Biblioteca y editorial' => 'publisherAndLibrary',
                'Biblioteca y autor' => 'libraryAndAuthor',
                'Biblioteca y título libro' => 'libraryAndTitle',
            ]])
            ->add('search', SubmitType::class, ['label' => 'Buscar libro'])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $choiceValue = $form->getData()['choice'];
            $param1 = $form->getData()['param1'];
            $param2 = $form->getData()['param2'];

            switch ($choiceValue) {
                case 'library':
                    return $this->render('book/show.html.twig', [
                        'searchType' => 'Libros de: ' . $param1,
                        'books' => $bookRepository->findByLibrary($param1),
                    ]);
                    break;

                case 'title':
                    return $this->render('book/show_with_library.html.twig', [
                        'searchTitle' => 'Libros con título: ' . $param1,
                        'books' => $bookRepository->findBy(['title' => $param1])
                    ]);
                    break;


                case 'titleAndAvailability':
                    return $this->render('book/show_with_library.html.twig', [
                        'searchTitle' => sprintf('Libros con título %s y disponibilidad', $param1),
                        'books' => $bookRepository->findByTitleAndAvailabilityJoinedToLibrary($param1)
                    ]);

                    break;

                case 'publisherAndLibrary':
                    if ($param2 == null || $param2 == '') {
                        $form->addError(new FormError('Campo de la biblioteca sin rellenar'));
                    } else {
                        return $this->render('book/show_with_library.html.twig', [
                            'searchTitle' => sprintf('Libros de %s en la biblioteca: %s', $param2, $param1),
                            'books' => $bookRepository->findByPublisherAndLibraryJoinedToLibrary($param2, $param1)
                        ]);
                    }
                    break;

                case 'libraryAndAuthor':
                    if ($param2 == null || $param2 == '') {
                        $form->addError(new FormError('Campo del autor sin rellenar'));
                    } else {
                        return $this->render('book/show_with_library.html.twig', [
                            'searchTitle' => sprintf('Libros de %s en la biblioteca: %s', $param2, $param1),
                            'books' => $bookRepository->findByAuthorAndLibraryJoinedToLibrary($param2, $param1)
                        ]);
                    }
                    break;

                case 'libraryAndTitle':
                    if ($param2 == null || $param2 == '') {
                        $form->addError(new FormError('Campo del título sin rellenar'));
                    } else {
                        return $this->render('book/show_with_library.html.twig', [
                            'searchTitle' => sprintf('Libro %s en la biblioteca: %s', $param2, $param1),
                            'books' => $bookRepository->findByTitleAndLibraryJoinedToLibrary($param2, $param1)
                        ]);
                    }
                    break;

                default:
                    throw new UnexpectedValueException('El filtro elegido no es válido');
                    break;
            }
        }

        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
            'form' => $form
        ]);
    }

    public function newBook(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('booksIndex', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    public function findByLibrary(BookRepository $bookRepository, Request $request): Response
    {
        $decoded = $this->decodeRequest($request);

        try {
            $libraryName = $decoded->libraryName;
            $books = $bookRepository->findByLibrary($libraryName);
            return $this->render('book/show.html.twig', [
                'books' => $books,
                'searchType' => sprintf('Libros de la biblioteca %s', $libraryName)
            ]);
        } catch (ErrorException $exception) {
            throw new HttpException(statusCode: Response::HTTP_BAD_REQUEST, message: "Parece haber un problema con los parámetros de la petición. " . $exception->getMessage());
        }
    }

    public function findByTitle(BookRepository $bookRepository, Request $request, string $title = ''): Response
    {

        try {
            if(!$title){
                $decoded = $this->decodeRequest($request);
                $title = $decoded->title;
            }

            $criteria = ['title' => $title];
            $books = $bookRepository->findBy($criteria);

            return $this->render('book/show_with_library.html.twig', [
                'books' => $books,
                'searchTitle' => sprintf('Libros con título: %s', $title),
            ]);

        } catch (ErrorException $exception) {
            throw new HttpException(statusCode: Response::HTTP_BAD_REQUEST, message: "Parece haber un error con los parámetros de la petición. " . $exception->getMessage());
        }
    }

    public function findByTitleAndAvailability(BookRepository $bookRepository, Request $request): Response
    {
        $decoded = $this->decodeRequest($request);


        try {
            $title = $decoded->title;
            $query = $bookRepository->findByTitleAndAvailabilityJoinedToLibrary($title);

            return $this->render('book/show_with_library.html.twig', [
                'queryResult' => $query,
                'searchTitle' => sprintf('Libros disponibles con título %s', $title),
            ]);

        } catch (ErrorException $exception) {
            throw new HttpException(statusCode: Response::HTTP_BAD_REQUEST, message: "Parece haber un error con los parámetros de la petición. " . $exception->getMessage());
        }
    }

    public function findByTitleAndLibrary(BookRepository $bookRepository, Request $request): Response
    {
        $decoded = $this->decodeRequest($request);

        try {
            $title = $decoded->title;
            $libraryName = $decoded->libraryName;
            $results = $bookRepository->findByTitleAndLibraryJoinedToLibrary(title: $title, libraryName: $libraryName);
        } catch (ErrorException $exception) {
            throw new HttpException(
                statusCode: Response::HTTP_BAD_REQUEST,
                message: "Parece haber un problema con los parámetros de la petición. " . $exception->getMessage()
            );
        }

        return $this->render('book/show_with_library.html.twig', [
            'queryResult' => $results,
            'searchTitle' => sprintf('Libros con título %s y biblioteca %s', $title, $libraryName),
        ]);
    }

    public function findByPublisherAndLibrary(BookRepository $bookRepository, Request $request): Response
    {
        $decoded = $this->decodeRequest($request);

        try {
            $publisher = $decoded->publisher;
            $libraryName = $decoded->libraryName;
            $results = $bookRepository->findByPublisherAndLibraryJoinedToLibrary(publisher: $publisher, libraryName: $libraryName);
        } catch (ErrorException $exception) {
            throw new HttpException(
                statusCode: Response::HTTP_BAD_REQUEST,
                message: "Parece haber un problema con los parámetros de la petición. " . $exception->getMessage()
            );
        }

        return $this->render('book/show_with_library.html.twig', [
            'queryResult' => $results,
            'searchTitle' => sprintf('Libros con editorial %s y biblioteca %s', $publisher, $libraryName),
        ]);
    }

    public function findByAuthorAndLibrary(BookRepository $bookRepository, Request $request): Response
    {
        $decoded = $this->decodeRequest($request);

        try {
            $author = $decoded->author;
            $libraryName = $decoded->libraryName;
            $results = $bookRepository->findByAuthorAndLibraryJoinedToLibrary(author: $author, libraryName: $libraryName);
        } catch (ErrorException $exception) {
            throw new HttpException(
                statusCode: Response::HTTP_BAD_REQUEST,
                message: "Parece haber un problema con los parámetros de la petición. " . $exception->getMessage()
            );
        }

        return $this->render('book/show_with_library.html.twig', [
            'queryResult' => $results,
            'searchTitle' => sprintf('Libros del autor %s y biblioteca %s', $author, $libraryName),
        ]);
    }

    public function findById(BookRepository $bookRepository, string $id): Response
    {
        return $this->render('book/show.html.twig', [
            'searchType' => 'Libro con id: ' . $id,
            'books' => $bookRepository->findBy([
                'id' => $id
            ])
        ]);
    }

    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $book->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('booksIndex', [], Response::HTTP_SEE_OTHER);
    }

    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookType::class, $book, [
            'method' => 'PUT',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('booksIndex', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }
}
