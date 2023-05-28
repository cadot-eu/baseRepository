<?php

namespace App\Repository\base;

trait SearchRepositoryTrait
{
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
            if ($field != 'categories') {
                $ors = [];
                foreach (explode(' ', $search) as $s) {
                    $s = str_replace("'", "''", $s);
                    $ors[] = $qb->expr()->orx("a.$field LIKE '%$s%' ");
                }
                $ORX->add(join(' AND ', $ors));
            }
        }
        $qb->andWhere($ORX);
        if ($categorie != null) {
            $qb->andwhere($qb->expr()->isMemberOf(':categorie', 'a.categories'))->setParameter('categorie', $categorie);
        }
        return $qb->orderBy($sort, strtoupper($direction))
        ->getQuery()
        ->getResult();
    }

    /**
     * Function for searchs
     * exemple: ->search(['roles' => '!SUPERADMIN'], deleted:false)
     *
     * @param array searchs An array of fields ex:
     * @param sort The field to sort the results by. It defaults to 'id' if not provided.
     * @param direction ASC or DESC
     * @param categorie The "categorie" parameter is a string that represents a category
     * @param deleted  If set to false or null, only non-deleted records will be returned.
     * @return ?array an array of results
     */
    public function search(array $searchs = [], ?string $sort = 'id', ?string $direction = 'ASC', ?string $categorie = null, ?bool $deleted = false, ?int $limit = 10): ?array
    {
        $qb = $this->createQueryBuilder('a');
        if ($deleted) {
            $qb->where($qb->expr()->isNotNull('a.deletedAt'));
        } else {
            $qb->where($qb->expr()->isNull('a.deletedAt'));
        }
        $ORX = $qb->expr()->orx();
        //les recherches
         $ors = [];
           //si le field est composé de field séparé par des virgules
        if ($searchs && strlen($searchs[key($searchs)])) {
            $search = $searchs[key($searchs)];
            foreach (explode(',', key($searchs)) as $field) {
                $s = str_replace("'", "''", $search);
                if ($s[0] == '!') {
                    $ors[] = $qb->expr()->orx("a.$field NOT LIKE '%" . substr($s, 1) . "%' ");
                } else {
                    $ors[] = $qb->expr()->orx("a.$field LIKE '%$s%' ");
                }
                $ORX->add(join(' AND ', $ors));
            }
        }
        $qb->andWhere($ORX);
        if ($categorie != null) {
            $qb->andwhere($qb->expr()->isMemberOf(':categorie', 'a.categories'))->setParameter('categorie', $categorie);
        }
        if ($limit) {
            $qb->setMaxResults($limit);
        }
            return $qb->orderBy('a.' . $sort, strtoupper($direction))->getQuery()->getResult();
    }
}
