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
     * @ORM\ManyToOne(targetEntity="jeus\JoueurBundle\Entity\Joueur")
     */
    protected $joueur;

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
     * Set joueur
     *
     * @param \jeus\JoueurBundle\Entity\Joueur $joueur
     * @return Deck
     */
    public function setJoueur(\jeus\JoueurBundle\Entity\Joueur $joueur)
    {
        $this->joueur = $joueur;

        return $this;
    }

    /**
     * Get joueur
     *
     * @return \jeus\JoueurBundle\Entity\Joueur
     */
    public function getJoueur()
    {
        return $this->joueur;
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
		
}
