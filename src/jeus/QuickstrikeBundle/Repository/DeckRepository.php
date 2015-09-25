<?php

namespace jeus\QuickstrikeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DeckRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DeckRepository extends EntityRepository
{

    public function findDeckByJoueurAndName($Joueur, $nom) {
        return $this->createQueryBuilder('d')
                ->where('d.joueur = :Joueur')
                ->andWhere('d.nom = :nom')
                ->setParameters(array('Joueur' => $Joueur, 'nom' => $nom))
                ->getQuery()
                ->getResult();        
    }
    

}
