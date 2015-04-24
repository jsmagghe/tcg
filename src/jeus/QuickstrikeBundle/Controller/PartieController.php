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

}
