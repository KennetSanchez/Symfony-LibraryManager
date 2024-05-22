<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

//        TODO: Update the querys so they only takes the params that we are going to use


    public function findByTitleAndAvailabilityJoinedToLibrary(string $title): array
    {

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQueryBuilder()
            ->select('Books', 'Library')
            ->from('App\Entity\Book', 'Books')
            ->innerJoin('Books.library', 'Library')
            ->where('Books.title = :title')
            ->andWhere('Books.copies >  0')
            ->setParameter('title', $title)
            ->getQuery();

        return $query->getResult();
    }

    public function findByPublisherAndLibraryJoinedToLibrary(string $publisher, string $libraryName): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQueryBuilder()
            ->select('Books', 'Library')
            ->from('App\Entity\Book', 'Books')
            ->innerJoin('Books.library', 'Library')
            ->where('Books.publisher = :publisher')
            ->andWhere('Library.name = :libraryName')
            ->setParameter('publisher', $publisher)
            ->setParameter('libraryName', $libraryName)
            ->getQuery();

        return $query->getResult();
    }

    public function findByAuthorAndLibraryJoinedToLibrary(string $author, string $libraryName): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQueryBuilder()
            ->select('Books', 'Library')
            ->from('App\Entity\Book', 'Books')
            ->innerJoin('Books.library', 'Library')
            ->where('Books.author = :author')
            ->andWhere('Library.name = :libraryName')
            ->setParameter('author', $author)
            ->setParameter('libraryName', $libraryName)
            ->getQuery();

        return $query->getResult();
    }

    public function findByTitleAndLibraryJoinedToLibrary(string $title, string $libraryName): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQueryBuilder()
            ->select('Books', 'Library')
            ->from('App\Entity\Book', 'Books')
            ->innerJoin('Books.library', 'Library')
            ->where('Books.title = :title')
            ->andWhere('Library.name = :libraryName')
            ->setParameter('title', $title)
            ->setParameter('libraryName', $libraryName)
            ->getQuery();

        return $query->getResult();
    }

    public function findByLibrary(string $libraryName) : array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQueryBuilder()
            ->select('Books')
            ->from('App\Entity\Book', 'Books')
            ->innerJoin('Books.library', 'Library')
            ->where('Library.name = :libraryName')
            ->setParameter('libraryName', $libraryName)
            ->getQuery();

        return $query->getResult();
    }
}
