<?php

namespace jeus\JoueurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/* ENTITY */
use jeus\JoueurBundle\Entity\Joueur;

/* FORM TYPE */
use jeus\JoueurBundle\Form\JoueurType;

class JoueurController extends Controller
{

    private $em;

    public function indexAction()
    {
        $Joueur = $this->get('security.context')->getToken()->getUser();
        if (($Joueur !== null) && ($Joueur!=='anon.')) {
            if ($Joueur->getJeuEnCours() == 'bleach')
                return $this->redirect($this->generateUrl('jeus_bleach_carte'));
            if ($Joueur->getJeuEnCours() == 'saintseiya')
                return $this->redirect($this->generateUrl('jeus_saintseiya_carte'));
            
            return $this->redirect($this->generateUrl('jeus_quickstrike_carte'));
        }
        return $this->redirect($this->generateUrl('login'));
    }

    public function creerEditerAction($id = null)
    {
        $this->em = $this->getDoctrine()->getManager();
        $Request = $this->getRequest();

        if (is_null($id)) {
            $Joueur = new Joueur();
        } else {
            $Joueur = $this->em->getRepository('jeusJoueurBundle:Joueur')->find($id);
            $oldPassword = $Joueur->getPassword();
        }

        $formJoueur = $this->createForm(new JoueurType(), $Joueur);

        if ($Request->getMethod() == "POST") {
            $formJoueur->bind($Request);

            if ($formJoueur->isValid()) {
                if (is_null($id)) {
                    $this->em->persist($Joueur);
                }

                if (!is_null($Joueur->getPassword())) {
                    $factory = $this->get('security.encoder_factory');
                    $encoder = $factory->getEncoder($Joueur);
                    $passwordEncode = $encoder->encodePassword($Joueur->getPassword(), $Joueur->getSalt());
                    $Joueur->setPassword($passwordEncode);
                } else if (!is_null($id)) {
                    $Joueur->setPassword($oldPassword);
                }

                //$Joueur->setRole($formJoueur->get('role')->getData());
                $Joueur->setRole(null);
                $Joueur->setActif(true);

                $this->em->flush();
                return $this->redirect($this->generateUrl('jeus_joueur_joueur'));
            }
        }

        return $this->render('jeusJoueurBundle:Joueur:inscription.html.twig', array(
                    'form' => $formJoueur->createView(),
        ));
    }

    public function supprimerAction(Joueur $Joueur, $id)
    {
        $this->em = $this->getDoctrine()->getManager();

        $this->em->remove($Joueur);
        $this->em->flush();
        return new Response(0);
    }

}
