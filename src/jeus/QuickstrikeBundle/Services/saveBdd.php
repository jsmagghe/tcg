<?php

namespace jeus\QuickstrikeBundle\Services;

use Symfony\Component\DependencyInjection\Container;

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
        $mode = 3;
        $connexion = mysql_connect($serveur, $login, $password);
        mysql_select_db($base, $connexion);
     
        $entete = "-- dump de la base ".$base." au ".date("d-M-Y H:i:s")."\n";
        $entete .= "-- ----------------------\n\n\n";
        $creations = "";
        $insertions = "\n\n";
     
        $listeTables = mysql_query("show tables", $connexion);
        while($table = mysql_fetch_array($listeTables))
        {
            // si l'utilisateur a demandé la structure ou la totale
            if($mode == 1 || $mode == 3)
            {
                $creations .= "-- -----------------------------\n";
                $creations .= "-- creation de la table ".$table[0]."\n";
                $creations .= "-- -----------------------------\n";
                $listeCreationsTables = mysql_query("show create table ".$table[0], $connexion);
                while($creationTable = mysql_fetch_array($listeCreationsTables))
                {
                  $creations .= $creationTable[1].";\n\n";
                }
            }
            // si l'utilisateur a demandé les données ou la totale
            if($mode > 1)
            {
                $donnees = mysql_query("SELECT * FROM ".$table[0]);
                $insertions .= "-- -----------------------------\n";
                $insertions .= "-- insertions dans la table ".$table[0]."\n";
                $insertions .= "-- -----------------------------\n";
                while($nuplet = mysql_fetch_array($donnees))
                {
                    $insertions .= "INSERT INTO ".$table[0]." VALUES(";
                    for($i=0; $i < mysql_num_fields($donnees); $i++)
                    {
                      if($i != 0)
                         $insertions .=  ", ";
                      if(mysql_field_type($donnees, $i) == "string" || mysql_field_type($donnees, $i) == "blob")
                         $insertions .=  "'";
                      $insertions .= addslashes($nuplet[$i]);
                      if(mysql_field_type($donnees, $i) == "string" || mysql_field_type($donnees, $i) == "blob")
                        $insertions .=  "'";
                    }
                    $insertions .=  ");\n";
                }
                $insertions .= "\n";
            }
        }
     
        mysql_close($connexion);
     
        $fichierDump = fopen($base . '.sql', "wb");
        fwrite($fichierDump, $entete);
        fwrite($fichierDump, $creations);
        fwrite($fichierDump, $insertions);
        fclose($fichierDump);
        return "Sauvegarde réalisée avec succès !!";

    }


}
