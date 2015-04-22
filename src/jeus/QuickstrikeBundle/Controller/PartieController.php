<?php

namespace jeus\QuickstrikeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/* Entity */
use jeus\QuickstrikeBundle\Entity\Deck;
use jeus\QuickstrikeBundle\Entity\Partie;
use jeus\QuickstrikeBundle\Entity\CartePartie;

/* Form */


class PartieController extends Controller {

    private $em;
    private $CarteEnJeus;
    private $Partie;
    private $Joueur;

    public function indexAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if (($Joueur === null) || ($Joueur=='anon.')) {
            return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
        } else {
            $this->em = $this->getDoctrine()->getManager();
            //$listeJoueur = $this->em->getRepository('jeusJoueurBundle:Joueur')->findBy(array('enAttenteQuickstrike' => true));
            $listeJoueur = $this->em->getRepository('jeusJoueurBundle:Joueur')->findJoueurEnAttente($Joueur);
            $listePartie = $this->em->getRepository('jeusQuickstrikeBundle:Partie')->findPartieByJoueur($Joueur);
            
            return $this->render('::parties.html.twig', array(
                        'Joueur' => $Joueur,
                        'jeu' => 'quickstrike',
                        'liste' => $listeJoueur,
                        'listePartie' => $listePartie,
            ));
        }

        return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
    }
    
    public function joueurEnAttenteAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $this->em = $this->getDoctrine()->getManager();
        $DeckValides = $this->em->getRepository('jeusQuickstrikeBundle:Deck')->findBy(array('joueur' => $Joueur, 'valide' => true));
        if ($DeckValides != null) {
            $Joueur->setEnAttenteQuickstrike(true);
            $this->em->persist($Joueur);
            $this->em->flush();
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
    }

    public function joueurAnnulerAttenteAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $this->em = $this->getDoctrine()->getManager();
        $Joueur->setEnAttenteQuickstrike(false);
        $this->em->persist($Joueur);
        $this->em->flush();
        return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
    }

    public function joueurAffronterAction($idAdversaire) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $this->em = $this->getDoctrine()->getManager();
        $Adversaire = $this->em->getRepository('jeusJoueurBundle:Joueur')->find($idAdversaire); 
        if (($Adversaire != null) && ($Joueur != null)) {
            $Adversaire->setEnAttenteQuickstrike(false);
            $Joueur->setEnAttenteQuickstrike(false);
            $Partie = new Partie($Joueur,$Adversaire);
            $this->em->persist($Joueur);
            $this->em->persist($Adversaire);
            $this->em->persist($Partie);
            $this->em->flush();
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
    }
    
    public function entrainementAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $this->em = $this->getDoctrine()->getManager();
        if ($Joueur != null) {
            $Partie = new Partie($Joueur,$Joueur);
            $this->em->persist($Partie);
            $this->em->flush();
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
    }
    
    public function inverserAction(Partie $Partie) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $this->em = $this->getDoctrine()->getManager();
        if (($Joueur == $Partie->getJoueur1()) && ($Joueur == $Partie->getJoueur2())) {
            if ($Partie->getJoueur1()->getId()==$Partie->getJoueur2()->getId()) {
                $JoueurBas = ($Partie->getJoueurBas() != null)?$Partie->getJoueurBas():1;
            } else {
                $JoueurBas = ($Joueur == $Partie->getJoueur1())?1:2;
            }
            if ($JoueurBas==2) {
                $Partie->setJoueurBas(1);
            } else {
                $Partie->setJoueurBas(2);
            }
            $this->em->persist($Partie);
            $this->em->flush();            
        }        
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
    }

    public function choixDeckAction(Partie $Partie, $idDeck) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $this->em = $this->getDoctrine()->getManager();
        if (($Joueur == $Partie->getJoueur1()) || ($Joueur == $Partie->getJoueur2())) {
            $Deck = $this->em->getRepository('jeusQuickstrikeBundle:Deck')->find($idDeck);
            $this->choixDeck($Partie,$Deck,$Joueur);
            $this->em->persist($Partie);
            $this->em->flush();    
            return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
        } else {
            return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
        }        
    }

    public function partieAction(Partie $Partie) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $this->em = $this->getDoctrine()->getManager();
        if (($Joueur == $Partie->getJoueur1()) || ($Joueur == $Partie->getJoueur2())) {
            $this->gestionPile($Partie);
            $this->em->persist($Partie);
            $this->em->flush();
            
            $choixPossibles = $this->actionPossibles($Partie,$Joueur);
            $carteJoueurs = array();
            $carteAdversaires = array();
            $CarteParties = $this->em
                                 ->getRepository('jeusQuickstrikeBundle:CartePartie')
                                 ->findBy(array(
                                      'Partie' => $Partie
                                      )
                                      ,array('numeroJoueur'=>'ASC', 'emplacement'=>'ASC', 'position'=>'ASC')
                                    );

            $carte = array();
            foreach($CarteParties as $CartePartie) {
                $carte['id'] = $CartePartie->getId();
                $carte['lien'] = $CartePartie->getLien();
                if ($CartePartie->getEmplacement()!='OPENING') {
                    if ($this->numeroJoueur($Partie,$Joueur)==$CartePartie->getNumeroJoueur()) {
                        $carte['agrandi'] = $CartePartie->getLienAgrandi();
                        if ((!isset($carteJoueurs[strtolower($CartePartie->getEmplacement())])) || ($CartePartie->getEmplacement()=='AVANTAGE'))
                            $carteJoueurs[strtolower($CartePartie->getEmplacement())][]=$carte;
                    } else {
                        $carte['agrandi'] = $CartePartie->getLienAgrandi(true);
                        if ((!isset($carteAdversaires[strtolower($CartePartie->getEmplacement())])) || ($CartePartie->getEmplacement()=='AVANTAGE'))
                            $carteAdversaires[strtolower($CartePartie->getEmplacement())][]=$carte;    
                    }
                }
            }

            $emplacementCharges = array();
            $emplacementChargeAdversaires = array();
            if ($Partie->isZoneChargee($this->numeroJoueur($Partie,$Joueur),'CHAMBER'))
                $emplacementCharges['chamber'] = 'chargee';
            if ($Partie->isZoneChargee($this->numeroJoueur($Partie,$Joueur),'DECK'))
                $emplacementCharges['deck'] = 'chargee';
            if ($Partie->isZoneChargee($this->numeroJoueur($Partie,$Joueur),'DISCARD'))
                $emplacementCharges['discard'] = 'chargee';
            if ($Partie->isZoneChargee($this->numeroJoueur($Partie,$Joueur,true),'CHAMBER'))
                $emplacementChargeAdversaires['chamber'] = 'chargee';
            if ($Partie->isZoneChargee($this->numeroJoueur($Partie,$Joueur,true),'DECK'))
                $emplacementChargeAdversaires['deck'] = 'chargee';
            if ($Partie->isZoneChargee($this->numeroJoueur($Partie,$Joueur,true),'DISCARD'))
                $emplacementChargeAdversaires['discard'] = 'chargee';

            $energiedisponibles = array();
            $energiedisponibles['energie_verte_disponible'] = $this->energiedisponible($this->numeroJoueur($Partie,$Joueur),'VERTE');
            $energiedisponibles['energie_jaune_disponible'] = $this->energiedisponible($this->numeroJoueur($Partie,$Joueur),'JAUNE');
            $energiedisponibles['energie_rouge_disponible'] = $this->energiedisponible($this->numeroJoueur($Partie,$Joueur),'ROUGE');
            $energiedisponibles['energie_verte_disponible-adverse'] = $this->energiedisponible($this->numeroJoueur($Partie,$Joueur,true),'VERTE');
            $energiedisponibles['energie_jaune_disponible-adverse'] = $this->energiedisponible($this->numeroJoueur($Partie,$Joueur,true),'JAUNE');
            $energiedisponibles['energie_rouge_disponible-adverse'] = $this->energiedisponible($this->numeroJoueur($Partie,$Joueur,true),'ROUGE');

            return $this->render('::partie.html.twig', array(
                        'carteJoueurs' => $carteJoueurs,
                        'carteAdversaires' => $carteAdversaires,
                        'jeu' => 'quickstrike',
                        'inversable' => $Partie->getJoueur1()->getId()==$Partie->getJoueur2()->getId(),
                        'choixPossibles' => $choixPossibles,
                        'Partie' => $Partie,
                        'emplacementInclineJoueurs' => $emplacementCharges,
                        'emplacementInclineAdversaires' => $emplacementChargeAdversaires,
                        'energieDisponibles' => $energiedisponibles,
            ));
        } else {
            return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
        }        
    }

    public function choixEffetAction(Partie $Partie, $effet) {
        $this->em = $this->getDoctrine()->getManager();
        $this->CarteEnJeus=null;
        $this->chargerCarteEnJeu($Partie);
        $this->em->persist($Partie);
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $joueurConcerne = $this->numeroJoueur($Partie,$Joueur);

        if (($effet=='avantager') || ($effet=='recruter') || ($effet=='contre_attaquer')) {
            $this->payer($Partie,$joueurConcerne);
        }

        if (($effet=='attaquer') || ($effet=='defendre')) {
            $numeroAttaquant = $this->numeroJoueur($Partie,$Joueur,$effet=='defendre');
            $this->attaquer($Partie,$numeroAttaquant);
        }

        if (($effet=='avantager') || ($effet=='recruter')) {
            $this->jouer($Partie,$joueurConcerne,$effet);
        }

        if ($effet=='contre_attaquer') {
            $this->contreAttaquer($Partie,$joueurConcerne);
        }

        if ($effet=='focuser') {
            $this->focuser($Partie,$joueurConcerne,'focus');
        }

        if ($effet=='pitcher') {
            $this->focuser($Partie,$joueurConcerne,'pitch');
        }

        if ($effet=='discarder') {
            $this->focuser($Partie,$joueurConcerne,'discard');
        }
        $this->em->flush();

        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
    }











    // fonction de gestion de la partie

    private function chargerCarteEnJeu($Partie) {
        if ($this->CarteEnJeus==null) {
            $CarteEnJeus = $this->em->getRepository('jeusQuickstrikeBundle:CartePartie')
                                     ->findBy(array('Partie' => $Partie));

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

    private function numeroJoueur($Partie,$Joueur,$adversaire = false) {
        $numero = 1;
        if ($Partie->getJoueur1()->getId()==$Partie->getJoueur2()->getId()) {
            $numero = ($Partie->getJoueurBas() != null) ? $Partie->getJoueurBas() : 1;
        } else {
            $numero = ($Partie->getJoueur2()->getId()==$Joueur->getId()) ? 2 : 1;
        }
        if ($adversaire)
            $numero = ($numero==1) ? 2 : 1;

        return $numero;
    }

    private function choixDeck($Partie,$Deck, $Joueur)
    {
        if ($Deck->getValide()) {
            foreach ($Deck->getCartes() as $CarteDeck) {
                $Carte = $CarteDeck->getCarte();
                $CartePartie = new CartePartie($Carte,$Partie,$Partie->JoueurConcerne($Joueur),'DECK');
                $Partie->addCartePartie($CartePartie);
            }
            $CarteOpening = $this->em
                                 ->getRepository('jeusQuickstrikeBundle:Carte')
                                 ->findOneBy(array('nom' => 'opening attack'));
            $CartePartie = new CartePartie($CarteOpening,$Partie,$Partie->JoueurConcerne($Joueur),'OPENING');
            $Partie->addCartePartie($CartePartie);
            $this->melangerEmplacement($Partie,$Partie->JoueurConcerne($Joueur));
            $Partie->setEtape($Joueur, 'attenteDebut');
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id'=>$Partie->getId())));
    }


    private function melangerEmplacement($Partie,$joueurConcerne,$emplacement='DECK') {
        $CarteParties = $this->em
                             ->getRepository('jeusQuickstrikeBundle:CartePartie')
                             ->findBy(array(
                                  'Partie' => $Partie, 'numeroJoueur' => $joueurConcerne, 'emplacement' => $emplacement
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

    private function deplacerCarte($Partie,$joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal='DISCARD',$melanderDestination=false) {
        $CarteParties = $this->em
        ->getRepository('jeusQuickstrikeBundle:CartePartie')
        ->findBy(array(
            'Partie' => $Partie, 'numeroJoueur' => $joueurConcerne, 'emplacement' => $emplacementOrigine
            )
            ,array('position'=>'ASC')
        );
        $order = 'DESC';
        $CarteFinals = $this->em
        ->getRepository('jeusQuickstrikeBundle:CartePartie')
        ->findBy(array(
            'Partie' => $Partie, 'numeroJoueur' => $joueurConcerne, 'emplacement' => $emplacementFinal
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
            $this->melangerEmplacement($Partie,$joueurConcerne,$emplacementFinal);
        }
        // s'il n'y a plus de carte dans le deck on récupère toutes les cartes de la discard que l'on met dans le deck
        if (($nombre>0) && ($emplacementOrigine=='DECK')) {
            $this->deplacerCarte($Partie,$joueurConcerne,99,'DISCARD','DECK',true);
            $this->deplacerCarte($Partie,$joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal,$melanderDestination);
            $this->deplacerCarte($Partie,$joueurConcerne,5,'DECK','DISCARD');
        }
    }

    private function demarragePartie($Partie,$joueurConcerne) {
        $this->melangerEmplacement($Partie,$joueurConcerne);
        $this->deplacerCarte($Partie,$joueurConcerne,5,'DECK','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,2,'DECK','ENERGIE_VERTE');
        $this->deplacerCarte($Partie,$joueurConcerne,2,'DECK','ENERGIE_JAUNE');
        $this->deplacerCarte($Partie,$joueurConcerne,2,'DECK','ENERGIE_ROUGE');
    }

    private function viderCarte($Partie,$joueurConcerne) {
        //$this->deplacerCarte($Partie,$joueurConcerne,99,'AVANTAGE,STRIKE_VERT,STRIKE_JAUNE,STRIKE_ROUGE','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,99,'AVANTAGE','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,99,'STRIKE_VERT','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,99,'STRIKE_JAUNE','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,99,'STRIKE_ROUGE','DISCARD');
    }

    private function attaquer($Partie,$joueurConcerne,$depart = true) {
        if ($depart) 
            $this->viderCarte($Partie,$joueurConcerne);
            
        $Partie->setJoueurZoneEnCours(($joueurConcerne==1)?2:1,'STRIKE_VERT');
        $this->viderCarte($Partie,($joueurConcerne==1)?2:1);
        //$this->deplacerCarte($Partie,($joueurConcerne==1)?2:1,1,'DECK',$Partie->getJoueurZoneEnCours($joueurConcerne));
        $this->deplacerCarte($Partie,($joueurConcerne==1)?2:1,1,'DECK',$Partie->getJoueurZoneEnCours(($joueurConcerne==1)?2:1));
        if ($depart) {
            $this->deplacerCarte($Partie,$joueurConcerne,1,'OPENING','STRIKE_VERT');
            $this->deplacerCarte($Partie,($joueurConcerne==1)?2:1,1,'DISCARD','ENERGIE_VERTE');
        } else {
            if ($Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_ROUGE') {
                $this->deplacerCarte($Partie,($joueurConcerne==1)?2:1,1,'DISCARD','ENERGIE_ROUGE');
            }
            if (
                ($Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_ROUGE')
                &&($Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_JAUNE')
                ) {
                $this->deplacerCarte($Partie,($joueurConcerne==1)?2:1,1,'DISCARD','ENERGIE_JAUNE');
            }
            if (
                ($Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_ROUGE')
                &&($Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_JAUNE')
                &&($Partie->getJoueurZoneEnCours($joueurConcerne)=='STRIKE_VERT')
                ) {
                $this->deplacerCarte($Partie,($joueurConcerne==1)?2:1,1,'DISCARD','ENERGIE_VERTE');
            }
        }

        if ($joueurConcerne==1) {
            $Partie->setJoueur1Etape('attente');
            $Partie->setJoueur2Etape('defense');
        } else {
            $Partie->setJoueur1Etape('defense');
            $Partie->setJoueur2Etape('attente');
        }
    }

    private function zoneSuivante($zone) {
        $zoneSuivante = 'STRIKE_VERT';
        switch ($zone) {
            case 'STRIKE_VERT' : 
                $zoneSuivante = 'STRIKE_JAUNE';
                break;
            case 'STRIKE_JAUNE' : 
                $zoneSuivante = 'STRIKE_ROUGE';
                break;
            case 'STRIKE_ROUGE' : 
                $zoneSuivante = 'POINT';
                break;
        }
        return $zoneSuivante;
    }

    private function zoneCorrespondante($zone,$type='STRIKE') {
        $zoneCorrespondante = 'STRIKE_VERT';
        switch ($zone) {
            case 'STRIKE_VERT' : 
                $zoneCorrespondante = '_VERTE';
                break;
            case 'STRIKE_JAUNE' : 
                $zoneCorrespondante = '_JAUNE';
                break;
            case 'STRIKE_ROUGE' : 
                $zoneCorrespondante = '_ROUGE';
                break;
        }

        $zoneCorrespondante = $type . $zoneCorrespondante;
        return $zoneCorrespondante;
    }

    private function payer($Partie,$joueurConcerne) {       
        $payable = false;
        $Carte = null;
        if (isset($this->CarteEnJeus[$joueurConcerne][$Partie->getJoueurZoneEnCours($joueurConcerne)])) {
            $CarteActive = $this->CarteEnJeus[$joueurConcerne][$Partie->getJoueurZoneEnCours($joueurConcerne)];
            $Carte = $CarteActive->getCarte();
        }

        if ($Carte) {
            $payable = $this->isCartePayable($Partie, $joueurConcerne, $Carte, true);
        }
        return $payable;
    }

    public function verificationRecrutement($Partie,$joueurConcerne,$CarteActive,$zoneEnCours,$zoneAControler) {
        if ((isset($this->CarteEnJeus[$joueurConcerne][$zoneAControler])) && ($zoneEnCours!=$zoneAControler))  {
            $Cartejeu = $this->CarteEnJeus[$joueurConcerne][$zoneAControler];
            if (
                ($Cartejeu!=null) 
                && ($Cartejeu->getCarte()!=null) 
                && ($CarteActive!=null) 
                && ($CarteActive->getCarte()!=null) 
                && ($Cartejeu->getCarte()->getNomCours()==$CarteActive->getCarte()->getNomCours()))
                $this->deplacerCarte($Partie,$joueurConcerne,99,$zoneAControler,'DISCARD');
        }
    }

    private function jouer($Partie,$joueurConcerne,$action) {
        $zoneEnCours = $Partie->getJoueurZoneEnCours($joueurConcerne);
        if (isset($this->CarteEnJeus[$joueurConcerne][$zoneEnCours]))
            $CarteActive = $this->CarteEnJeus[$joueurConcerne][$zoneEnCours];
        if ($action=='avantager') {
            $zoneCorrespondante = 'AVANTAGE';
            $Partie->chargerZone($joueurConcerne,$zoneEnCours);
        }
        if ($action=='recruter') {
            $zoneCorrespondante = $this->zoneCorrespondante($zoneEnCours,'TEAMWORK');
            $this->deplacerCarte($Partie,$joueurConcerne,99,$zoneCorrespondante,'DISCARD');
            $this->verificationRecrutement($Partie,$joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_VERTE');
            $this->verificationRecrutement($Partie,$joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_JAUNE');
            $this->verificationRecrutement($Partie,$joueurConcerne,$CarteActive,$zoneCorrespondante,'TEAMWORK_ROUGE');
        }
        $this->deplacerCarte($Partie,$joueurConcerne,1,$zoneEnCours,$zoneCorrespondante);
        $this->deplacerCarte($Partie,$joueurConcerne,1,'DECK',$zoneEnCours);
    }

    private function contreAttaquer($Partie,$joueurConcerne) {
        $zoneEnCours = $Partie->getJoueurZoneEnCours($joueurConcerne);
        $zoneCorrespondante = $this->zoneCorrespondante($zoneEnCours);
        $this->attaquer($Partie,$joueurConcerne,false);
    }

    private function focuserPitcher($Partie,$joueurConcerne,$action) {
        $zoneEnCours = $Partie->getJoueurZoneEnCours($joueurConcerne);
        $zoneSuivante = $this->zoneSuivante($zoneEnCours);
        $zoneCorrespondante = 'DISCARD';
        if ($action=='focus')              
            $zoneCorrespondante = $this->zoneCorrespondante($zoneEnCours,'ENERGIE');
        $this->deplacerCarte($Partie,$joueurConcerne,1,$zoneEnCours,$zoneCorrespondante);
        $this->descendreDeZone($Partie,$joueurConcerne);
    }

    private function focuser($Partie,$joueurConcerne) {
        $this->focuserPitcher($Partie,$joueurConcerne,'focus');
    }

    private function pitcher($Partie,$joueurConcerne) {
        $this->focuserPitcher($Partie,$joueurConcerne,'pitch');
    }

    private function discarder($Partie,$joueurConcerne) {
        $this->focuserPitcher($Partie,$joueurConcerne,'discard');
    }

    private function descendreDeZone($Partie,$joueurConcerne) {
        $zoneEnCours = $Partie->getJoueurZoneEnCours($joueurConcerne);
        $zoneSuivante = $this->zoneSuivante($zoneEnCours);
        if ( $zoneSuivante == 'POINT') {
            $this->pointPourAdversaire($Partie,$joueurConcerne);
        } else {
            $Partie->setJoueurZoneEnCours($joueurConcerne,$zoneSuivante);
            $this->deplacerCarte($Partie,$joueurConcerne,1,'DECK',$Partie->getJoueurZoneEnCours($joueurConcerne));
        }
    }

    private function pointPourAdversaire($Partie,$joueurConcerne){
        $Partie->addPointAdversaire(($joueurConcerne==1) ? 2 : 1);
        $Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_VERT');
        $this->setEtapeJoueur($Partie,$joueurConcerne,'choixAttaquant');
    }

    private function setEtapeJoueur($Partie,$joueurConcerne,$etape) {
        $Partie->setEtapeByNumero(($joueurConcerne==1) ? 2 : 1,'attente');
        $Partie->setEtapeByNumero($joueurConcerne,$etape);
    }

    private function joueurChoisi() {
        $numero = rand(1,1000);
        return ($numero<=500)? 1 : 2;
    }

    private function numeroAttaquant($Partie) {
        $numeroAttaquant = ($Partie->getJoueur1Etape()=='defense') ? 2 : 1;
        return $numeroAttaquant;
    }

    private function numeroDefenseur($Partie) {
        $numeroDefenseur = ($Partie->getJoueur1Etape()=='defense') ? 1 : 2;
        return $numeroDefenseur;
    }

    private function bonusAttaque($Partie) {
        $bonus = 0;
        if (($Partie->getJoueur1Etape()=='defense') || ($Partie->getJoueur2Etape()=='defense')) {
            $numeroDefenseur = $this->numeroDefenseur($Partie);
            $numeroAttaquant = $this->numeroAttaquant($Partie);
            $CarteEnJeus = $this->CarteEnJeus[$numeroAttaquant]['ACTIVE'];
            foreach ($CarteEnJeus as $Cartejeu) {
                $Carte = $Cartejeu->getCarte();
                if ($Carte == null) {
                    continue;
                }
            }
        }

        return $bonus;
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

    private function bonusDefense($Partie) {
        $bonus = 0;
        if (($Partie->getJoueur1Etape()=='defense') || ($Partie->getJoueur2Etape()=='defense')) {
            $numeroDefenseur = $this->numeroDefenseur($Partie);
            $numeroAttaquant = $this->numeroAttaquant($Partie);
            if ($Partie->getJoueurZoneEnCours($numeroDefenseur)!='0') {
                $CarteActive = $this->CarteEnJeus[$numeroDefenseur][$Partie->getJoueurZoneEnCours($numeroDefenseur)];
                $Carte = $CarteActive->getCarte();
                if ($Carte == null) {
                    continue;
                }

            }
        }

        return $bonus;
    }

    private function defenseChamber($Partie) {
        $attaque = 0;
        if (($Partie->getJoueur1Etape()=='defense') || ($Partie->getJoueur2Etape()=='defense')) {
            $numeroDefenseur = $this->numeroDefenseur($Partie);
            $numeroAttaquant = $this->numeroAttaquant($Partie);

                if (isset($this->CarteEnJeus[$numeroDefenseur]['CHAMBER'])) {
                    $CarteActive = $this->CarteEnJeus[$numeroDefenseur]['CHAMBER'];
                    $Carte = $CarteActive->getCarte();
                }
                else 
                    $Carte = null;

                if ($Carte == null) {
                    return 4;
                }
                $attaque += $Carte->getAttaque();  
            }
        }

        return $attaque;
    }



    private function energiedisponible($joueurConcerne,$zone) {
        if (isset($this->CarteEnJeus[$joueurConcerne]['ENERGIE_'.$zone]))
            return count($this->CarteEnJeus[$joueurConcerne]['ENERGIE_'.$zone]);
        else 
            return 0;
    }

    private function isCartePayable($Partie, $joueurConcerne, $Carte,$payer = false) {
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
                $this->deplacerCarte($Partie,$joueurConcerne,$coutVert,'ENERGIE_VERTE','DISCARD',true);
                $coutVert -= $energieVerteDisponible;
                $coutVert = ($coutVert>0) ? $coutVert : 0;
            }
            $coutJaune += $coutVert;
            if ($coutJaune>0) {
                $this->deplacerCarte($Partie,$joueurConcerne,$coutJaune,'ENERGIE_JAUNE','DISCARD',true);
                $coutJaune -= $energieJauneDisponible;
                $coutJaune = ($coutJaune>0) ? $coutJaune : 0;
            }
            $coutRouge += $coutJaune;
            if ($coutRouge>0) {
                $this->deplacerCarte($Partie,$joueurConcerne,$coutRouge,'ENERGIE_ROUGE','DISCARD',true);
            }
        }

        return $payable;
    }

    private function gestionPile($Partie){
        // si les deux joueurs ont choisis leur deck on les passe en début de partie
        if (($Partie->getJoueur1Etape()=='attenteDebut')
            && ($Partie->getJoueur2Etape()=='attenteDebut')
           ) {
            $this->demarragePartie($Partie,1);
            $this->demarragePartie($Partie,2);
            if ($this->joueurChoisi()==1) {
                $Partie->setJoueur1Etape('choixAttaquant');
                $Partie->setJoueur2Etape('attente');
            } else {
                $Partie->setJoueur2Etape('choixAttaquant');
                $Partie->setJoueur1Etape('attente');
            }
        }
    }

    private function cartesJoueur($Partie,$Joueur) 
    {
        $CartesJoueur = null;
        if ($Partie->getJoueur1()->getId()==$Partie->getJoueur2()->getId()) {
            $JoueurBas = ($Partie->getJoueurBas() != null)?$Partie->getJoueurBas():1;
        } else {
            $JoueurBas = ($Joueur == $Partie->getJoueur1())?1:2;
        }

        $cartesJoueur = $this->CarteEnJeus[$JoueurBas];
        return $cartesJoueur;
    }

    private function isChamberUtilisable($Partie, $Joueur) {
        $isUtilisable = (
            ($Partie->getEtape($Joueur) == 'utilisationChamber') 
            && ($Partie->isZoneChargee($joueurConcerne,'CHAMBER'))
            && ($Partie->isZoneChargee($joueurConcerne,'DECK'))
            && ($Partie->isZoneChargee($joueurConcerne,'DISCARD'))
            && ($this->attaqueEnCours($Partie)<=$this->defenseChamber($Partie,$Joueur))
        );

        // si on ne peut pas utiliser la chamber on passe directement à la defense
        if (
            ($Partie->getEtape($Joueur) == 'utilisationChamber') 
            && (!$isUtilisable)
            )
        {
            $this->defendre($Partie,$Joueur);
        }

        return $isUtilisable;
    }



    private function actionPossibles($Partie,$Joueur) {
        $this->CarteEnJeus=null;
        $this->chargerCarteEnJeu($Partie);
        $action = array();
        if ($Partie->getJoueur1()->getId()==$Partie->getJoueur2()->getId()) {
            $JoueurBas = ($Partie->getJoueurBas() != null)?$Partie->getJoueurBas():1;
        } else {
            $JoueurBas = ($Joueur == $Partie->getJoueur1())?1:2;
        }

        $choixPossible = array();
        if ($JoueurBas==1) {
            $etape = $Partie->getJoueur1Etape();
        } else {
            $etape = $Partie->getJoueur2Etape();
        }

        $victoire = '';
        $score = '';
        if ($this->numeroJoueur($Partie,$Joueur)==1) {
            $score = $Partie->getJoueur1Point().'-'.$Partie->getJoueur2Point();
        } else {
            $score = $Partie->getJoueur2Point().'-'.$Partie->getJoueur1Point();
        }

        if (($Partie->getPointVictoire()<=$Partie->getJoueur1Point()) || ($Partie->getPointVictoire()<=$Partie->getJoueur2Point())) {

            if ($Partie->getJoueur1Point()<$Partie->getJoueur2Point()) {
                if ($this->numeroJoueur($Partie,$Joueur)==1) {
                    $victoire = 'perdu';
                } else {
                    $victoire = 'gagné';
                }
            } elseif ($Partie->getJoueur1Point()>$Partie->getJoueur2Point()) {
                if ($this->numeroJoueur($Partie,$Joueur)==2) {
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
                $Decks = $this->em->getRepository('jeusQuickstrikeBundle:Deck')->findBy(array('joueur' => $Joueur, 'valide' => true));
                foreach($Decks as $Deck) {
                    $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_deck',array('id' => $Partie->getId(),'idDeck' => $Deck->getId())).'">'.$Deck->getNom().'</a>';
                }
                break;
            case 'choixAttaquant':
                $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'attaquer')).'">Attaquer</a>';
                $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'defendre')).'">Defendre</a>';
                break;
            case 'defense':
                $attaque = $this->attaqueEnCours($Partie);
                $numeroDefenseur = $this->numeroDefenseur($Partie);
                $numeroAttaquant = $this->numeroAttaquant($Partie);
                $CarteEnJeus = $this->CarteEnJeus[$numeroDefenseur];

                if ($Partie->getJoueurZoneEnCours($numeroDefenseur)!='0') {
                    $CarteActive = null;
                    if (isset($CarteEnJeus[$Partie->getJoueurZoneEnCours($numeroDefenseur)])) {
                        $CarteActive = $CarteEnJeus[$Partie->getJoueurZoneEnCours($numeroDefenseur)];
                        $Carte = $CarteActive->getCarte();
                    }                    
                    if ($this->isCartePayable($Partie, $numeroDefenseur, $Carte)) {
                        if ($Carte->getTypeCarte()->getTag()=='STRIKE') 
                        {
                            $defense = $Carte->getIntercept()+$this->bonusDefense($Partie);  
                            if ($defense>=$attaque) 
                                $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'contre_attaquer')).'">Contre attaquer</a>';
                        }
                        if (($Carte->getTypeCarte()->getTag()=='TEAMWORK') 
                            && ($CarteActive->getEmplacement()==$Partie->getJoueurZoneEnCours($numeroDefenseur))
                            )
                        {
                            $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'recruter')).'">Recruter</a>';
                        }
                        if (($Carte->getTypeCarte()->getTag()=='ADVANTAGE') 
                            && ($CarteActive->getEmplacement()==$Partie->getJoueurZoneEnCours($numeroDefenseur))
                            )
                        {
                            $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'avantager')).'">Jouer</a>';
                        }
                    }
                }

                $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'pitcher')).'">Pitch</a>';
                $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'focuser')).'">Focus</a>';
                if (count($action) == 0)
                    $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'discarder')).'">Discard</a>';
                break;
        }                
            
        return $action;
    }


}
