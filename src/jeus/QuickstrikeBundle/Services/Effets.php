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
                case 7 : 
                case 34 : 
            	case 106 : 
            		$bonus += 1;
                    break;
                case 6 : 
                case 23 : 
                case 63 : 
                case 65 : 
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
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE'])) {
                        $bonus += 1;
                    } 
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE'])) {
                        $bonus += 1;
                    } 
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE'])) {
                        $bonus += 1;
                    } 
                    break;
                case 174 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_VERTE'])) {
                        $bonus += 1;
                    } 
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_JAUNE'])) {
                        $bonus += 1;
                    } 
                    if (isset($this->CarteEnJeus[$numeroAttaquant]['TEAMWORK_ROUGE'])) {
                        $bonus += 1;
                    } 
                    break;
            	case 110 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant][$this->tools->zoneCorrespondante($infos['ZoneAttaquant'],'TEAMWORK')])) {
                        $bonus += 2;
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
                    $bonus -= 2;
                    break;
                case 4 : 
                case 24 : 
                case 34 : 
                case 63 : 
                case 116 : 
                    $bonus += 1;
                    break;
                case 192 : 
                    $bonus += 2;
                    break;

                // zone rouge
                case 29 : 
                    if (($infos['ZoneDefenseur']=='STRIKE_ROUGE')) {
                        $bonus += 2;
                    }
                    break;

                // nombre carte
                case 127 :
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_VERTE'])) {
                        $bonus += 2;
                    } 
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_JAUNE'])) {
                        $bonus += 2;
                    } 
                    if (isset($this->CarteEnJeus[$numeroDefenseur]['TEAMWORK_ROUGE'])) {
                        $bonus += 2;
                    } 
                    break;

                // zone chargée
                case 173 : 
                    if (
                        (($infos['chamberChargeAttaquant']) && ($Cartejeu->getEmplacement()=='STRIKE_VERT'))
                        || (($infos['deckChargeAttaquant']) && ($Cartejeu->getEmplacement()=='STRIKE_JAUNE'))
                        || (($infos['discardChargeAttaquant']) && ($Cartejeu->getEmplacement()=='STRIKE_ROUGE'))
                    ) {
                        $bonus += 2;
                    }
                    break;
                case 193 : 
                    if (
                        (($infos['chamberChargeAttaquant']) && ($infos['ZoneDefenseur']=='STRIKE_VERT'))
                        || (($infos['deckChargeAttaquant']) && ($infos['ZoneDefenseur']=='STRIKE_JAUNE'))
                        || (($infos['discardChargeAttaquant']) && ($infos['ZoneDefenseur']=='STRIKE_ROUGE'))
                    ) {
                        $bonus += 2;
                    }
                    break;

            }
        }

        return $bonus;
    }




}
