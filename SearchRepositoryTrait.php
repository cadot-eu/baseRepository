<?php

namespace App\Repository\base;

trait SearchRepositoryTrait
{
    /**
     * This is a PHP function that searches for records in a database based on various parameters such
     * as search terms, fields, sorting, categories, and deleted status.
     *
     * @param search The search parameter is a string that is used to search for a specific value in the
     * database. It can contain one or more words separated by spaces.
     * @param fields An array of fields to search in.
     * @param sort The field to sort the results by. If null, the default sorting field is 'a.id'.
     * @param direction The direction parameter is used to specify the sorting direction of the results.
     * It can be either "ASC" for ascending order or "DESC" for descending order.
     * @param categorie The category to filter the results by.
     * @param deleted The  parameter is a boolean value that determines whether to include
     * deleted records in the search results or not. If it is set to true, the search will include
     * deleted records, and if it is set to false, the search will exclude deleted records.
     * @param etat The "etat" parameter is a filter for the "etat" field in the database table. It is
     * used to retrieve only the records that have a specific value in the "etat" field.
     *
     * @return the result of a query that searches for entities based on various parameters such as
     * search terms, fields to search in, sorting criteria, category, and deleted status. The result is
     * ordered by the specified sorting criteria and direction.
     */
    public function index($search, $fields, $sort, $direction, $categorie = null, $deleted = false, $etat = null, $conditions = [])
    {
        $sort = is_null($sort) ? 'a.id' : $sort;
        $qb = $this->createQueryBuilder('a');
        if ($deleted) {
            $qb->where($qb->expr()->isNotNull('a.deletedAt'));
        } else {
            $qb->where($qb->expr()->isNull('a.deletedAt'));
        }
        if ($etat != null) {
            $qb->andwhere($qb->expr()->eq('a.etat', ':etat'))->setParameter('etat', $etat);
        }
        $ORX = $qb->expr()->orx();
        foreach ($fields as $field) {
            $ors = [];
            foreach (explode(' ', $search) as $s) {
                $s = str_replace("'", "''", $s);
                if (strpos($field, '.') !== false) {
                    $qb->leftJoin("a." . explode('.', $field)[0], 'sc');
                    $fieldNameSupercat = 'sc.nom';
                    $ors[] = $qb->expr()->orx("$fieldNameSupercat LIKE '%$s%' ");
                } else {
                    $ors[] = $qb->expr()->orx("a.$field LIKE '%$s%' ");
                }
            }
            $ORX->add(join(' AND ', $ors));
        }
        $qb->andWhere($ORX);
        foreach ($conditions as $key => $value) {
            $qb->andwhere($qb->expr()->eq('a.' . $key, ':val'))->setParameter('val', $value);
        }
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

    /**
     * This function finds entities by categories with optional order and limit parameters.
     *
     * @param string categories a semicolon-separated string of category names to search for
     * @param string order The order in which the results should be sorted. It can be either 'ASC'
     * (ascending) or 'DESC' (descending). The default value is 'DESC'.
     * @param int limit The maximum number of results to be returned by the query. In this case, it is set
     * to 10 by default but can be overridden by passing a different value as an argument.
     *
     * @return ?array an array of results that match the given categories, ordered by creation date in
     * either ascending or descending order, and limited to a specified number of results. The categories
     * are passed as a semicolon-separated string and are used to filter the results using the Doctrine
     * ORM's MEMBER OF operator. If no results are found, the function returns null.
     */
    public function findByCategories(string $categories, string $order = 'DESC', int $limit = 10, ?string $categorieTrie = 'createdAt'): ?array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->where('a.deletedAt IS NULL');
        $qb->orderBy('a.' . $categorieTrie, $order);
        $qb->setMaxResults($limit);
        foreach (explode(';', $categories) as $key => $categorie) {
            $qb->orWhere(':categorie' . $key . ' MEMBER OF a.categories');
            $qb->setParameter('categorie' . $key, $categorie);
        }
        return $qb->getQuery()->getResult();
    }


    /**
     * This PHP function finds entities by category with a limit on the number of results.
     *
     * @param Categorie categorie The "categorie" parameter is an instance of the "Categorie" class, which
     * is used to filter the results of the query by checking if the given category is a member of the
     * "categories" property of each "a" entity in the query.
     * @param int limit The limit parameter is an optional integer value that specifies the maximum number
     * of results to be returned by the query. By default, it is set to 10, but it can be changed to any
     * positive integer value.
     *
     * @return ?array an array of Article objects that belong to the specified Categorie object and have
     * not been soft-deleted (deletedAt is null). The number of results is limited by the
     * parameter, which defaults to 10 if not specified.
     */
    public function findByCategorie(Categorie $categorie, int $limit = 10): ?array
    {
        return $this->createQueryBuilder('a')
            ->where('a.deletedAt IS NULL')
            ->andWhere(':categorie MEMBER OF a.categories')
            ->setParameter('categorie', $categorie)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
