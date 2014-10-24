<?php

namespace jeus\QuickstrikeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/* Entity */
use jeus\QuickstrikeBundle\Entity\Deck;

/* Form */


class PartieController extends Controller {

    public function indexAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if (($Joueur === null) || ($Joueur=='anon.')) {
            return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
        } else {
            $em = $this->getDoctrine()->getManager();
            $listeJoueur = $em->getRepository('jeusJoueurBundle:Joueur')->findBy(array('enAttenteQuickstrike' => true));
            
            return $this->render('::parties.html.twig', array(
                        'Joueur' => $Joueur,
                        'jeu' => 'quickstrike',
                        'liste' => $listeJoueur,
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
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie'));
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
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie'));
    }
    
   /* public function ($Partie) {
        
    }*/


}
