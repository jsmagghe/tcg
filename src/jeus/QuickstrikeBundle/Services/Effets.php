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
        $CarteEnJeus = (isset($this->CarteEnJeus[$numeroAttaquant]['ACTIVE'])) ? $this->CarteEnJeus[$numeroAttaquant]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
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
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['AVANTAGE'])) {
                        foreach ($this->CarteEnJeus[$numeroAttaquant]['AVANTAGE'] as $CarteJeu) {
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
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['AVANTAGE'])) {
                        foreach ($this->CarteEnJeus[$numeroAttaquant]['AVANTAGE'] as $CarteJeu) {
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
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['AVANTAGE'])) {
                        foreach ($this->CarteEnJeus[$numeroDefenseur]['AVANTAGE'] as $CarteJeu) {
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
                case 370 :
                case 539 :
                    if ($this->infos['nombreTeamworkDefenseur']<$this->infos['nombreTeamworkAttaquant']) {
                        $bonus += 2;
                    }
                    break;
                case 111 :
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
                    $bonus += (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_VERT']) == false) ? 1 : 0;
                    $bonus += (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_JAUNE']) == false) ? 1 : 0;
                    $bonus += (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_ROUGE']) == false) ? 1 : 0;
                    break;
                case 652 : 
                    $bonus += (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERT']) == false) ? 1 : 0;
                    $bonus += (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE']) == false) ? 1 : 0;
                    $bonus += (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE']) == false) ? 1 : 0;
                    break;

                // zone chargée
                case 130 : 
                    if (
                        (($this->infos['chamberChargeAttaquant']) && ($Cartejeu->getEmplacement()=='TEAMWORK_VERT'))
                        || (($this->infos['deckChargeAttaquant']) && ($Cartejeu->getEmplacement()=='TEAMWORK_JAUNE'))
                        || (($this->infos['discardChargeAttaquant']) && ($Cartejeu->getEmplacement()=='TEAMWORK_ROUGE'))
                    ) {
                        $bonus += 1;
                    }
                    break;
                case 196 : 
                case 197 : 
                case 279 : 
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

            }
        }
        $CarteEnJeus = (isset($this->CarteEnJeus[$numeroDefenseur]['ACTIVE'])) ? $this->CarteEnJeus[$numeroDefenseur]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
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

        return $bonus;
    }

    public function bonusDefense($numeroDefenseur,$numeroAttaquant) {
        $bonus = 0;
        $CarteEnJeus = $this->CarteEnJeus[$numeroDefenseur]['ACTIVE'];
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
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
                    if (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')]) == false) {
                        $bonus += 4;
                    } 
                    break;

                // zone chargée
                case 193 : 
                case 196 : 
                case 279 : 
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

            }
        }

        return $bonus;
    }

    public function chargementPossible($CarteActive,$joueurConcerne) {
        $chargementPossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        $Carte = $CarteActive->getCarte();
        if ($Carte == null) {
            continue;
        }
        $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
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

        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 684 : 
                    $chargementPossible = true;
                    break;
            }
        }


        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 22 : 
                case 27 : 
                case 284 : 
                case 353 : 
                case 427 : 
                case 535 : 
                case 617 : 
                case 736 : 
                    $chargementPossible = false;
                    break;
            }
        }



        return $chargementPossible;
    }

    public function avantageImmediat($CarteActive) 
    {
        $avantageImmediat = false;
        $Carte = $CarteActive->getCarte();
        if ($Carte == null) {
            continue;
        }
        $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
        $avantageImmediat = in_array($numeroEffet,array(5,8,31,38,42,47,51,52,70,72,79,81,101,104,105,115,121,131,
            199,200,201,202,204,208,209,210,212,213,214,215,216,218,219,222,223,278,429,432,433,434,435,436,438,
            440,444,449,450,452,499,501,504,546,549,712,717,718,721,725,726,730,732,733,735,737,739,740,744,746,747));

        return $avantageImmediat;
    }

    public function avantagePossible($joueurConcerne) {
        $avantagePossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 91 : 
                    $avantagePossible = false;
                    break;
            }
        }*/

        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
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

        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 65 : 
                case 71 : 
                case 91 : 
                    $focusPossible = false;
                    break;
            }
        }


        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
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

        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 91 : 
                    $pitchPossible = false;
                    break;
            }
        }*/


        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
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

    public function signaturePossible($joueurConcerne) {
        $signaturePossible = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        /*$CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 91 : 
                    $signaturePossible = false;
                    break;
            }
        }*/


        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
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

        $energieVerteDisponible = $this->infos['energieVerteDisponibleAttaquant'] + $this->infos['energieJauneDisponibleAttaquant'] + $this->infos['energieRougeDisponibleAttaquant'];
        $energieJauneDisponible = $this->infos['energieJauneDisponibleAttaquant'] + $this->infos['energieRougeDisponibleAttaquant'];
        $energieRougeDisponible = $this->infos['energieRougeDisponibleAttaquant'];

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
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
                    if ($energieVerteDisponibleAttaquant>1) {
                        $reflip['reflip_green'] = 'Reflip: green';                        
                    }
                    break;
                case 416 : 
                    if ($energieJauneDisponibleAttaquant>1) {
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
                        (isset($this->CarteEnJeus[$joueurConcerne][$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK')])) 
                        && ($energieVerteDisponibleAttaquant>1)
                        )
                        {
                        $reflip['reflip_green'] = 'Reflip: green';
                    } 
                    break;
                // defausser la carte donnabt le reflip
                case 203 : 
                    if ($Cartejeu->getEmplacement() == 'AVANTAGE') {
                        $reflip['reflip_' . $Cartejeu->getId()] = 'Reflip: Eliminate Pai sho mastery';
                    }
                    break;
                case 467 : 
                    if (
                        ($Cartejeu->getEmplacement() == 'TEAMWORK_VERT')
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
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 339 : 
                    if ($energieVerteDisponibleAttaquant>1) {
                        $reflip['reflip_green'] = 'Reflip: green';
                    }
                    break;
            }
        }
        // carte adverse empechant le reflip
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 388 : 
                case 535 : 
                    $reflip = array();
                    break;
            }
        }

        return $reflip;
    }

    public function effetJouer($joueurConcerne,$action) {
        $joueurAdverse = ($joueurConcerne==1)?2:1;
        $CarteJouee = $this->CarteEnJeus[$joueurConcerne][$this->infos['ZoneDefenseur']];

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                // effets qui charge
                case 1 :
                case 62 :
                case 87 :
                case 177 :
                case 178 :
                case 260 :
                case 620 :
                    if ($action == 'counter attack')  {
                        $this->Partie->chargerZone($joueurConcerne,$this->infos['ZoneDefenseur']);
                    }
                    break;
                // effets qui change de zone
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
                case 121 :
                case 737 :
                    if ($action == 'jouer')  {
                        $this->Partie->setJoueurZoneEnCours($joueurConcerne,'STRIKE_VERT');
                    }
                    break;
                // effets qui supprime les avantages ou les teamworks
                case 293 : 
                    $this->interactions->deplacerCarte($joueurAdverse,99,'AVANTAGE','DISCARD');
                    $this->interactions->deplacerCarte($joueurAdverse,1,'TEAMWORK_VERT','DISCARD');
                    $this->interactions->deplacerCarte($joueurAdverse,1,'TEAMWORK_JAUNE','DISCARD');
                    $this->interactions->deplacerCarte($joueurAdverse,1,'TEAMWORK_ROUGE','DISCARD');
                    break;                    
                case 295 : 
                    $this->interactions->deplacerCarte($joueurAdverse,1,'TEAMWORK_VERT','DISCARD');
                    $this->interactions->deplacerCarte($joueurAdverse,1,'TEAMWORK_JAUNE','DISCARD');
                    $this->interactions->deplacerCarte($joueurAdverse,1,'TEAMWORK_ROUGE','DISCARD');
                    break;                    
                // effets qui supprime des energies à l'adversaire
                case 75 :
                case 98 : 
                    $this->interactions->deplacerCarte($joueurAdverse,1,'ENERGIE_VERTE','DISCARD');
                    break;
                case 259 : 
                    $this->interactions->deplacerCarte($joueurAdverse,1,'ENERGIE_JAUNE','DISCARD');
                    break;
                case 37 :
                    if ($action == 'counter attack')  {
                        $this->interactions->deplacerCarte($joueurAdverse,1,'ENERGIE_ROUGE','DISCARD');
                    }
                    break;
                // effets qui ajoute des energies à l'adversaire
                case 53 :
                    if ($action == 'counter attack')  {
                        $this->interactions->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                // effet qui rajoute ou déplace des énegies au joueur
                case 228 :
                    if ($action == 'counter attack') {
                        $this->interactions->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                        $this->interactions->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_VERTE');
                    }
                    break;
                case 231 :
                    if ($action == 'counter attack') {
                        $this->interactions->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                        $this->interactions->deplacerCarte($joueurAdverse,1,'ENERGIE_VERTE','DISCARD');
                    }
                    break;
                case 38 :
                case 211 :
                    if (($action == 'jouer') && ($action == 'counter attack')) {
                        $this->interactions->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_JAUNE');
                    }
                    break;
                case 42 :
                    if ($action == 'jouer')  {
                        $this->interactions->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_JAUNE');
                    }
                    break;
                case 215 :
                    if ($action == 'jouer')  {
                        $this->interactions->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                case 72 :
                case 115 :
                    if ($action == 'jouer')  {
                        $this->interactions->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                case 45 :
                    if ($action == 'counter attack')  {
                        $this->interactions->deplacerCarte($joueurConcerne,1,'ENERGIE_JAUNE','ENERGIE_ROUGE');
                    }
                    break;
                case 169 :
                    if ($action == 'counter attack')  {
                        $this->interactions->deplacerCarte($joueurConcerne,1,'ENERGIE_VERTE','ENERGIE_JAUNE');
                    }
                    break;
                case 280 :
                    $this->interactions->deplacerCarte($joueurConcerne,3,'ENERGIE_VERTE','ENERGIE_JAUNE');
                    break;
                case 281 :
                    $this->interactions->deplacerCarte($joueurConcerne,99,'ENERGIE_JAUNE','ENERGIE_ROUGE');
                    $this->interactions->deplacerCarte($joueurConcerne,99,'ENERGIE_VERTE','ENERGIE_JAUNE');
                    break;
                case 108 :
                    if ($action == 'counter attack')  {
                        $this->interactions->deplacerCarte($joueurConcerne,2,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    }
                    break;
                case 93 : 
                    if (($action == 'counter attack') && ($CarteJouee!=null) && ($CarteJouee->getCoutJaune()>0) && ($CarteJouee->getCoutRouge()>0))  {
                        $this->interactions->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
                    }
                    break;
                case 124 : 
                    if (($action == 'counter attack') && ($this->infos['ZoneDefenseur'] == 'STRIKE_VERT')) {
                        $this->interactions->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                case 131 : 
                    if ($action == 'jouer') {
                        $this->interactions->deplacerCarte($joueurConcerne,$this->infos['nombreTeamworkDefenseur'],'DISCARD','ENERGIE_VERTE');
                    }
                    break;
                case 170 : 
                case 175 : 
                    if (($action == 'counter attack') && (
                        (($this->infos['chamberChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_VERT'))
                        || (($this->infos['deckChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_JAUNE'))
                        || (($this->infos['discardChargeDefenseur']) && ($this->infos['ZoneDefenseur']=='STRIKE_ROUGE'))
                    )) {
                        $this->interactions->deplacerCarte($joueurConcerne,1,'DISCARD','ENERGIE_VERTE');
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
                        $this->interactions->deplacerCarte($joueurConcerne,$nombre,'DISCARD','ENERGIE_VERTE');                            
                    }
                    break;
                // manipulation énergies des deux joueurs
                case 271 :
                    $this->interactions->deplacerCarte($joueurConcerne,1,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    $this->interactions->deplacerCarte($joueurAdverse,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    break;

            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 217 : 
                    if (($action == 'counter attack') && ($this->infos['ZoneDefenseur'] == 'STRIKE_VERT')) {
                        $this->interactions->deplacerCarte($joueurAdverse,2,'DISCARD','ENERGIE_VERTE');
                    }
                    break;
                case 32 : 
                    if (($action == 'counter attack') && ($this->infos['ZoneDefenseur'] == 'STRIKE_VERT')) {
                        $this->interactions->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_JAUNE');
                    }
                    break;
                case 119 : 
                    if (($action == 'counter attack') && ($this->infos['ZoneDefenseur'] == 'STRIKE_VERT')) {
                        $this->interactions->deplacerCarte($joueurAdverse,1,'DISCARD','ENERGIE_ROUGE');
                    }
                    break;
                case 100 :
                case 255 :
                    if ($action == 'counter attack')  {
                        $this->Partie->dechargerZone($joueurConcerne,$this->infos['ZoneDefenseur']);
                    }
                    break;
                case 254 : 
                    if ($action == 'counter attack') {
                        $this->interactions->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK'),'DISCARD');
                    }
                    break;
                case 258 : 
                    if ($action == 'counter attack') {
                        $this->interactions->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    }
                    break;
                case 256 : 
                    if ($action == 'counter attack') {
                        $this->Partie->dechargerZone($joueurConcerne,$this->infos['ZoneDefenseur']);
                        $this->interactions->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'TEAMWORK'),'DISCARD');
                        $this->interactions->deplacerCarte($joueurConcerne,1,$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'),'DISCARD');
                    }
                    break;
            }
        }
    }

    public function effetPitcher($joueurConcerne) {
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 19 :
                case 261 :
                    $this->interactions->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_VERTE');
                    break;
                case 46 :
                case 89 :
                    $this->interactions->deplacerCarte($joueurConcerne,2,'DISCARD',$this->tools->zoneCorrespondante($this->infos['ZoneDefenseur'],'ENERGIE'));
                    break;
                case 60 :
                case 259 :
                    $this->interactions->deplacerCarte($joueurAdverse,1,'ENERGIE_JAUNE','DISCARD');
                    break;
                case 61 :
                    $this->interactions->deplacerCarte($joueurConcerne,2,'ENERGIE_JAUNE','ENERGIE_ROUGE');
                    break;
                case 260 :
                case 262 :
                    $this->Partie->chargerZone($joueurConcerne,$this->infos['ZoneDefenseur']);
                    break;
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
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
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                /*case 147 :
                    $this->interactions->deplacerCarte($joueurConcerne,2,'DISCARD','ENERGIE_VERTE');
                    break;*/
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 147 : 
                case 148 : 
                    $this->interactions->deplacerCarte($joueurConcerne,1,'ENERGIE_VERTE','DISCARD');
                    break;
            }
        }
    }

    

    public function zoneDepart($joueurConcerne) {
        $zoneDepart = 'STRIKE_VERT';
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                /*case 0 : 
                    $zoneDepart = false;
                    break;*/
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 28 : 
                case 73 : 
                case 122 : 
                case 171 : 
                case 283 : 
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
            }
        }

        return $zoneDepart;
    }

    /*public function effetVoulu($joueurConcerne) {
        $effetVoulu = true;
        $joueurAdverse = ($joueurConcerne==1)?2:1;

        // effet des cartes du joueur concerné
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurConcerne]['ACTIVE'])) ? $this->CarteEnJeus[$joueurConcerne]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 0 : 
                    $effetVoulu = false;
                    break;
            }
        }

        // effet des cartes de l'adversaire
        $CarteEnJeus = (isset($this->CarteEnJeus[$joueurAdverse]['ACTIVE'])) ? $this->CarteEnJeus[$joueurAdverse]['ACTIVE'] : null;
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
                case 0 : 
                    $effetVoulu = false;
                    break;
            }
        }

        return $effetVoulu;
    }*/



}
