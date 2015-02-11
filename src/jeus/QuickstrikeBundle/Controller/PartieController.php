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
    private $CarteEnJeus;
    private $Partie;
    private $Joueur;

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
                if ($CartePartie->getEmplacement()!='OPENING') {
                    if ($this->numeroJoueur($Partie,$Joueur)==$CartePartie->getNumeroJoueur()) {
                        if ((!isset($carteJoueurs[strtolower($CartePartie->getEmplacement())])) || ($CartePartie->getEmplacement()=='AVANTAGE'))
                            $carteJoueurs[strtolower($CartePartie->getEmplacement())][]=$carte;
                    } else {
                        if ((!isset($carteAdversaires[strtolower($CartePartie->getEmplacement())])) || ($CartePartie->getEmplacement()=='AVANTAGE'))
                            $carteAdversaires[strtolower($CartePartie->getEmplacement())][]=$carte;    
                    }
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

        if (($effet=='attaquer') || ($effet=='defendre')) {
            $numeroAttaquant = $this->numeroJoueur($Partie,$Joueur,$effet=='defendre');
            $this->attaquer($Partie,$numeroAttaquant);

        }
        return $this->redirect($this->generateUrl('jeus_quickstrike_partie',array('id' => $Partie->getId())));
    }











    // fonction de gestion de la partie

    private function chargerCarteEnJeu($Partie) {
        if ($this->CarteEnJeus==null) {
            $CarteEnJeus = $this->em->getRepository('jeusQuickstrikeBundle:CartePartie')
                                     ->findBy(array('Partie' => $Partie));

            foreach($CarteEnJeus as $CartePartie) {
                $emplacement = $CartePartie->getEmplacement();
                $numeroJoueur = $CartePartie->getNumeroJoueur();
                if (($emplacement!='AVANTAGE') && ($emplacement!='DECK') && ($emplacement!='DISCARD')) {
                    $this->CarteEnJeus[$numeroJoueur][$emplacement] = $CartePartie;
                } else {
                    $this->CarteEnJeus[$numeroJoueur][$emplacement][] = $CartePartie;
                }
                if (($emplacement!='DECK') && ($emplacement!='DISCARD') && (strpos($emplacement,'ENERGIE_') === false)) {
                    $this->CarteEnJeus[$numeroJoueur]['ACTIVE'][] = $CartePartie;
                }
            }

        }
    }

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
            $CarteOpening = $this->em
                                 ->getRepository('jeusQuickstrikeBundle:Carte')
                                 ->findOneBy(array('nom' => 'opening attack'));
            $CartePartie = new CartePartie($CarteOpening,$Partie,$Partie->JoueurConcerne($Joueur),'OPENING');
            $Partie->addCartePartie($CartePartie);
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
        // s'il n'y a plus de carte dans le deck on récupère toutes les cartes de la discard que l'on met dans le deck
        if (($nombre>0) && ($emplacementOrigine=='DECK')) {
            $this->deplacerCarte($Partie,$joueurConcerne,99,'DISCARD','DECK',true);
            $this->deplacerCarte($Partie,$joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal,$melanderDestination);
            $this->deplacerCarte($Partie,$joueurConcerne,5,'DECK','DISCARD');
        }
    }

    private function demarragePartie($Partie,$joueurConcerne) {
        $this->melangerEmplacement($Partie,$joueurConcerne);
        $this->deplacerCarte($Partie,$joueurConcerne,5,'DECK','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,2,'DECK','ENERGIE_VERTE');
        $this->deplacerCarte($Partie,$joueurConcerne,2,'DECK','ENERGIE_JAUNE');
        $this->deplacerCarte($Partie,$joueurConcerne,2,'DECK','ENERGIE_ROUGE');
    }

    private function viderCarte($Partie,$joueurConcerne) {
        //$this->deplacerCarte($Partie,$joueurConcerne,99,'AVANTAGE,STRIKE_VERT,STRIKE_JAUNE,STRIKE_ROUGE','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,99,'AVANTAGE','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,99,'STRIKE_VERT','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,99,'STRIKE_JAUNE','DISCARD');
        $this->deplacerCarte($Partie,$joueurConcerne,99,'STRIKE_ROUGE','DISCARD');
    }

    private function attaquer($Partie,$joueurConcerne) {
        $this->viderCarte($Partie,1);
        $this->viderCarte($Partie,2);
        $this->deplacerCarte($Partie,$joueurConcerne,1,'DECK',$Partie->zoneEnCours($joueurConcerne));
        $this->deplacerCarte($Partie,($joueurConcerne==1)?2:1,1,'OPENING','STRIKE_VERT');
        if ($joueurConcerne==1) {
            $Partie->setJoueur1Etape('attente');
            $Partie->setJoueur2Etape('defense');
        } else {
            $Partie->setJoueur1Etape('attente');
            $Partie->setJoueur2Etape('defense');
        }
    }

    private function zoneSuivante($zone) {
        $zoneSuivante = 'STRIKE_VERT';
        switch ($zone) {
            case 'STRIKE_VERT' : 
                $zoneSuivante = 'STRIKE_JAUNE';
                break;
            case 'STRIKE_JAUNE' : 
                $zoneSuivante = 'STRIKE_ROUGE';
                break;
            case 'STRIKE_ROUGE' : 
                $zoneSuivante = 'POINT';
                break;
        }
        return $zoneSuivante;
    }

    private function zoneCorrespondante($zone,$type='STRIKE') {
        $zoneCorrespondante = 'STRIKE_VERT';
        switch ($zone) {
            case 'STRIKE_VERT' : 
                $zoneCorrespondante = '_VERTE';
                break;
            case 'STRIKE_JAUNE' : 
                $zoneCorrespondante = '_JAUNE';
                break;
            case 'STRIKE_ROUGE' : 
                $zoneCorrespondante = '_ROUGE';
                break;
        }
        $zoneCorrespondante = $type . $zoneCorrespondante;
        return $zoneCorrespondante;
    }

    private function focuserPitcher($Partie,$joueurConcerne,$action) {
        $zoneEnCours = $Partie->zoneEnCours($joueurConcerne);
        $zoneSuivante = $this->zoneSuivante($zoneEnCours);
        if ( $zoneSuivante == 'POINT') {
            $this->pointPourAdversaire($Partie,$joueurConcerne);
        } else {
            $zoneCorrespondante = 'DISCARD';
            if ($action=='focus')              
                $zoneCorrespondante = $this->zoneCorrespondante($zoneEnCours,'ENERGIE');
            $this->deplacerCarte($Partie,$joueurConcerne,1,$zoneEnCours,$zoneCorrespondante);
            $this->descendreDeZone($Partie,$joueurConcerne);
        }
    }

    private function focuser($Partie,$joueurConcerne) {
        $this->focuserPitcher($Partie,$joueurConcerne,'focus');
    }

    private function pitcher($Partie,$joueurConcerne) {
        $this->focuserPitcher($Partie,$joueurConcerne,'pitch');
    }

    private function descendreDeZone($Partie,$joueurConcerne) {
        $zoneEnCours = $Partie->zoneEnCours($joueurConcerne);
        $zoneSuivante = $this->zoneSuivante($zoneEnCours);
        if ( $zoneSuivante == 'POINT') {
            $this->pointPourAdversaire($Partie,$joueurConcerne);
        } else {
            $Partie->setJoueurZoneEnCours($joueurConcerne,$zoneSuivante);
        }
    }

    private function pointPourAdversaire($Partie,$joueurConcerne){
        $Partie->addPointAdversaire($joueurConcerne);
        $Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_VERT');
        $this->setEtapeJoueur($joueurConcerne,'choixAttaquant');
    }

    private function setEtapeJoueur($Partie,$joueurConcerne,$etape) {
        $Partie->setEtapeByNumero(($joueurConcerne==1) ? 2 : 1,'attente');
        $Partie->setEtapeByNumero($joueurConcerne,$etape);
    }

    private function joueurChoisi() {
        $numero = rand(1,1000);
        return ($numero<=500)? 1 : 2;
    }

    private function numeroAttaquant($Partie) {
        $numeroAttaquant = ($Partie->getJoueur1Etape()=='defense') ? 2 : 1;
        return $numeroAttaquant;
    }

    private function numeroDefenseur($Partie) {
        $numeroDefenseur = ($Partie->getJoueur1Etape()=='defense') ? 1 : 2;
        return $numeroDefenseur;
    }

    private function bonusAttaque($Partie) {
        $bonus = 0;
        if (($Partie->getJoueur1Etape()=='defense') || ($Partie->getJoueur2Etape()=='defense')) {
            $numeroDefenseur = $this->numeroDefenseur($Partie);
            $numeroAttaquant = $this->numeroAttaquant($Partie);
            $CarteEnJeus = $this->CarteEnJeus[$numeroAttaquant]['ACTIVE'];
            foreach ($CarteEnJeus as $Cartejeu) {
                $Carte = $Cartejeu->getCarte();
                if ($Carte == null) {
                    continue;
                }
            }
        }

        return $bonus;
    }

    private function attaqueEnCours($Partie) {
        $attaque = 0;
        if (($Partie->getJoueur1Etape()=='defense') || ($Partie->getJoueur2Etape()=='defense')) {
            $numeroDefenseur = $this->numeroDefenseur($Partie);
            $numeroAttaquant = $this->numeroAttaquant($Partie);
            if ($Partie->getJoueurZoneEnCours($numeroAttaquant)!='0') {
                $CarteActive = $this->CarteEnJeus[$numeroAttaquant][$Partie->getJoueurZoneEnCours($numeroAttaquant)];

                $Carte = $CarteActive->getCarte();
                if ($Carte == null) {
                    return 0;
                }
                if (($Carte->getTypeCarte()->getTag()=='STRIKE') || ($Carte->getTypeCarte()->getTag()=='CHAMBER')){
                    $attaque += $Carte->getAttaque();  
                }                
            }
        }

        return $attaque+$this->bonusAttaque($Partie);
    }

    private function bonusDefense($Partie) {
        $bonus = 0;
        if (($Partie->getJoueur1Etape()=='defense') || ($Partie->getJoueur2Etape()=='defense')) {
            $numeroDefenseur = $this->numeroDefenseur($Partie);
            $numeroAttaquant = $this->numeroAttaquant($Partie);
            if ($Partie->getJoueurZoneEnCours($numeroDefenseur)!='0') {
                $CarteActive = $this->CarteEnJeus[$numeroDefenseur][$Partie->getJoueurZoneEnCours($numeroDefenseur)];
                $Carte = $CarteActive->getCarte();
                if ($Carte == null) {
                    continue;
                }

            }
        }

        return $bonus;
    }

    private function isCartePayable($Partie, $Joueur, $Cartejeu) {
        $payable = true;
        if ($Cartejeu == null)
            return false;

        $Carte = $Cartejeu->getCarte();

        if ($Carte == null) 
            return false;
        
        $coutVert = $Carte->getCoutVert();
        $coutJaune = $Carte->getCoutJaune();
        $coutRouge = $Carte->getCoutRouge();

        $energieVerteDisponible = count($this->CarteEnJeus[$this->numeroJoueur($Partie,$Joueur)]['ENERGIE_VERTE']);
        $energieJauneDisponible = count($this->CarteEnJeus[$this->numeroJoueur($Partie,$Joueur)]['ENERGIE_JAUNE']);
        $energieRougeDisponible = count($this->CarteEnJeus[$this->numeroJoueur($Partie,$Joueur)]['ENERGIE_ROUGE']);

        if ($energieRougeDisponible>=$coutRouge) {
            $energieRougeDisponible-=$coutRouge;
            $coutRouge = 0;
        }

        if ($energieJauneDisponible+$energieRougeDisponible>=$coutJaune) {
            $energieJauneDisponible-=$coutJaune;
            if ($energieJauneDisponible<0) {
                $energieRougeDisponible -= $energieJauneDisponible;
                $energieJauneDisponible = 0;
            }
            $coutJaune = 0;
        }

        if ($energieVerteDisponible+$energieJauneDisponible+$energieRougeDisponible>=$coutVert) {
            $energieVerteDisponible-=$coutVert;
            if ($energieVerteDisponible<0) {
                $energieJauneDisponible -= $energieVerteDisponible;
                $energieVerteDisponible = 0;
            }
            if ($energieJauneDisponible<0) {
                $energieRougeDisponible -= $energieJauneDisponible;
                $energieJauneDisponible = 0;
            }
            $coutVert = 0;
        }


        $payable = (($coutVert<=0) && ($coutJaune<=0) && ($coutRouge<=0));

        return $payable;
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
        $this->CarteEnJeus=null;
        $this->chargerCarteEnJeu($Partie);
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
            case 'defense':
                $attaque = $this->attaqueEnCours($Partie);
                $numeroDefenseur = $this->numeroDefenseur($Partie);
                $numeroAttaquant = $this->numeroAttaquant($Partie);
                $CarteEnJeus = $this->CarteEnJeus[$numeroDefenseur];

                if ($Partie->getJoueurZoneEnCours($numeroDefenseur)!='0') {
                    $CarteActive = $CarteEnJeus[$Partie->getJoueurZoneEnCours($numeroDefenseur)];
                    
                    $Carte = $CarteActive->getCarte();
                    if ($this->isCartePayable($Partie, $Joueur, $CarteActive)) {
                        if ($Carte->getTypeCarte()->getTag()=='STRIKE') 
                        {
                            $defense = $Carte->getIntercept()+$this->bonusDefense($Partie);  
                            if ($defense>=$attaque) 
                                $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'contre_attaquer')).'">Contre attaquer</a>';
                        }
                        if (($Carte->getTypeCarte()->getTag()=='TEAMWORK') 
                            && ($Cartejeu->getEmplacement()==$Partie->getJoueurZoneEnCours($numeroDefenseur))
                            )
                        {
                            $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'recruter')).'">Recruter</a>';
                        }
                        if (($Carte->getTypeCarte()->getTag()=='AVANTAGE') 
                            && ($Cartejeu->getEmplacement()==$Partie->getJoueurZoneEnCours($numeroDefenseur))
                            )
                        {
                            $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'avantager')).'">Joueur</a>';
                        }
                    }
                }

                $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'pitcher')).'">Pitch</a>';
                $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'focuser')).'">Focus</a>';
                if (count($action) == 0)
                    $action[] = '<a href="'.$this->generateUrl('jeus_quickstrike_partie_choix_effet',array('id' => $Partie->getId(),'effet' => 'discarder')).'">Discard</a>';
                break;
        }                
            
        return $action;
    }


}
