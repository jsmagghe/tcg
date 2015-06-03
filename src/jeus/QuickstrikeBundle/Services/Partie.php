<?php

namespace jeus\QuickstrikeBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;

/* Entity */
use jeus\QuickstrikeBundle\Entity\Deck;
use jeus\QuickstrikeBundle\Entity\CartePartie;

/**
 *
 * @author Julien S
 */
class Partie
{

    protected $em;
    protected $container;
    protected $tools;
    protected $effets;
    protected $router;

    protected $Partie;
    protected $Joueur;
    public $CarteEnJeus;
    public $numeroAttaquant;
    public $numeroDefenseur;
    public $numeroJoueur;
    public $numeroAdversaire;

    public function __construct(ObjectManager $em, $container, $tools, $effets,$router)
    {
        $this->em = $em;
        $this->container = $container;
        $this->tools = $tools;
        $this->effets = $effets;
        $this->router = $router;
    }

    public function chargement($Partie,$Joueur) {
        $this->Partie = $Partie;
        $this->Joueur = $Joueur;
        $this->chargerCarteEnJeu();
        $this->numeroDefenseur = $this->numeroDefenseur();
        $this->numeroAttaquant = $this->numeroAttaquant();
        $this->numeroJoueur = $this->numeroJoueur();
        $this->numeroAdversaire = $this->numeroJoueur(true);
    }

    public function numeroAttaquant() {
        $numeroAttaquant = ($this->Partie->getJoueur1Etape()=='defense') ? 2 : 1;
        return $numeroAttaquant;
    }

    public function numeroDefenseur() {
        $numeroDefenseur = ($this->Partie->getJoueur1Etape()=='defense') ? 1 : 2;
        return $numeroDefenseur;
    }

    public function numeroJoueur($adversaire = false) {
        $numero = 1;
        if ($this->Partie->getJoueur1()->getId()==$this->Partie->getJoueur2()->getId()) {
            $numero = ($this->Partie->getJoueurBas() != null) ? $this->Partie->getJoueurBas() : 1;
        } else {
            $numero = ($this->Partie->getJoueur2()->getId()==$this->Joueur->getId()) ? 2 : 1;
        }
        if ($adversaire) {
            $numero = ($numero==1) ? 2 : 1;
        }

        return $numero;
    }

    public function chargerCarteEnJeu() {
        $this->CarteEnJeus = array();
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
            if (
                ($emplacement=='AVANTAGE') || ($emplacement=='STRIKE_VERT') 
                || ($emplacement=='STRIKE_JAUNE') || ($emplacement=='STRIKE_ROUGE')
                || (($emplacement=='CHAMBER') && ($this->Partie->getJoueurZoneEnCours($numeroJoueur)=='CHAMBER'))
                || (($emplacement=='TEAMWORK_VERTE') && ($this->Partie->getJoueurZoneEnCours($numeroJoueur)=='STRIKE_VERT'))
                || (($emplacement=='TEAMWORK_JAUNE') && ($this->Partie->getJoueurZoneEnCours($numeroJoueur)=='STRIKE_JAUNE'))
                || (($emplacement=='TEAMWORK_ROUGE') && ($this->Partie->getJoueurZoneEnCours($numeroJoueur)=='STRIKE_ROUGE'))
                ){
                $this->CarteEnJeus[$numeroJoueur]['ACTIVE'][] = $CartePartie;
            }
        }
        $this->effets->chargerCarteEnJeu($this->CarteEnJeus);

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
            1
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

            // l'iopening ne peut êrte que dans la zone verte ou en attente dans la zone opening
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

    public function demarragePartie($joueurConcerne) {
        $this->melangerEmplacement($joueurConcerne);
        $this->deplacerCarte($joueurConcerne,5,'DECK','DISCARD');
        $this->deplacerCarte($joueurConcerne,2,'DECK','ENERGIE_VERTE');
        $this->deplacerCarte($joueurConcerne,2,'DECK','ENERGIE_JAUNE');
        $this->deplacerCarte($joueurConcerne,2,'DECK','ENERGIE_ROUGE');
    }

    public function viderCarte($joueurConcerne) {
        $this->deplacerCarte($joueurConcerne,99,'AVANTAGE','DISCARD');
        $this->deplacerCarte($joueurConcerne,99,'STRIKE_VERT','DISCARD');
        $this->deplacerCarte($joueurConcerne,99,'STRIKE_JAUNE','DISCARD');
        $this->deplacerCarte($joueurConcerne,99,'STRIKE_ROUGE','DISCARD');
    }

    public function attaquer($joueurConcerne,$depart = true, $chamber = false) {
        $joueurAdverse = ($joueurConcerne==1)?2:1;
        if ($depart) 
            $this->viderCarte($joueurConcerne);
            
        if ($depart) {
            $this->retournerChamber($joueurAdverse);

            $this->Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_VERT');
            $this->Partie->setJoueurZoneEnCours($joueurAdverse,'STRIKE_VERT');
            $this->deplacerCarte($joueurConcerne,1,'OPENING','STRIKE_VERT');
            $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');
        } elseif ($chamber) {
            $this->Partie->dechargerZone($joueurConcerne,'STRIKE_VERT');
            $this->Partie->dechargerZone($joueurConcerne,'STRIKE_JAUNE');
            $this->Partie->dechargerZone($joueurConcerne,'STRIKE_ROUGE');
            $this->Partie->setJoueurZoneEnCours($joueurConcerne,'CHAMBER');
        } else {
            if ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_ROUGE') {
                $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');
            }
            if (
                ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_ROUGE')
                || ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_JAUNE')
                ) {
                $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_JAUNE');
            }
            if (
                ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_ROUGE')
                || ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_JAUNE')
                || ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_VERT')
                ) {
                $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');
            }
        }

        $this->Partie->setEtapeByNumero($joueurConcerne,'attente');
        $this->viderCarte($joueurAdverse);
        $this->Partie->setEtapeByNumero($joueurAdverse,'utilisationChamber');
    }

    public function choixDeck($Deck)
    {
        if ($Deck->getValide()) {
            foreach ($Deck->getCartes() as $CarteDeck) {
                $Carte = $CarteDeck->getCarte();
                $CartePartie = new CartePartie($Carte,$this->Partie,$this->numeroJoueur,'DECK');
                $this->Partie->addCartePartie($CartePartie);
            }
            $CarteOpening = $this->em
                                 ->getRepository('jeusQuickstrikeBundle:Carte')
                                 ->findOneBy(array('nom' => 'opening attack'));
            $CartePartie = new CartePartie($CarteOpening,$this->Partie,$this->numeroJoueur,'OPENING');
            $this->Partie->addCartePartie($CartePartie);
            $this->melangerEmplacement($this->Partie,$this->numeroJoueur);
            $this->Partie->setEtape($this->numeroJoueur, 'attenteDebut');
        }
        //return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id'=>$this->Partie->getId())));
        //return $this->redirect($this->router->generate('jeus_quickstrike_partie',array('id'=>$this->Partie->getId())));
    }

    public function payer($joueurConcerne,$chamber=false) {       
        $payable = false;
        $Carte = null;
        if ($chamber) {
            if (isset($this->CarteEnJeus[$joueurConcerne]['CHAMBER'])) {
                $CarteActive = $this->CarteEnJeus[$joueurConcerne]['CHAMBER'];
                $Carte = $CarteActive->getCarte();
            }        
        } else if (isset($this->CarteEnJeus[$joueurConcerne][$this->Partie->getJoueurZoneEnCours($joueurConcerne)])) {
            $CarteActive = $this->CarteEnJeus[$joueurConcerne][$this->Partie->getJoueurZoneEnCours($joueurConcerne)];
            $Carte = $CarteActive->getCarte();
        }

        if ($Carte) {
            $payable = $this->isCartePayable($joueurConcerne, $Carte, true);
        }
        return $payable;
    }

    public function payerCout($joueurConcerne,$couts) {       
        $paye = false;
        if (!is_array($couts)) {
            $couts = array($couts);
        }
        foreach($couts as $cout) {
            switch ($cout) {
                case 'free' :
                    // rien
                    break;
                case 'green' : 
                ici
                    break;
            }
        }

        return $payable;
    }

    public function verificationRecrutement($joueurConcerne,$CarteActive,$zoneEnCours,$zoneAControler) {
        if ((isset($this->CarteEnJeus[$joueurConcerne][$zoneAControler])) && ($zoneEnCours!=$zoneAControler))  {
            $Cartejeu = $this->CarteEnJeus[$joueurConcerne][$zoneAControler];
            if (
                ($Cartejeu!=null) 
                && ($Cartejeu->getCarte()!=null) 
                && ($CarteActive!=null) 
                && ($CarteActive->getCarte()!=null) 
                && ($Cartejeu->getCarte()->getNomCours()==$CarteActive->getCarte()->getNomCours()))
                $this->deplacerCarte($joueurConcerne,99,$zoneAControler,'DISCARD');
        }
    }

    public function jouer($joueurConcerne,$action) {
        $zoneEnCours = $this->Partie->getJoueurZoneEnCours($joueurConcerne);
        if (isset($this->CarteEnJeus[$joueurConcerne][$zoneEnCours]))
            $CarteActive = $this->CarteEnJeus[$joueurConcerne][$zoneEnCours];
        if ($action=='avantager') {
            $zoneCorrespondante = 'AVANTAGE';
            if ($this->effets->chargementPossible($CarteActive,$joueurConcerne)) {
                $this->Partie->chargerZone($joueurConcerne,$zoneEnCours);                
            }
        }
        if ($action=='recruter') {
            $zoneCorrespondante = $this->tools->zoneCorrespondante($zoneEnCours,'TEAMWORK');
            $this->deplacerCarte($joueurConcerne,99,$zoneCorrespondante,'DISCARD');
            $this->verificationRecrutement($joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_VERTE');
            $this->verificationRecrutement($joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_JAUNE');
            $this->verificationRecrutement($joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_ROUGE');
        }
        if ($this->effets->avantageImmediat($CarteActive)) {
            $this->deplacerCarte($joueurConcerne,1,$zoneEnCours,'DISCARD');
        } else {
            $this->deplacerCarte($joueurConcerne,1,$zoneEnCours,$zoneCorrespondante);            
        }
        $this->deplacerCarte($joueurConcerne,1,'DECK',$zoneEnCours);
    }

    public function contreAttaquer($joueurConcerne,$chamber) {
        $joueurAdverse = ($joueurConcerne==1)?2:1;
        $this->retournerChamber($joueurAdverse);
        $this->attaquer($joueurConcerne,false,$chamber);
    }

    public function retournerChamber($joueurConcerne) {
        if (
            (isset($this->CarteEnJeus[$joueurConcerne]['CHAMBER'])) 
            && ($this->CarteEnJeus[$joueurConcerne]['CHAMBER']->getCarte()!=null)
            && ($this->CarteEnJeus[$joueurConcerne]['CHAMBER']->getCarte()->getNumero()!='')
            && ($this->Partie->getJoueurZoneEnCours($joueurConcerne) == 'CHAMBER')
            )
        {
            $this->Partie->setJoueurZoneEnCours($joueurConcerne, 'STRIKE_VERT');
            $Chamber = $this->CarteEnJeus[$joueurConcerne]['CHAMBER'];
            $numeroChamber = $Chamber->getCarte()->getNumero();
            if (strpos($numeroChamber,'v') === false) {
                $numeroChamber .= 'v';
            } else {
                $numeroChamber = str_replace('v', '', $numeroChamber);
            }
            $Carte = $this->em->getRepository('jeusQuickstrikeBundle:Carte')->findOneByNumero($numeroChamber);
            if ($Carte!=null) {
                $Chamber->setCarte($Carte);
                $this->em->persist($Chamber);
                $this->em->flush();
            }           
        }
    }

    public function focuserPitcher($joueurConcerne,$action) {
        $zoneEnCours = $this->Partie->getJoueurZoneEnCours($joueurConcerne);
        $zoneSuivante = $this->tools->zoneSuivante($zoneEnCours);
        $zoneCorrespondante = 'DISCARD';
        if ($action=='focus')              
            $zoneCorrespondante = $this->tools->zoneCorrespondante($zoneEnCours,'ENERGIE');
        $this->deplacerCarte($joueurConcerne,1,$zoneEnCours,$zoneCorrespondante);
        if (strpos($action,'reflip_')===false) {
            $this->descendreDeZone($joueurConcerne);
        } else {
            $tab = explode('_', $action);
            $this->payerCout($tab[1]);
        }
    }

    public function descendreDeZone($joueurConcerne) {
        $zoneEnCours = $this->Partie->getJoueurZoneEnCours($joueurConcerne);
        $zoneSuivante = $this->tools->zoneSuivante($zoneEnCours);
        if ( $zoneSuivante == 'POINT') {
            $this->pointPourAdversaire($joueurConcerne);
        } else {
            $this->Partie->setJoueurZoneEnCours($joueurConcerne,$zoneSuivante);
            $this->deplacerCarte($joueurConcerne,1,'DECK',$this->Partie->getJoueurZoneEnCours($joueurConcerne));
        }
    }

    public function pointPourAdversaire($joueurConcerne){
        $this->Partie->addPointAdversaire(($joueurConcerne==1) ? 2 : 1);
        $this->Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_VERT');
        $this->setEtapeJoueur($joueurConcerne,'choixAttaquant');
    }

    public function setEtapeJoueur($joueurConcerne,$etape) {
        $this->Partie->setEtapeByNumero(($joueurConcerne==1) ? 2 : 1,'attente');
        $this->Partie->setEtapeByNumero($joueurConcerne,$etape);
    }

    public function infos(){
        $ZoneAttaquant = $this->Partie->getJoueurZoneEnCours($this->numeroAttaquant);
        $ZoneDefenseur = $this->Partie->getJoueurZoneEnCours($this->numeroDefenseur);
        $chamberChargeAttaquant = $this->Partie->isZoneChargee($this->numeroAttaquant,'VERT');
        $deckChargeAttaquant = $this->Partie->isZoneChargee($this->numeroAttaquant,'JAUNE');
        $discardChargeAttaquant = $this->Partie->isZoneChargee($this->numeroAttaquant,'ROUGE');
        $chamberChargeDefenseur = $this->Partie->isZoneChargee($this->numeroDefenseur,'VERT');
        $deckChargeDefenseur = $this->Partie->isZoneChargee($this->numeroDefenseur,'JAUNE');
        $discardChargeDefenseur = $this->Partie->isZoneChargee($this->numeroDefenseur,'ROUGE');
        $nombreTeamworkAttaquant = 0;
        if (isset($this->CarteEnJeus[$this->numeroAttaquant]['TEAMWORK_VERTE'])) {
            $nombreTeamworkAttaquant += 1;
        } 
        if (isset($this->CarteEnJeus[$this->numeroAttaquant]['TEAMWORK_JAUNE'])) {
            $nombreTeamworkAttaquant += 1;
        } 
        if (isset($this->CarteEnJeus[$this->numeroAttaquant]['TEAMWORK_ROUGE'])) {
            $nombreTeamworkAttaquant += 1;
        } 
        $nombreTeamworkDefenseur = 0;
        if (isset($this->CarteEnJeus[$this->numeroDefenseur]['TEAMWORK_VERTE'])) {
            $nombreTeamworkDefenseur += 1;
        } 
        if (isset($this->CarteEnJeus[$this->numeroDefenseur]['TEAMWORK_JAUNE'])) {
            $nombreTeamworkDefenseur += 1;
        } 
        if (isset($this->CarteEnJeus[$this->numeroDefenseur]['TEAMWORK_ROUGE'])) {
            $nombreTeamworkDefenseur += 1;
        } 

        $energieVerteDisponibleAttaquant = $this->energiedisponible($this->numeroAttaquant,'VERTE');
        $energieJauneDisponibleAttaquant = $this->energiedisponible($this->numeroAttaquant,'JAUNE');
        $energieRougeDisponibleAttaquant = $this->energiedisponible($this->numeroAttaquant,'ROUGE');

        return array(
            'ZoneAttaquant' => $ZoneAttaquant,
            'ZoneDefenseur' => $ZoneDefenseur,
            'chamberChargeAttaquant' => $chamberChargeAttaquant,
            'deckChargeAttaquant' => $deckChargeAttaquant,
            'discardChargeAttaquant' => $discardChargeAttaquant,
            'chamberChargeDefenseur' => $chamberChargeDefenseur,
            'deckChargeDefenseur' => $deckChargeDefenseur,
            'discardChargeDefenseur' => $discardChargeDefenseur,
            'nombreTeamworkAttaquant' => $nombreTeamworkAttaquant,
            'nombreTeamworkDefenseur' => $nombreTeamworkDefenseur,
            'energieVerteDisponibleAttaquant' => $energieVerteDisponibleAttaquant,
            'energieJauneDisponibleAttaquant' => $energieJauneDisponibleAttaquant,
            'energieRougeDisponibleAttaquant' => $energieRougeDisponibleAttaquant,
            );
    }

    public function attaqueEnCours() {
        $attaque = 0;
        if (($this->Partie->getJoueur1Etape()=='defense') || ($this->Partie->getJoueur2Etape()=='defense')) {
            if ($this->Partie->getJoueurZoneEnCours($this->numeroAttaquant)!='0') {
                if (isset($this->CarteEnJeus[$this->numeroAttaquant][$this->Partie->getJoueurZoneEnCours($this->numeroAttaquant)])) {
                    $CarteActive = $this->CarteEnJeus[$this->numeroAttaquant][$this->Partie->getJoueurZoneEnCours($this->numeroAttaquant)];
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

        return $attaque+$this->effets->bonusAttaque($this->numeroAttaquant,$this->numeroDefenseur,$this->infos());
    }

    public function interceptEnCours() {
        $intercept = 0;
        if (($this->Partie->getJoueur1Etape()=='defense') || ($this->Partie->getJoueur2Etape()=='defense')) {
            if ($this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)!='0') {
                if (isset($this->CarteEnJeus[$this->numeroDefenseur][$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)])) {
                    $CarteActive = $this->CarteEnJeus[$this->numeroDefenseur][$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)];
                    $Carte = $CarteActive->getCarte();
                }
                else 
                    $Carte = null;

                if ($Carte == null) {
                    return 4;
                }
                if (($Carte->getTypeCarte()->getTag()=='STRIKE') || ($Carte->getTypeCarte()->getTag()=='CHAMBER')){
                    $intercept += $Carte->getIntercept();  
                }                
            }
        }

        return $intercept+$this->effets->bonusDefense($this->numeroDefenseur,$this->numeroAttaquant,$this->infos());
    }

    public function defenseChamber() {
        $defense = 0;
        if (($this->Partie->getJoueur1Etape()=='defense') || ($this->Partie->getJoueur2Etape()=='defense')) {

                if (isset($this->CarteEnJeus[$this->numeroDefenseur]['CHAMBER'])) {
                    $CarteActive = $this->CarteEnJeus[$this->numeroDefenseur]['CHAMBER'];
                    $Carte = $CarteActive->getCarte();
                } else 
                    $Carte = null;

                if ($Carte == null) {
                    return 4;
                }
                $defense += $Carte->getAttaque();  
        }

        return $defense+$this->effets->bonusDefense($this->numeroDefenseur,$this->numeroAttaquant,$this->infos());
    }

    public function energiedisponible($joueurConcerne,$zone) {
        if (isset($this->CarteEnJeus[$joueurConcerne]['ENERGIE_'.$zone]))
            return count($this->CarteEnJeus[$joueurConcerne]['ENERGIE_'.$zone]);
        else 
            return 0;
    }

    public function isCartePayable( $joueurConcerne, $Carte,$payer = false) {
        $payable = true;
        if ($Carte == null) 
            return false;

        $coutVert = $Carte->getCoutVert();
        $coutJaune = $Carte->getCoutJaune();
        $coutRouge = $Carte->getCoutRouge();

        $energieVerteDisponible = $this->energiedisponible($joueurConcerne,'VERTE');
        $energieJauneDisponible = $this->energiedisponible($joueurConcerne,'JAUNE');
        $energieRougeDisponible = $this->energiedisponible($joueurConcerne,'ROUGE');

        if ($energieRougeDisponible>=$coutRouge) {
            $energieRougeDisponible-=$coutRouge;
            $coutRouge = 0;
        }

        if ($energieJauneDisponible+$energieRougeDisponible>=$coutJaune) {
            $energieJauneDisponible-=$coutJaune;
            if ($energieJauneDisponible<0) {
                $energieRougeDisponible -= $energieJauneDisponible;
                $energieJauneDisponible = 0;
            }
            $coutJaune = 0;
        }

        if ($energieVerteDisponible+$energieJauneDisponible+$energieRougeDisponible>=$coutVert) {
            $energieVerteDisponible-=$coutVert;
            if ($energieVerteDisponible<0) {
                $energieJauneDisponible -= $energieVerteDisponible;
                $energieVerteDisponible = 0;
            }
            if ($energieJauneDisponible<0) {
                $energieRougeDisponible -= $energieJauneDisponible;
                $energieJauneDisponible = 0;
            }
            $coutVert = 0;
        }


        $payable = (($coutVert<=0) && ($coutJaune<=0) && ($coutRouge<=0));

        if (($payer) && ($payable)) {
            $coutVert = $Carte->getCoutVert();
            $coutJaune = $Carte->getCoutJaune();
            $coutRouge = $Carte->getCoutRouge();
            $energieVerteDisponible = $this->energiedisponible($joueurConcerne,'VERTE');
            $energieJauneDisponible = $this->energiedisponible($joueurConcerne,'JAUNE');
            $energieRougeDisponible = $this->energiedisponible($joueurConcerne,'ROUGE');

            if ($coutVert>0) {
                $this->deplacerCarte($joueurConcerne,$coutVert,'ENERGIE_VERTE','DISCARD',true);
                $coutVert -= $energieVerteDisponible;
                $coutVert = ($coutVert>0) ? $coutVert : 0;
            }
            $coutJaune += $coutVert;
            if ($coutJaune>0) {
                $this->deplacerCarte($joueurConcerne,$coutJaune,'ENERGIE_JAUNE','DISCARD',true);
                $coutJaune -= $energieJauneDisponible;
                $coutJaune = ($coutJaune>0) ? $coutJaune : 0;
            }
            $coutRouge += $coutJaune;
            if ($coutRouge>0) {
                $this->deplacerCarte($joueurConcerne,$coutRouge,'ENERGIE_ROUGE','DISCARD',true);
            }
        }

        return $payable;
    }

    public function gestionPile(){
        // si les deux joueurs ont choisis leur deck on les passe en début de partie
        if (($this->Partie->getJoueur1Etape()=='attenteDebut')
            && ($this->Partie->getJoueur2Etape()=='attenteDebut')
           ) {
            $this->demarragePartie(1);
            $this->demarragePartie(2);
            if ($this->tools->joueurChoisi()==1) {
                $this->Partie->setJoueur1Etape('choixAttaquant');
                $this->Partie->setJoueur2Etape('attente');
            } else {
                $this->Partie->setJoueur2Etape('choixAttaquant');
                $this->Partie->setJoueur1Etape('attente');
            }
        }
    }

    public function isChamberUtilisable() {
        $isUtilisable = (
            ($this->Partie->getEtape($this->Joueur) == 'utilisationChamber') 
            && ($this->Partie->isZoneChargee($this->numeroJoueur,'CHAMBER'))
            && ($this->Partie->isZoneChargee($this->numeroJoueur,'DECK'))
            && ($this->Partie->isZoneChargee($this->numeroJoueur,'DISCARD'))
            && ($this->attaqueEnCours()<=$this->defenseChamber())
        );

        if (
            ($this->Partie->getEtape($this->Joueur) == 'utilisationChamber')
            && ($isUtilisable==false)
            ) {
            $this->Partie->setEtapeByNumero($this->numeroJoueur,'defense');
            $this->Partie->setJoueurZoneEnCours($this->numeroJoueur,'STRIKE_VERT');
            $this->deplacerCarte($this->numeroJoueur,1,'DECK',$this->Partie->getJoueurZoneEnCours($this->numeroJoueur));
        }

        return $isUtilisable;
    }

    public function noChamber() 
    {
        $this->Partie->setEtapeByNumero($this->numeroJoueur,'defense');
        $this->Partie->setJoueurZoneEnCours($this->numeroJoueur,'STRIKE_VERT');
        $this->deplacerCarte($this->numeroJoueur,1,'DECK',$this->Partie->getJoueurZoneEnCours($this->numeroJoueur));
    }

    public function actionPossibles() {
        $this->CarteEnJeus=null;
        $this->chargerCarteEnJeu();
        $action = array();
        if ($this->Partie->getJoueur1()->getId()==$this->Partie->getJoueur2()->getId()) {
            $JoueurBas = ($this->Partie->getJoueurBas() != null)?$this->Partie->getJoueurBas():1;
        } else {
            $JoueurBas = ($this->Joueur == $this->Partie->getJoueur1())?1:2;
        }

        $choixPossible = array();
        $this->isChamberUtilisable();
        $etape = $this->Partie->getEtape($this->Joueur);

        $victoire = '';
        $score = '';
        if ($this->numeroJoueur($this->Joueur)==1) {
            $score = $this->Partie->getJoueur1Point().'-'.$this->Partie->getJoueur2Point();
        } else {
            $score = $this->Partie->getJoueur2Point().'-'.$this->Partie->getJoueur1Point();
        }

        if (($this->Partie->getPointVictoire()<=$this->Partie->getJoueur1Point()) || ($this->Partie->getPointVictoire()<=$this->Partie->getJoueur2Point())) {


            if ($this->Partie->getJoueur1Point()<$this->Partie->getJoueur2Point()) {
                if ($this->numeroJoueur==1) {
                    $victoire = 'perdu';
                } else {
                    $victoire = 'gagné';
                }
            } elseif ($this->Partie->getJoueur1Point()>$this->Partie->getJoueur2Point()) {
                if ($this->numeroJoueur==2) {
                    $victoire = 'perdu';
                } else {
                    $victoire = 'gagné';
                }
            }
            if ($victoire<>'')
                $action[] = '<span class="partie-finie">Vous avez '.$victoire.'</span>';
        }

        $action[] = '<span class="score">score: '.$score.'</span>';

        if ($victoire<>'')
            return $action;


        switch ($etape) {
            case 'choix deck' :
                $Decks = $this->em->getRepository('jeusQuickstrikeBundle:Deck')->findBy(array('joueur' => $this->Joueur, 'valide' => true));
                foreach($Decks as $Deck) {
                    $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_deck',array('id' => $this->Partie->getId(),'idDeck' => $Deck->getId())).'">'.$Deck->getNom().'</a>';
                }
                break;
            case 'choixAttaquant':
                $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'attaquer')).'">Attaquer</a>';
                $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'defendre')).'">Defendre</a>';
                break;
            case 'utilisationChamber':
                $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'jouer_chamber')).'">Jouer la Chamber</a>';
                $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'no_chamber')).'">Ne pas jouer la Chamber </a>';
                break;
            case 'defense':
                $attaque = $this->attaqueEnCours();
                $CarteEnJeus = $this->CarteEnJeus[$this->numeroDefenseur];

                if ($this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)!='0') {
                    $CarteActive = null;
                    $Carte = null;
                    if (isset($CarteEnJeus[$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)])) {
                        $CarteActive = $CarteEnJeus[$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)];
                        $Carte = $CarteActive->getCarte();
                    }                    
                    if ($this->isCartePayable($this->numeroDefenseur, $Carte)) {
                        if ($Carte->getTypeCarte()->getTag()=='STRIKE') 
                        {
                            $defense = $Carte->getIntercept()+$this->effets->bonusDefense($this->numeroDefenseur,$this->numeroAttaquant,$this->infos());  
                            if ($defense>=$attaque) 
                                $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'contre_attaquer')).'">Contre attaquer</a>';
                        }
                        if (($Carte->getTypeCarte()->getTag()=='TEAMWORK') 
                            && ($CarteActive->getEmplacement()==$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur))
                            )
                        {
                            $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'recruter')).'">Recruter</a>';
                        }
                        if (($Carte->getTypeCarte()->getTag()=='ADVANTAGE') 
                            && ($CarteActive->getEmplacement()==$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur))
                            && ($this->effets->avantagePossible($this->numeroDefenseur))
                            )
                        {
                            $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'avantager')).'">Jouer</a>';
                        }
                    }
                }

                if ($this->effets->pitchPossible($JoueurBas)){
                    $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'pitcher')).'">Pitch</a>';
                }
                if ($this->effets->focusPossible($JoueurBas)){
                    $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'focuser')).'">Focus</a>';                    
                }
                $reflips = $this->effets->reflipsPossible($JoueurBas) {
                    foreach ($$reflips as $reflip => $libelle) {
                        $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => $reflip)).'">'. $libelle .'</a>';                    
                    }
                }
                if (count($action) == 1)
                    $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'discarder')).'">Discard</a>';
                break;
        }                
            
        return $action;
    }





}
