<?php

namespace jeus\QuickstrikeBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Persistence\ObjectManager;

/**
 *
 * @author Julien S
 */
class Carte
{

    protected $em;
    protected $container;

    public function __construct(ObjectManager $em, $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function rechercheCarte($tableau) {
        $tableau['Cartes'] = $this->em->getRepository('jeusQuickstrikeBundle:Carte')->findByCritere($tableau['filtre']);

        if (
            (isset($tableau['filtre']['traitCarte']))
            && (isset($tableau['filtre']['idChamber']))
        ) {
            $nombreTraits = 0;
            $tableauTraitId = '';
            foreach ($tableau['filtre']['traitCarte'] as $Trait) {
                if ( $Trait->getTag() != 'NEUTRE') {
                    $nombreTraits++;
                    if ($tableauTraitId != '') {
                        $tableauTraitId .= ',';
                    }
                    $tableauTraitId .= $Trait->getId();                    
                }
            }
            $tableauTraitId = '(' . $tableauTraitId  .')';
    
            $bdd = $this->em->getConnection()
                        ->prepare('select distinct(a.id) 
                                   from quickstrike_carte a 
                                   join (
                                            select distinct carte_id 
                                            from quickstrike_carte_traitcarte 
                                            where traitcarte_id in '. $tableauTraitId .' 
                                            group by carte_id 
                                            having COUNT(*) >= '. $nombreTraits .'
                                        ) c on a.id = c.carte_id 
                                   where a.typeCarte_id = '. $tableau['filtre']['idChamber'] .' and a.numero not like \'%v\'
                                   ');
            $bdd->execute();
            $tableauId = $bdd->fetchAll();
            $tableauRepository = array();
            foreach ($tableauId as $key => $tab) {
                $tableauRepository[] = $tab['id'];
            }
            $tableau['Cartes2'] = $this->em->getRepository('jeusQuickstrikeBundle:Carte')->findByIds($tableauRepository);
            $tableau['Cartes'] = array_merge($tableau['Cartes2'],$tableau['Cartes']);
        }

        $tableau['page'] = isset($tableau['filtre']['page']) ? $tableau['filtre']['page'] : 1;

        $tableau['nbPage'] = ceil(count($tableau['Cartes']) / $this->container->getParameter('carte_par_page'));
        if (($tableau['page']<1) || ($tableau['page']>$tableau['nbPage'])) {
            $tableau['page'] = 1;
        }

        $pageEnCours = 1;
        $nbCarteEnCours = 0;
        foreach ($tableau['Cartes'] as $key => $value) {
            if ($nbCarteEnCours>=$this->container->getParameter('carte_par_page')) {
                $pageEnCours++;
                $nbCarteEnCours = 0;
            }
            if ($pageEnCours != $tableau['page']) {
                unset($tableau['Cartes'][$key]);
            }
            $nbCarteEnCours++;

        }

        return $tableau;
    }



}
