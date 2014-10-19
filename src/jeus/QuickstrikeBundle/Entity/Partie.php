<?php

namespace jeus\QuickstrikeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Partie
 *
 * @ORM\Table(name="quickstrike_partie")
 * @ORM\Entity(repositoryClass="jeus\QuickstrikeBundle\Partie\PartieRepository")
 */
class Partie
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
     * @ORM\Column(name="pointJoueur1", type="smallint")
     */
    private $pointJoueur1;

    /**
     * @var integer
     *
     * @ORM\Column(name="pointJoueur2", type="smallint")
     */
    private $pointJoueur2;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\JoueurBundle\Entity\Joueur")
     */
    protected $joueur1;  // en attente de la partie joueur

    /**
     * @ORM\ManyToOne(targetEntity="jeus\JoueurBundle\Entity\Joueur")
     */
    protected $joueur2;  // en attente de la partie joueur

    /**
     * @ORM\ManyToOne(targetEntity="jeus\QuickstrikeBundle\Entity\CartePartie")
     */
    protected $carteParties;


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
     * Set pointJoueur1
     *
     * @param integer $pointJoueur1
     * @return Partie
     */
    public function setPointJoueur1($pointJoueur1)
    {
        $this->pointJoueur1 = $pointJoueur1;

        return $this;
    }

    /**
     * Get pointJoueur1
     *
     * @return integer 
     */
    public function getPointJoueur1()
    {
        return $this->pointJoueur1;
    }

    /**
     * Set pointJoueur2
     *
     * @param integer $pointJoueur2
     * @return Partie
     */
    public function setPointJoueur2($pointJoueur2)
    {
        $this->pointJoueur2 = $pointJoueur2;

        return $this;
    }

    /**
     * Get pointJoueur2
     *
     * @return integer 
     */
    public function getPointJoueur2()
    {
        return $this->pointJoueur2;
    }
	
    /**
     * Add cartePartie
     *
     * @param \jeus\QuickstrikeBundle\Entity\CartePartie $cartePartie
     * @return Partie
     */
    public function addEtatCarte(\jeus\QuickstrikeBundle\Entity\CartePartie $cartePartie)
    {
        $this->carteParties[] = $cartePartie;

        return $this;
    }

    /**
     * Remove cartePartie
     *
     * @param \jeus\QuickstrikeBundle\Entity\CartePartie $cartePartie
     * @return Partie
     */
    public function removeEtatCarte(\jeus\QuickstrikeBundle\Entity\CartePartie $cartePartie)
    {
        $this->carteParties->removeElement($cartePartie);
		
		return $this;
    }
    
    /**
     * Get carteParties
     *
     * @return \jeus\QuickstrikeBundle\Entity\CartePartie
     */
    public function getEtatCartes()
    {
        return $this->carteParties;
    }
		
    /**
     * Set joueur1
     *
     * @param \jeus\JoueurBundle\Entity\Joueur $joueur1
     * @return Deck
     */
    public function setJoueur1(\jeus\JoueurBundle\Entity\Joueur $joueur1)
    {
        $this->joueur1 = $joueur1;

        return $this;
    }

    /**
     * Get joueur1
     *
     * @return \jeus\JoueurBundle\Entity\Joueur
     */
    public function getJoueur1()
    {
        return $this->joueur1;
    }
		
    /**
     * Set joueur2
     *
     * @param \jeus\JoueurBundle\Entity\Joueur $joueur2
     * @return Deck
     */
    public function setJoueur2(\jeus\JoueurBundle\Entity\Joueur $joueur2)
    {
        $this->joueur2 = $joueur2;

        return $this;
    }

    /**
     * Get joueur2
     *
     * @return \jeus\JoueurBundle\Entity\Joueur
     */
    public function getJoueur2()
    {
        return $this->joueur2;
    }
		

}
