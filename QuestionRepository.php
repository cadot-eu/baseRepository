<?php

namespace App\Repository;
use App\Entity\Categorie;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Question>
 *
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Question $entity, bool $flush = false): void
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
    public function remove(Question $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    //    /**
    //     * @return Question[] Returns an array of Question objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findQuestionsNonEffaceEnligneAvecReponses(int $number = 10): ?array
    {
        return $this->createQueryBuilder('q')
            ->Where('q.deletedAt IS NULL')
            ->andWhere('q.etat = :etat')
            ->setParameter('etat', 'en ligne')
            ->andWhere('q.reponse IS NOT NULL')
            ->orderBy('q.id', 'ASC')
            ->setMaxResults($number)
            ->getQuery()
            ->getResult();
    }
    public function findQuestionsEnligne(): int
    {
        return count($this->createQueryBuilder('q')
            ->Where('q.deletedAt IS NULL')
            ->andWhere('q.etat = :etat')
            ->setParameter('etat', 'en ligne')
            ->getQuery()
            ->getResult());
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
