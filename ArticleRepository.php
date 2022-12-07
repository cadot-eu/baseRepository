<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Query\Expr\Andx;


/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Article $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Article $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    public function SearchCategories(?string $term): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.updatedAt', 'DESC');
        if ($term) {
            $qb->innerJoin('c.categories', 'g')
                ->andWhere('g.id LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }
        return $qb;
    }
    public function SearchTitres(?string $term): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.updatedAt', 'DESC');
        if ($term) {
            $qb->Where('c.titre LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }
        return $qb;
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findPlusVues(int $number = 10): ?array
    {
        return $this->createQueryBuilder('q')
            ->Where('q.deletedAt IS NULL')
            ->andWhere('q.etat = :etat')
            ->setParameter('etat', 'en ligne')
            ->orderBy('q.vues', 'DESC')
            ->setMaxResults($number)
            ->getQuery()
            ->getResult();
    }












                                                                                  public function index($search, $fields, $sort, $direction ,$categorie=null, $deleted = false, $etat = null)
  {
      $sort = is_null($sort) ? 'a.id' : $sort;
      $qb = $this->createQueryBuilder('a');
      if ($deleted) {
          $qb->where($qb->expr()->isNotNull('a.deletedAt'));
      } else {
          $qb->where($qb->expr()->isNull('a.deletedAt'));
      }
      if ($etat != null) {
          $qb->andwhere($qb->expr()->eq('a.etat', ':etat'))
              ->setParameter('etat', $etat);
      }
      $ORX = $qb->expr()->orx();
      foreach ($fields as $field) {
          $ors = [];
          foreach (explode(' ', $search) as $s) {
              $s = str_replace("'", "''", $s);
              $ors[] = $qb->expr()->orx("a.$field LIKE '%$s%' ");
          }
          $ORX->add(join(' AND ', $ors));
      }
      $qb->andWhere($ORX);
      if($categorie !=null)
          $qb->andwhere($qb->expr()->isMemberOf(':categorie', 'a.categories'))->setParameter('categorie', $categorie);

      return $qb->orderBy($sort, strtoupper($direction))
          ->getQuery()
          ->getResult();
  }
//fin index

















































































}
