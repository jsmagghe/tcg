<?php

namespace jeus\QuickstrikeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    public function TraitsByTypeAction(Request $Request) {
        $em = $this->getDoctrine()->getManager();
        $Traits = $em->getRepository('jeusQuickstrikeBundle:TraitCarte')->findAll();
        $ids = $Request->get('ids');
        $ids = explode('_', $ids);
        $typeSelectionnes = array();
        foreach ($ids as $id) {
            if (trim($id)) {
                $TypeCarte = $em->getRepository('jeusQuickstrikeBundle:TypeCarte')->find($id);
                if ($TypeCarte != null) {
                    $typeSelectionnes[] = $TypeCarte->getTag();                    
                }
            }
        }

        $traitsDisponibles = array();
        foreach($Traits as $Trait) {
            if (($Trait->getTag()=='NEUTRE') || (in_array('CHAMBER', $typeSelectionnes))) {
                $traitsDisponibles[] = $Trait->getId();
            } else if (in_array($Trait->getTag(), array('BODY','MIND','SPIRIT'))) {
                if (in_array('ADVANTAGE', $typeSelectionnes)) {
                    $traitsDisponibles[] = $Trait->getId();
                }
            } else if (in_array($Trait->getTag(), array('DARK','LIGHT','SHADOW'))) {
                if (in_array('TEAMWORK', $typeSelectionnes)) {
                    $traitsDisponibles[] = $Trait->getId();
                }
            } else if (in_array('STRIKE', $typeSelectionnes)) {
                $traitsDisponibles[] = $Trait->getId();
            }

        }
        return new JsonResponse($traitsDisponibles);
    }

}
