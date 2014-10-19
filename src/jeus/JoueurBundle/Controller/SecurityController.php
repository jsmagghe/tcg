<?php

// src/jeus/JoueurBundle/Controller/SecurityController.php;

namespace jeus\JoueurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller
{

    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        // On vérifie s'il y a des erreurs d'une précédente soumission du formulaire
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $Joueur = $this->get('security.context')->getToken()->getUser();
            if ($Joueur->getJeuEnCours() == 'bleach')
                return $this->redirect($this->generateUrl('jeus_bleach_carte'));
            if ($Joueur->getJeuEnCours() == 'quickstrike')
                return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
            if ($Joueur->getJeuEnCours() == 'saintseiya')
                return $this->redirect($this->generateUrl('jeus_saintseiya_carte'));
            
            return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
        }


        return $this->render('jeusJoueurBundle:Security:login.html.twig', array(
                    // Valeur du précédent nom d'utilisateur entré par l'internaute
                    'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                    'error' => $error,
                ));
    }

}

