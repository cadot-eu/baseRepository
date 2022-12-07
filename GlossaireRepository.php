<?php
namespace App\Repository;
use App\Entity\Categorie;

use App\Entity\Glossaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Glossaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Glossaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Glossaire[]    findAll()
 * @method Glossaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlossaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Glossaire::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Glossaire $entity, bool $flush = true): void
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
    public function remove(Glossaire $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
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
              