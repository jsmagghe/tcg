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

    public function joueurAffronterAction($Adversaire) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $this->em = $this->getDoctrine()->getManager();
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
        if (($Joueur == $Partie->getJoueur1()) && ($Joueur == $Partie->getJoueur2())) {
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
                if ($this->numeroJoueur($Partie,$Joueur)==$CartePartie->getNumeroJoueur()) {
                    if ((!isset($carteJoueurs[strtolower($CartePartie->getEmplacement())])) || ($CartePartie->getEmplacement()=='AVANTAGE'))
                        $carteJoueurs[strtolower($CartePartie->getEmplacement())][]=$carte;
                } else {
                    if ((!isset($carteAdversaires[strtolower($CartePartie->getEmplacement())])) || ($CartePartie->getEmplacement()=='AVANTAGE'))
                        $carteAdversaires[strtolower($CartePartie->getEmplacement())][]=$carte;    
                }
            }

            return $this->render('::partie.html.twig', array(
                        'carteJoueurs' => $carteJoueurs,
                        'carteAdversaires' => $carteAdversaires,
                        'jeu' => 'quickstrike',
                        'inversable' => $Partie->getJoueur1()->getId()==$Partie->getJoueur2()->getId(),
                        'choixPossibles' => $choixPossibles,
                        'Partie' => $Partie,
            ));
        } else {
            return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
        }        
    }

    public function choixEffetAction(Partie $Partie, $effet) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $this->em = $this->getDoctrine()->getManager();

        

    }











    // fonction de gestion de la partie

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

            $CartePartie->setEmplacement($emplacementFinal);
            $CartePartie->setPosition($position);
            $position++;

            $nombre--;
        }
        $this->em->flush();
        if ($melanderDestination) {
            $this->melangerEmplacement($Partie,$joueurConcerne,$emplacementFinal);
        }
    }

    private function demarragePartie($Partie,$joueurConcerne) {
        $this->melangerEmplacement($Partie,$joueurConcerne);
        $this->deplacerCarte($Partie,$joueurConcerne,5,'DECK','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,2,'DECK','ENERGIE_VERTE');
        $this->deplacerCarte($Partie,$joueurConcerne,2,'DECK','ENERGIE_JAUNE');
        $this->deplacerCarte($Partie,$joueurConcerne,2,'DECK','ENERGIE_ROUGE');
    }

    private function joueurChoisi() {
        $numero = rand(1,1000);
        return ($numero<=500)? 1 : 2;
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

    private function actionPossibles($Partie,$Joueur) {
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
        }                
            
        return $action;
    }


}
