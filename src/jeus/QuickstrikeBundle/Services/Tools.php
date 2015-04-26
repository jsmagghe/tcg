<?php

namespace jeus\QuickstrikeBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;

/**
 *
 * @author Julien S
 */
class Tools
{

    public function zoneSuivante($zone) {
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

    public function zoneCorrespondante($zone,$type='STRIKE') {
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

    public function joueurChoisi() {
        $numero = rand(1,1000);
        return ($numero<=500)? 1 : 2;
    }



}
