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
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible;

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
    protected $carte;


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
     * Set visible
     *
     * @param boolean $visible
     * @return EtatCarte
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
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
     * Set carte
     *
     * @param \jeus\QuickstrikeBundle\Entity\Carte $carte
     * @return CartePartie
     */
    public function setCarte(\jeus\QuickstrikeBundle\Entity\Carte $carte)
    {
        $this->carte = $carte;

        return $this;
    }

    /**
     * Get carte
     *
     * @return \jeus\QuickstrikeBundle\Entity\Carte
     */
    public function getCarte()
    {
        return $this->carte;
    }
		
		
    public function __construct($Carte,$Partie,$numeroJoueur,$emplacement = 'DECK') {
        $this->carte = $Carte;
        $this->numeroJoueur = $numeroJoueur;
        $this->Partie = $Partie;
        $this->position = 0;
        if ($Carte->getTypeCarte()->getTag()=='CHAMBER') {
            $this->emplacement = 'CHAMBER';
        } else {
            $this->emplacement = $emplacement;
        }
        if ($this->emplacement == 'DECK') {
            $this->visible = false;
        } else {
            $this->visible = true;
        }
    }
}
