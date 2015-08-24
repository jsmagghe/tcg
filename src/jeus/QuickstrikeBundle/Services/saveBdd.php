<?php

namespace jeus\QuickstrikeBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Process\Process;

/**
 *
 * @author Julien S
 */
class SaveBdd
{

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function sauvegardeBdd()
    {
        $serveur = $this->container->getParameter('database_host');
        $login = $this->container->getParameter('database_user');
        $password = $this->container->getParameter('database_password');
        $base = $this->container->getParameter('database_name');
        $mysqldump=  new Process(sprintf('mysqldump -h '.$serveur.' -u '.$login.' --password='.$password.' '.$base.' > '.$base.'.sql'));
        $mysqldump->run();
        if ($mysqldump->isSuccessful()) {
            return true;
        } else {
            return false;            
        }
    }

    public function renommerSauvegardeBdd($idPartie) {
        $base = $this->container->getParameter('database_name');
        $nomSauvegarde = $base . '.sql';
        $nomSauvegardeBug = str_replace('.sql', '_'.$idPartie . date("_dmY_His") . '.sql', $nomSauvegarde);
        if (file_exists($nomSauvegarde)) {
            copy($nomSauvegarde,$nomSauvegardeBug);
        }
    }

}
