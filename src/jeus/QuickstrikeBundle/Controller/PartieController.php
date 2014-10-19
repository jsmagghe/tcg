<?php

namespace jeus\QuickstrikeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/* Entity */
use jeus\QuickstrikeBundle\Entity\Deck;

/* Form */


class PartieController extends Controller {

    public function partieAction() {

        return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
    }
    
    public function joueurEnAttenteAction() {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        $Decks = $em->getRepository('jeusQuickstrikeBundle:Deck')->findBy(array('Joueur' => $Joueur, 'valide' => true));
        if ($Decks) {
            $Joueur->setEnAttenteQuickstrike($Decks != null);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($Joueur);
        $em->flush();
        return $this->redirect($this->generateUrl('jeus_quickstrike_parties'));
    }


}
