<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(User $entity, bool $flush = true): void
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
    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findByRoleAndSituation(string $role)
    {
        $role = mb_strtoupper($role);

        return $this->createQueryBuilder('u')
            ->Where('u.situation = :situation')
            ->setParameter('situation', 'actif')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', '"' . $role . '"')
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
