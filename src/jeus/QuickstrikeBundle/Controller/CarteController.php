<?php

namespace jeus\QuickstrikeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/* Entity */
use jeus\QuickstrikeBundle\Entity\Deck;
use jeus\QuickstrikeBundle\Entity\CarteDeck;

/* Form */
use jeus\QuickstrikeBundle\Form\SelecteurType;


class CarteController extends Controller {

    public function carteAction() {

        $Request = $this->getRequest();
        $tableau = $this->rechercheCarte($Request);
        $filtre = $tableau['filtre'];
        $formSelecteur = $tableau['form'];
        $Cartes = $tableau['Cartes'];

        if ($Request->isXmlHttpRequest()) {
            return $this->render('::cartes.html.twig', array(
                        'cartes' => $Cartes,
            ));
        }

        return $this->render('::cartes.html.twig', array(
                    'cartes' => $Cartes,
                    'form' => $formSelecteur->createView(),
                    'jeu' => 'quickstrike',
        ));
    }

    public function rechercheCarte($Request) {
        $tableau = array();
        $em = $this->getDoctrine()->getManager();
        $formSelecteur = $this->createForm(new SelecteurType());
        $filtre = array();
        $Request = $this->getRequest();
        if ($Request->getMethod() == 'POST') {
            $formSelecteur->handleRequest($Request);

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
        } else {
                $filtre['extension'] = $em->getRepository('jeusQuickstrikeBundle:Extension')->findByLibelle('Shaman King');
        }
        $tableau['filtre'] = $filtre;
        $tableau['form'] = $formSelecteur;
        $tableau['Cartes'] = $em->getRepository('jeusQuickstrikeBundle:Carte')->findByCritere($filtre);


        return $tableau;
    }

}
