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
    protected $Partie;
    protected $CarteEnJeus;
    protected $numeroAttaquant;
    protected $numeroDefenseur;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function chargement($Partie) {
        $this->Partie = $Partie;
        $this->chargerCarteEnJeu();
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
            $numeroDefenseur = $this->numeroDefenseur($Partie);
            $numeroAttaquant = $this->numeroAttaquant($Partie);
            if ($Partie->getJoueurZoneEnCours($numeroAttaquant)!='0') {
                if (isset($this->CarteEnJeus[$numeroAttaquant][$Partie->getJoueurZoneEnCours($numeroAttaquant)])) {
                    $CarteActive = $this->CarteEnJeus[$numeroAttaquant][$Partie->getJoueurZoneEnCours($numeroAttaquant)];
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
