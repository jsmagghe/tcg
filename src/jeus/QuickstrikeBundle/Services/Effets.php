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
    private $CarteEnJeus;

    public function __construct($tools)
    {
        $this->tools = $tools;
    }

    public function chargerCarteEnJeu($CarteEnJeus) {
        $this->CarteEnJeus = $CarteEnJeus;
    }

    public function bonusAttaque($numeroAttaquant,$numeroDefenseur,$infos) {
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
                case 7 : 
                case 34 : 
                case 106 : 
                case 220 : 
                case 237 : 
                case 448 : 
                case 466 : 
                case 470 : 
                    $bonus += 1;
                    break;
                case 6 : 
                case 23 : 
                case 63 : 
                case 65 : 
                case 270 : 
                case 445 : 
                    $bonus += 2;
            		break;
                case 17 : 
                    $bonus += 3;
                    break;

                // zone verte
                case 95 : 
                    if ($infos['ZoneDefenseur']=='STRIKE_VERT') {
                        $bonus -= 3;
                    }
                    break;
                case 112 : 
                case 195 : 
                    if ($infos['ZoneDefenseur']=='STRIKE_VERT') {
                        $bonus += 2;
                    }
                    break;

                // zone rouge
                case 10 : 
                    if ($infos['ZoneDefenseur']=='STRIKE_ROUGE') {
                        $bonus -= 4;
                    } else {
                        $bonus += 1;                        
                    }
                    break;
                case 24 : 
                    if ($infos['ZoneDefenseur']=='STRIKE_ROUGE') {
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
                case 67 :
                    $bonus += $infos['nombreTeamworkDefenseur'];
                    break;
                case 174 :
                case 542 :
                    $bonus += $infos['nombreTeamworkAttaquant'];
                    break;
                case 422 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant][$this->tools->zoneCorrespondante($infos['ZoneAttaquant'],'TEAMWORK')])) {
                        $bonus += 1;
                    } 
                    break;
                case 110 :
            	case 498 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant][$this->tools->zoneCorrespondante($infos['ZoneAttaquant'],'TEAMWORK')])) {
                        $bonus += 2;
                    } 
            		break;
                case 370 :
                case 539 :
                    if ($infos['nombreTeamworkDefenseur']<$infos['nombreTeamworkAttaquant']) {
                        $bonus += 2;
                    }
                    break;
                case 538 :
                    if ($infos['nombreTeamworkDefenseur']<$infos['nombreTeamworkAttaquant']) {
                        $bonus += 3;
                    }
                    break;
                case 534 :
                case 536 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['ENERGIE_ROUGE'])) {
                        $bonus = count($this->CarteEnJeus[$numeroAttaquant]['ENERGIE_ROUGE']);
                    }
                    break;                    

                // zone chargée
                case 130 : 
                    if (
                        (($infos['chamberChargeAttaquant']) && ($Cartejeu->getEmplacement()=='TEAMWORK_VERT'))
                        || (($infos['deckChargeAttaquant']) && ($Cartejeu->getEmplacement()=='TEAMWORK_JAUNE'))
                        || (($infos['discardChargeAttaquant']) && ($Cartejeu->getEmplacement()=='TEAMWORK_ROUGE'))
                    ) {
                        $bonus += 1;
                    }
                    break;
                case 196 : 
                case 197 : 
                case 279 : 
                    if (
                        (($infos['chamberChargeAttaquant']) && ($infos['ZoneAttaquant']=='STRIKE_VERT'))
                        || (($infos['deckChargeAttaquant']) && ($infos['ZoneAttaquant']=='STRIKE_JAUNE'))
                        || (($infos['discardChargeAttaquant']) && ($infos['ZoneAttaquant']=='STRIKE_ROUGE'))
                    ) {
                        $bonus += 1;
                    }
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
            }
        }

        return $bonus;
    }

    public function bonusDefense($numeroDefenseur,$numeroAttaquant,$infos) {
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
                    $bonus -= 1;
                    break;
                case 4 : 
                case 24 : 
                case 34 : 
                case 63 : 
                case 116 : 
                case 224 : 
                    $bonus += 1;
                    break;
                case 192 : 
                case 428 : 
                    $bonus += 2;
                    break;

                // zone rouge
                case 220 : 
                    if (($infos['ZoneDefenseur']=='STRIKE_ROUGE')) {
                        $bonus += 1;
                    }
                    break;
                case 29 : 
                    if (($infos['ZoneDefenseur']=='STRIKE_ROUGE')) {
                        $bonus += 2;
                    }
                    break;

                // nombre carte
                case 541 :
                    $bonus += $infos['nombreTeamworkDefenseur'];
                    break;
                case 127 :
                    $bonus += 2 * $infos['nombreTeamworkDefenseur'];
                    break;
                case 369 :
                    if ($infos['nombreTeamworkDefenseur']>$infos['nombreTeamworkAttaquant']) {
                        $bonus += 2;
                    }
                    break;
                case 420 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur][$this->tools->zoneCorrespondante($infos['ZoneDefenseur'],'TEAMWORK')])) {
                        $bonus += 1;
                    } 
                    break;

                // zone chargée
                case 193 : 
                case 196 : 
                case 279 : 
                    if (
                        (($infos['chamberChargeDefenseur']) && ($infos['ZoneDefenseur']=='STRIKE_VERT'))
                        || (($infos['deckChargeDefenseur']) && ($infos['ZoneDefenseur']=='STRIKE_JAUNE'))
                        || (($infos['discardChargeDefenseur']) && ($infos['ZoneDefenseur']=='STRIKE_ROUGE'))
                    ) {
                        $bonus += 1;
                    }
                    break;
                case 173 : 
                    if (
                        (($infos['chamberChargeDefenseur']) && ($Cartejeu->getEmplacement()=='STRIKE_VERT'))
                        || (($infos['deckChargeDefenseur']) && ($Cartejeu->getEmplacement()=='STRIKE_JAUNE'))
                        || (($infos['discardChargeDefenseur']) && ($Cartejeu->getEmplacement()=='STRIKE_ROUGE'))
                    ) {
                        $bonus += 2;
                    }
                    break;

            }
        }

        return $bonus;
    }




}
