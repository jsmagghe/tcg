<?php

namespace jeus\QuickstrikeBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;

use jeus\QuickstrikeBundle\Entity\Deck;
use jeus\QuickstrikeBundle\Entity\CarteDeck;

/**
 *
 * @author Julien S
 */
class Carte
{

    protected $em;
    protected $container;

    public function __construct(ObjectManager $em, $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function rechercheCarte($tableau, $sansRestriction = false) {
        $tableau['Cartes'] = $this->em->getRepository('jeusQuickstrikeBundle:Carte')->findByCritere($tableau['filtre']);
        /*if (isset($tableau['filtre']['test'])) {
            var_dump(count($tableau['Cartes']));
        }*/


        if (
            (isset($tableau['filtre']['traitCarte']))
            && (isset($tableau['filtre']['idChamber']))
        ) {
            if (isset($tableau['filtre']['typeCarte'])) {
                $trouve = false;
                $nombre = 0;
                foreach ($tableau['filtre']['typeCarte'] as $typeCarte) {
                    if ($typeCarte->getTag() == 'CHAMBER') {
                        $trouve = true;
                    }
                    $nombre++;
                }
            }
            if ($nombre==0 || $trouve) {
                $tableauExtensionId = '';
                foreach ($tableau['filtre']['extension'] as $Extension) {
                    if ($tableauExtensionId != '') {
                        $tableauExtensionId .= ',';
                    }
                    $tableauExtensionId .= $Extension->getId();                    
                }
                $selectionExtension = '';
                if (trim($tableauExtensionId) != '') {
                    $tableauExtensionId = '(' . $tableauExtensionId  .')';
                    $selectionExtension = ' and a.extension_id in '.$tableauExtensionId;
                }

                $nombreTraits = 0;
                $tableauTraitId = '';
                foreach ($tableau['filtre']['traitCarte'] as $Trait) {
                    if ( $Trait->getTag() != 'NEUTRE') {
                        $nombreTraits++;
                        if ($tableauTraitId != '') {
                            $tableauTraitId .= ',';
                        }
                        $tableauTraitId .= $Trait->getId();                    
                    }
                }
                $selectionTrait = '';
                if (trim($tableauTraitId)!='') {
                    $tableauTraitId = '(' . $tableauTraitId  .')';
                    $selectionTrait = ' join (
                                                select distinct carte_id 
                                                from quickstrike_carte_traitcarte 
                                                where traitcarte_id in '. $tableauTraitId .' 
                                                group by carte_id 
                                                having COUNT(*) >= '. $nombreTraits .'
                                            ) c on a.id = c.carte_id ';
                    
                }

                $bdd = $this->em->getConnection()
                            ->prepare('select distinct(a.id) from quickstrike_carte a '.$selectionTrait.' where a.typeCarte_id = '. $tableau['filtre']['idChamber'] .' and a.numero not like \'%v\' '.$selectionExtension);
                $bdd->execute();
                $tableauId = $bdd->fetchAll();
                $tableauRepository = array();
                foreach ($tableauId as $key => $tab) {
                    $tableauRepository[] = $tab['id'];
                }
                $tableau['Cartes2'] = $this->em->getRepository('jeusQuickstrikeBundle:Carte')->findByIds($tableauRepository);
                $tableau['Cartes'] = array_merge($tableau['Cartes2'],$tableau['Cartes']);
            }
        }
        /*if (isset($tableau['filtre']['test'])) {
            var_dump(count($tableau['Cartes']));
            exit;
        }*/

        $tableau['page'] = isset($tableau['filtre']['page']) ? $tableau['filtre']['page'] : 1;

        $tableau['nbPage'] = ceil(count($tableau['Cartes']) / $this->container->getParameter('carte_par_page'));
        if (($tableau['page']<1) || ($tableau['page']>$tableau['nbPage'])) {
            $tableau['page'] = 1;
        }

        $pageEnCours = 1;
        $nbCarteEnCours = 0;
        foreach ($tableau['Cartes'] as $key => $value) {
            if ($nbCarteEnCours>=$this->container->getParameter('carte_par_page')) {
                $pageEnCours++;
                $nbCarteEnCours = 0;
            }
            if (($pageEnCours != $tableau['page']) && (!$sansRestriction)) {
                unset($tableau['Cartes'][$key]);
            }
            $nbCarteEnCours++;
        }

        return $tableau;
    }

    private function ajouterCarteDeck(&$Deck,$Carte) {
        $CarteDeck = new CarteDeck();
        $CarteDeck->setCarte($Carte);
        $CarteDeck->setDeck($Deck);

        $erreur = $Deck->carteAjoutable($CarteDeck);
        if ($erreur=='') {
            $this->em->persist($CarteDeck);
            $Deck->addCarte($CarteDeck);
        }
        $this->em->persist($Deck);
        $this->em->flush();
        $this->em->refresh($Deck);

        return $erreur;
    }

    public function deckAleatoire($tableau, $Joueur) 
    {
        unset($tableau['filtre']['page']);

        $DeckAleatoires = $this->em->getRepository('jeusQuickstrikeBundle:Deck')->findDeckByJoueurAndName($Joueur,'Aléatoire');
        $Deck = null;
        foreach ($DeckAleatoires as $DeckAleatoire) {
            $Deck = $DeckAleatoire;
            break;
        }
        if ($Deck === null) {
            $Deck = new Deck();
            $Deck->setNom('Aléatoire');
            $Deck->setJoueur($Joueur);
            $Deck->setValide(false);
            $this->em->persist($Deck);
            $this->em->flush();        
        }

        // on supprime toutes les cartes présentes dans le deck
        foreach($Deck->getCartes() as $CarteDeck) {
            $this->em->remove($CarteDeck);
        }
        $this->em->flush();        

        // choix de la chamber
        $tableau['filtre']['typeCarte'] = $this->em->getRepository('jeusQuickstrikeBundle:TypeCarte')->findByTag('CHAMBER');
        $cartePossibles = $this->rechercheCarte($tableau, true);

        $CarteChoisie = rand(0,count($cartePossibles['Cartes2']));
        $Chamber = $cartePossibles['Cartes2'][$CarteChoisie];
        $this->ajouterCarteDeck($Deck, $Chamber);
        // on filtre par les traits de la chamber choisie
        $tableau['filtre']['traitCarte'] = array();
        foreach ($Chamber->getTraitCartes() as $Trait) {
            $tableau['filtre']['traitCarte'][] = $Trait;
        }
        $tableau['filtre']['traitCarte'][] = $this->em->getRepository('jeusQuickstrikeBundle:TraitCarte')->findOneByTag('NEUTRE');

        // définition du nombre de carte des différents types
        $nombreTeamwork = rand(8,12);
        $nombreAvantage = rand(24,30) - $nombreTeamwork;
        $nombreStrike = 60 - $nombreTeamwork - $nombreAvantage;

        //$tableau['filtre']['test'] = true;
        // ajout des teamworks
        $tableau['filtre']['typeCarte'] = $this->em->getRepository('jeusQuickstrikeBundle:TypeCarte')->findByTag('TEAMWORK');
        $nombreTentative = 500;
        $cartePossibles = $this->rechercheCarte($tableau, true);

        while (($nombreTeamwork>0) && ($nombreTentative>0)) {
            $CarteChoisie = rand(0,count($cartePossibles['Cartes']));
            $Carte = isset($cartePossibles['Cartes'][$CarteChoisie]) ? $cartePossibles['Cartes'][$CarteChoisie] : null;
            if ($Carte !== null) {
                $nombreExemplaire = rand(1,4);
                $erreur = '';
                while (($nombreExemplaire>0) && ($nombreTeamwork>0) && ($erreur=='')) {
                    $erreur = $this->ajouterCarteDeck($Deck, $Carte);
                    if ($erreur=='') {
                        $nombreTeamwork--;
                        $nombreExemplaire--;                        
                    }
                }                
                unset($cartePossibles['Cartes'][$CarteChoisie]);
                sort($cartePossibles['Cartes']);
            }
            $nombreTentative--;
        }

        // ajout des avantages
        $tableau['filtre']['typeCarte'] = $this->em->getRepository('jeusQuickstrikeBundle:TypeCarte')->findByTag('ADVANTAGE');
        $nombreTentative = 500;
        $cartePossibles = $this->rechercheCarte($tableau, true);

        while (($nombreAvantage>0) && ($nombreTentative>0)) {
            $CarteChoisie = rand(0,count($cartePossibles['Cartes']));
            $Carte = isset($cartePossibles['Cartes'][$CarteChoisie]) ? $cartePossibles['Cartes'][$CarteChoisie] : null;
            if ($Carte !== null) {
                $nombreExemplaire = rand(1,4);
                $erreur = '';
                while (($nombreExemplaire>0) && ($nombreAvantage>0) && ($erreur=='')) {
                    $erreur = $this->ajouterCarteDeck($Deck, $Carte);
                    if ($erreur=='') {
                        $nombreAvantage--;
                        $nombreExemplaire--;                        
                    }
                }                
                unset($cartePossibles['Cartes'][$CarteChoisie]);
                sort($cartePossibles['Cartes']);
            }
            $nombreTentative--;
        }

        // ajout des strikes
        $tableau['filtre']['typeCarte'] = $this->em->getRepository('jeusQuickstrikeBundle:TypeCarte')->findByTag('STRIKE');
        $nombreTentative = 1000;
        $cartePossibles = $this->rechercheCarte($tableau, true);

        while (($nombreStrike>0) && ($nombreTentative>0)) {
            $CarteChoisie = rand(0,count($cartePossibles['Cartes']));
            $Carte = isset($cartePossibles['Cartes'][$CarteChoisie]) ? $cartePossibles['Cartes'][$CarteChoisie] : null;
            if ($Carte !== null) {
                $nombreExemplaire = rand(1,4);
                $erreur = '';
                while (($nombreExemplaire>0) && ($nombreStrike>0) && ($erreur=='')) {
                    $erreur = $this->ajouterCarteDeck($Deck, $Carte);
                    if ($erreur=='') {
                        $nombreStrike--;
                        $nombreExemplaire--;                        
                    }
                }                
                unset($cartePossibles['Cartes'][$CarteChoisie]);
                sort($cartePossibles['Cartes']);
            }
            $nombreTentative--;
        }
    }



}
