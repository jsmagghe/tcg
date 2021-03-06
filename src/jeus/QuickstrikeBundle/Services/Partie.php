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
    protected $interactions;
    protected $router;

    protected $Partie;
    protected $Joueur;
    public $CarteEnJeus;
    public $numeroAttaquant;
    public $numeroDefenseur;
    public $numeroJoueur;
    public $numeroAdversaire;

    public function __construct(ObjectManager $em, $container, $tools, $effets,$interactions,$router)
    {
        $this->em = $em;
        $this->container = $container;
        $this->tools = $tools;
        $this->effets = $effets;
        $this->interactions = $interactions;
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
        $this->effets->chargerPartie($Partie);
        $this->interactions->chargerPartie($Partie);
        $this->effets->chargerInfos($this->infos('chargement'));
    }

    public function numeroAttaquant() {
        $numeroAttaquant = ($this->Partie->getJoueur1Etape()=='defense' || $this->Partie->getJoueur1Etape()=='utilisationChamber') ? 2 : 1;
        return $numeroAttaquant;
    }

    public function numeroDefenseur() {
        $numeroDefenseur = ($this->numeroAttaquant() == 2) ? 1 : 2;
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
                || (($emplacement=='CHAMBER') && ($this->Partie->getEtape($numeroJoueur)=='utilisationChamber'))
                || (($emplacement=='TEAMWORK_VERTE') && ($this->Partie->getJoueurZoneEnCours($numeroJoueur)=='STRIKE_VERT') && ($this->Partie->getEtape($numeroJoueur)!='utilisationChamber'))
                || (($emplacement=='TEAMWORK_JAUNE') && ($this->Partie->getJoueurZoneEnCours($numeroJoueur)=='STRIKE_JAUNE'))
                || (($emplacement=='TEAMWORK_ROUGE') && ($this->Partie->getJoueurZoneEnCours($numeroJoueur)=='STRIKE_ROUGE'))
                ){
                $this->CarteEnJeus[$numeroJoueur]['ACTIVE'][] = $CartePartie;
            }
        }
        $this->effets->chargerCarteEnJeu($this->CarteEnJeus);
        $this->interactions->chargerCarteEnJeu($this->CarteEnJeus);
    }

    public function demarragePartie($joueurConcerne) {
        $this->interactions->melangerEmplacement($joueurConcerne);
        $this->effets->deplacerCarte($joueurConcerne,5,'DECK','DISCARD');
        $this->effets->deplacerCarte($joueurConcerne,1,'DECK','ENERGIE_VERTE');
        $this->effets->deplacerCarte($joueurConcerne,1,'DECK','ENERGIE_JAUNE');
        $this->effets->deplacerCarte($joueurConcerne,1,'DECK','ENERGIE_ROUGE');
    }

    public function viderCarte($joueurConcerne) {
        $this->effets->deplacerCarte($joueurConcerne,99,'AVANTAGE','DISCARD');
        $this->effets->deplacerCarte($joueurConcerne,99,'STRIKE_VERT','DISCARD');
        $this->effets->deplacerCarte($joueurConcerne,99,'STRIKE_JAUNE','DISCARD');
        $this->effets->deplacerCarte($joueurConcerne,99,'STRIKE_ROUGE','DISCARD');
        $this->chargerCarteEnJeu();
    }

    public function attaquer($joueurConcerne,$depart = true, $chamber = false) {
        $joueurAdverse = ($joueurConcerne==1)?2:1;
        $this->interactions->initialiserEffets($joueurAdverse);
        $this->viderCarte($joueurAdverse);
        if ($depart) 
            $this->viderCarte($joueurConcerne);

        if ($depart) {
            $this->interactions->initialiserEffets($joueurAdverse);
            $this->interactions->initialiserEffets($joueurConcerne);
            $this->retournerChamber($joueurAdverse);

            $this->Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_VERT');
            $this->Partie->setJoueurZoneEnCours($joueurAdverse,'STRIKE_VERT');
            $this->effets->deplacerCarte($joueurConcerne,1,'OPENING','STRIKE_VERT');
            $this->effets->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');
        } elseif ($chamber) {
            $this->Partie->setJoueurZoneEnCours($joueurConcerne,'CHAMBER');
            $this->Partie->setJoueurZoneEnCours($joueurAdverse,$this->effets->zoneDepart($joueurAdverse));
        } else {
            if ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_ROUGE') {
                $this->effets->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');
            }
            if (
                ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_ROUGE')
                || ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_JAUNE')
                ) {
                $this->effets->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_JAUNE');
            }
            if (
                ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_ROUGE')
                || ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_JAUNE')
                || ($this->Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_VERT')
                ) {
                $this->effets->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');
            }
            $this->Partie->setJoueurZoneEnCours($joueurAdverse,$this->effets->zoneDepart($joueurAdverse));
        }

        $this->Partie->setEtapeByNumero($joueurConcerne,'attente');
        $this->Partie->setEtapeByNumero($joueurAdverse,'utilisationChamber');
        $this->isChamberUtilisable($joueurAdverse);
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
            $this->interactions->melangerEmplacement($this->Partie,$this->numeroJoueur);
            $this->Partie->setEtape($this->numeroJoueur, 'attenteDebut');
        }
        //return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id'=>$this->Partie->getId())));
        //return $this->redirect($this->router->generate('jeus_quickstrike_partie',array('id'=>$this->Partie->getId())));
    }

    public function payer($joueurConcerne,$chamber=false) {       
        $payable = false;
        $Carte = null;       
        $effetNonExecutes = $this->Partie->getJoueurEffetNonExecutes();

        foreach ($effetNonExecutes as $joueur => $effets) {
            foreach ($effets as $effet) {
                if ($this->payerCout($joueur,array($effet['cout']))) {
                    $this->interactions->ajoutEffet($joueur,$effet['idCarte'],$effet['type'],$effet['effet']);
                }
            }
        }

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
            switch (true) {
                case ($cout == 'free') :
                    // rien
                    break;
                case (strpos($cout,'green')!==false) : 
                    $coutVert = str_replace('green', '', $cout);
                    $coutVert = ($coutVert != '') ? $coutVert : 1;
                    $this->payerParEnergie($joueurConcerne, array('coutVert' => $coutVert));
                    break;
                case (strpos($cout,'yellow')!==false) : 
                    $coutJaune = str_replace('yellow', '', $cout);
                    $coutJaune = ($coutJaune != '') ? $coutJaune : 1;
                    $this->payerParEnergie($joueurConcerne, array('coutJaune' => $coutJaune));
                    break;
                case (strpos($cout,'red')!==false) : 
                    $coutRouge = str_replace('red', '', $cout);
                    $coutRouge = ($coutRouge != '') ? $coutRouge : 1;
                    $this->payerParEnergie($joueurConcerne, array('coutRouge' => $coutRouge));
                    break;
                case (strpos($cout,'point')!==false) : 
                    $point = str_replace('point', '', $point);
                    $point = ($point != '') ? $point : 1;
                    $this->pointPourAdversaire($joueurConcerne,true);
                    break;
                case (is_int($cout)===true) : 
                    $CartePartie = $this->em->getRepository('jeusQuickstrikeBundle:CartePartie')->find($cout);
                    $CartePartie->setEmplacement('DISCARD');
                    $this->em->flush();
                    break;
            }
        }

        return $paye;
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
                $this->effets->deplacerCarte($joueurConcerne,99,$zoneAControler,'DISCARD');
        }
    }

    public function isEnergieDisponible($joueurConcerne,$zone) {
        $zone = strtoupper($zone);
        if (isset($this->CarteEnJeus[$joueurConcerne]['ENERGIE_'.$zone]))
            return count($this->CarteEnJeus[$joueurConcerne]['ENERGIE_'.$zone]);
        else 
            return 0;
    }

    public function energieDisponible($joueurConcerne,$retirerCoutCarteActive = true)
    {
        $energieVerteDisponible = $this->isEnergieDisponible($joueurConcerne,'VERTE');
        $energieJauneDisponible = $this->isEnergieDisponible($joueurConcerne,'JAUNE');
        $energieRougeDisponible = $this->isEnergieDisponible($joueurConcerne,'ROUGE');


        if ($retirerCoutCarteActive) {
            $CartePartie = null;
            if (isset($this->CarteEnJeus[$this->numeroDefenseur][$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)])) {
                $CartePartie = $this->CarteEnJeus[$this->numeroDefenseur][$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)];
                $couts = $this->effets->coutsCarte($joueurConcerne,$CartePartie);
                $energieVerteDisponible -= $couts['coutVert'];
                $energieJauneDisponible -= $couts['coutJaune'];
                $energieRougeDisponible -= $couts['coutRouge'];

                if ($energieVerteDisponible<0) {
                    $energieJauneDisponible += $energieVerteDisponible;
                    $energieVerteDisponible = 0;
                }
                if ($energieJauneDisponible<0) {
                    $energieRougeDisponible += $energieJauneDisponible;
                    $energieJauneDisponible = 0;
                }                
            }
        }

        $effetNonExecutes = $this->Partie->getJoueurEffetNonExecutes();
        if (isset($effetNonExecutes[$joueurConcerne])) {
            $effets = $effetNonExecutes[$joueurConcerne];
            foreach ($effets as $idCarte => $effet) {                
                $energieVerteDisponible -= substr_count($effet['cout'], 'green');
                $energieJauneDisponible -= substr_count($effet['cout'], 'yellow');
                $energieRougeDisponible -= substr_count($effet['cout'], 'red');
            }
        }

        return array($energieVerteDisponible,$energieJauneDisponible,$energieRougeDisponible);
    }

    public function jouer($joueurConcerne,$action) {
        $zoneEnCours = $this->Partie->getJoueurZoneEnCours($joueurConcerne);
        if (isset($this->CarteEnJeus[$joueurConcerne][$zoneEnCours]))
            $CarteActive = $this->CarteEnJeus[$joueurConcerne][$zoneEnCours];
        $this->effets->effetJouer($joueurConcerne,$action);
        if ($action=='jouer') {
            $zoneCorrespondante = 'AVANTAGE';
            $this->effets->chargerUneZone($joueurConcerne,$zoneEnCours,$CarteActive);                
        }
        if ($action=='recruter') {
            $zoneCorrespondante = $this->tools->zoneCorrespondante($zoneEnCours,'TEAMWORK');
            $this->effets->deplacerCarte($joueurConcerne,99,$zoneCorrespondante,'DISCARD');
            $this->verificationRecrutement($joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_VERTE');
            $this->verificationRecrutement($joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_JAUNE');
            $this->verificationRecrutement($joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_ROUGE');
        }
        if ($this->effets->avantageImmediat($joueurConcerne,$CarteActive)) {
            $this->effets->deplacerCarte($joueurConcerne,1,$zoneEnCours,'DISCARD');
        } else {
            $this->effets->deplacerCarte($joueurConcerne,1,$zoneEnCours,$zoneCorrespondante);            
        }
        $this->effets->deplacerCarte($joueurConcerne,1,'DECK',$zoneEnCours);
    }

    public function contreAttaquer($joueurConcerne,$chamber) {
        $joueurAdverse = ($joueurConcerne==1)?2:1;
        $this->interactions->initialiserEffets($joueurAdverse);
        if ($chamber===true) {
            $this->Partie->dechargerZone($joueurConcerne,'STRIKE_VERT');
            $this->Partie->dechargerZone($joueurConcerne,'STRIKE_JAUNE');
            $this->Partie->dechargerZone($joueurConcerne,'STRIKE_ROUGE');
        }
        $this->effets->effetJouer($joueurConcerne,'counter attack');
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
            $numeroEffet = $this->numeroEffet($numeroAttaquant,$Chamber->getCarte());
            if ($numeroEffet != 357) {
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
    }

    public function focuserPitcher($joueurConcerne,$action) {
        $zoneEnCours = $this->Partie->getJoueurZoneEnCours($joueurConcerne);
        $zoneSuivante = $this->tools->zoneSuivante($zoneEnCours);
        $zoneCorrespondante = 'DISCARD';
        if ($action=='pitch')              
            $this->effets->effetPitcher($joueurConcerne);
        if ($action=='focus') {
            $zoneCorrespondante = $this->tools->zoneCorrespondante($zoneEnCours,'ENERGIE');
            $this->effets->effetFocuser($joueurConcerne);
        }            
        $this->effets->deplacerCarte($joueurConcerne,1,$zoneEnCours,$zoneCorrespondante);
        if (strpos($action,'reflip_')===false) {
            $this->descendreDeZone($joueurConcerne);
        } else {
            $tab = explode('_', $action);
            $this->payerCout($joueurConcerne,$tab[1]);
            $this->effets->deplacerCarte($joueurConcerne,1,'DECK',$this->Partie->getJoueurZoneEnCours($joueurConcerne));

        }
    }

    public function deployer($joueurConcerne,$action) {
        $zoneEnCours = $this->Partie->getJoueurZoneEnCours($joueurConcerne);
        if (isset($this->CarteEnJeus[$joueurConcerne][$zoneEnCours]))
            $CarteActive = $this->CarteEnJeus[$joueurConcerne][$zoneEnCours];
        if ($CarteActive != null) {
            $tab = explode('_', $action);
            $this->payerCout($joueurConcerne,$tab[2]);

            if ($tab[1]=='red') {
                $this->effets->deplacerCarte($joueurConcerne,1,$zoneEnCours,'TEAMWORK_ROUGE');            
            } else if ($tab[1]=='yellow') {
                $this->effets->deplacerCarte($joueurConcerne,1,$zoneEnCours,'TEAMWORK_JAUNE');            
            } else {
                $this->effets->deplacerCarte($joueurConcerne,1,$zoneEnCours,'TEAMWORK_VERTE');                        
            }
            $zoneCorrespondante = $this->tools->zoneCorrespondante($zoneEnCours,'TEAMWORK');
            $this->verificationRecrutement($joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_VERTE');
            $this->verificationRecrutement($joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_JAUNE');
            $this->verificationRecrutement($joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_ROUGE');
            $this->effets->deplacerCarte($joueurConcerne,1,'DECK',$zoneEnCours);                                    
        }
    }

    public function stockerChoix($joueurConcerne,$action) {
        // on ne paye pas l'effet de suite car la carte peut ne pas être jouée
        $this->ajoutEffetNonExecute($joueurConcerne,$action);
    }

    public function celebration($joueurConcerne,$parEffet = false) {
        if ($this->effets->celebrationPossible($joueurConcerne)) {
            if (isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_VERTE'])) {
                $this->effets->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');                        
            }
            if (isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_JAUNE'])) {
                $this->effets->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');                        
            }
            if (isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_ROUGE'])) {
                $this->effets->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');                        
            }
            $effetSupplementaires = $this->effets->effetCelebration($joueurConcerne);
            if (($parEffet == false) && (isset($effetSupplementaires['twice']))) {
                $this->celebration($joueurConcerne,true);
            }
        }
    }

    public function descendreDeZone($joueurConcerne) {
        $zoneEnCours = $this->Partie->getJoueurZoneEnCours($joueurConcerne);
        $zoneSuivante = $this->tools->zoneSuivante($zoneEnCours);
        if ( $zoneSuivante == 'POINT') {
            $this->pointPourAdversaire($joueurConcerne);
            $this->celebration(($joueurConcerne==1) ? 2 : 1);
        } else {
            $this->Partie->setJoueurZoneEnCours($joueurConcerne,$zoneSuivante);
            $this->effets->deplacerCarte($joueurConcerne,1,'DECK',$this->Partie->getJoueurZoneEnCours($joueurConcerne));
        }
    }

    public function pointPourAdversaire($joueurConcerne, $parEffet = false){
        $this->Partie->addPointAdversaire(($joueurConcerne==1) ? 2 : 1);
        if (!$parEffet) {
            $this->Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_VERT');
            $this->setEtapeJoueur($joueurConcerne,'choixAttaquant');            
        }
    }

    public function setEtapeJoueur($joueurConcerne,$etape) {
        $this->Partie->setEtapeByNumero(($joueurConcerne==1) ? 2 : 1,'attente');
        $this->Partie->setEtapeByNumero($joueurConcerne,$etape);
    }

    public function infos($provenance = ''){
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

        $energieVerteDisponibleDefenseur = $this->isEnergieDisponible($this->numeroDefenseur,'VERTE');
        $energieJauneDisponibleDefenseur = $this->isEnergieDisponible($this->numeroDefenseur,'JAUNE');
        $energieRougeDisponibleDefenseur = $this->isEnergieDisponible($this->numeroDefenseur,'ROUGE');
        $CartePartie = null;
        if (isset($this->CarteEnJeus[$this->numeroDefenseur][$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)])) {
            $CartePartie = $this->CarteEnJeus[$this->numeroDefenseur][$this->Partie->getJoueurZoneEnCours($this->numeroDefenseur)];
        }

        $tabInfos = array(
            'numeroAttaquant' => $this->numeroAttaquant,
            'numeroDefenseur' => $this->numeroDefenseur,
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
            'energieVerteDisponibleDefenseur' => $energieVerteDisponibleDefenseur,
            'energieJauneDisponibleDefenseur' => $energieJauneDisponibleDefenseur,
            'energieRougeDisponibleDefenseur' => $energieRougeDisponibleDefenseur,
            'typeCarteActive' => ($CartePartie != null) ? $CartePartie->getCarte()->getTypeCarte()->getTag() : 0,
            'carteActive' => $CartePartie,
            );

        if ($provenance =='') {
            $tabInfos['attaqueAttaquant'] = $this->attaqueEnCours();
        }

        $this->effets->chargerInfos($tabInfos);

        return $tabInfos;
    }

    public function attaqueEnCours($joueurConcerne = null) {
        if ($joueurConcerne == null) {
            $joueurConcerne = $this->numeroAttaquant;
        }
        $joueurAdverse = ($joueurConcerne == 1) ? 2 : 1;

        $attaque = 0;
        if (
            ($this->Partie->getJoueur1Etape()=='defense') 
            || ($this->Partie->getJoueur2Etape()=='defense')
            || ($this->Partie->getJoueur1Etape()=='utilisationChamber')
            || ($this->Partie->getJoueur2Etape()=='utilisationChamber')
            ) {
            if ($this->Partie->getJoueurZoneEnCours($joueurConcerne)!='0') {
                if (isset($this->CarteEnJeus[$joueurConcerne][$this->Partie->getJoueurZoneEnCours($joueurConcerne)])) {
                    $CarteActive = $this->CarteEnJeus[$joueurConcerne][$this->Partie->getJoueurZoneEnCours($joueurConcerne)];
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
        $this->infos('attaqueEnCours');

        return $attaque+$this->effets->bonusAttaque($joueurConcerne,$joueurAdverse,'test');
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

        return $intercept+$this->effets->bonusDefense($this->numeroDefenseur,$this->numeroAttaquant);
    }

    public function defenseChamber($joueurConcerne = null) {
        $defense = 0;
        if ($joueurConcerne == null) {
            $joueurConcerne = $this->numeroDefenseur;
        }

        if ($this->Partie->getEtape($joueurConcerne)=='utilisationChamber') {

                if (isset($this->CarteEnJeus[$joueurConcerne]['CHAMBER'])) {
                    $CarteActive = $this->CarteEnJeus[$joueurConcerne]['CHAMBER'];
                    $Carte = $CarteActive->getCarte();
                } else 
                    $Carte = null;

                if ($Carte == null) {
                    return 4;
                }
                $defense += $Carte->getIntercept();  
        }

        return $defense+$this->effets->bonusDefense($joueurConcerne,($joueurConcerne == 1) ? 2 : 1);
    }

    public function payerParEnergie($joueurConcerne,$couts) {
        $coutVert = (isset($couts['coutVert'])) ? $couts['coutVert'] : 0;
        $coutJaune = (isset($couts['coutJaune'])) ? $couts['coutJaune'] : 0;
        $coutRouge = (isset($couts['coutRouge'])) ? $couts['coutRouge'] : 0;

        $energieVerteDisponible = $this->isEnergieDisponible($joueurConcerne,'VERTE');
        $energieJauneDisponible = $this->isEnergieDisponible($joueurConcerne,'JAUNE');
        $energieRougeDisponible = $this->isEnergieDisponible($joueurConcerne,'ROUGE');

        if ($coutVert>0) {
            $this->effets->deplacerCarte($joueurConcerne,$coutVert,'ENERGIE_VERTE','DISCARD',true);
            $coutVert -= $energieVerteDisponible;
            $coutVert = ($coutVert>0) ? $coutVert : 0;
        }
        $coutJaune += $coutVert;
        if ($coutJaune>0) {
            $this->effets->deplacerCarte($joueurConcerne,$coutJaune,'ENERGIE_JAUNE','DISCARD',true);
            $coutJaune -= $energieJauneDisponible;
            $coutJaune = ($coutJaune>0) ? $coutJaune : 0;
        }
        $coutRouge += $coutJaune;
        if ($coutRouge>0) {
            $this->effets->deplacerCarte($joueurConcerne,$coutRouge,'ENERGIE_ROUGE','DISCARD',true);
        }
    }

    public function isCartePayable( $joueurConcerne, $Carte,$payer = false) {
        $payable = true;
        if ($Carte == null) 
            return false;

        $tab = $this->effets->coutsCarte($joueurConcerne,$Carte);
        $coutVert = $tab['coutVert'];
        $coutJaune = $tab['coutJaune'];
        $coutRouge = $tab['coutRouge'];

        $energieVerteDisponible = $this->isEnergieDisponible($joueurConcerne,'VERTE');
        $energieJauneDisponible = $this->isEnergieDisponible($joueurConcerne,'JAUNE');
        $energieRougeDisponible = $this->isEnergieDisponible($joueurConcerne,'ROUGE');

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
            $this->payerParEnergie($joueurConcerne, array(
                'coutVert' => $tab['coutVert'],
                'coutJaune' => $tab['coutJaune'],
                'coutRouge' => $tab['coutRouge'],
                ));
        }

        return $payable;
    }

    public function gestionPile(){
        // si les deux joueurs ont choisis leur deck on les passe en début de partie
        if (($this->Partie->getJoueur1Etape()=='attenteDebut')
            && ($this->Partie->getJoueur2Etape()=='attenteDebut')
           ) {
            if ($this->tools->joueurChoisi()==1) {
                $this->Partie->setJoueur1Etape('choixAttaquant');
                $this->Partie->setJoueur2Etape('attente');
            } else {
                $this->Partie->setJoueur2Etape('choixAttaquant');
                $this->Partie->setJoueur1Etape('attente');
            }
            $this->demarragePartie(1);
            $this->demarragePartie(2);
        }
    }

    public function isChamberUtilisable($joueurConcerne = null) {
        $this->infos();
        if ($joueurConcerne==null) {
            $joueurConcerne = $this->numeroDefenseur;
        }
        $joueurAdverse = ($joueurConcerne == 1) ? 2 : 1;

        $isUtilisable = (
            ($this->Partie->getEtape($joueurConcerne) == 'utilisationChamber') 
            && ($this->Partie->isZoneChargee($joueurConcerne,'CHAMBER'))
            && ($this->Partie->isZoneChargee($joueurConcerne,'DECK'))
            && ($this->Partie->isZoneChargee($joueurConcerne,'DISCARD'))
            && ($this->attaqueEnCours($joueurAdverse)<=$this->defenseChamber($joueurConcerne))
            && ($this->effets->signaturePossible($joueurConcerne))
        );

        if (
            ($this->Partie->getEtape($joueurConcerne) == 'utilisationChamber')
            && ($isUtilisable==false)
            ) {
            $this->Partie->setEtapeByNumero($joueurConcerne,'defense');
            $this->Partie->setJoueurZoneEnCours($joueurConcerne,$this->effets->zoneDepart($joueurConcerne));
            $this->effets->deplacerCarte($joueurConcerne,1,'DECK',$this->Partie->getJoueurZoneEnCours($joueurConcerne));
        }

        return $isUtilisable;
    }

    public function noChamber() 
    {
        $this->Partie->setEtapeByNumero($this->numeroJoueur,'defense');
        $this->Partie->setJoueurZoneEnCours($this->numeroJoueur,'STRIKE_VERT');
        $this->effets->deplacerCarte($this->numeroJoueur,1,'DECK',$this->Partie->getJoueurZoneEnCours($this->numeroJoueur));
    }

    public function deckVisible($joueurConcerne) 
    {
        return $this->effets->deckVisible($joueurConcerne);
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

        /*if ($this->Partie->getJoueurZoneEnCours($JoueurBas)!='0') {
            if (isset($this->CarteEnJeus[$JoueurBas][$this->Partie->getJoueurZoneEnCours($JoueurBas)])) {
                $CarteActive = $this->CarteEnJeus[$JoueurBas][$this->Partie->getJoueurZoneEnCours($JoueurBas)];
                $Carte = $CarteActive->getCarte();
                $action[] = '<span class="infos">Carte en cours: '.$Carte->getNom().'</span><br/>';
            }                    
        }*/

        $choixPossible = array();
        $this->infos();
        $this->isChamberUtilisable();
        $etape = $this->Partie->getEtape($this->Joueur);

        $victoire = '';
        $score = '';
        if ($this->numeroJoueur()==1) {
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
        $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'remonter_bug')).'">Signaler un bug</a>';                    

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
                    if (
                        ($this->isCartePayable($this->numeroDefenseur, $Carte)) 
                        && ($this->effets->jouerPossible($this->numeroDefenseur))
                        )
                    {
                        if ($Carte->getTypeCarte()->getTag()=='STRIKE') 
                        {
                            $defense = $Carte->getIntercept()+$this->effets->bonusDefense($this->numeroDefenseur,$this->numeroAttaquant);  
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
                            $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'jouer')).'">Jouer</a>';
                        }
                    }
                }
                $choixpossibles = array();
                $choixpossibles = array_merge(
                    $this->effets->reflipsPossible($JoueurBas),
                    $this->effets->deployPossible($this->numeroDefenseur),
                    $this->effets->choixPossible($this->numeroDefenseur)
                    );

                foreach ($choixpossibles as $effet => $libelle) {
                    $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => $effet)).'">'. $libelle .'</a>';                    
                }

                $pitchPossible = $this->effets->pitchPossible($JoueurBas);
                $focusPossible = $this->effets->focusPossible($JoueurBas);
                if ($focusPossible){
                    $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'focuser')).'">Focus</a>';                    
                }
                if ($pitchPossible){
                    $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'pitcher')).'">Pitch</a>';
                }
                if ((!$pitchPossible) && (!$focusPossible)) {
                    $action[] = '<a href="'.$this->router->generate('jeus_quickstrike_partie_choix_effet',array('id' => $this->Partie->getId(),'effet' => 'discarder')).'">Discard</a>';                    
                }
                break;
        }                
            
        return $action;
    }





}
