<?php

namespace App\Repository\base;

use App\Entity\Chat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chat>
 *
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Chat $entity, bool $flush = false): void
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
    public function remove(Chat $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    //    /**
    //     * @return Chat[] Returns an array of Chat objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Chat
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }










    public function index($search, $fields, $sort, $direction, $categorie = null, $deleted = false, $etat = null)
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
        if ($categorie != null)
            $qb->andwhere($qb->expr()->isMemberOf(':categorie', 'a.categories'))->setParameter('categorie', $categorie);

        return $qb->orderBy($sort, strtoupper($direction))
            ->getQuery()
            ->getResult();
    }
    //fin index





























































































}
