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
    protected $joueur1;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\JoueurBundle\Entity\Joueur")
     */
    protected $joueur2;

    /**
     * @var integer
     *
     * @ORM\Column(name="joueurActif", type="smallint")
     */
    private $joueurActif;

    /**
     * @var integer
     *
     * @ORM\Column(name="joueurBas", type="smallint", nullable=true)
     */
    private $joueurBas;

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
     * @var datetime
     *
     * @ORM\Column(name="dateDerniereAction", type="datetime")
     */
    private $dateDerniereAction;

    /**
     * @ORM\OneToMany(targetEntity="jeus\QuickstrikeBundle\Entity\CartePartie", mappedBy = "Partie", cascade={"persist","remove"})
     */
    protected $CarteParties;

    function __construct($joueur1, $joueur2)
    {
        $this->pointVictoire = 3;
        $this->joueurActif = rand() % 2;

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
        
        $this->dateDerniereAction = new \Datetime();
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
     * Add CartePartie
     *
     * @param \jeus\QuickstrikeBundle\Entity\CartePartie $CartePartie
     * @return Partie
     */
    public function addCartePartie(\jeus\QuickstrikeBundle\Entity\CartePartie $CartePartie)
    {
        $this->CarteParties[] = $CartePartie;

        return $this;
    }

    /**
     * Remove CartePartie
     *
     * @param \jeus\QuickstrikeBundle\Entity\CartePartie $CartePartie
     * @return Partie
     */
    public function removeCartePartie(\jeus\QuickstrikeBundle\Entity\CartePartie $CartePartie)
    {
        $this->CarteParties->removeElement($CartePartie);

        return $this;
    }

    /**
     * Get CarteParties
     *
     * @return \jeus\QuickstrikeBundle\Entity\CartePartie
     */
    public function getCarteParties()
    {
        return $this->CarteParties;
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

    public function getJoueur1Point()
    {
        return $this->Joueur1Point;
    }

    public function getJoueur2Point()
    {
        return $this->Joueur2Point;
    }

    public function getJoueur1Etape()
    {
        return $this->Joueur1Etape;
    }

    public function getJoueur2Etape()
    {
        return $this->Joueur2Etape;
    }

    public function getJoueurActif()
    {
        return $this->joueurActif;
    }

    public function getJoueur1ChamberCharge()
    {
        return $this->Joueur1ChamberCharge;
    }

    public function getJoueur1DeckCharge()
    {
        return $this->Joueur1DeckCharge;
    }

    public function getJoueur1DiscardCharge()
    {
        return $this->Joueur1DiscardCharge;
    }

    public function getJoueur1ZoneEnCours()
    {
        return $this->Joueur1ZoneEnCours;
    }

    public function getJoueur2ChamberCharge()
    {
        return $this->Joueur2ChamberCharge;
    }

    public function getJoueur2DeckCharge()
    {
        return $this->Joueur2DeckCharge;
    }

    public function getJoueur2DiscardCharge()
    {
        return $this->Joueur2DiscardCharge;
    }

    public function getJoueur2ZoneEnCours()
    {
        return $this->Joueur2ZoneEnCours;
    }

    public function setJoueur1Point($Joueur1Point)
    {
        $this->Joueur1Point = $Joueur1Point;
        return $this;
    }

    public function setJoueur2Point($Joueur2Point)
    {
        $this->Joueur2Point = $Joueur2Point;
        return $this;
    }

    public function setJoueur1Etape($Joueur1Etape)
    {
        $this->Joueur1Etape = $Joueur1Etape;
        return $this;
    }

    public function setJoueur2Etape($Joueur2Etape)
    {
        $this->Joueur2Etape = $Joueur2Etape;
        return $this;
    }

    public function setJoueurActif($joueurActif)
    {
        $this->joueurActif = $joueurActif;
        return $this;
    }

    public function setJoueur1ChamberCharge($Joueur1ChamberCharge)
    {
        $this->Joueur1ChamberCharge = $Joueur1ChamberCharge;
        return $this;
    }

    public function setJoueur1DeckCharge($Joueur1DeckCharge)
    {
        $this->Joueur1DeckCharge = $Joueur1DeckCharge;
        return $this;
    }

    public function setJoueur1DiscardCharge($Joueur1DiscardCharge)
    {
        $this->Joueur1DiscardCharge = $Joueur1DiscardCharge;
        return $this;
    }

    public function setJoueur1ZoneEnCours($Joueur1ZoneEnCours)
    {
        $this->Joueur1ZoneEnCours = $Joueur1ZoneEnCours;
        return $this;
    }

    public function setJoueur2ChamberCharge($Joueur2ChamberCharge)
    {
        $this->Joueur2ChamberCharge = $Joueur2ChamberCharge;
        return $this;
    }

    public function setJoueur2DeckCharge($Joueur2DeckCharge)
    {
        $this->Joueur2DeckCharge = $Joueur2DeckCharge;
        return $this;
    }

    public function setJoueur2DiscardCharge($Joueur2DiscardCharge)
    {
        $this->Joueur2DiscardCharge = $Joueur2DiscardCharge;
        return $this;
    }

    public function setJoueur2ZoneEnCours($Joueur2ZoneEnCours)
    {
        $this->Joueur2ZoneEnCours = $Joueur2ZoneEnCours;
        return $this;
    }

    public function getJoueurBas()
    {
        return $this->joueurBas;
    }

    public function setJoueurBas($joueurBas)
    {
        $this->joueurBas = $joueurBas;
        return $this;
    }

    public function JoueurConcerne($Joueur)
    {
        if (
                ($this->getJoueur2() == $Joueur)
                && (
                ($this->getJoueurBas() == 2) || ($this->getJoueur1() != $Joueur)
                )
        ) {
            $joueurConcerne = 2;
        } else {
            $joueurConcerne = 1;
        }

        return $joueurConcerne;
    }
    
    public function getDateDerniereAction()
    {
        return $this->dateDerniereAction;
    }

    public function setDateDerniereAction($dateDerniereAction)
    {
        $this->dateDerniereAction = $dateDerniereAction;
        return $this;
    }

    public function setEtape($Joueur, $etape)
    {
        if ($this->JoueurConcerne($Joueur) == 2) {
            $this->setJoueur2Etape($etape);
        } else {
            $this->setJoueur1Etape($etape);
        }
        // si les deux joueurs ont choisis leur deck on les passe en début de partie
        if (($this->getJoueur1Etape()=='attenteDebut')
            && ($this->getJoueur2Etape()=='attenteDebut')
           ) {
            $this->setJoueur1Etape('choix attaque');
            $this->setJoueur2Etape('choix attaque');
        }
        
        $this->setDateDerniereAction(new \Datetime());
    }

    public function choixDeck($Deck, $Joueur)
    {
        if ($Deck->getValide()) {
            foreach ($Deck->getCartes() as $CarteDeck) {
                $Carte = $CarteDeck->getCarte();
                $CartePartie = new CartePartie($Carte,$this,$this->JoueurConcerne($Joueur),'DECK');
                $this->addCartePartie($CartePartie);
            }
            $this->melangerDeck($this->JoueurConcerne($Joueur),'DECK');
            $this->setEtape($Joueur, 'attenteDebut');
        }
    }

    public function melangerDeck($joueurConcerne,$emplacement) {
        $position = array();
        $iteration = 0;
        foreach($this->getCarteParties() as $CartePartie) {
            $iteration++;
            if ($CartePartie->getEtatCarte()->getEmplacement()==$emplacement) {
                $position[$iteration] = $iteration;
            }
        }
        var_dump($position);
        shuffle($position);
        var_dump($position);
        exit;
        foreach($this->getCarteParties() as $CartePartie) {
            $iteration++;
            $CartePartie->setPosition($position[$iteration]);
        }

    }

    public function getPartieAffichee($Joueur)
    {
        $PartieAffichee = array();
        $PartieAffichee['id'] = $this->getId();

        if ($this->getCarteParties() != null) {
            if ($this->JoueurConcerne($Joueur) == 2) {
                foreach ($this->getCarteParties() as $CartePartie) {
                    if ($CartePartie->getJoueur() == $this->getJoueur2()) {
                        //$PartieAffichee[2] = 
                    } else {
                        //$PartieAffichee[1] = 
                    }
                }
            } else {
                foreach ($this->getCarteParties() as $CartePartie) {
                    if ($CartePartie->getJoueur() == $this->getJoueur1()) {
                        //$PartieAffichee[1] = 
                    } else {
                        //$PartieAffichee[2] = 
                    }
                }
            }
        }
        return $PartieAffichee;
    }

}
