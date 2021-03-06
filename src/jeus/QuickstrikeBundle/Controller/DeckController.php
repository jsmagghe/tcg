<?php

namespace jeus\QuickstrikeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/* Entity */
use jeus\QuickstrikeBundle\Entity\Deck;
use jeus\QuickstrikeBundle\Entity\CarteDeck;

/* Form */
use jeus\QuickstrikeBundle\Form\SelecteurType;



class DeckController extends Controller {

    public function deckListeAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if (($Joueur === null) || ($Joueur=='anon.')) {
            return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
        } else {
            $em = $this->getDoctrine()->getManager();
            $ListeDecks = $em->getRepository('jeusQuickstrikeBundle:Deck')->findByJoueur($Joueur);

            return $this->render('::decks.html.twig', array(
                        'liste' => $ListeDecks,
                        'nom' => 'deck',
                        'jeu' => 'quickstrike',
            ));
        }
    }

    public function deckCreerAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if (($Joueur === null) || ($Joueur=='anon.')) {
            return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
        } else {
            $em = $this->getDoctrine()->getManager();
            $Deck = new Deck();
            $Deck->setNom('Nouveau deck');
            $Deck->setJoueur($Joueur);
            $Deck->setValide(false);
            $em->persist($Deck);
            $em->flush();
            return $this->redirect($this->generateUrl('jeus_quickstrike_deck_editer', array('id' => $Deck->getId())));
        }
    }

    public function deckEditerAction(Deck $Deck) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if ($Joueur != $Deck->getJoueur()) {
            return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
        } else {
            $tableau = array();
            $em = $this->getDoctrine()->getManager();
            $Request = $this->getRequest();

            $formSelecteur = $this->createForm(new SelecteurType());
            $filtre = array();
            if ($Request->getMethod() == 'POST') {
                $formSelecteur->handleRequest($Request);

                if ($formSelecteur->isValid()) {
                    $filtre['extension'] = $formSelecteur->get('extension')->getData();
                    $filtre['typeCarte'] = $formSelecteur->get('typeCarte')->getData();
                    $filtre['traitCarte'] = $formSelecteur->get('traitCarte')->getData();
                    $filtre['attaque'] = $formSelecteur->get('attaque')->getData();
                    $filtre['intercept'] = $formSelecteur->get('intercept')->getData();
                    $filtre['effet'] = $formSelecteur->get('effet')->getData();
                    $filtre['page'] = $formSelecteur->get('page')->getData();
                    $filtre['idChamber'] = $em->getRepository('jeusQuickstrikeBundle:TypeCarte')->findOneByTag('CHAMBER')->getId();
                }
            } else {
                    $filtre['extension'] = $em->getRepository('jeusQuickstrikeBundle:Extension')->findByLibelle('Shaman King');
            }
            $tableau['filtre'] = $filtre;
            $tableau['form'] = $formSelecteur;

            $serviceCarte = $this->get('jeus_quickstrike_carte');
            $tableau = $serviceCarte->rechercheCarte($tableau);

            $Cartes = $tableau['Cartes'];
            $nbPage = $tableau['nbPage'];
            $page = $tableau['page'];

            return $this->render('::cartes.html.twig', array(
                        'Deck' => $Deck,
                        'cartes' => $Cartes,
                        'form' => $formSelecteur->createView(),
                        'nom' => 'deck',
                        'jeu' => 'quickstrike',
                        'nbPage' => $nbPage,
                        'page' => $page,
            ));
        }
    }

    public function deckRenommerAction(Deck $Deck) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if ($Deck->getJoueur()!=$Joueur) {
            return Response(0);
        }
        $Request = $this->getRequest();
        if ($Request->isXmlHttpRequest()) {
            $Deck->setNom($Request->request->get('nom'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($Deck);
            $em->flush();
            return $this->render('::deck_ajax.html.twig', array(
                        'Deck' => $Deck,
                        'nom' => 'deck',
                        'jeu' => 'quickstrike',
            ));
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
    }

    public function deckAjouterCarteAction(Deck $Deck) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if ($Deck->getJoueur()!=$Joueur) {
            return Response(0);
        }
        $Request = $this->getRequest();
        if ($Request->isXmlHttpRequest()) {
            $idCarte = $Request->request->get('idCarte');
            $em = $this->getDoctrine()->getManager();
            $Carte = $em->getRepository('jeusQuickstrikeBundle:Carte')->find($idCarte);
            $CarteDeck = new CarteDeck();
            $CarteDeck->setCarte($Carte);
            $CarteDeck->setDeck($Deck);

            $erreur = $Deck->carteAjoutable($CarteDeck);
            if ($erreur=='') {
                $em->persist($CarteDeck);
                $em->flush();
                $Deck->addCarte($CarteDeck);
            }

            $em->persist($Deck);
            $em->flush();
            $em->refresh($Deck);
            $avertissement = $Deck->isValide();
            $em->flush();
            $DeckValides = $em->getRepository('jeusQuickstrikeBundle:Deck')->findBy(array('joueur'=> $Joueur,'valide'=>true));
            $Joueur->setIsDeckQuickstrikeDisponible($DeckValides!=null);
            $em->persist($Joueur);
            $em->flush();
            
            if ($avertissement == '') 
                $avertissement ='deck valide';
            return $this->render('::deck_ajax.html.twig', array(
                        'Deck' => $Deck,
                        'nom' => 'deck',
                        'jeu' => 'quickstrike',
                        'erreur' => $erreur,
                        'avertissement' => $avertissement,
            ));
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
    }

    public function deckSupprimerCarteAction(Deck $Deck) {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if ($Deck->getJoueur()!=$Joueur) {
            return Response(0);
        }
        $Request = $this->getRequest();
        if ($Request->isXmlHttpRequest()) {
            $idCarte = $Request->request->get('idCarte');
            $em = $this->getDoctrine()->getManager();
            $Cartedeck = $em->getRepository('jeusQuickstrikeBundle:Cartedeck')->find($idCarte);
            $em->remove($Cartedeck);
            // s'il la carte supprimer est une chamber on supprime toutes les cartes
            if ($Cartedeck->getCarte()->getTypeCarte()->getTag()=='CHAMBER') {
                foreach($Deck->getCartes() as $CarteDeckEnCours) {
                    $em->remove($CarteDeckEnCours);
                }
            }
            $em->flush();
            $em->refresh($Deck);
            $avertissement = $Deck->isValide();
            $em->flush();
            $DeckValides = $em->getRepository('jeusQuickstrikeBundle:Deck')->findBy(array('joueur'=> $Joueur,'valide'=>true));
            $Joueur->setIsDeckQuickstrikeDisponible($DeckValides!=null);
            $em->persist($Joueur);
            $em->flush();

            if ($avertissement == '') 
                $avertissement ='deck valide';
            
            return $this->render('::deck_ajax.html.twig', array(
                        'Deck' => $Deck,
                        'nom' => 'deck',
                        'jeu' => 'quickstrike',
                        'avertissement' => $avertissement,
            ));
        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
    }

    public function rechercheCarte($Request) {
        $tableau = array();
        $em = $this->getDoctrine()->getManager();
        $formSelecteur = $this->createForm(new SelecteurType());
        $filtre = array();
        $Request = $this->getRequest();
        if ($Request->getMethod() == 'POST') {
            $formSelecteur->bind($Request);

            if ($formSelecteur->isValid()) {
                $filtre['extension'] = $formSelecteur->get('extension')->getData();
                $filtre['typeCarte'] = $formSelecteur->get('typeCarte')->getData();
                $filtre['traitCarte'] = $formSelecteur->get('traitCarte')->getData();
                $filtre['attaque'] = $formSelecteur->get('attaque')->getData();
                $filtre['intercept'] = $formSelecteur->get('intercept')->getData();
                $filtre['effet'] = $formSelecteur->get('effet')->getData();
                //$filtre['nombreCarte'] = $formSelecteur->get('nombreCarte')->getData();
                $filtre['idChamber'] = $em->getRepository('jeusQuickstrikeBundle:TypeCarte')->findOneByTag('CHAMBER')->getId();
            }
        }
        $tableau['filtre'] = $filtre;
        $tableau['form'] = $formSelecteur;
        $tableau['Cartes'] = $em->getRepository('jeusQuickstrikeBundle:Carte')->findByCritere($filtre);


        return $tableau;
    }

    
    
}
