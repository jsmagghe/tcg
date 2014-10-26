<?php

namespace jeus\QuickstrikeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Partie
 *
 * @ORM\Table(name="quickstrike_partie")
 * @ORM\Entity(repositoryClass="jeus\QuickstrikeBundle\Repository\PartieRepository")
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
     * @ORM\Column(name="pointVictoire", type="smallint")
     */
    private $pointVictoire;

    /**
     * @var integer
     *
     * @ORM\Column(name="Joueur1Point", type="smallint")
     */
    private $Joueur1Point;

    /**
     * @var integer
     *
     * @ORM\Column(name="Joueur2Point", type="smallint")
     */
    private $Joueur2Point;

    /**
     * @var string
     *
     * @ORM\Column(name="Joueur1Etape", type="string")
     */
    private $Joueur1Etape;

    /**
     * @var string
     *
     * @ORM\Column(name="Joueur2Etape", type="string")
     */
    private $Joueur2Etape;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\JoueurBundle\Entity\Joueur")
     */
    protected $joueur1;  // en attente de la partie joueur

    /**
     * @ORM\ManyToOne(targetEntity="jeus\JoueurBundle\Entity\Joueur")
     */
    protected $joueur2;  // en attente de la partie joueur

    /**
     * @var integer
     *
     * @ORM\Column(name="joueurActif", type="smallint")
     */
    private $joueurActif;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Joueur1ChamberCharge", type="boolean")
     */
    private $Joueur1ChamberCharge;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Joueur1DeckCharge", type="boolean")
     */
    private $Joueur1DeckCharge;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Joueur1DiscardCharge", type="boolean")
     */
    private $Joueur1DiscardCharge;

    /**
     * @var integer
     *
     * @ORM\Column(name="Joueur1ZoneEnCours", type="smallint")
     */
    private $Joueur1ZoneEnCours;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Joueur2ChamberCharge", type="boolean")
     */
    private $Joueur2ChamberCharge;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Joueur2DeckCharge", type="boolean")
     */
    private $Joueur2DeckCharge;

    /**
     * @var boolean
     *
     * @ORM\Column(name="Joueur2DiscardCharge", type="boolean")
     */
    private $Joueur2DiscardCharge;

    /**
     * @var integer
     *
     * @ORM\Column(name="Joueur2ZoneEnCours", type="smallint")
     */
    private $Joueur2ZoneEnCours;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\QuickstrikeBundle\Entity\CartePartie")
     */
    protected $carteParties;

    function __construct($joueur1, $joueur2)
    {
        $this->pointVictoire = 3;
        $this->joueurActif = rand()%2;
        
        $this->joueur1 = $joueur1;
        $this->Joueur1Point = 0;
        $this->Joueur1Etape = 'choix deck';
        $this->Joueur1ChamberCharge = false;
        $this->Joueur1DeckCharge = false;
        $this->Joueur1DiscardCharge = false;
        $this->Joueur1ZoneEnCours = 0;
        
        $this->joueur2 = $joueur2;
        $this->Joueur2Point = 0;
        $this->Joueur2Etape = 'choix deck';
        $this->Joueur2ChamberCharge = false;
        $this->Joueur2DeckCharge = false;
        $this->Joueur2DiscardCharge = false;
        $this->Joueur2ZoneEnCours = 0;
    }

    
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
     * Set pointVictoire
     *
     * @param integer $pointVictoire
     * @return Partie
     */
    public function setPointVictoire($pointVictoire)
    {
        $this->pointVictoire = $pointVictoire;

        return $this;
    }

    /**
     * Get pointVictoire
     *
     * @return integer 
     */
    public function getPointVictoire()
    {
        return $this->pointVictoire;
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
		
    public function getEtapeJoueur1() {
        return $this->etapeJoueur1;
    }

    public function setEtapeJoueur1($etapeJoueur1) {
        $this->etapeJoueur1 = $etapeJoueur1;
        return $this;
    }

    public function getEtapeJoueur2() {
        return $this->etapeJoueur2;
    }

    public function setEtapeJoueur2($etapeJoueur2) {
        $this->etapeJoueur2 = $etapeJoueur2;
        return $this;
    }


}
