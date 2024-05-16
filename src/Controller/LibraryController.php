<?php

namespace App\Controller;

use App\Entity\Library;
use App\Form\LibraryType;
use App\Repository\BookRepository;
use App\Repository\LibraryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LibraryController extends AbstractController
{
    public function index(Request $request, LibraryRepository $libraryRepository, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder(null)
            ->add('name', TextType::class, ['label' => 'Nombre'])
            ->add('choice', ChoiceType::class, ['label' => 'Buscar por:',  'choices' => [
                'Nombre' => 'name',
                'Ciudad' => 'city'
            ]])
            ->add('search', SubmitType::class, ['label' => 'Buscar biblioteca'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $choiceValue = $form->getData()['choice']; // Debe almacenar "name" o "city"
            $inputTextValue = $form->getData()['name'];
            return $this->findLibraryBy($inputTextValue, $choiceValue, $form, $entityManager);
        }

        return $this->render('library/index.html.twig', [
            'libraries' => $libraryRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    private function findLibraryBy(string $inputTextValue, string $choiceValue, FormInterface $form, EntityManagerInterface $entityManager): Response
    {
        $libraries = null;

        if ($choiceValue === 'name') {
            $query = $entityManager->createQuery(
                'SELECT l FROM App\Entity\Library l WHERE l.name LIKE :name'
            )->setParameter('name', '%'.$inputTextValue.'%');
            $libraries = $query->getArrayResult();
        }

        if ($choiceValue === 'city') {
            $query = $entityManager->createQuery(
                'SELECT l FROM App\Entity\Library l WHERE l.city LIKE :city'
            )->setParameter('city', '%'.$inputTextValue.'%');
            $libraries = $query->getArrayResult();
        }

        return $this->render('library/index.html.twig', [
            'libraries' => $libraries,
            'form' => $form->createView(),
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

    /*
     * (Sin uso)
     * Ejemplo de cómo buscar una librería por el nombre. Desde el campo "name" en el body de la request.
     */
    private function getLibraryByName(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $query = $entityManager->createQuery(
            'SELECT l FROM App\Entity\Library l WHERE l.name LIKE :name'
        )->setParameter('name', '%'.$data['name'].'%');

        $libraries = $query->getArrayResult();

        //return $this->json($entityManager->getRepository(Library::class)->findAll());

        return $this->render('library/index.html.twig', [
            'libraries' => $libraries,
        ]);
    }

    public function showBooksLibrary(int $id, LibraryRepository $libraryRepository, EntityManagerInterface $entityManager): Response
    {

        $query = $entityManager->createQuery(
            'SELECT b FROM App\Entity\Book b WHERE b.library = :idLibrary'
        )->setParameter('idLibrary', $id);

        $books = $query->getArrayResult();

        //return $this->json($entityManager->getRepository(Library::class)->findAll());

        return $this->render('library/show_library_books.html.twig', [
            'library' => $libraryRepository->find($id),
            'books' => $books,
        ]);
    }


}
