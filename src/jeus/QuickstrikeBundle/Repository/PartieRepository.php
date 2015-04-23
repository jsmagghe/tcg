<?php

namespace jeus\QuickstrikeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PartieRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PartieRepository extends EntityRepository
{
    /*public function findPartieByJoueur($Joueur) {
        return $this->createQueryBuilder('p')
                ->where('p.joueur1=:Joueur')
                ->orWhere('p.joueur2=:Joueur')
                ->setParameter('Joueur', $Joueur)
                ->getQuery()
                ->getResult();        
    }*/
    
    public function findPartieByJoueur($Joueur) {
        return $this->createQueryBuilder('p')
                ->where('p.joueur1=:Joueur OR p.joueur2=:Joueur')
                ->andWhere('p.pointVictoire<=p.Joueur1Point OR p.pointVictoire<=p.Joueur2Point')
                ->setParameter('Joueur', $Joueur)
                ->getQuery()
                ->getResult();        
    }
    
}
