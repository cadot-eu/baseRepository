<?php

namespace App\Repository\base;

use Doctrine\ORM\Tools\Pagination\Paginator;

trait SearchRepositoryTrait
{

    /**
     * Recherche paginée avec filtres
     *
     * @param int $page Page actuelle
     * @param int $limit Nombre d'éléments par page
     * @param string|null $search Terme de recherche
     * @param array $fields Champs de recherche
     * @param string $sort Champ de tri
     * @param string $direction Direction du tri
     * @param int|null $categorie Filtre par catégorie
     * @param bool $deleted Inclure les éléments supprimés
     * @param string|null $etat Filtre par état
     * @param array $conditions Conditions supplémentaires
     *
     * @return array Résultats de recherche avec métadonnées
     */
    public function search(
        int $page = 1,
        int $limit = 10,
        string|null $search = '',
        array $fields = ['id'],
        string $sort = 'id',
        string $direction = 'ASC',
        ?int $categorie = null,
        bool $deleted = false,
        ?string $etat = null,
        array $conditions = []
    ): array {
        $qb = $this->createQueryBuilder('e');

        // Appliquer les filtres de recherche
        if (!empty($search)) {
            $orX = $qb->expr()->orX();

            foreach ($fields as $field) {
                // Éviter d'appliquer LIKE sur des champs de date
                if (in_array($field, ['deletedAt', 'createdAt', 'updatedAt'])) {
                    continue;
                }

                // Gérer différemment selon le type de champ
                $metadata = $this->getClassMetadata();
                $fieldType = $metadata->hasField($field) ? $metadata->getTypeOfField($field) : null;

                // Si le champ est un entier, on compare en égalité si c'est un nombre
                if ($fieldType === 'integer' && is_numeric($search)) {
                    $orX->add($qb->expr()->eq("e.$field", $search));
                }
                // Sinon on utilise LIKE pour les champs texte
                elseif ($fieldType === 'string' || $fieldType === 'text') {
                    $orX->add($qb->expr()->like("e.$field", ':search_text'));
                }
            }

            if ($orX->count() > 0) {
                $qb->andWhere($orX);
                if (strpos($qb->getDQL(), ':search_text') !== false) {
                    $qb->setParameter('search_text', '%' . $search . '%');
                }
            }
        }

        // Filtre par catégorie
        if ($categorie !== null) {
            $qb->andWhere('e.categorie = :categorie')
                ->setParameter('categorie', $categorie);
        }

        // Filtre par état
        if ($etat !== null) {
            $qb->andWhere('e.etat = :etat')
                ->setParameter('etat', $etat);
        }

        // Gestion du soft delete avec deletedAt
        if (!$deleted) {
            $qb->andWhere('e.deletedAt IS NULL');
        }

        // Appliquer les conditions supplémentaires
        foreach ($conditions as $condition => $value) {
            $qb->andWhere("e.$condition = :$condition")
                ->setParameter($condition, $value);
        }

        // Tri
        $qb->orderBy("e.$sort", $direction);

        // Pagination
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);
        $results = $paginator->getQuery()->getResult();

        return [
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'limit' => $limit,
            'search' => $search,
            'fields' => $fields,
            'sort' => $sort,
            'direction' => $direction,
            'categorie' => $categorie,
            'deleted' => $deleted,
            'result' => $results,
        ];
    }
}
