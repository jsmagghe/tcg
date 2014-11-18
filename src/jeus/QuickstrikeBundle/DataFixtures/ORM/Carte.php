<?php

// src/jeus/QuickstrikeBundle/DataFixtures/ORM/01-Carte.php

namespace jeus\QuickstrikeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use jeus\QuickstrikeBundle\Entity\Carte;
use jeus\QuickstrikeBundle\Entity\TraitCarte;
use jeus\QuickstrikeBundle\Entity\Extension;
use jeus\QuickstrikeBundle\Entity\TypeCarte;
use jeus\QuickstrikeBundle\Entity\Effet;
use jeus\QuickstrikeBundle\Entity\Emplacement;

class Cartes implements FixtureInterface {

    // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
    public function load(ObjectManager $manager) {
        if (file_exists('C:\wamp\www\cartes-online\src\jeus\QuickstrikeBundle\DataFixtures\ORM\base_quickstrike.csv')) {
            $baseQuickstrike = fopen('C:\wamp\www\cartes-online\src\jeus\QuickstrikeBundle\DataFixtures\ORM\base_quickstrike.csv', 'r');

            $typeCartes = array();
            rewind($baseQuickstrike);
            $ligne = fgets($baseQuickstrike);
            while (!feof($baseQuickstrike)) {
                $ligne = fgets($baseQuickstrike);
                $ligne = str_replace("\r\n", '', $ligne);
                $tab = explode(';', $ligne);
                if (isset($tab[2])) {
                    if (isset($typeCartes[$tab[2]]))
                        $typeCartes[$tab[2]] = $typeCartes[$tab[2]] + 1;
                    else
                        $typeCartes[$tab[2]] = 1;
                }
            }
            foreach ($typeCartes as $key => $typeCarte) {
                $TypeCarte = new TypeCarte();
                $TypeCarte->setLibelle($key);
                $TypeCarte->setTag(strtoupper($key));
                $manager->persist($TypeCarte);
            }
            // On déclenche l'enregistrement
            $manager->flush();

            $extension = array();
            rewind($baseQuickstrike);
            $ligne = fgets($baseQuickstrike);
            while (!feof($baseQuickstrike)) {
                $ligne = fgets($baseQuickstrike);
                $ligne = str_replace("\r\n", '', $ligne);
                $tab = explode(';', $ligne);
                if (isset($tab[0])) {
                    if (isset($extension[$tab[0]]))
                        $extension[$tab[0]] = $extension[$tab[0]] + 1;
                    else
                        $extension[$tab[0]] = 1;
                }
            }

            foreach ($extension as $key => $typeCarte) {
                if (trim($key) !== '') {
                    $Extension = new Extension();
                    $Extension->setLibelle($key);
                    $manager->persist($Extension);
                }
            }
            // On déclenche l'enregistrement
            $manager->flush();

            $traitCartes = array();
            rewind($baseQuickstrike);
            $ligne = fgets($baseQuickstrike);
            while (!feof($baseQuickstrike)) {
                $ligne = fgets($baseQuickstrike);
                $ligne = str_replace("\r\n", '', $ligne);
                $tab = explode(';', $ligne);
                $colonnes = array(10, 13, 14, 15, 16);
                foreach ($colonnes as $colonne) {
                    if ((isset($tab[$colonne])) && (trim($tab[$colonne]) != '')) {
                        if (strtoupper($tab[2])!='CHAMBER')
                            $traitCartes[$tab[2].'-'.$tab[$colonne]] = strtoupper($tab[$colonne]);
                    }
                }
            }
            ksort($traitCartes);
            $traitCartes = array_merge(array('neutre'=>'NEUTRE'),$traitCartes);
            
            foreach ($traitCartes as $key => $tag) {
                $TraitCarte = new TraitCarte();
                $TraitCarte->setLibelle($key);
                $TraitCarte->setTag(strtoupper($tag));
                $manager->persist($TraitCarte);
            }
            // On déclenche l'enregistrement
            $manager->flush();

            if (file_exists('C:\wamp\www\cartes-online\src\jeus\QuickstrikeBundle\DataFixtures\ORM\Effet_quickstrike.csv')) {
                $effetQuickstrike = fopen('C:\wamp\www\cartes-online\src\jeus\QuickstrikeBundle\DataFixtures\ORM\Effet_quickstrike.csv', 'r');
                $effets = array();
                $ligne = fgets($effetQuickstrike);
                $iteration = 1;
                while (!feof($effetQuickstrike)) {
                    $ligne = fgets($effetQuickstrike);
                    $ligne = str_replace("\r\n", '', $ligne);
                    if (trim($ligne) !== '') {
                        $tab = explode(';', $ligne);

                        $effets[$iteration]['numero'] = strtoupper($tab[0]);
                        $effets[$iteration]['texte'] = $tab[1];
                        $effets[$iteration]['texteFr'] = $tab[2];

                        $iteration++;
                    }
                }
                fclose($effetQuickstrike);
                foreach ($effets as $key => $effet) {
                    $Effet = new Effet();
                    $numero = explode(' ', $effet['numero']);
                    $Effet->setNumero($numero[1]);
                    $Effet->setTexte($effet['texte']);
                    $Effet->setTexteFr($effet['texteFr']);
                    $manager->persist($Effet);
                }
                // On déclenche l'enregistrement
                $manager->flush();
            }

            $cartes = array();
            rewind($baseQuickstrike);
            $ligne = fgets($baseQuickstrike);
            $iteration = 1;
            while (!feof($baseQuickstrike)) {
                $ligne = fgets($baseQuickstrike);
                $ligne = str_replace("\r\n", '', $ligne);
                if (trim($ligne) !== '') {
                    $tab = explode(';', $ligne);
                    $carte = new Carte();
                    $Extension = $manager->getRepository('jeusQuickstrikeBundle:Extension')->findOneByLibelle($tab[0]);
                    $carte->setExtension($Extension);
                    $TypeCarte = $manager->getRepository('jeusQuickstrikeBundle:TypeCarte')->findOneByLibelle($tab[2]);
                    $carte->setTypeCarte($TypeCarte);
                    if ($tab[11] !== '') {
                        $numeroEffet = explode(' ', $tab[11]);
                        $Effet = $manager->getRepository('jeusQuickstrikeBundle:Effet')->findOneByNumero($numeroEffet[1]);
                        $carte->setEffet($Effet);
                    }

                    $colonnes = array(10, 13, 14, 15, 16);
                    foreach ($colonnes as $colonne) {
                        if (trim($tab[$colonne]) !== '') {
                            $TraitCarte = $manager->getRepository('jeusQuickstrikeBundle:TraitCarte')->findOneByTag(strtoupper($tab[$colonne]));
                            $carte->addTraitCarte($TraitCarte);
                        } elseif (($colonne==10) && (strtoupper($tab[2])!='CHAMBER')) {
                            $TraitCarte = $manager->getRepository('jeusQuickstrikeBundle:TraitCarte')->findOneByTag('NEUTRE');
                            $carte->addTraitCarte($TraitCarte);
                        }
                    }
                    $carte->setNom($tab[4]);
                    $carte->setNumero($tab[1]);
                    $carte->setImage(str_replace(".bmp", ".png", str_replace(" ", "-", $tab[3])));
                    $carte->setCoutVert($tab[5]);
                    $carte->setCoutJaune($tab[6]);
                    $carte->setCoutRouge($tab[7]);
                    $carte->setIntercept($tab[8]);
                    $carte->setAttaque($tab[9]);
                    $carte->setPersonnageChamber($tab[12]);

                    $cartes[] = $carte;

                    $iteration++;
                }
            }
            foreach ($cartes as $Carte) {
                $manager->persist($Carte);
            }
            // On déclenche l'enregistrement
            $manager->flush();
            fclose($baseQuickstrike);
        } else
            die('echec trait de carte');
    }

}
