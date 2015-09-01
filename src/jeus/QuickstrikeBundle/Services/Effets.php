<?php

namespace jeus\QuickstrikeBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;

/**
 *
 * @author Julien S
 */
class Effets
{
    protected $tools;
    protected $interactions;
    private $CarteEnJeus;
    private $Partie;
    private $infos;

    public function __construct($tools,$interactions)
    {
        $this->tools = $tools;
        $this->interactions = $interactions;
    }

    public function chargerCarteEnJeu($CarteEnJeus) {
        $this->CarteEnJeus = $CarteEnJeus;
    }

    public function chargerPartie($Partie) {
        $this->Partie = $Partie;
    }

    public function chargerinfos($infos) {
        $this->infos = $infos;
    }

    public function bonusAttaque($numeroAttaquant,$numeroDefenseur) {
        $bonus = 0;

        $proprieteEffetAttaquant = "getJoueur".$numeroAttaquant."Effets";
        $proprieteEffetDefenseur = "getJoueur".$numeroDefenseur."Effets";

        $CarteEnJeus = (isset($this->CarteEnJeus[$numeroAttaquant]['ACTIVE'])) ? $this->CarteEnJeus[$numeroAttaquant]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($numeroAttaquant,$Carte);
            switch ($numeroEffet) {
                case 428 : 
                    $bonus -= 2;
                    break;
                case 670 : 
            		$bonus -= 1;
                    break;
                case 7 : 
                case 34 : 
                case 106 : 
                case 220 : 
                case 237 : 
                case 448 : 
                case 439 : 
                case 466 : 
                case 470 : 
                case 681 : 
                case 715 : 
                    $bonus += 1;
                    break;
                case 6 : 
                case 23 : 
                case 63 : 
                case 64 : 
                case 65 : 
                case 270 : 
                case 445 : 
                case 727 : 
                case 745 : 
                    $bonus += 2;
            		break;
                case 17 : 
                    $bonus += 3;
                    break;

                // +1 force par avantage joué ce tour
                case 664 :
                    $effets = $this->Partie->$proprieteEffetAttaquant();
                    foreach ($effets as $tab) {
                        if (isset($tab['avantage'])) {
                            $bonus++;
                        }
                    }
                    break;

                // +1 force / teamwork GCPD
                case 664 :
                        if (
                            (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_VERTE']) 
                            && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_VERTE'],array('type'=> 'TEAMWORK', 'extension' => 'batman', 'trait' => 'shadow'))))
                            ) {
                            $bonus++;
                        }
                        if (
                            (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_JAUNE']) 
                            && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_JAUNE'],array('type'=> 'TEAMWORK', 'extension' => 'batman', 'trait' => 'shadow'))))
                            ) {
                            $bonus++;
                        }
                        if (
                            (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_ROUGE']) 
                            && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_ROUGE'],array('type'=> 'TEAMWORK', 'extension' => 'batman', 'trait' => 'shadow'))))
                            ) {
                            $bonus++;
                        }
                    break;


                // zone verte
                case 95 : 
                    if ($this->infos['ZoneDefenseur']=='STRIKE_VERT') {
                        $bonus -= 3;
                    }
                    break;
                case 112 : 
                case 195 : 
                    if ($this->infos['ZoneDefenseur']=='STRIKE_VERT') {
                        $bonus += 2;
                    }
                    break;

                // zone rouge
                case 10 : 
                    if ($this->infos['ZoneDefenseur']=='STRIKE_ROUGE') {
                        $bonus -= 4;
                    } else {
                        $bonus += 1;                        
                    }
                    break;
                case 24 : 
                    if ($this->infos['ZoneDefenseur']=='STRIKE_ROUGE') {
                        $bonus -= 3;
                    }
                    break;

                // nombre carte
                case 59 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['ADVANTAGE'])) {
                        foreach ($this->CarteEnJeus[$numeroAttaquant]['ADVANTAGE'] as $CarteJeu) {
                            if (
                                ($CarteJeu->getCarte()!=null)
                                && ($CarteJeu->getCarte()->getTypeCarte()!=null)
                                && ($CarteJeu->getCarte()->getTypeCarte()->getTag()=='ADVANTAGE')
                                ) {
                                $bonus += 2;
                                break;
                            }
                        }
                    }
                    break;
                case 612 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['ADVANTAGE'])) {
                        foreach ($this->CarteEnJeus[$numeroAttaquant]['ADVANTAGE'] as $CarteJeu) {
                            if (
                                ($CarteJeu->getCarte()!=null)
                                && ($CarteJeu->getCarte()->getTypeCarte()!=null)
                                && ($CarteJeu->getCarte()->getTypeCarte()->getTag()=='ADVANTAGE')
                                ) {
                                $bonus += 1;
                            }
                        }
                    }
                    break;
                case 548 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['ADVANTAGE'])) {
                        foreach ($this->CarteEnJeus[$numeroDefenseur]['ADVANTAGE'] as $CarteJeu) {
                            if (
                                ($CarteJeu->getCarte()!=null)
                                && ($CarteJeu->getCarte()->getTypeCarte()!=null)
                                && ($CarteJeu->getCarte()->getTypeCarte()->getTag()=='ADVANTAGE')
                                ) {
                                $bonus += 1;
                            }
                        }
                    }
                    break;
                case 67 :
                case 576 :
                    $bonus += $this->infos['nombreTeamworkDefenseur'];
                    break;
                case 174 :
                case 314 :
                case 542 :
                case 595 :
                    $bonus += $this->infos['nombreTeamworkAttaquant'];
                    break;
                case 569 :
                    $bonus += 2 * $this->infos['nombreTeamworkDefenseur'] + 2 * $this->infos['nombreTeamworkAttaquant'];
                    break;
                case 422 :
                case 633 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant][$this->tools->zoneCorrespondante($this->infos['ZoneAttaquant'],'TEAMWORK')])) {
                        $bonus += 1;
                    } 
                    break;
                case 110 :
                case 182 :
                case 498 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant][$this->tools->zoneCorrespondante($this->infos['ZoneAttaquant'],'TEAMWORK')])) {
                        $bonus += 2;
                    } 
                    break;
                case 629 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant][$this->tools->zoneCorrespondante($this->infos['ZoneAttaquant'],'TEAMWORK')])) {
                        $bonus += 3;
                    } 
                    break;
                case 636 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant][$this->tools->zoneCorrespondante($this->infos['ZoneAttaquant'],'TEAMWORK')])) {
                        $bonus += 4;
                    } 
                    break;
                case 602 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')]) == false) {
                        $bonus += 1;
                    } 
                    break;
                case 513 : 
                    if ($this->infos['nombreTeamworkDefenseur'] == 3) {
                        $bonus += 2;
                    }
                    break;
                case 370 :
                case 539 :
                    if ($this->infos['nombreTeamworkDefenseur']<$this->infos['nombreTeamworkAttaquant']) {
                        $bonus += 2;
                    }
                    break;
                case 111 :
                case 366 :
                case 538 :
                    if ($this->infos['nombreTeamworkDefenseur']<$this->infos['nombreTeamworkAttaquant']) {
                        $bonus += 3;
                    }
                    break;
                case 534 :
                case 536 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['ENERGIE_ROUGE'])) {
                        $bonus = count($this->CarteEnJeus[$numeroAttaquant]['ENERGIE_ROUGE']);
                    }
                    break;                    
                case 622 : 
                    $bonus += 3 - $this->infos['nombreTeamworkAttaquant'];
                    break;
                case 652 : 
                    $bonus += (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE']) == false) ? 1 : 0;
                    $bonus += (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE']) == false) ? 1 : 0;
                    $bonus += (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE']) == false) ? 1 : 0;
                    break;
                case 398 :
                    if ($this->CarteEnJeus[$numeroAttaquant][$this->tools->zoneCorrespondante($this->infos['ZoneAttaquant'],'TEAMWORK')]) {
                        $bonus += 1;
                    }
                    break;

                // zone chargée
                case 130 : 
                    if (
                        (($this->infos['chamberChargeAttaquant']) && ($Cartejeu->getEmplacement()=='TEAMWORK_VERTE'))
                        || (($this->infos['deckChargeAttaquant']) && ($Cartejeu->getEmplacement()=='TEAMWORK_JAUNE'))
                        || (($this->infos['discardChargeAttaquant']) && ($Cartejeu->getEmplacement()=='TEAMWORK_ROUGE'))
                    ) {
                        $bonus += 1;
                    }
                    break;
                case 196 : 
                case 197 : 
                case 279 : 
                case 398 : 
                    if (
                        (($this->infos['chamberChargeAttaquant']) && ($this->infos['ZoneAttaquant']=='STRIKE_VERT'))
                        || (($this->infos['deckChargeAttaquant']) && ($this->infos['ZoneAttaquant']=='STRIKE_JAUNE'))
                        || (($this->infos['discardChargeAttaquant']) && ($this->infos['ZoneAttaquant']=='STRIKE_ROUGE'))
                    ) {
                        $bonus += 1;
                    }
                    break;
                case 584 : 
                    $bonus += $this->infos['chamberChargeDefenseur'] ? 1 : 0;
                    $bonus += $this->infos['deckChargeDefenseur'] ? 1 : 0;
                    $bonus += $this->infos['discardChargeDefenseur'] ? 1 : 0;
                    break;
                // aucun non strike
                case 96 :
                    $effets = $this->Partie->$proprieteEffetDefenseur();
                    $trouve = false;
                    foreach ($effets as $tab) {
                        if (isset($tab['non-strike'])) {
                            $trouve = true;
                            break;
                        }
                    }
                    if ($trouve) {
                        $bonus += 5;
                    }
                    break;
                case 644 : 
                    if (
                        (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE']) && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE'],array('type'=> 'TEAMWORK', 'nom' => 'robin'))))
                        && (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE']) && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE'],array('type'=> 'TEAMWORK', 'nom' => 'robin'))))
                        && (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE']) && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE'],array('type'=> 'TEAMWORK', 'nom' => 'robin'))))
                        ) {
                        $bonus += 4;
                    }
                    break;


            }
        }
        $CarteEnJeus = (isset($this->CarteEnJeus[$numeroDefenseur]['ACTIVE'])) ? $this->CarteEnJeus[$numeroDefenseur]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($numeroDefenseur,$Carte);
            switch ($numeroEffet) {
                case 502 : 
                    $bonus -= 2;
                    break;
                case 678 : 
                case 706 : 
                case 712 : 
                    $bonus -= 1;
                    break;
                // zone rouge
                case 742 : 
                    if ($this->infos['ZoneDefenseur']=='STRIKE_ROUGE') {
                        $bonus -= 3;
                    }
                    break;
            }
        }

        $effets = $this->Partie->$proprieteEffetAttaquant();

        foreach ($effets as $tab) {
            if (isset($tab['force'])) {
                $bonus += (int)$tab['force'];
            }
        }

        return $bonus;
    }

    public function bonusDefense($numeroDefenseur,$numeroAttaquant) {
        $bonus = 0;
        $CarteEnJeus = $this->CarteEnJeus[$numeroDefenseur]['ACTIVE'];
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($numeroDefenseur,$Carte);
            switch ($numeroEffet) {
            	case 17 : 
            		$bonus -= 3;
            		break;
                case 6 : 
                case 23 : 
                case 445 : 
                    $bonus -= 2;
                    break;
                case 220 : 
                case 270 : 
                case 466 : 
                case 439 : 
                case 472 : 
                case 727 : 
                    $bonus -= 1;
                    break;
                case 4 : 
                case 24 : 
                case 34 : 
                case 63 : 
                case 116 : 
                case 224 : 
                case 456 : 
                case 670 : 
                case 716 : 
                    $bonus += 1;
                    break;
                case 192 : 
                case 428 : 
                case 728 : 
                case 734 : 
                    $bonus += 2;
                    break;
                case 743 : 
                    $bonus += 3;
                    break;

                // = force adverse 
                case 297 : 
                case 299 : 
                case 540 : 
                    $bonus += $this->infos['attaqueAttaquant'];
                    break;

                // zone rouge
                case 220 : 
                    if (($this->infos['ZoneDefenseur']=='STRIKE_ROUGE')) {
                        $bonus += 1;
                    }
                    break;
                case 29 : 
                    if (($this->infos['ZoneDefenseur']=='STRIKE_ROUGE')) {
                        $bonus += 2;
                    }
                    break;

                // nombre carte
                case 541 :
                case 563 :
                case 709 :
                    $bonus += $this->infos['nombreTeamworkDefenseur'];
                    break;
                case 127 :
                    $bonus += 2 * $this->infos['nombreTeamworkDefenseur'];
                    break;
                case 369 :
                    if ($this->infos['nombreTeamworkDefenseur']>$this->infos['nombreTeamworkAttaquant']) {
                        $bonus += 2;
                    }
                    break;
                case 420 :
                case 636 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')])) {
                        $bonus += 1;
                    } 
                    break;
                case 633 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')])) {
                        $bonus += 4;
                    } 
                    break;

                // zone chargée
                case 193 : 
                case 196 : 
                case 279 : 
                case 399 : 
                    if (
                        (($this->infos['chamberChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_VERT'))
                        || (($this->infos['deckChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_JAUNE'))
                        || (($this->infos['discardChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_ROUGE'))
                    ) {
                        $bonus += 1;
                    }
                    break;
                case 173 : 
                    if (
                        (($this->infos['chamberChargeDefenseur']) && ($Cartejeu->getEmplacement()=='STRIKE_VERT'))
                        || (($this->infos['deckChargeDefenseur']) && ($Cartejeu->getEmplacement()=='STRIKE_JAUNE'))
                        || (($this->infos['discardChargeDefenseur']) && ($Cartejeu->getEmplacement()=='STRIKE_ROUGE'))
                    ) {
                        $bonus += 2;
                    }
                    break;

                // force adverse
                case 425 : 
                    if ($this->infos['attaqueAttaquant']>=7) {
                        $bonus += 3;
                    }
                    break;

                case 591 : 
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE']) && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE'],array('type'=> 'TEAMWORK', 'trait' => 'neutre')))) {
                        $bonus += 1;
                    }
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE']) && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE'],array('type'=> 'TEAMWORK', 'trait' => 'neutre')))) {
                        $bonus += 1;
                    }
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE']) && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE'],array('type'=> 'TEAMWORK', 'trait' => 'neutre')))) {
                        $bonus += 1;
                    }
                    break;
                case 644 : 
                    if (
                        (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE']) && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE'],array('type'=> 'TEAMWORK', 'nom' => 'batman'))))
                        && (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE']) && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE'],array('type'=> 'TEAMWORK', 'nom' => 'batman'))))
                        && (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE']) && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE'],array('type'=> 'TEAMWORK', 'nom' => 'batman'))))
                        ) {
                        $bonus += 4;
                    }
                    break;
            }
        }
        $CarteEnJeus = $this->CarteEnJeus[$numeroDefenseur]['ACTIVE'];
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($numeroDefenseur,$Carte);
            switch ($numeroEffet) {
                case 391 : 
                    if (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')])) {
                        $bonus += 2;
                    }
                    break;
                
            }
        }

        return $bonus;
    }

    public function chargementPossible($joueurConcerne,$CarteActive=null) {
        $chargementPossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        $Carte = null;
        if ($CarteActive!=null) {
            $Carte = $CarteActive->getCarte();            
        }
        $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
        switch ($numeroEffet) {
            case 38 : 
            case 42 : 
            case 72 : 
            case 115 : 
            case 546 : 
            case 705 : 
            case 707 : 
            case 711 : 
            case 712 : 
            case 713 : 
            case 715 : 
            case 716 : 
            case 718 : 
            case 721 : 
            case 722 : 
            case 727 : 
            case 728 : 
            case 731 : 
            case 735 : 
            case 737 : 
            case 738 : 
                $chargementPossible = false;
                break;
        }
        var_dump($chargementPossible);

        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 684 : 
                    $chargementPossible = true;
                    break;
            }
        }
        var_dump($chargementPossible);


        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 22 : 
                case 27 : 
                case 156 : 
                case 284 : 
                case 353 : 
                case 427 : 
                case 524 : 
                case 535 : 
                case 736 : 
                    $chargementPossible = false;
                    break;
                case 617 :
                    if ($joueurAdverse == $this->infos['numeroAttaquant']) {
                        $chargementPossible = false;
                        break;                        
                    }

            }
        }

        return $chargementPossible;
    }

    public function dechargementPossible($joueurConcerne) {
        $dechargementPossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 724 : 
                case 731 : 
                    $dechargementPossible = false;
                    break;
                // non déchargeable quand on attauqe
                case 185 : 
                case 198 : 
                case 646 : 
                    if ($joueurConcerne == $this->numeroAttaquant) {
                        $dechargementPossible = false;                        
                    }
                    break;
            }
        }


        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 0 : 
                    $dechargementPossible = false;
                    break;
            }
        }*/



        return $dechargementPossible;
    }

    public function avantageImmediat($joueurConcerne, $CarteActive) 
    {
        $avantageImmediat = false;
        $Carte = $CarteActive->getCarte();
        if ($Carte == null) {
            continue;
        }
        $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
        $avantageImmediat = in_array($numeroEffet,array(5,8,31,38,42,47,51,52,70,72,79,81,101,104,105,115,121,131,
            199,200,201,202,204,208,209,210,212,213,214,215,216,218,219,222,223,278,429,432,433,434,435,436,438,
            440,444,449,450,452,499,501,504,546,549,712,717,718,721,725,726,730,732,733,735,737,739,740,744,746,747));

        return $avantageImmediat;
    }

    public function avantagePossible($joueurConcerne) {
        $avantagePossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 91 : 
                    $avantagePossible = false;
                    break;
            }
        }*/

        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 647 : 
                    $avantagePossible = false;
                    break;
            }
        }

        return $avantagePossible;

    }

    public function focusPossible($joueurConcerne) {
        $focusPossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        $proprieteEffetJoueurConcerne = "getJoueur".$joueurConcerne."Effets";
        $effets = $this->Partie->$proprieteEffetJoueurConcerne();
        foreach ($effets as $tab) {
            if (isset($tab['no-focus'])) {
                $focusPossible = false;
            }
        }

        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 65 : 
                case 71 : 
                case 91 : 
                    $focusPossible = false;
                    break;
            }
        }


        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 27 : 
                case 123 : 
                case 194 : 
                case 353 : 
                case 647 : 
                    $focusPossible = false;
                    break;
            }
        }

        return $focusPossible;

    }

    public function pitchPossible($joueurConcerne) {
        $pitchPossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        $proprieteEffetJoueurConcerne = "getJoueur".$joueurConcerne."Effets";
        $effets = $this->Partie->$proprieteEffetJoueurConcerne();
        foreach ($effets as $tab) {
            if (isset($tab['no-pitch'])) {
                $pitchPossible = false;
            }
        }

        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 91 : 
                    $pitchPossible = false;
                    break;
            }
        }*/


        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 27 : 
                case 123 : 
                case 194 : 
                case 353 : 
                case 647 : 
                case 714 : 
                    $pitchPossible = false;
                    break;
            }
        }

        return $pitchPossible;

    }

    public function jouerPossible($joueurConcerne) {
        $jouerPossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 64 : 
                    if (
                        ($this->infos['typeCarteActive']=='STRIKE')
                        && (($this->infos['ZoneDefenseur']=='STRIKE_VERT') || ($this->infos['ZoneDefenseur']=='STRIKE_JAUNE'))
                        ) {
                        $jouerPossible = false;                        
                    }
                    break;
            }
        }


        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 132 : 
                    if (
                        ($this->infos['ZoneDefenseur']=='STRIKE_VERT')
                        && (($this->infos['typeCarteActive']=='TEAMWORK') || ($this->infos['typeCarteActive']=='ADVANTAGE'))
                        ) {
                        $jouerPossible = false;                        
                    }
                    break;
                case 426 : 
                    if (
                        ($this->infos['ZoneDefenseur']=='STRIKE_VERT')
                        && (($this->infos['typeCarteActive']=='STRIKE') || ($this->infos['typeCarteActive']=='TEAMWORK'))
                        ) {
                        $jouerPossible = false;                        
                    }
                    break;
                case 490 : 
                    if (
                        ($this->infos['ZoneDefenseur']=='STRIKE_VERT') && ($this->infos['typeCarteActive']=='STRIKE')
                        ) {
                        $jouerPossible = false;                        
                    }
                    break;
                case 503 : 
                    if ($this->infos['ZoneDefenseur']=='STRIKE_VERT') {
                        $jouerPossible = false;                        
                    }
                    break;
                case 571 : 
                    if (($this->infos['typeCarteActive']=='ADVANTAGE') || ($this->infos['typeCarteActive']=='TEAMWORK')) {
                        $jouerPossible = false;                        
                    }
                    break;
                case 606 : 
                    if ($this->infos['typeCarteActive']=='ADVANTAGE') {
                        $jouerPossible = false;                        
                    }
                    break;
            }
        }

        return $jouerPossible;

    }

    public function signaturePossible($joueurConcerne) {
        $signaturePossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 91 : 
                    $signaturePossible = false;
                    break;
            }
        }*/


        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 22 : 
                case 27 : 
                case 168 : 
                case 284 : 
                case 427 : 
                case 503 : 
                case 682 : 
                case 693 : 
                case 705 : 
                case 736 : 
                    $signaturePossible = false;
                    break;
            }
        }

        return $signaturePossible;

    }

    public function reflipsPossible($joueurConcerne) {
        $reflip = array();
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        $energieVerteDisponible = $this->infos['energieVerteDisponibleDefenseur'] + $this->infos['energieJauneDisponibleDefenseur'] + $this->infos['energieRougeDisponibleDefenseur'];
        $energieJauneDisponible = $this->infos['energieJauneDisponibleDefenseur'] + $this->infos['energieRougeDisponibleDefenseur'];
        $energieRougeDisponible = $this->infos['energieRougeDisponibleDefenseur'];

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                // actif que pour la carte elle même
                case 9 : 
                case 17 : 
                case 39 : 
                case 84 : 
                case 102 : 
                case 147 : 
                case 158 : 
                case 177 : 
                case 191 : 
                case 213 : 
                case 216 : 
                case 231 : 
                case 245 : 
                case 257 : 
                case 373 : 
                case 406 : 
                case 425 : 
                case 445 : 
                case 487 : 
                case 491 : 
                case 671 : 
                    if (
                        ($Cartejeu->getEmplacement() == $this->infos['ZoneDefenseur'])
                        && ($energieVerteDisponible>1)
                        ) {
                            $reflip['reflip_green'] = 'Reflip: green';                        
                    }
                    break;                        
                case 416 : 
                    if (
                        ($Cartejeu->getEmplacement() == $this->infos['ZoneDefenseur']) 
                        && ($energieJauneDisponibler>1)
                        ) {
                            $reflip['reflip_yellow'] = 'Reflip: yellow';
                        }
                    break;
                case 479 : 
                    if ($Cartejeu->getEmplacement() == 'STRIKE_JAUNE') {
                        $reflip['reflip_free'] = 'Reflip: free';
                    }
                    break;
                // type de carte
                case 451 :
                case 458 :
                    if ($this->infos['typeCarteActive'] == 'STRIKE') {
                        $reflip['reflip_green'] = 'Reflip: green';                        
                    }
                    break;
                case 453 :
                    if (($this->infos['typeCarteActive'] == 'ADVANTAGE') || ($this->infos['typeCarteActive'] == 'TEAMWORK')) {
                        $reflip['reflip_green'] = 'Reflip: green';                        
                    }
                    break;
                case 496 :
                    if (($this->infos['typeCarteActive'] == 'ADVANTAGE') || ($this->infos['typeCarteActive'] == 'TEAMWORK')) {
                        $reflip['reflip_free'] = 'Reflip: free';                        
                    }
                    break;
                // dans la zone d'un teamwork
                case 20 :
                case 418 :
                    if (
                        ($Cartejeu->getEmplacement() == $this->infos['ZoneDefenseur']) 
                        && (isset($this->CarteEnJeus[$joueurConcerne][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')])) 
                        && ($energieVerteDisponible>1)
                        )
                        {
                        $reflip['reflip_green'] = 'Reflip: green';
                    } 
                    break;
                // defausser la carte donnabt le reflip
                case 203 : 
                    if ($Cartejeu->getEmplacement() == 'ADVANTAGE') {
                        $reflip['reflip_' . $Cartejeu->getId()] = 'Reflip: Eliminate Pai sho mastery';
                    }
                    break;
                case 467 : 
                    if (
                        ($Cartejeu->getEmplacement() == 'TEAMWORK_VERTE')
                        || ($Cartejeu->getEmplacement() == 'TEAMWORK_JAUNE')
                        || ($Cartejeu->getEmplacement() == 'TEAMWORK_ROUGE')
                        ) {
                        $reflip['reflip_' . $Cartejeu->getId()] = 'Reflip: Eliminate bootstrap bill';
                    }
                    break;

            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 339 : 
                    if ($energieVerteDisponible>1) {
                        $reflip['reflip_green'] = 'Reflip: green';
                    }
                    break;
            }
        }
        // carte adverse empechant le reflip
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 388 : 
                case 535 : 
                    $reflip = array();
                    break;
            }
        }

        return $reflip;
    }

    public function coutsCarte($joueurConcerne,$CartePartie) {
        $coutVert = 0;
        $coutJaune = 0;
        $coutRouge = 0;
        $jauneEnVert = false;
        $rougeEnJaune = false;
        $pasVert = false;
        $pasJaune = false;
        $pasRouge = false;

        if ($CartePartie!=null) {
            if ($CartePartie instanceof \jeus\QuickstrikeBundle\Entity\CartePartie) {
                $Carte = $CartePartie->getCarte();
            } else {
                $Carte = $CartePartie;
            }

            if (!$Carte instanceof \jeus\QuickstrikeBundle\Entity\Carte) {
                var_dump($Carte);
                var_dump($CartePartie);
                exit;
            }

            $coutVert = $Carte->getCoutVert();
            $coutJaune = $Carte->getCoutJaune();
            $coutRouge = $Carte->getCoutRouge();

            $joueurAdverse = ($joueurConcerne==1)?2:1;

            $proprieteEffetJoueurConcerne = "getJoueur".$joueurConcerne."Effets";


            // effet des cartes du joueur concerné
            $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
            foreach ((array)$CarteEnJeus as $Cartejeu) {
                $Carte = $Cartejeu->getCarte();
                if ($Carte == null) {
                    continue;
                }
                $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
                switch ($numeroEffet) {
                    // payer les jaunes en vertes
                    case 83 : 
                        $jauneEnVert = true;
                        break;

                    // payer les jaunes en vertes pour les avantages et alliés
                    case 544 : 
                        if (($this->infos['typeCarteActive']=='ADVANTAGE') || ($this->infos['typeCarteActive']=='TEAMWORK')) {
                            $jauneEnVert = true;
                        }
                        break;
                    // payer les rouges en jaunes
                    case 78 : 
                    case 505 : 
                        $rougeEnJaune = true;
                        break;
                    // -1 vert si strike
                    case 703 :
                        if ($this->infos['typeCarteActive']=='STRIKE') {
                            $coutVert--;
                        }
                        break;
                    // -1 vert par avantage joué ce tour
                    case 604 :
                        $effets = $this->Partie->$proprieteEffetJoueurConcerne();
                        foreach ($effets as $tab) {
                            if (isset($tab['avantage'])) {
                                $coutVert--;
                            }
                        }
                        break;
                    // -1 rouge si teamwork
                    case 658 : 
                        if (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')])) {
                            $coutRouge--;
                        }
                        break;
                    // pas de cout de la zone en cours pour les strikes
                    case 697 : 
                        if ($this->infos['typeCarteActive']=='STRIKE') {
                            if ($this->infos['ZoneDefenseur']=='STRIKE_VERT') {
                                $pasVert = true;
                            }
                            if ($this->infos['ZoneDefenseur']=='STRIKE_JAUNE') {
                                $pasJaune = true;
                            }
                            if ($this->infos['ZoneDefenseur']=='STRIKE_ROUGE') {
                                $pasRouge = true;
                            }
                        }
                        break;
                }
            }

            // effet des cartes de l'adversaire
            $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
            foreach ((array)$CarteEnJeus as $Cartejeu) {
                $Carte = $Cartejeu->getCarte();
                if ($Carte == null) {
                    continue;
                }
                $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
                switch ($numeroEffet) {
                    // +1 jaune
                    case 589 :
                        $coutJaune++;                            
                        break;
                    // +1 vert \ strike
                    case 380 :
                    case 639 :
                        if ($this->infos['typeCarteActive']=='STRIKE') {
                            $coutVert++;                            
                        }
                        break;
                    // +1 vert \ avantage
                    case 691 :
                        if ($this->infos['typeCarteActive']=='ADVANTAGE') {
                            $coutVert++;                            
                        }
                        break;
                    // +1 vert si pas de non strike
                    case 97 :
                        $effets = $this->Partie->$proprieteEffetJoueurConcerne();
                        $trouve = false;
                        foreach ($effets as $tab) {
                            if (isset($tab['non-strike'])) {
                                $trouve = true;
                                break;
                            }
                        }
                        if (!$trouve) {
                            $coutVert++;                                                        
                        }
                        break;
                    // +1 jaune \ strike
                    case 158 :
                    case 159 :
                        if ($this->infos['typeCarteActive']=='STRIKE') {
                            $coutJaune++;                            
                        }
                        break;
                    // +1 rouge \ strike
                    case 277 :
                    case 537 :
                        if ($this->infos['typeCarteActive']=='STRIKE') {
                            $coutRouge++;                            
                        }
                        break;
                    // +1 vert \ strike  si pas de teamwork
                    case 92 : 
                        if (
                            ($this->infos['typeCarteActive']=='STRIKE')
                            && (!isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')]))
                            ) {
                            $coutVert++;
                        }
                        break;
                }
            }


        }

        $coutVert = max($coutVert,0);
        $coutJaune = max($coutJaune,0);
        $coutRouge = max($coutRouge,0);

        if ($pasVert) {
            $coutVert = 0;
        }
        if ($pasJaune) {
            $coutJaune = 0;
        }
        if ($pasRouge) {
            $coutRouge = 0;
        }

        if ($jauneEnVert) {
            $coutVert += $coutJaune;
            $coutJaune = 0;
        }
        if ($rougeEnJaune) {
            $coutJaune += $coutRouge;
            $coutRouge = 0;
        }

        return array(
            'coutVert' => $coutVert,
            'coutJaune' => $coutJaune,
            'coutRouge' => $coutRouge,
            );
    }

    public function deployPossible($joueurConcerne) {
        $deploy = array();
        if ($this->infos['typeCarteActive'] !== 'TEAMWORK') {
            return $deploy;
        }
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        $energieVerteDisponible = $this->infos['energieVerteDisponibleDefenseur'] + $this->infos['energieJauneDisponibleDefenseur'] + $this->infos['energieRougeDisponibleDefenseur'];
        $energieJauneDisponible = $this->infos['energieJauneDisponibleDefenseur'] + $this->infos['energieRougeDisponibleDefenseur'];
        $energieRougeDisponible = $this->infos['energieRougeDisponibleDefenseur'];
        $coutsCarte = $this->coutsCarte($joueurConcerne,$this->infos['carteActive']);
        $energieRougeDisponible -= $coutsCarte['coutRouge'];
        $energieJauneDisponible -= $coutsCarte['coutRouge'] - $coutsCarte['coutJaune'];
        $energieVerteDisponible -= $coutsCarte['coutRouge'] - $coutsCarte['coutJaune'] - $coutsCarte['coutVert'];

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 431 : 
                case 472 : 
                    if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_VERTE'])) {
                        $deploy['deploy_green_free'] = 'Deploy green: free';
                    }
                    if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_JAUNE'])) {
                        $deploy['deploy_yellow_free'] = 'Deploy yellow: free';
                    }
                    if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_ROUGE'])) {
                        $deploy['deploy_red_free'] = 'Deploy red: free';
                    }
                    break;
                case 704 : 
                    if ($this->tools->isCarteCorrespondante($this->infos['CarteActive'],array('type'=> 'TEAMWORK', 'extension' => 'Batman', 'trait' => 'light'))) {
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_VERTE'])) {
                            $deploy['deploy_green_free'] = 'Deploy green: free';
                        }
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_JAUNE'])) {
                            $deploy['deploy_yellow_free'] = 'Deploy yellow: free';
                        }
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_ROUGE'])) {
                            $deploy['deploy_red_free'] = 'Deploy red: free';
                        }
                    }
                    break;
                case 454 : 
                case 456 : 
                case 458 : 
                case 476 : 
                    if ($energieVerteDisponible>1) {
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_VERTE'])) {
                            $deploy['deploy_green_green'] = 'Deploy green: green';
                        }
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_JAUNE'])) {
                            $deploy['deploy_yellow_green'] = 'Deploy yellow: green';
                        }
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_ROUGE'])) {
                            $deploy['deploy_red_green'] = 'Deploy red: green';
                        }
                    }
                    break;
                case 464 : 
                case 465 : 
                case 467 : 
                case 696 : 
                    if ($energieJauneDisponible>1) {
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_VERTE'])) {
                            $deploy['deploy_green_yellow'] = 'Deploy green: yellow';
                        }
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_JAUNE'])) {
                            $deploy['deploy_yellow_yellow'] = 'Deploy yellow: yellow';
                        }
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_ROUGE'])) {
                            $deploy['deploy_red_yellow'] = 'Deploy red: yellow';
                        }
                    }
                    break;
                case 470 : 
                    if ($energieRougeDisponible>1) {
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_VERTE'])) {
                            $deploy['deploy_green_red'] = 'Deploy green: red';
                        }
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_JAUNE'])) {
                            $deploy['deploy_yellow_red'] = 'Deploy yellow: red';
                        }
                        if (!isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_ROUGE'])) {
                            $deploy['deploy_red_red'] = 'Deploy red: red';
                        }
                    }
                    break;
            }
        }

        // carte adverse empechant le deploy
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 388 : 
                case 394 : 
                case 535 : 
                    $deploy = array();
                    break;
            }
        }

        return $deploy;
    }

    public function effetJouer($joueurConcerne,$action) {
        $joueurAdverse = ($joueurConcerne==1)?2:1;
        if ($this->Partie->getEtape($joueurConcerne) == 'utilisationChamber') {
            $CarteJouee = $this->CarteEnJeus[$joueurConcerne]['CHAMBER'];
        } else {
            $CarteJouee = $this->CarteEnJeus[$joueurConcerne][$this->infos['ZoneDefenseur']];            
        }

        if ($this->infos['typeCarteActive'] != 'STRIKE') {
            $this->interactions->ajoutEffet($joueurConcerne,null,'non-strike','oui');
        }

        if ($this->infos['typeCarteActive'] == 'ADVANTAGE') {
            $this->interactions->ajoutEffet($joueurConcerne,null,'avantage','1');
        }

        $numeroDefenseur = $this->infos['numeroDefenseur'];
        $numeroAttaquant = $this->infos['numeroAttaquant'];

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            // si la carte jouée est un teamwork on n'applique pas son effet
            if (($Cartejeu->getId()==$CarteJouee->getId()) && ($this->infos['typeCarteActive'] == 'TEAMWORK'))  {
                continue;
            }

            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                // charge zone en cours
                case 1 :
                case 62 :
                case 87 :
                case 177 :
                case 178 :
                case 260 :
                case 620 :
                    $this->chargerUneZone($joueurConcerne,$this->infos['ZoneDefenseur']);
                    break;
                // charge zone en cours si force adverse <=3
                case 620 :
                case 651 :
                    if ($this->infos['attaqueAttaquant']<=3) {
                        $this->chargerUneZone($joueurConcerne,$this->infos['ZoneDefenseur']);                        
                    }
                    break;
                // charge zone en cours si teamwork dans la zone
                case 651 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')])) {
                        $this->chargerUneZone($joueurConcerne,$this->infos['ZoneDefenseur']);                        
                    }
                    break;
                // charge chamber
                case 333 : 
                    $this->chargerUneZone($joueurConcerne,'STRIKE_VERT');
                    break;
                // charge deck
                case 333 : 
                    $this->chargerUneZone($joueurConcerne,'STRIKE_JAUNE');
                    break;
                // charge deck si zone jaune
                case 482 : 
                    if ($this->infos['ZoneDefenseur']=='STRIKE_JAUNE') {
                        $this->chargerUneZone($joueurConcerne,'STRIKE_JAUNE');                        
                    }
                    break;
                // charge toutes les zones
                case 746 : 
                    $this->chargerUneZone($joueurConcerne,'STRIKE_VERT');
                    $this->chargerUneZone($joueurConcerne,'STRIKE_JAUNE');
                    $this->chargerUneZone($joueurConcerne,'STRIKE_ROUGE');
                    break;
                // charge toutes les zones avec teamwork batman
                case 561 : 
                    if (
                        (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE']))
                        && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE'],array('type'=> 'TEAMWORK', 'extension' => 'Batman', 'trait' => 'light')))
                            ) {
                        $this->chargerUneZone($joueurConcerne,'STRIKE_VERT');                            
                    }
                    if (
                        (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE']))
                        && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE'],array('type'=> 'TEAMWORK', 'extension' => 'Batman', 'trait' => 'light')))
                            ) {
                        $this->chargerUneZone($joueurConcerne,'STRIKE_JAUNE');
                    }
                    if (
                        (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE']))
                        && ($this->tools->isCarteCorrespondante($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE'],array('type'=> 'TEAMWORK', 'extension' => 'Batman', 'trait' => 'light')))
                            ) {
                        $this->chargerUneZone($joueurConcerne,'STRIKE_ROUGE');
                    }
                    break;
                // decharger toutes les zones
                case 351 : 
                    $this->dechargerUneZone($joueurAdverse,'STRIKE_VERT');
                    $this->dechargerUneZone($joueurAdverse,'STRIKE_JAUNE');
                    $this->dechargerUneZone($joueurAdverse,'STRIKE_ROUGE');
                    break;
                // decharger toutes les zones sans teamwork
                case 515 : 
                    if (!isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE'])) {
                        $this->dechargerUneZone($joueurAdverse,'STRIKE_VERT');
                    }
                    if (!isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE'])) {
                        $this->dechargerUneZone($joueurAdverse,'STRIKE_JAUNE');
                    }
                    if (!isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE'])) {
                        $this->dechargerUneZone($joueurAdverse,'STRIKE_ROUGE');
                    }
                    break;

                // monte d'1 zone
                case 105 :
                case 208 :
                    if ($action == 'jouer')  {
                        if ($this->infos['ZoneDefenseur'] == 'STRIKE_JAUNE') {
                            $this->Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_VERT');
                        }
                        if ($this->infos['ZoneDefenseur'] == 'STRIKE_ROUGE') {
                            $this->Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_JAUNE');
                        }
                    }
                    break;
                // repasse en zone verte
                case 121 :
                case 737 :
                    $this->Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_VERT');
                    break;
                // discard teamwork et avantage
                case 293 : 
                    $this->deplacerCarte($joueurAdverse,99,'ADVANTAGE','DISCARD');
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_VERTE','DISCARD');
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_JAUNE','DISCARD');
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_ROUGE','DISCARD');
                    break;                    
                // discard teamwork
                case 295 : 
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_VERTE','DISCARD');
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_JAUNE','DISCARD');
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_ROUGE','DISCARD');
                    break;                    
                // discard all teamwork
                case 586 : 
                    $this->deplacerCarte($joueurConcerne,1,'TEAMWORK_VERTE','DISCARD');
                    $this->deplacerCarte($joueurConcerne,1,'TEAMWORK_JAUNE','DISCARD');
                    $this->deplacerCarte($joueurConcerne,1,'TEAMWORK_ROUGE','DISCARD');
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_VERTE','DISCARD');
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_JAUNE','DISCARD');
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_ROUGE','DISCARD');
                    break;                    
                // -1 verte \ adversaire
                case 75 :
                case 98 : 
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_VERTE','DISCARD');
                    break;
                // -1 jaune \ adversaire
                case 259 : 
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_JAUNE','DISCARD');
                    break;
                // -1 rouge \ adversaire
                case 37 :
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_ROUGE','DISCARD');
                    break;
                // -1 jaune ou vert \ adversaire
                case 101 :
                    $nombre = 1;
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_JAUNE','DISCARD');
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_VERTE','DISCARD');
                    break;
                // -1 jaune ou -2 vert \ adversaire
                case 730 :
                    $nombre = 1;
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_JAUNE','DISCARD');
                    if ($nombre==1) {
                        $this->deplacerCarte($joueurAdverse,2,'ENERGIE_VERTE','DISCARD');                        
                    }
                    break;
                // -1 energie \ adversaire
                case 497 :
                    $nombre = 1;
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_ROUGE','DISCARD');
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_JAUNE','DISCARD');
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_VERTE','DISCARD');
                    break;
                // -2 energie \ adversaire
                case 349 :
                    $nombre = 2;
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_ROUGE','DISCARD');
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_JAUNE','DISCARD');
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_VERTE','DISCARD');
                    break;
                // si force <3 -3 energie \ adversaire
                case 294 :
                    if ($this->infos['attaqueAttaquant']<=3) {
                        $nombre = 3;
                        $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_ROUGE','DISCARD');
                        $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_JAUNE','DISCARD');
                        $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_VERTE','DISCARD');                        
                    }
                    break;
                // -1 energie de la zone en cours \ adversaire
                case 721 :
                    $this->deplacerCarte($joueurAdverse,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    break;
                // -4 energies de la zone en cours \ adversaire
                case 748 :
                    $this->deplacerCarte($joueurAdverse,4,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    break;
                // +1 rouge \ adversaire
                case 53 :
                    if ($action == 'counter attack')  {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                // effet qui rajoute ou déplace des énegies au joueur
                case 228 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');
                    break;
                case 320 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_ROUGE','DISCARD');
                    break;
                case 231 :
                    if ($action == 'counter attack') {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                        $this->deplacerCarte($joueurAdverse,1,'ENERGIE_VERTE','DISCARD');
                    }
                    break;
                // +1 vert \ joueur
                case 464 :
                case 580 :
                case 685 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    break;
                // +1 vert \ joueur / teamwork
                case 511 :
                    $this->deplacerCarte($joueurConcerne,$this->infos['nombreTeamworkDefenseur'],'DISCARD','ENERGIE_VERTE');
                    break;
                // +1 vert \ joueur si teamwork
                case 417 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    }
                    break;
                // +1 vert pour chaque carte en avantage discardé \ joueur
                case 47 :
                    $nombre += $this->deplacerCarte($joueurConcerne,99,'ADVANTAGE','DISCARD');
                    $nombre += $this->deplacerCarte($joueurAdverse,99,'ADVANTAGE','DISCARD');
                    $this->deplacerCarte($joueurConcerne,$nombre,'DISCARD','ENERGIE_VERTE');
                    break;
                // +1 jaune \ joueur
                case 38 :
                case 211 :
                case 547 :
                case 600 :
                    if (($action == 'jouer') || ($action == 'counter attack')) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');
                    }
                    break;
                case 700 :
                    if ($action == 'jouer') {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');
                    }
                    break;
                // +1 jaune \ joueur si zone chargée
                case 409 : 
                    if (
                        (($this->infos['chamberChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_VERT'))
                        || (($this->infos['deckChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_JAUNE'))
                        || (($this->infos['discardChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_ROUGE'))
                    ) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');
                    }
                    break;
                // +2 jaune \ joueur
                case 42 :
                    $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_JAUNE');
                    break;
                // +3 jaune \ joueur
                case 310 :
                    $this->deplacerCarte($joueurConcerne,3,'DISCARD','ENERGIE_JAUNE');
                    break;
                case 215 :
                case 335 :
                case 733 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    break;
                case 72 :
                case 115 :
                case 313 :
                case 334 :
                    $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_ROUGE');
                    break;
                // deplacer 1 energie adverse vers rouge joueur
                case 529 :
                    $nombre = 1;
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_ROUGE','DISCARD');
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_JAUNE','DISCARD');
                    $nombre -= $this->deplacerCarte($joueurAdverse,$nombre,'ENERGIE_VERTE','DISCARD');
                    $this->deplacerCarte($joueurConcerne,1-$nombre,'DISCARD','ENERGIE_ROUGE');
                    break;                    
                case 45 :
                case 157 :
                    if ($action == 'counter attack')  {
                        $this->deplacerCarte($joueurConcerne,1,'ENERGIE_JAUNE','ENERGIE_ROUGE');
                    }
                    break;
                case 169 :
                    if ($action == 'counter attack')  {
                        $this->deplacerCarte($joueurConcerne,1,'ENERGIE_VERTE','ENERGIE_JAUNE');
                    }
                    break;
                case 280 :
                case 443 :
                    $this->deplacerCarte($joueurConcerne,3,'ENERGIE_VERTE','ENERGIE_JAUNE');
                    break;
                // toutes les energies en rouge
                case 528 :
                    $this->deplacerCarte($joueurConcerne,99,'ENERGIE_VERTE','ENERGIE_ROUGE');
                    $this->deplacerCarte($joueurConcerne,99,'ENERGIE_JAUNE','ENERGIE_ROUGE');
                    break;
                // 3 energies vers rouge
                case 530 :
                    $nombre = $this->infos['energieVerteDisponibleDefenseur'];
                    $nombre = min(3,$nombre);
                    $this->deplacerCarte($joueurConcerne,$nombre,'ENERGIE_VERTE','ENERGIE_ROUGE');
                    $this->deplacerCarte($joueurConcerne,3-$nombre,'ENERGIE_JAUNE','ENERGIE_ROUGE');
                    break;
                // 1 vert => 1 jaune \ les 2
                case 443 :
                    $this->deplacerCarte($joueurConcerne,1,'ENERGIE_VERTE','ENERGIE_JAUNE');
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_VERTE','ENERGIE_JAUNE');
                    break;
                // 1 jaune => 1 vert \ adversaire
                case 478 :
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_JAUNE','ENERGIE_VERTE');
                    break;
                // 1 jaune => 1 vert \ adversaire
                case 642 :
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_ROUGE','ENERGIE_JAUNE');
                    break;
                // +1 energie \ joueur si teamwork dans zone contre attaque
                case 401 :
                    if (
                        ($action == 'counter attack') 
                        && (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')]))
                        ) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    }
                    break;
                // +1 vert / +1 jaune / +1 rouge \ joueur
                case 331 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    break;
                // +1 vert / +1 jaune / +1 rouge \ joueur pour chaque zone avec teamwork
                case 331 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');                        
                    }
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');                        
                    }
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;

                // +1 vert / +1 jaune / +1 rouge \ joueur pour chaque zone sans énergie
                case 331 :
                    if (!isset($this->CarteEnJeus[$numeroDefenseur]['ENERGIE_VERTE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');                        
                    }
                    if (!isset($this->CarteEnJeus[$numeroDefenseur]['ENERGIE_JAUNE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');                        
                    }
                    if (!isset($this->CarteEnJeus[$numeroDefenseur]['ENERGIE_ROUGE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;

                // +2 vert / +2 jaune / +2 rouge \ joueur
                case 317 :
                case 559 :
                    $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_VERTE');
                    $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_JAUNE');
                    $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_ROUGE');
                    break;
                // +2 vert / +2 jaune / +2 rouge \ joueur pour chaque zone avec teamwork
                case 331 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE'])) {
                        $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_VERTE');                        
                    }
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE'])) {
                        $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_JAUNE');                        
                    }
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE'])) {
                        $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                // +2 vert / +2 jaune / +2 rouge \ joueur si force adverse <3
                case 567 : 
                    if ($this->infos['attaqueAttaquant']<=3) {
                        $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_VERTE');                        
                        $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_JAUNE');                        
                        $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_ROUGE');                        
                    }
                    break;
                // discard all energie
                case 332 : 
                    $this->deplacerCarte($joueurConcerne,99,'ENERGIE_VERTE','DISCARD');
                    $this->deplacerCarte($joueurConcerne,99,'ENERGIE_JAUNE','DISCARD');                        
                    $this->deplacerCarte($joueurConcerne,99,'ENERGIE_ROUGE','DISCARD');                        
                    break;
                // -1 vert \ adversaire
                case 143 : 
                case 580 : 
                case 717 : 
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_VERTE','DISCARD');
                    break;
                // -2 vert \ adversaire
                case 144 : 
                case 599 : 
                    $this->deplacerCarte($joueurAdverse,2,'ENERGIE_VERTE','DISCARD');
                    break;
                // -x vert \ adversaire (x nb teamwork)
                case 517 : 
                    $this->deplacerCarte($joueurAdverse,$this->infos['nombreTeamworkDefenseur'],'ENERGIE_VERTE','DISCARD');
                    break;
                // -1 jaune \ adversaire
                case 521 : 
                case 650 : 
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_JAUNE','DISCARD');
                    break;
                // -3 jaune \ adversaire
                case 519 : 
                    $this->deplacerCarte($joueurAdverse,3,'ENERGIE_JAUNE','DISCARD');
                    break;
                // -1 rouge \ adversaire
                case 552 : 
                case 656 : 
                case 665 : 
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_ROUGE','DISCARD');
                    break;
                // -1 vert -1 jaune -1 rouge \ adversaire
                case 556 : 
                    $this->deplacerCarte($joueurConcerne,1,'ENERGIE_VERTE','DISCARD');
                    $this->deplacerCarte($joueurConcerne,1,'ENERGIE_JAUNE','DISCARD');                        
                    $this->deplacerCarte($joueurConcerne,1,'ENERGIE_ROUGE','DISCARD');                        
                    break;
                // -1 energie \ adversaire pour chaque zone avec teamwork
                case 331 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_VERTE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');                        
                    }
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_JAUNE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_JAUNE');                        
                    }
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_ROUGE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                case 281 :
                    $this->deplacerCarte($joueurConcerne,99,'ENERGIE_JAUNE','ENERGIE_ROUGE');
                    $this->deplacerCarte($joueurConcerne,99,'ENERGIE_VERTE','ENERGIE_JAUNE');
                    break;
                case 108 :
                    if ($action == 'counter attack')  {
                        $this->deplacerCarte($joueurConcerne,2,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    }
                    break;
                case 93 : 
                    if (($action == 'counter attack') && ($CarteJouee!=null) && ($CarteJouee->getCoutJaune()>0) && ($CarteJouee->getCoutRouge()>0))  {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    }
                    break;
                case 124 : 
                    if (($action == 'counter attack') && ($this->infos['ZoneDefenseur'] == 'STRIKE_VERT')) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                case 131 : 
                    if ($action == 'jouer') {
                        $this->deplacerCarte($joueurConcerne,$this->infos['nombreTeamworkDefenseur'],'DISCARD','ENERGIE_VERTE');
                    }
                    break;
                case 170 : 
                case 175 : 
                    if (($action == 'counter attack') && (
                        (($this->infos['chamberChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_VERT'))
                        || (($this->infos['deckChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_JAUNE'))
                        || (($this->infos['discardChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_ROUGE'))
                    )) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    }
                    break;
                case 180 : 
                    if ($action == 'counter attack') {
                        $nombre = 0;
                        if ($this->infos['chamberChargeDefenseur']) {
                            $nombre++;
                        }
                        if ($this->infos['deckChargeDefenseur']) {
                            $nombre++;
                        }
                        if ($this->infos['discardChargeDefenseur']) {
                            $nombre++;
                        }
                        $this->deplacerCarte($joueurConcerne,$nombre,'DISCARD','ENERGIE_VERTE');                            
                    }
                    break;
                // manipulation énergies des deux joueurs
                case 271 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    $this->deplacerCarte($joueurAdverse,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    break;
                // +1 energie zone en cours \ joueur
                case 590 :
                case 608 :
                case 643 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    break;

                // force = force adverse 
                case 297 : 
                case 299 : 
                case 347 : 
                case 540 : 
                    $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force',$this->infos['attaqueAttaquant']);
                    break;
                    
                // -3 force => +2 force
                case 86 : 
                case 616 : 
                    if ($this->infos['attaqueAttaquant']<=3) {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force','2');
                    }
                    break;

                // -3 force => pas de focus ou de pitch
                case 86 : 
                case 616 : 
                    if ($this->infos['attaqueAttaquant']<=3) {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'no-focus',true);
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'no-pitch',true);
                    }
                    break;

                // decharge toutes les zones adverses +1 force pour chaque zone déchargée
                case 315 : 
                    $force = 0;
                    if ($this->dechargerUneZone($joueurAdverse,'STRIKE_VERT')) {
                        $force++;
                    }
                    if ($this->dechargerUneZone($joueurAdverse,'STRIKE_JAUNE')) {
                        $force++;
                    }
                    if ($this->dechargerUneZone($joueurAdverse,'STRIKE_ROUGE')) {
                        $force++;
                    }
                    if ($force>0) {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force',$force);                        
                    }
                    break;
                // charge toutes les zones adverses +2 force pour chaque zone chargée
                case 322 : 
                    $force = 0;
                    if ($this->chargerUneZone($joueurAdverse,'STRIKE_VERT')) {
                        $force++;
                    }
                    if ($this->chargerUneZone($joueurAdverse,'STRIKE_JAUNE')) {
                        $force++;
                    }
                    if ($this->chargerUneZone($joueurAdverse,'STRIKE_ROUGE')) {
                        $force++;
                    }
                    if ($force>0) {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force',2*$force);                        
                    }
                    break;
                case 137 : 
                case 138 : 
                case 145 : 
                case 306 : 
                case 360 : 
                case 361 : 
                    if ($this->infos['attaqueAttaquant']<=3) {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force','3');
                    }
                    break;
                case 56 : 
                case 607 : 
                    if ($this->infos['attaqueAttaquant']<=3) {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force','4');
                    }
                    break;
                // -4 force
                case 554 : 
                    if ($this->infos['attaqueAttaquant']<=4) {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force','3');
                    }
                    break;

            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 217 : 
                    if (($action == 'counter attack') && ($this->infos['ZoneDefenseur'] == 'STRIKE_VERT')) {
                        $this->deplacerCarte($joueurAdverse,2,'DISCARD','ENERGIE_VERTE');
                    }
                    break;
                case 32 : 
                case 710 : 
                    if (($action == 'counter attack') && ($this->infos['ZoneDefenseur'] == 'STRIKE_VERT')) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_JAUNE');
                    }
                    break;
                case 119 : 
                case 711 : 
                    if (($action == 'counter attack') && ($this->infos['ZoneDefenseur'] == 'STRIKE_VERT')) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                case 100 :
                case 255 :
                    if ($action == 'counter attack')  {
                        $this->dechargerUneZone($joueurConcerne,$this->infos['ZoneDefenseur']);
                    }
                    break;
                case 254 : 
                    if ($action == 'counter attack') {
                        $this->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK'),'DISCARD');
                    }
                    break;
                case 258 : 
                    if ($action == 'counter attack') {
                        $this->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    }
                    break;
                case 518 : 
                    if ($action == 'counter attack') {
                        $this->deplacerCarte($joueurConcerne,99,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    }
                    break;
                case 256 : 
                    if ($action == 'counter attack') {
                        $this->dechargerUneZone($joueurConcerne,$this->infos['ZoneDefenseur']);
                        $this->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK'),'DISCARD');
                        $this->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    }
                    break;
                // supprimer des energies pour chaque carte jouée
                case 341 : 
                    $this->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    break;
                // charger zone à la contre attaque
                case 405 :
                    if ($action == 'counter attack') {
                        $this->chargerUneZone($joueurAdverse,$this->infos['ZoneDefenseur']);
                    }
                    break;
                // - x force 
                case 55 : 
                    if ($this->infos['typeCarteActive'] != 'STRIKE') {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force','-2');
                    }
                    break;
                case 583 : 
                    if ($this->infos['typeCarteActive'] != 'STRIKE') {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force','-1');
                    }
                    break;
                // + x force 
                case 33 : 
                case 76 : 
                case 741 : 
                    if ($this->infos['typeCarteActive'] != 'STRIKE') {
                        $this->interactions->ajoutEffet($joueurConcerne,$Cartejeu->getId(),'force','1');
                    }
                    break;
            }
        }

    }

    public function effetPitcher($joueurConcerne) {
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 19 :
                case 261 :
                    $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_VERTE');
                    break;
                case 46 :
                case 89 :
                    $this->deplacerCarte($joueurConcerne,2,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    break;
                case 60 :
                case 259 :
                    $this->deplacerCarte($joueurAdverse,1,'ENERGIE_JAUNE','DISCARD');
                    break;
                case 61 :
                    $this->deplacerCarte($joueurConcerne,2,'ENERGIE_JAUNE','ENERGIE_ROUGE');
                    break;
                case 260 :
                case 262 :
                    $this->chargerUneZone($joueurConcerne,$this->infos['ZoneDefenseur']);
                    break;
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                /*case 0 : 
                    $effetVoulu = false;
                    break;*/
            }
        }
    }

    public function effetFocuser($joueurConcerne) {
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                /*case 147 :
                    $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_VERTE');
                    break;*/
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 147 : 
                case 148 : 
                    $this->deplacerCarte($joueurConcerne,1,'ENERGIE_VERTE','DISCARD');
                    break;

                // -2 force
                case 99 : 
                case 161 : 
                case 329 : 
                case 589 : 
                    $this->interactions->ajoutEffet($joueurAdverse,$Cartejeu->getId(),'force','-2');
                    break;                    

                // -1 force
                case 36 : 
                case 162 : 
                case 163 : 
                case 328 : 
                    $this->interactions->ajoutEffet($joueurAdverse,$Cartejeu->getId(),'force','-1');
                    break;                    

                // +1 force
                case 49 : 
                case 227 : 
                case 253 : 
                case 257 : 
                case 337 : 
                case 338 : 
                case 421 : 
                case 708 : 
                    $this->interactions->ajoutEffet($joueurAdverse,$Cartejeu->getId(),'force','1');
                    break;

                // discard teamwork de la zone
                case 373 : 
                case 374 : 
                    $this->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK'),'DISCARD');
                    break;
                // +1 energie de cette zone
                case 345 : 
                    $this->deplacerCarte($joueurAdverse,1,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    break;

            }
        }
    }

    public function chargerUneZone($joueurConcerne,$zoneChargee,$CarteActive = null) {
        $zone = explode('_', $zoneChargee);
        if (isset($zone[1])) { 
            $chargementFait = false;
            $zone = $zone[1];
            if ((!$this->Partie->isZoneChargee($joueurConcerne,$zone)) && ($this->chargementPossible($joueurConcerne,$CarteActive))) {
                $this->Partie->chargerZone($joueurConcerne,$zoneChargee);
                $this->effetCharger($joueurConcerne,$zoneChargee);
                $chargementFait = $this->Partie->isZoneChargee($joueurConcerne,$zone);
            }
            return $chargementFait;
        } else {
            return false;
        }
    }

    public function dechargerUneZone($joueurConcerne,$zoneDechargee) {
        $zone = explode('_', $zoneDechargee);
        if (isset($zone[1])) { 
            $dechargementFait = false;
            $zone = $zone[1];
            if (($this->Partie->isZoneChargee($joueurConcerne,$zone)) && ($this->dechargementPossible($joueurConcerne))) {
                $this->dechargerUneZone($joueurConcerne,$zoneDechargee);
                $dechargementFait = $this->Partie->isZoneChargee($joueurConcerne,$zone);
            }
            return $dechargementFait;
        } else {
            return false;
        }
    }

    public function effetCharger($joueurConcerne,$zoneChargee = '') {
        $effetVoulu = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;
        if ($zoneChargee=='') {
            $zoneChargee = $this->infos['ZoneDefenseur'];
        }

        // effet des cartes du joueur concerné
        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 0 : 
                    $effetVoulu = false;
                    break;
            }
        }*/

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 136 : 
                    $this->interactions->ajoutEffet($joueurAdverse,$Cartejeu->getId(),'force','1');
                    break;
                case 509 : 
                    $this->Partie->chargerZone($joueurAdverse,$zoneChargee);
                    break;
            }
        }

        return $effetVoulu;
    }


    public function zoneDepart($joueurConcerne) {
        $zoneDepart = 'STRIKE_VERT';
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                /*case 0 : 
                    $zoneDepart = false;
                    break;*/
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 28 : 
                case 73 : 
                case 122 : 
                case 171 : 
                case 283 : 
                case 292 : 
                case 307 : 
                case 314 : 
                case 316 : 
                case 557 : 
                case 573 : 
                case 585 : 
                case 632 : 
                    if ($zoneDepart == 'STRIKE_VERT') {
                        $zoneDepart = 'STRIKE_JAUNE';                    
                    }
                    break;
                case 272 : 
                case 308 : 
                case 346 : 
                case 558 : 
                case 630 : 
                case 638 : 
                case 659 : 
                case 722 : 
                    $zoneDepart = 'STRIKE_ROUGE';                    
                    break;
                case 596 :
                case 616 :
                    if (
                        ($this->infos['attaqueAttaquant']<=3) 
                        &&($zoneDepart == 'STRIKE_VERT')
                        ) {
                        $zoneDepart = 'STRIKE_JAUNE';                    
                    }
                    break;
                case 523 :
                    if ($this->infos['attaqueAttaquant']<=3) {
                        $zoneDepart = 'STRIKE_ROUGE';                    
                    }
                    break;
                case 655 :
                    if ($this->infos['attaqueAttaquant']<=4) {
                        $zoneDepart = 'STRIKE_ROUGE';                    
                    }
                    break;

            }
        }

        return $zoneDepart;
    }

    public function effetCelebration($joueurConcerne) {
        $effetsSupplementaire = array();
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                // +1 vert \ joueur
                case 598 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    break;
                // +1 jaune \ joueur
                case 439 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');
                    break;
                // +2 jaune \ joueur
                case 611 :
                    $this->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_JAUNE');
                    break;
                // +1 red \ joueur
                case 442 :
                case 473 :
                case 707 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    break;
                // +1 energie par zone
                case 635 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    break;
                // +1 energie par zone
                case 695 :
                case 701 :
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    break;
                // 2 vert => 2 jaune \ joueur
                case 395 :
                    $this->deplacerCarte($joueurConcerne,2,'ENERGIE_VERTE','ENERGIE_JAUNE');
                    break;
                // +1 energie par zone avec teamwork \ chaque joueur
                case 510 :
                case 526 :
                    if (isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_VERTE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_JAUNE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_ROUGE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');                        
                    }
                    break;
                // +1 energie par zone avec teamwork \ adversaire 
                case 387 :
                    if (isset($this->CarteEnJeus[$joueurAdverse]['TEAMWORK_VERTE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurAdverse]['TEAMWORK_JAUNE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_JAUNE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurAdverse]['TEAMWORK_ROUGE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');                        
                    }
                    break;
                // +1 energie par zone avec teamwork \ chaque joueur
                case 396 :
                    if (isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_VERTE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_JAUNE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurConcerne]['TEAMWORK_ROUGE'])) {
                        $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurAdverse]['TEAMWORK_VERTE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurAdverse]['TEAMWORK_JAUNE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_JAUNE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurAdverse]['TEAMWORK_ROUGE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');                        
                    }
                    break;
                // celebrer deux fois
                case 623 :
                    $effetsSupplementaire['twice'] = true;
                    break;
                // eliminer tous les teamworks adverses
                case 570 : 
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_VERTE','DISCARD');     
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_JAUNE','DISCARD');     
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_ROUGE','DISCARD');     
                    break;                   
                // eliminer tous les teamworks adverses et +1 energie dans chaque zone
                case 663 : 
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_VERTE','DISCARD');     
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_JAUNE','DISCARD');     
                    $this->deplacerCarte($joueurAdverse,1,'TEAMWORK_ROUGE','DISCARD');     
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    break;                   
                // charger toutes les zones
                case 669 :
                    $this->chargerUneZone($joueurConcerne,'STRIKE_VERT');
                    $this->chargerUneZone($joueurConcerne,'STRIKE_JAUNE');
                    $this->chargerUneZone($joueurConcerne,'STRIKE_ROUGE');                    
                    break;
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                // +1 energie par zone avec teamwork \ adversaire 
                case 500 :
                    if (isset($this->CarteEnJeus[$joueurAdverse]['TEAMWORK_VERTE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurAdverse]['TEAMWORK_JAUNE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_JAUNE');                        
                    }
                    if (isset($this->CarteEnJeus[$joueurAdverse]['TEAMWORK_ROUGE'])) {
                        $this->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');                        
                    }
                    break;
            }
        }
        return $effetsSupplementaire;
    }

    public function celebrationPossible($joueurConcerne) {
        $celebrationPossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 456 : 
                    $celebrationPossible = false;
                    break;
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 424 : 
                case 514 : 
                    $celebrationPossible = false;
                    break;
            }
        }

        return $celebrationPossible;
    }

    public function eliminationPossible($joueurConcerne,$typeCarte) {
        $eliminationPossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 117 : 
                case 179 : 
                case 198 : 
                case 601 : 
                case 646 : 
                case 723 : 
                case 731 : 
                case 738 : 
                    if ($typeCarte=='TEAMWORK') {
                        $eliminationPossible = false;                        
                    }
                    break;
                case 408 : 
                case 626 : 
                    if ($typeCarte=='ENERGIE') {
                        $eliminationPossible = false;                        
                    }
                    break;
                case 423 : 
                case 625 : 
                    if (($typeCarte=='TEAMWORK') || ($typeCarte=='ENERGIE')) {
                        $eliminationPossible = false;                        
                    }
                    break;
            }
        }

        // effet des cartes de l'adversaire
        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 0 : 
                    $eliminationPossible = false;
                    break;
            }
        }*/

        return $eliminationPossible;
    }

    public function deplacementPossible($joueurConcerne,$typeCarte) {
        $deplacementPossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 179 : 
                case 198 : 
                    if ($typeCarte=='TEAMWORK') {
                        $deplacementPossible = false;                        
                    }
                    break;
                case 408 : 
                    if ($typeCarte=='ENERGIE') {
                        $deplacementPossible = false;                        
                    }
                    break;
                case 423 : 
                    if (($typeCarte=='TEAMWORK') || ($typeCarte=='ENERGIE')) {
                        $deplacementPossible = false;                        
                    }
                    break;
            }
        }

        // effet des cartes de l'adversaire
        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 0 : 
                    $deplacementPossible = false;
                    break;
            }
        }*/

        return $deplacementPossible;
    }

    public function deplacerCarte($joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal='DISCARD',$melanderDestination=false,$nombreDejaDeplace=0) {
        $tab = explode('_',$emplacementOrigine);
        $typeCarte = $tab[0];
        if ($emplacementFinal=='DISCARD') {
            if ($this->eliminationPossible($joueurConcerne,$typeCarte)) {
                $nombreDejaDeplace += $this->interactions->deplacerCarte($joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal,$melanderDestination,$nombreDejaDeplace);
            }
        } else {
            if ($this->deplacementPossible($joueurConcerne,$typeCarte)) {
                $nombreDejaDeplace += $this->interactions->deplacerCarte($joueurConcerne,$nombre,$emplacementOrigine,$emplacementFinal,$melanderDestination,$nombreDejaDeplace);
                if (strpos($emplacementFinal, 'STRIKE')===0) {
                    $this->effetsFlip($joueurConcerne);                    
                }
            }
        }

        return $nombreDejaDeplace;
    }

    public function numeroEffet($joueurConcerne,$CarteVoulu) {
        $numeroEffet = (($CarteVoulu!=null) && ($CarteVoulu->getEffet()!=null)) ? $CarteVoulu->getEffet()->getNumero(): 0;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        if ($numeroEffet != 0) {
            // effet des cartes de l'adversaire
            $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
            foreach ((array)$CarteEnJeus as $Cartejeu) {
                $Carte = $Cartejeu->getCarte();
                if ($Carte == null) {
                    continue;
                }
                $numeroEffetCarte = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
                switch ($numeroEffetCarte) {
                    case 698 : 
                    case 619 : 
                        if ($Carte->getTypeCarte()->getTag()=='TEAMWORK') {
                            $numeroEffet = 0;                        
                        }
                        break;
                }
            }
        }

        return $numeroEffet;
    }

    public function deckVisible($joueurConcerne) {
        $deckVisible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 206 : 
                case 229 : 
                case 381 : 
                case 390 : 
                case 458 : 
                case 465 : 
                case 673 : 
                    $deckVisible = false;
                    break;
                case 720 : 
                    if ($Cartejeu->getEmplacement()=='ADVANTAGE') {
                        $deckVisible = false;                        
                    }
                    break;
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 465 : 
                case 673 : 
                    $deckVisible = false;
                    break;
            }
        }

        return $deckVisible;
    }

    public function choixPossible($joueurConcerne) {
        $choix = array();
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        $energieVerteDisponible = $this->infos['energieVerteDisponibleDefenseur'] + $this->infos['energieJauneDisponibleDefenseur'] + $this->infos['energieRougeDisponibleDefenseur'];
        $energieJauneDisponible = $this->infos['energieJauneDisponibleDefenseur'] + $this->infos['energieRougeDisponibleDefenseur'];
        $energieRougeDisponible = $this->infos['energieRougeDisponibleDefenseur'];

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 2 : 
                    if (
                        ($Cartejeu->getEmplacement() == $this->infos['ZoneDefenseur'])
                        && ($energieVerteDisponible>1)
                        ) {
                            $choix['choix_green_'.$Cartejeu->getId().'_intercept_+1'] = 'green => +1 intercept';
                    }
                    break;                        
            }
        }

        // effet des cartes de l'adversaire
        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 339 : 
                    if ($energieVerteDisponible>1) {
                        $choix['choix_green'] = 'Reflip: green';
                    }
                    break;
            }
        }*/
        return $choix;
    }

    public function effetsFlip($joueurConcerne) {
        $effetVoulu = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 679 : 
                    $this->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    break;
            }
        }

        // effet des cartes de l'adversaire
        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 0 : 
                    $effetVoulu = false;
                    break;
            }
        }*/

        return $effetVoulu;
    }

    /*public function effetVoulu($joueurConcerne) {
        $effetVoulu = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurConcerne,$Carte);
            switch ($numeroEffet) {
                case 0 : 
                    $effetVoulu = false;
                    break;
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ((array)$CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = $this->numeroEffet($joueurAdverse,$Carte);
            switch ($numeroEffet) {
                case 0 : 
                    $effetVoulu = false;
                    break;
            }
        }

        return $effetVoulu;
    }*/



}
