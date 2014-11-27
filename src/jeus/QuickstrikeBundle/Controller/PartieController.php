<?php

namespace jeus\QuickstrikeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/* Entity */
use jeus\QuickstrikeBundle\Entity\Deck;
use jeus\QuickstrikeBundle\Entity\Partie;

/* Form */


class PartieController extends Controller {

    private $em;

    public function indexAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if (($Joueur === null) || ($Joueur=='anon.')) {
            return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
        } else {
            $em = $this->getDoctrine()->getManager();
            //$listeJoueur = $em->getRepository('jeusJoueurBundle:Joueur')->findBy(array('enAttenteQuickstrike' => true));
            $listeJoueur = $em->getRepository('jeusJoueurBundle:Joueur')->findJoueurEnAttente($Joueur);
            $listePartie = $em->getRepository('jeusQuickstrikeBundle:Partie')->findPartieByJoueur($Joueur);
            
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
        $em = $this->getDoctrine()->getManager();
        $DeckValides = $em->getRepository('jeusQuickstrikeBundle:Deck')->findBy(array('joueur' => $Joueur, 'valide' => true));
        if ($DeckValides != null) {
            $Joueur->setEnAttenteQuickstrike(true);
            $em->persist($Joueur);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
    }

    public function joueurAnnulerAttenteAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $Joueur->setEnAttenteQuickstrike(false);
        $em->persist($Joueur);
        $em->flush();
        return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
    }

    public function joueurAffronterAction($Adversaire) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        if (($Adversaire != null) && ($Joueur != null)) {
            $Adversaire->setEnAttenteQuickstrike(false);
            $Joueur->setEnAttenteQuickstrike(false);
            $Partie = new Partie($Joueur,$Adversaire);
            $em->persist($Joueur);
            $em->persist($Adversaire);
            $em->persist($Partie);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
    }
    
    public function entrainementAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        if ($Joueur != null) {
            $Partie = new Partie($Joueur,$Joueur);
            $em->persist($Partie);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
    }
    
    public function inverserAction(Partie $Partie) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        if (($Joueur == $Partie->getJoueur1()) && ($Joueur == $Partie->getJoueur2())) {
            if ($Partie->getJoueur1()->getId()==$Partie->getJoueur2()->getId()) {
                $JoueurBas = ($Partie->getJoueurBas() != null)?$Partie->getJoueurBas():1;
            } else {
                $JoueurBas = ($Joueur == $Partie->getJoueur1())?1:2;
            }
            if ($Partie->getJoueurBas()==2) {
                $Partie->setJoueurBas(1);
            } else {
                $Partie->setJoueurBas(2);
            }
            $em->persist($Partie);
            $em->flush();            
        }        
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
    }

    public function choixDeckAction(Partie $Partie, $idDeck) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        if (($Joueur == $Partie->getJoueur1()) && ($Joueur == $Partie->getJoueur2())) {
            $Deck = $em->getRepository('jeusQuickstrikeBundle:Deck')->find($idDeck);
            $this->choixDeck($Partie,$Deck,$Joueur);
            $em->persist($Partie);
            $em->flush();    
            return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
        } else {
            return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
        }        
    }

    public function partieAction(Partie $Partie) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        if (($Joueur == $Partie->getJoueur1()) || ($Joueur == $Partie->getJoueur2())) {
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
                
            switch ($etape) {
                case 'choix deck' :
                    $Decks = $em->getRepository('jeusQuickstrikeBundle:Deck')->findBy(array('joueur' => $Joueur, 'valide' => true));
                    break;
                case 'choix attaquant':
                    $choixPossible[] = 'attaquer';
                    $choixPossible[] = 'defendre';
                    break;
            }                
            
            return $this->render('::partie.html.twig', array(
                        'Partie' => $Partie->getPartieAffichee($Joueur),
                        'jeu' => 'quickstrike',
                        'inversable' => $Partie->getJoueur1()->getId()==$Partie->getJoueur2()->getId(),
                        'listeDecks' => (isset($Decks)) ? $Decks : null,
            ));
        } else {
            return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
        }        
    }


    // fonction de gestion de la partie

    private function choixDeck($Partie,$Deck, $Joueur)
    {
        if ($Deck->getValide()) {
            foreach ($Deck->getCartes() as $CarteDeck) {
                $Carte = $CarteDeck->getCarte();
                $CartePartie = new CartePartie($Carte,$Partie,$Partie->JoueurConcerne($Joueur),'DECK');
                $Partie->addCartePartie($CartePartie);
            }
            $this->melangerDeck($Partie,$Partie->JoueurConcerne($Joueur));
            $Partie->setEtape($Joueur, 'attenteDebut');
        }
    }


    private function melangerEmplacement($Partie,$joueurConcerne,$emplacement='DECK') {
        $CarteParties = $this->em
        ->getRepository('jeusQuickstrikeBundle:CartePartie')
        ->findBy(array(
            'Partie' => $Partie, 'joueurConcerne' => $joueurConcerne, 'emplacement' => $emplacement
            )
            ,array('position'=>'ASC')
        );

        $positions = array();       
        $iteration = 0;
        foreach($PartieCartes as $CartePartie) {
            $iteration++;            
            $positions[$iteration] = $iteration;
        }

        for ($i = 1; $i <= 5; $i++) {
            shuffle($positions);
        }
        $iteration = 0;
        foreach($PartieCartes as $CartePartie) {
            $CartePartie->setPosition($positions[$iteration]);
            $iteration++;
        }
        // on enregistre car au début de la fonction on va chercher les infos en bdd
        $this->em->flush();
    }

    public function deplacerCarte($Partie,$joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal='DISCARD',$melanderDestination=false) {
        $CarteParties = $this->em
        ->getRepository('jeusQuickstrikeBundle:CartePartie')
        ->findBy(array(
            'Partie' => $Partie, 'joueurConcerne' => $joueurConcerne, 'emplacement' => $emplacementOrigine
            )
            ,array('position'=>'ASC')
        );
        $order = ($emplacementFinal=='DISCARD') ? 'DESC' : 'ASC';
        $CarteFinals = $this->em
        ->getRepository('jeusQuickstrikeBundle:CartePartie')
        ->findBy(array(
            'Partie' => $Partie, 'joueurConcerne' => $joueurConcerne, 'emplacement' => $emplacementFinal
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

        $CarteDeplacees = array();
        foreach($CarteParties as $CartePartie) {
            if ($nombre<=0) 
                break;

            $CartePartie->setPosition($position);
            $position++;

            $nombre--;
        }
        $this->em->flush();
        if ($melanderDestination) {
            $this->melangerEmplacement($Partie,$joueurConcerne,$emplacementFinal);
        }
    }


}
