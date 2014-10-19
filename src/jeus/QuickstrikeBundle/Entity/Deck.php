<?php

namespace jeus\QuickstrikeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Deck
 *
 * @ORM\Table(name="quickstrike_deck")
 * @ORM\Entity(repositoryClass="jeus\QuickstrikeBundle\Repository\DeckRepository")
 */
class Deck
{
    const NOMBRE_CARTE_PAR_DECK  = 60;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=50)
     */
    private $nom;

    /**
     * @var boolean
     *
     * @ORM\Column(name="valide", type="boolean")
     */
    private $valide;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\JoueurBundle\Entity\Joueur", inversedBy="Decks")
     */
    protected $joueur;

    /**
     * @ORM\OneToOne(targetEntity="jeus\QuickstrikeBundle\Entity\Carte")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $chamberRecto;

    /**
     * @ORM\OneToOne(targetEntity="jeus\QuickstrikeBundle\Entity\Carte")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $chamberVerso;

    /**
     * @ORM\OneToOne(targetEntity="jeus\QuickstrikeBundle\Entity\Carte")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $CarteDepart;

    /**
     * @ORM\OneToMany(targetEntity="jeus\QuickstrikeBundle\Entity\CarteDeck", mappedBy="Deck", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $Cartes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Cartes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nom
     *
     * @param string $nom
     * @return Deck
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set valide
     *
     * @param boolean $valide
     * @return Deck
     */
    public function setValide($valide)
    {
        $this->valide = $valide;

        return $this;
    }

    /**
     * Get valide
     *
     * @return boolean 
     */
    public function getValide()
    {
        return $this->valide;
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
     * Set chamberRecto
     *
     * @param \jeus\QuickstrikeBundle\Entity\Carte $chamberRecto
     * @return Deck
     */
    public function setChamberRecto(\jeus\QuickstrikeBundle\Entity\Carte $chamberRecto)
    {
        $this->chamberRecto = $chamberRecto;

        return $this;
    }

    /**
     * Get chamberRecto
     *
     * @return \jeus\QuickstrikeBundle\Entity\Carte
     */
    public function getChamberRecto()
    {
        return $this->chamberRecto;
    }

    /**
     * Set chamberVerso
     *
     * @param \jeus\QuickstrikeBundle\Entity\Carte $chamberVerso
     * @return Deck
     */
    public function setChamberVerso(\jeus\QuickstrikeBundle\Entity\Carte $chamberVerso)
    {
        $this->chamberVerso = $chamberVerso;

        return $this;
    }

    /**
     * Get chamberVerso
     *
     * @return \jeus\QuickstrikeBundle\Entity\Carte
     */
    public function getChamberVerso()
    {
        return $this->chamberVerso;
    }

    /**
     * Set CarteDepart
     *
     * @param \jeus\QuickstrikeBundle\Entity\Carte $CarteDepart
     * @return Deck
     */
    public function setCarteDepart(\jeus\QuickstrikeBundle\Entity\Carte $CarteDepart)
    {
        $this->CarteDepart = $CarteDepart;

        return $this;
    }

    /**
     * Get CarteDepart
     *
     * @return \jeus\QuickstrikeBundle\Entity\Carte
     */
    public function getCarteDepart()
    {
        return $this->CarteDepart;
    }

    /**
     * Add carte
     *
     * @param \jeus\QuickstrikeBundle\Entity\CarteDeck $carte
     * @return Deck
     */
    public function addCarte(\jeus\QuickstrikeBundle\Entity\CarteDeck $carte)
    {
        $this->Cartes[] = $carte;

        return $this;
    }

    /**
     * Remove carte
     *
     * @param \jeus\QuickstrikeBundle\Entity\CarteDeck $carte
     * @return Carte
     */
    public function removeCarte(\jeus\QuickstrikeBundle\Entity\CarteDeck $carte)
    {
        $this->Cartes->removeElement($carte);

        return $this;
    }

    /**
     * Get cartes
     *
     * @return \jeus\QuickstrikeBundle\Entity\CarteDeck
     */
    public function getCartes()
    {
        return $this->Cartes;
    }

    public function carteAjoutable(\jeus\QuickstrikeBundle\Entity\CarteDeck $CarteDeck)
    {
        $erreur = '';
        //return $erreur;

        if ($CarteDeck->getCarte()->getTypeCarte()->getTag() == 'CHAMBER') {
            foreach ($this->getCartes() as $CarteDeckEnCours) {
                if (
                        ($CarteDeck->getId() != $CarteDeckEnCours->getId())
                        && ($CarteDeckEnCours->getCarte()->getTypeCarte()->getTag() == 'CHAMBER')
                ) {
                    $erreur = 'Une chamber maximum par deck';
                }
            }
        } else {
            $CarteDeckChamber = null;
            foreach ($this->getCartes() as $CarteDeckEnCours) {
                if ($CarteDeckEnCours->getCarte()->getTypeCarte()->getTag() == 'CHAMBER') {
                    $CarteDeckChamber = $CarteDeckEnCours;
                    break;
                }
            }
            if ($CarteDeckChamber == null)
                $erreur = "Vous devez choisir une chamber avant de rajouter d'autre carte";

            if (
                    ($erreur == '')
                    && ($CarteDeck->getCarte()->getPersonnageChamber() != '')
                    && ($CarteDeck->getCarte()->getPersonnageChamber() != $CarteDeckChamber->getCarte()->getPersonnageChamber())
            ) {
                $erreur = "Cette carte est à jouer avec " . $CarteDeck->getCarte()->getPersonnageChamber();
            }


            if ($erreur == '') {
                $tableauTraits = array();
                foreach ($CarteDeckChamber->getCarte()->getTraitCartes() as $TraitCarte) {
                    $tableauTraits[] = $TraitCarte->getId();
                }

                foreach ($CarteDeck->getCarte()->getTraitCartes() as $TraitCarte) {
                    if ((!in_array($TraitCarte->getId(), $tableauTraits)) 
                            && ($TraitCarte->getTag()!='NEUTRE')) {
                        $erreur = "Cette carte n'a pas le bon trait";
                        break;
                    }
                }
            }
            if ($erreur == '') {
                $nombre = 0;
                foreach ($this->getCartes() as $CarteDeckEnCours) {
                    if (
                            ($CarteDeckEnCours->getId() != $CarteDeck->getId())
                            && ($CarteDeckEnCours->getCarte()->getId() == $CarteDeck->getCarte()->getId())
                    ) {
                        $nombre++;
                    }
                }
                if ($nombre >= 4)
                    $erreur = "Vous avez déjà le maximum d'exemplaire pour cette carte";
            }
        }

        return $erreur;
    }

    public function isValide() {
        $avertissement = '';
        //return $avertissement;
        $Chamber = null;
        $nombreCarte = null;
        foreach ($this->getCartes() as $CarteDeckEnCours) {
            if ($CarteDeckEnCours->getCarte()->getTypeCarte()->getTag() == 'CHAMBER') {
                $Chamber = $CarteDeckEnCours;
            } else {
                $nombreCarte++;
            }
        }
        if ($Chamber == null) 
            $avertissement = "Il faut une Chamber dans le deck";
        if ($nombreCarte != self::NOMBRE_CARTE_PAR_DECK) {
            $avertissement = $nombreCarte." Un deck doit contenir ".self::NOMBRE_CARTE_PAR_DECK." cartes";
        }
        $this->setValide ($avertissement=='');
        
        return $avertissement;
    }
    
}
