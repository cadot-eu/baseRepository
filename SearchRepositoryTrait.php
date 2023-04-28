<?php

namespace App\Repository\base;

trait SearchRepositoryTrait
{
          /** Function php pour lancer une recherche dans le repository
         * example: $...Repository->search(['couleur' => 'rouge', 'marque' => '!renault'],deletd:true)
         * @param array searchs tableau avec valeur de recherche et! pour not like
         * @param string sort champ de tri par défaut id
         * @param string direction ASC ou DESC
         * @param string categorie nom de la categorie
         * @param bool deleted true pour afficher les supprimés
         * @param array etats tableau avec les états ['en cours','terminé']
         *
         */
    public function search(array $searchs = [], string $sort = 'id', ?string $direction = 'ASC', ?string $categorie = null, ?bool $deleted = false, array $etats = []): ?array
    {
        $qb = $this->createQueryBuilder('a');
        if ($deleted) {
                $qb->where($qb->expr()->isNotNull('a.deletedAt'));
        } else {
            $qb->where($qb->expr()->isNull('a.deletedAt'));
        }
        $ORX = $qb->expr()->orx();
        //les recherches
        foreach ($searchs as $field => $search) {
            $ors = [];
            foreach (explode(' ', $search) as $s) {
                $s = str_replace("'", "''", $s);
                if ($s[0] == '!') {
                    $ors[] = $qb->expr()->orx("a.$field NOT LIKE '%" . substr($s, 1) . "%' ");
                } else {
                    $ors[] = $qb->expr()->orx("a.$field LIKE '%$s%' ");
                }
            }
            $ORX->add(join(' AND ', $ors));
        }
                $qb->andWhere($ORX);
        //les états
            $ors = [];
        foreach ($etats as $etat) {
                $s = str_replace("'", "''", $etat);
            if ($s[0] == '!') {
                $ors[] = $qb->expr()->orx("a.etat NOT LIKE '%" . substr($s, 1) . "%' ");
            } else {
                $ors[] = $qb->expr()->orx("a.etat LIKE '%$s%' ");
            }
            $ORX->add(join(' AND ', $ors));
        }
        $qb->andWhere($ORX);
        if ($categorie != null) {
            $qb->andwhere($qb->expr()->isMemberOf(':categorie', 'a.categories'))->setParameter('categorie', $categorie);
        }
                return $qb->orderBy('a.' . $sort, strtoupper($direction))->getQuery()->getResult();
    }
}
