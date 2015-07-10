<?php

namespace jeus\QuickstrikeBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;

/**
 *
 * @author Julien S
 */
class Interactions
{

    protected $em;
    protected $tools;
    private $Partie;
    private $CarteEnJeus;

    public function __construct(ObjectManager $em,$tools)
    {
        $this->em = $em;
        $this->tools = $tools;
    }

    public function chargerCarteEnJeu($CarteEnJeus) {
        $this->CarteEnJeus = $CarteEnJeus;
    }

    public function chargerPartie($Partie) {
        $this->Partie = $Partie;
    }

    public function deplacerCarte($joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal='DISCARD',$melanderDestination=false) {
        $CarteParties = $this->em
        ->getRepository('jeusQuickstrikeBundle:CartePartie')
        ->findBy(array(
            'Partie' => $this->Partie, 'numeroJoueur' => $joueurConcerne, 'emplacement' => $emplacementOrigine
            )
            ,array('position'=>'ASC')
        );
        $order = 'DESC';
        $CarteFinals = $this->em
        ->getRepository('jeusQuickstrikeBundle:CartePartie')
        ->findBy(array(
            'Partie' => $this->Partie, 'numeroJoueur' => $joueurConcerne, 'emplacement' => $emplacementFinal
            ),
            array('position'=>$order),
            1  // limit
        );

        $position = 0;
        foreach($CarteFinals as $position=>$CartePartie) {
            if ($CartePartie->getPosition()>=$position)
                $position = $CartePartie->getPosition();
        }
        $position++;

        foreach($CarteParties as $CartePartie) {
            if ($nombre<=0) 
                break;

            // l'opening ne peut êrte que dans la zone verte ou en attente dans la zone opening
            if (($emplacementFinal!='STRIKE_VERT') && ($CartePartie->getCarte()->getNom()=='opening attack')) {
                $CartePartie->setEmplacement('OPENING');
            } else {
                $CartePartie->setEmplacement($emplacementFinal);
                $CartePartie->setPosition($position);
                $position++;            
            }

            $nombre--;
        }
        $this->em->flush();
        if ($melanderDestination) {
            $this->melangerEmplacement($joueurConcerne,$emplacementFinal);
        }
        // s'il n'y a plus de carte dans le deck on récupère toutes les cartes de la discard que l'on met dans le deck
        if (($nombre>0) && ($emplacementOrigine=='DECK')) {
            $this->deplacerCarte($joueurConcerne,99,'DISCARD','DECK',true);
            $this->deplacerCarte($joueurConcerne,5,'DECK','DISCARD');
            $this->deplacerCarte($joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal,$melanderDestination);
        }
    }

    public function melangerEmplacement($joueurConcerne,$emplacement='DECK') {
        $CarteParties = $this->em
                             ->getRepository('jeusQuickstrikeBundle:CartePartie')
                             ->findBy(array(
                                  'Partie' => $this->Partie, 'numeroJoueur' => $joueurConcerne, 'emplacement' => $emplacement
                                  )
                                  ,array('position'=>'ASC')
                                );

        $positions = array();       
        $iteration = 0;
        foreach($CarteParties as $CartePartie) {
            $iteration++;            
            $positions[$iteration] = $iteration;
        }

        for ($i = 1; $i <= 5; $i++) {
            shuffle($positions);
        }
        $iteration = 0;
        foreach($CarteParties as $CartePartie) {
            $CartePartie->setPosition($positions[$iteration]);
            $iteration++;
        }
        // on enregistre car au début de la fonction on va chercher les infos en bdd
        $this->em->flush();
    }

    public function ajoutEffet($joueurConcerne,$idCarte,$typeEffet,$effet) {
        $getProprieteEffet = "getJoueur".$joueurConcerne."Effets";
        $setProprieteEffet = "setJoueur".$joueurConcerne."Effets";
        $effets = $this->Partie->$getProprieteEffet();

        $effets[] = array(
            'idCarte' => $idCarte,
            $typeEffet => $effet,
            );

        $this->Partie->$setProprieteEffet($effets);
        $this->em->persist($this->Partie);
        $this->em->flush();
    }



}
