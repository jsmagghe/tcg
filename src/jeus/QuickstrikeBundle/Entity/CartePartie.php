<?php

namespace jeus\QuickstrikeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * CartePartie
 *
 * @ORM\Table(name="quickstrike_cartepartie")
 * @ORM\Entity(repositoryClass="jeus\QuickstrikeBundle\Repository\CartePartieRepository")
 */
class CartePartie
{
    private $em;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="smallint", nullable=true)
     */
    private $position;

    /**
     * @var integer
     *
     * @ORM\Column(name="numeroJoueur", type="smallint")
     */
    private $numeroJoueur;

    /**
     * @var string
     *
     * @ORM\Column(name="emplacement", type="string", length=50)
     */
    protected $emplacement;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\QuickstrikeBundle\Entity\Partie", inversedBy="CarteParties", cascade={"persist","remove"})
     */
    protected $Partie;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\QuickstrikeBundle\Entity\Carte")
     */
    protected $Carte;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return CartePartie
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }
    
    /**
     * Set numeroJoueur
     *
     * @param integer $numeroJoueur
     * @return CartePartie
     */
    public function setNumeroJoueur($numeroJoueur)
    {
        $this->numeroJoueur = $numeroJoueur;

        return $this;
    }

    /**
     * Get numeroJoueur
     *
     * @return integer 
     */
    public function getNumeroJoueur()
    {
        return $this->numeroJoueur;
    }
	
    /**
     * Set emplacement
     *
     * @param string $emplacement
     * @return Partie
     */
    public function setEmplacement($emplacement)
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    /**
     * Get emplacement
     *
     * @return string
     */
    public function getEmplacement()
    {
        return $this->emplacement;
    }

    /**
     * Set Partie
     *
     * @param \jeus\QuickstrikeBundle\Entity\Partie $Partie
     * @return Deck
     */
    public function setPartie(\jeus\QuickstrikeBundle\Entity\Partie $Partie)
    {
        $this->Partie = $Partie;

        return $this;
    }

    /**
     * Get Partie
     *
     * @return \jeus\QuickstrikeBundle\Entity\Partie
     */
    public function getPartie()
    {
        return $this->Partie;
    }
		
    /**
     * Set Carte
     *
     * @param \jeus\QuickstrikeBundle\Entity\Carte $Carte
     * @return CartePartie
     */
    public function setCarte(\jeus\QuickstrikeBundle\Entity\Carte $Carte)
    {
        $this->Carte = $Carte;

        return $this;
    }

    /**
     * Get Carte
     *
     * @return \jeus\QuickstrikeBundle\Entity\Carte
     */
    public function getCarte()
    {
        return $this->Carte;
    }
        
    public function getLien($parametres = null)
    {
        $chamberVisible = (isset($parametres['chamberVisible'.$this->numeroJoueur])) ? $parametres['chamberVisible'.$this->numeroJoueur] : false;
        $lien = '';
        if (
            (($this->emplacement == 'DECK') && ($parametres['deckVisible'.$this->numeroJoueur] == false)) 
            || ($this->emplacement == 'ENERGIE_VERTE')
            || ($this->emplacement == 'ENERGIE_JAUNE')
            || ($this->emplacement == 'ENERGIE_ROUGE')
            ) {
            $lien = 'back.png';
        } elseif ($this->emplacement == 'CHAMBER') {
            if ($chamberVisible==true) {
                $lien = $this->Carte->getImage();
            } else {
                if (strpos($this->Carte->getNumero(), 'v') === false)  {
                    $lien = 'recto-' . $this->Carte->getPersonnageChamber() . '.png';                    
                } else {
                    $lien = 'verso-' . $this->Carte->getPersonnageChamber() . '.png';                    
                }
            }
        } else
            $lien = $this->Carte->getImage();

        return $lien;
    }
        
    public function getLienAgrandi($parametres = null)
    {
        $adverse = (isset($parametres['adverse'])) ? $parametres['adverse'] : false;
        $lien = '';
        if ($this->Carte->getTypeCarte()->getTag() === 'CHAMBER') {
            if ($adverse) {
                $lien = $this->getLien($parametres);                
            } else {
                $lien = $this->Carte->getImage();                
            }
        } else if (
            (($this->emplacement == 'DECK') && ($parametres['deckVisible'.$this->numeroJoueur] == false)) 
            || ($this->emplacement == 'ENERGIE_VERTE')
            || ($this->emplacement == 'ENERGIE_JAUNE')
            || ($this->emplacement == 'ENERGIE_ROUGE')
            )  {
            $lien = 'back.png';                
        } else{
            $lien = $this->Carte->getImage();
        }

        return $lien;
    }
		
		
    public function __construct($Carte,$Partie,$numeroJoueur,$emplacement = 'DECK') {
        $this->Carte = $Carte;
        $this->numeroJoueur = $numeroJoueur;
        $this->Partie = $Partie;
        $this->position = 0;
        if ($Carte->getTypeCarte()->getTag()=='CHAMBER') {
            $this->emplacement = 'CHAMBER';
        } else {
            $this->emplacement = $emplacement;
        }
    }
}
