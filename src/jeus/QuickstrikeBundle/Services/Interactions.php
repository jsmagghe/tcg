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
    protected $effets;
    private $Partie;
    private $CarteEnJeus;
    private $emplacementEnJeu = array('STRIKE_VERT', 'STRIKE_JAUNE', 'STRIKE_ROUGE', 'TEAMWORK_VERTE', 'TEAMWORK_JAUNE', 'TEAMWORK_ROUGE', 'ADVANTAGE');

    public function __construct(ObjectManager $em, $tools, $effets)
    {
        $this->em = $em;
        $this->tools = $tools;
        $this->effets = $effets;
    }

    public function chargerCarteEnJeu($CarteEnJeus) {
        $this->CarteEnJeus = $CarteEnJeus;
    }

    public function chargerPartie($Partie) {
        $this->Partie = $Partie;
    }

    protected function positionSuivante($joueurConcerne, $emplacementVise, $derniere = true)
    {
        $order = ($derniere) ? 'DESC' : 'ASC';
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

        return $position;
    }

    public function deplacerCarteVisee($joueurConcerne, $CartePartie, $emplacementVise, &$position = null) {
        if ($position == null) {
            $position = $this->positionSuivante($joueurConcerne, $emplacementVise);
        }

        $deplacee = false;
        // l'opening ne peut êrte que dans la zone verte ou en attente dans la zone opening
        if (($emplacementVise!='STRIKE_VERT') && ($CartePartie->getCarte()->getNom()=='opening attack')) {
            $CartePartie->setEmplacement('OPENING');
        } else {
            $CartePartie->setEmplacement($emplacementVise);
            $CartePartie->setPosition($position);
            $deplacee = true
            $position++;            

            // si une carte est envoyé dans un emplacement en dehors du jeu depuis une zone en jeu on déclenche l'effet de sortie
            if (!in_array($emplacementVise, $this->emplacementEnJeu) && in_array($emplacementOrigine, $this->emplacementEnJeu)) {
                $this->effets->effetSortie($joueurConcerne,$CartePartie);
            } 
        }

    }

    public function deplacerCarte($joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal='DISCARD',$melanderDestination=false,$nombreDejaDeplace=0) {
        if ($nombre >0) {
            $CarteParties = $this->em
            ->getRepository('jeusQuickstrikeBundle:CartePartie')
            ->findBy(array(
                'Partie' => $this->Partie, 'numeroJoueur' => $joueurConcerne, 'emplacement' => $emplacementOrigine
                )
                ,array('position'=>'ASC')
            );

            $position = $this->positionSuivante($joueurConcerne, $emplacementFinal);

            foreach($CarteParties as $CartePartie) {
                if ($nombre<=0) 
                    break;

                if ($this->deplacerCarteVisee($joueurConcerne, $CartePartie, $position)) {
                    $nombreDejaDeplace++;
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
                $this->deplacerCarte($joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal,$melanderDestination,$nombreDejaDeplace);
            }
        }
        return $nombreDejaDeplace;
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

    public function initialiserEffets($joueurConcerne) {
        $setProprieteEffet = "setJoueur".$joueurConcerne."Effets";
        $this->Partie->$setProprieteEffet(array());
        $this->em->persist($this->Partie);
        $this->em->flush();
    }

    public function initialiserEffetNonExecutes($joueurConcerne) {
        $this->Partie->$setJoueurEffetNonExecutes(array());
        $this->em->persist($this->Partie);
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


    public function ajoutEffetNonExecute($joueurConcerne,$action) {
        $effets = $this->Partie->getJoueurEffetNonExecutes();
        $tab = explode('_', $action);
        $cout = $tab[1];
        $idCarte = $tab[2];
        $typeEffet = $tab[3];
        $effet = $tab[4];


        $effets[$joueurConcerne][$idCarte][] = array(
            'idCarte' => $idCarte,
            'typeEffet' => $typeEffet,
            'effet' => $effet,
            'cout' => $cout,
            );

        $this->Partie->$setJoueurEffetNonExecutes($effets);
        $this->em->persist($this->Partie);
        $this->em->flush();
    }



}
