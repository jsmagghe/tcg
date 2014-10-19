<?php

namespace jeus\QuickstrikeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/* Entity */

/* Form */


class QuickstrikeController extends Controller {

    public function indexAction() {

        $Joueur = $this->get('security.context')->getToken()->getUser();
        if (($Joueur !== null) && ($Joueur !== 'anon.')) {
            $Joueur->setJeuEnCours('quickstrike');
            $em = $this->getDoctrine()->getManager();
            $em->persist($Joueur);
            $em->flush();

        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
    }

}
