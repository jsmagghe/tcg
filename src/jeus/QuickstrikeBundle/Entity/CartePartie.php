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
     * @ORM\Column(name="position", type="smallint")
     */
    private $position;

    /**
     * @var integer
     *
     * @ORM\Column(name="numeroJoueur", type="smallint")
     */
    private $numeroJoueur;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\JoueurBundle\Entity\Partie", inversedBy="CarteParties", cascade={"persist,remove"})
     */
    protected $Partie;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\QuickstrikeBundle\Entity\Carte")
     */
    protected $carte;

    /**
     * @ORM\OneToOne(targetEntity="jeus\QuickstrikeBundle\Entity\EtatCarte")
     */
    protected $etatCarte;


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
		
    /**
     * Set etatCarte
     *
     * @param \jeus\QuickstrikeBundle\Entity\EtatCarte $etatCarte
     * @return CartePartie
     */
    public function setEtatCarte(\jeus\QuickstrikeBundle\Entity\EtatCarte $etatCarte)
    {
        $this->etatCarte = $etatCarte;

        return $this;
    }

    /**
     * Get etatCarte
     *
     * @return \jeus\QuickstrikeBundle\Entity\EtatCarte
     */
    public function getEtatCarte()
    {
        return $this->etatCarte;
    }
		
    public function __construct($Carte,$Partie,$numeroJoueur,$em = null ,$tagEmplacement = 'DECK') {
        $this->carte = $Carte;
        $this->numeroJoueur = $numeroJoueur;
        $this->Partie = $Partie;
        if ($em!=null) {
            $EmplacementDeck = $em->getRepository('jeusQuickstrikeBundle:Emplacement')->findByTag($tagEmplacement);
            $EtatCarte = new EtatCarte($EmplacementDeck);
            $this->etatCarte = $EtatCarte;
        }
    }
}
