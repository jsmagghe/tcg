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
    private $CarteEnJeus;

    public function chargerCarteEnJeu($CarteEnJeus) {
        $this->CarteEnJeus = $CarteEnJeus;
    }

    public function bonusAttaque($numeroAttaquant,$numeroDefenseur,$infos) {
        $bonus = 0;
        $CarteEnJeus = $this->CarteEnJeus[$numeroAttaquant]['ACTIVE'];
        foreach ($CarteEnJeus as $Cartejeu) {
            $Carte = $Cartejeu->getCarte();
            if ($Carte == null) {
                continue;
            }
            $numeroEffet = ($Carte->getEffet()!=null) ? $Carte->getEffet()->getNumero(): 0;
            switch ($numeroEffet) {
            	case 6 : 
            		$bonus += 2;
            		break;
            	case 7 : 
            		$bonus += 1;
            		break;
            	case 10 : 
            		if ((isset($infos['ZoneDefenseur'])) && ($infos['ZoneDefenseur']=='STRIKE_ROUGE')) {
            			$bonus -= 4;
            		} else {
            			$bonus += 1;            			
            		}
            		break;
            	case 17 : 
            		$bonus += 3;
            		break;
            	case 23 : 
            	
            		$bonus += 2;
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
            	case 4 : 
            		$bonus += 1;
            		break;
            	case 6 : 
            		$bonus -= 2;
            		break;
            	case 17 : 
            		$bonus -= 3;
            		break;
            	case 23 : 
            		$bonus -= 2;
            		break;
            }
        }

        return $bonus;
    }




}
