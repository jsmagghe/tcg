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
                    if ((isset($infos['ZoneDefenseur'])) && ($infos['ZoneDefenseur']=='STRIKE_VERT')) {
                        $bonus -= 3;
                    }
                    break;
                case 112 : 
                    if ((isset($infos['ZoneDefenseur'])) && ($infos['ZoneDefenseur']=='STRIKE_VERT')) {
                        $bonus += 2;
                    }
                    break;

                // zone rouge
                case 10 : 
                    if ((isset($infos['ZoneDefenseur'])) && ($infos['ZoneDefenseur']=='STRIKE_ROUGE')) {
                        $bonus -= 4;
                    } else {
                        $bonus += 1;                        
                    }
                    break;
                case 24 : 
                    if ((isset($infos['ZoneDefenseur'])) && ($infos['ZoneDefenseur']=='STRIKE_ROUGE')) {
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
            	case 110 :
                    if (isset($this->CarteEnJeus[$numeroAttaquant][$this->tools->zoneCorrespondante($infos['ZoneAttaquant'],'TEAMWORK')])) {
                        $bonus += 2;
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

                // zone rouge
                case 29 : 
                    if ((isset($infos['ZoneDefenseur'])) && ($infos['ZoneDefenseur']=='STRIKE_ROUGE')) {
                        $bonus += 2;
                    }
                    break;
            }
        }

        return $bonus;
    }




}
