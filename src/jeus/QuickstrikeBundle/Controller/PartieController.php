<?php

namespace jeus\QuickstrikeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

/* Entity */
use jeus\QuickstrikeBundle\Entity\Deck;
use jeus\QuickstrikeBundle\Entity\Partie;
use jeus\QuickstrikeBundle\Entity\CartePartie;

/* Form */


class PartieController extends Controller {

    private $em;
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
            $servicePartie = $this->get('jeus_quickstrike_partie');
            $servicePartie->chargement($Partie,$Joueur);
            $servicePartie->choixDeck($Deck);
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
        $serviceSaveBdd = $this->get('jeus_quickstrike_saveBdd');
        $serviceSaveBdd->sauvegardeBdd();
        if (($Joueur == $Partie->getJoueur1()) || ($Joueur == $Partie->getJoueur2())) {
            $servicePartie = $this->get('jeus_quickstrike_partie');
            $servicePartie->chargement($Partie,$Joueur);
            $servicePartie->gestionPile();
            $this->em->persist($Partie);
            $this->em->flush();
            
            $choixPossibles = $servicePartie->actionPossibles();
            $carteJoueurs = array();
            $carteAdversaires = array();
            $CarteParties = $this->em
                                 ->getRepository('jeusQuickstrikeBundle:CartePartie')
                                 ->findBy(array(
                                      'Partie' => $Partie
                                      )
                                      ,array('numeroJoueur'=>'ASC', 'emplacement'=>'ASC', 'position'=>'ASC')
                                    );

            $parametres = array(
                'chamberVisible1' => ($Partie->getJoueurZoneEnCours(1) == 'CHAMBER'),
                'chamberVisible2' => ($Partie->getJoueurZoneEnCours(2) == 'CHAMBER'),
                'deckVisible1' => $servicePartie->deckVisible(1),
                'deckVisible2' => $servicePartie->deckVisible(2),
                    );
            $parametreAdverses = $parametres;
            $parametreAdverses['adverse'] = true;
            foreach($CarteParties as $CartePartie) {
                $carte = array();
                $carte['id'] = $CartePartie->getId();
                $carte['lien'] = $CartePartie->getLien($parametres);
                if ($CartePartie->getEmplacement()!='OPENING') {
                    if (
                        ($CartePartie->getEmplacement() == $Partie->getJoueurZoneEnCours($CartePartie->getNumeroJoueur())) 
                        && (
                            ($CartePartie->getCarte()->getTypeCarte()->getTag()=='STRIKE')
                            || ($CartePartie->getCarte()->getTypeCarte()->getTag()=='CHAMBER')
                            )
                        )
                        {
                        $etape = $Partie->getEtape($CartePartie->getNumeroJoueur());
                        if (($etape=='defense') || ($etape == 'utilisationChamber')) {
                            $carte['hint'] = 'intercept : ' . $servicePartie->interceptEnCours();
                        } else {
                            $carte['hint'] = 'force : ' . $servicePartie->attaqueEnCours();
                        }
                    }
                    if ($servicePartie->numeroJoueur==$CartePartie->getNumeroJoueur()) {
                        $carte['agrandi'] = $CartePartie->getLienAgrandi($parametres);
                        if ((!isset($carteJoueurs[strtolower($CartePartie->getEmplacement())])) || ($CartePartie->getEmplacement()=='AVANTAGE'))
                            $carteJoueurs[strtolower($CartePartie->getEmplacement())][]=$carte;
                    } else {
                        $carte['agrandi'] = $CartePartie->getLienAgrandi($parametreAdverses);
                        if ((!isset($carteAdversaires[strtolower($CartePartie->getEmplacement())])) || ($CartePartie->getEmplacement()=='AVANTAGE'))
                            $carteAdversaires[strtolower($CartePartie->getEmplacement())][]=$carte;    
                    }
                }
            }

            $emplacementCharges = array();
            $emplacementChargeAdversaires = array();
            if ($Partie->isZoneChargee($servicePartie->numeroJoueur,'CHAMBER'))
                $emplacementCharges['chamber'] = 'chargee';
            if ($Partie->isZoneChargee($servicePartie->numeroJoueur,'DECK'))
                $emplacementCharges['deck'] = 'chargee';
            if ($Partie->isZoneChargee($servicePartie->numeroJoueur,'DISCARD'))
                $emplacementCharges['discard'] = 'chargee';
            if ($Partie->isZoneChargee($servicePartie->numeroAdversaire,'CHAMBER'))
                $emplacementChargeAdversaires['chamber'] = 'chargee';
            if ($Partie->isZoneChargee($servicePartie->numeroAdversaire,'DECK'))
                $emplacementChargeAdversaires['deck'] = 'chargee';
            if ($Partie->isZoneChargee($servicePartie->numeroAdversaire,'DISCARD'))
                $emplacementChargeAdversaires['discard'] = 'chargee';

            $energiedisponibles = array();
            $energiedisponibles['energie_verte_disponible'] = $servicePartie->energiedisponible($servicePartie->numeroJoueur,'VERTE');
            $energiedisponibles['energie_jaune_disponible'] = $servicePartie->energiedisponible($servicePartie->numeroJoueur,'JAUNE');
            $energiedisponibles['energie_rouge_disponible'] = $servicePartie->energiedisponible($servicePartie->numeroJoueur,'ROUGE');
            $energiedisponibles['energie_verte_disponible-adverse'] = $servicePartie->energiedisponible($servicePartie->numeroAdversaire,'VERTE');
            $energiedisponibles['energie_jaune_disponible-adverse'] = $servicePartie->energiedisponible($servicePartie->numeroAdversaire,'JAUNE');
            $energiedisponibles['energie_rouge_disponible-adverse'] = $servicePartie->energiedisponible($servicePartie->numeroAdversaire,'ROUGE');
            $choixPossibles = $servicePartie->actionPossibles();

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
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $servicePartie = $this->get('jeus_quickstrike_partie');
        $servicePartie->chargement($Partie,$Joueur);
        $this->em->persist($Partie);
        $joueurConcerne = $servicePartie->numeroJoueur;

        if (($effet=='avantager') || ($effet=='recruter') || ($effet=='contre_attaquer') || ($effet=='jouer_chamber') || (strpos($effet,'deploy_')!==false)) {
            $servicePartie->payer($joueurConcerne,$effet=='jouer_chamber');
        }

        if (($effet=='attaquer') || ($effet=='defendre')) {
            $numeroAttaquant = $servicePartie->numeroJoueur($effet=='defendre');
            $servicePartie->attaquer($numeroAttaquant);
        }

        if ($effet=='no_chamber') {
            $servicePartie->noChamber();
        }

        if (($effet=='avantager') || ($effet=='recruter')) {
            $servicePartie->jouer($joueurConcerne,$effet);
        }

        if (($effet=='contre_attaquer') || ($effet=='jouer_chamber')) {
            $servicePartie->contreAttaquer($joueurConcerne,$effet=='jouer_chamber');
        }

        if ($effet=='focuser') {
            $servicePartie->focuserPitcher($joueurConcerne,'focus');
        }

        if ($effet=='pitcher') {
            $servicePartie->focuserPitcher($joueurConcerne,'pitch');
        }

        if ($effet=='discarder') {
            $servicePartie->focuserPitcher($joueurConcerne,'discard');
        }

        if (strpos($effet,'reflip_')!==false) {
            $servicePartie->focuserPitcher($joueurConcerne,$effet);
        }

        if (strpos($effet,'deploy_')!==false) {
            $servicePartie->deployer($joueurConcerne,$effet);
        }
        $Partie->setDateDerniereAction(new \Datetime());
        $this->em->flush();

        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
    }

    public function partieTimestampAction(Partie $Partie) {
        $retour = array(
            'timestamp' => $Partie->getDateDerniereAction()->getTimestamp(),
            /*'html' =>
            $this->renderView('AppBundle:BackOffice:formulaire-code-agence.html.twig', array(
                'titre' => 'Paramètre Code Agence',
                'nom' => 'code_agence',
                'Agences' => $CodeAgences,
                'fields' => ParametreCodeAgence::getFields(),
                'formulaire' => false,
                'mode' => 'editer',
                'paginationAgence' => $pagination,
            ))*/
        );

        return new JsonResponse($retour);
    }

}
