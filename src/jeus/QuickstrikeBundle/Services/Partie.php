<?php

namespace jeus\QuickstrikeBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;

/**
 *
 * @author Julien S
 */
class FormattageHM
{

    protected $em;
    protected $container;

    protected $Partie;
    protected $Joueur;
    protected $CarteEnJeus;
    protected $numeroAttaquant;
    protected $numeroDefenseur;
    protected $numeroJoueur;
    protected $numeroAdversaire;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function chargement($Partie) {
        $this->Partie = $Partie;
        $this->Joueur = $Joueur;
        $this->chargerCarteEnJeu();
        $this->numeroDefenseur = $this->numeroDefenseur();
        $this->numeroAttaquant = $this->numeroAttaquant();
        $this->numeroJoueur = $this->numeroJoueur();
        $this->numeroAdversaire = $this->numeroJoueur(true);
    }

    private function numeroAttaquant() {
        $numeroAttaquant = ($this->Partie->getJoueur1Etape()=='defense') ? 2 : 1;
        return $numeroAttaquant;
    }

    private function numeroDefenseur() {
        $numeroDefenseur = ($this->Partie->getJoueur1Etape()=='defense') ? 1 : 2;
        return $numeroDefenseur;
    }

    private function numeroJoueur($adversaire = false) {
        $numero = 1;
        if ($this->Partie->getJoueur1()->getId()==$this->Partie->getJoueur2()->getId()) {
            $numero = ($this->Partie->getJoueurBas() != null) ? $this->Partie->getJoueurBas() : 1;
        } else {
            $numero = ($this->Partie->getJoueur2()->getId()==$this->Joueur->getId()) ? 2 : 1;
        }
        if ($adversaire)
            $numero = ($numero==1) ? 2 : 1;

        return $numero;
    }

    private function chargerCarteEnJeu() {
        if ($this->CarteEnJeus==null) {
            $CarteEnJeus = $this->em->getRepository('jeusQuickstrikeBundle:CartePartie')
                                    ->findBy(array('Partie' => $this->Partie));

            foreach($CarteEnJeus as $CartePartie) {
                $emplacement = $CartePartie->getEmplacement();
                $numeroJoueur = $CartePartie->getNumeroJoueur();
                if (($emplacement!='AVANTAGE') && ($emplacement!='DECK') && ($emplacement!='DISCARD') && (strpos($emplacement,'ENERGIE_') === false)) {
                    $this->CarteEnJeus[$numeroJoueur][$emplacement] = $CartePartie;
                } else {
                    $this->CarteEnJeus[$numeroJoueur][$emplacement][] = $CartePartie;
                }
                if (($emplacement!='DECK') && ($emplacement!='DISCARD') && (strpos($emplacement,'ENERGIE_') === false)) {
                    $this->CarteEnJeus[$numeroJoueur]['ACTIVE'][] = $CartePartie;
                }
            }

        }
    }

    private function attaqueEnCours($Partie) {
        $attaque = 0;
        if (($Partie->getJoueur1Etape()=='defense') || ($Partie->getJoueur2Etape()=='defense')) {
            if ($Partie->getJoueurZoneEnCours($this->numeroAttaquant)!='0') {
                if (isset($this->CarteEnJeus[$this->numeroAttaquant][$Partie->getJoueurZoneEnCours($this->numeroAttaquant)])) {
                    $CarteActive = $this->CarteEnJeus[$this->numeroAttaquant][$Partie->getJoueurZoneEnCours($this->numeroAttaquant)];
                    $Carte = $CarteActive->getCarte();
                }
                else 
                    $Carte = null;

                if ($Carte == null) {
                    return 4;
                }
                if (($Carte->getTypeCarte()->getTag()=='STRIKE') || ($Carte->getTypeCarte()->getTag()=='CHAMBER')){
                    $attaque += $Carte->getAttaque();  
                }                
            }
        }

        return $attaque+$this->bonusAttaque($Partie);
    }

    private function defenseChmaber() {

    }



}
