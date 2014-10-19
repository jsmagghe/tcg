<?php

namespace jeus\QuickstrikeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Carte
 *
 * @ORM\Table(name="quickstrike_carte")
 * @ORM\Entity(repositoryClass="jeus\QuickstrikeBundle\Repository\CarteRepository")
 */
class Carte {

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
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=10)
     */
    private $numero;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255)
     */
    private $image;

    /**
     * @var integer
     *
     * @ORM\Column(name="coutVert", type="smallint")
     */
    private $coutVert;

    /**
     * @var integer
     *
     * @ORM\Column(name="coutJaune", type="smallint")
     */
    private $coutJaune;

    /**
     * @var integer
     *
     * @ORM\Column(name="coutRouge", type="smallint")
     */
    private $coutRouge;

    /**
     * @var integer
     *
     * @ORM\Column(name="intercept", type="smallint")
     */
    private $intercept;

    /**
     * @var integer
     *
     * @ORM\Column(name="attaque", type="smallint")
     */
    private $attaque;

    /**
     * @var string
     *
     * @ORM\Column(name="personnageChamber", type="string", length=20)
     */
    private $personnageChamber;

    /**
     * @ORM\ManyToMany(targetEntity="jeus\QuickstrikeBundle\Entity\TraitCarte")
     * @ORM\joinTable(name="quickstrike_carte_traitcarte")
     */
    protected $traitCartes;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\QuickstrikeBundle\Entity\Extension")
     */
    protected $extension;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\QuickstrikeBundle\Entity\TypeCarte")
     */
    protected $typeCarte;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\QuickstrikeBundle\Entity\Effet")
     */
    protected $effet;

    /**
     * Constructor
     */
    public function __construct() {
        $this->traitCartes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Carte
     */
    public function setNom($nom) {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom() {
        return $this->nom;
    }

    /**
     * Set numero
     *
     * @param string $numero
     * @return Carte
     */
    public function setNumero($numero) {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero() {
        return $this->numero;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Carte
     */
    public function setImage($image) {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Set coutVert
     *
     * @param integer $coutVert
     * @return Carte
     */
    public function setCoutVert($coutVert) {
        $this->coutVert = $coutVert;

        return $this;
    }

    /**
     * Get coutVert
     *
     * @return integer 
     */
    public function getCoutVert() {
        return $this->coutVert;
    }

    /**
     * Set coutJaune
     *
     * @param integer $coutJaune
     * @return Carte
     */
    public function setCoutJaune($coutJaune) {
        $this->coutJaune = $coutJaune;

        return $this;
    }

    /**
     * Get coutJaune
     *
     * @return integer 
     */
    public function getCoutJaune() {
        return $this->coutJaune;
    }

    /**
     * Set coutRouge
     *
     * @param integer $coutRouge
     * @return Carte
     */
    public function setCoutRouge($coutRouge) {
        $this->coutRouge = $coutRouge;

        return $this;
    }

    /**
     * Get coutRouge
     *
     * @return integer 
     */
    public function getCoutRouge() {
        return $this->coutRouge;
    }

    /**
     * Set intercept
     *
     * @param integer $intercept
     * @return Carte
     */
    public function setIntercept($intercept) {
        $this->intercept = $intercept;

        return $this;
    }

    /**
     * Get intercept
     *
     * @return integer 
     */
    public function getIntercept() {
        return $this->intercept;
    }

    /**
     * Set attaque
     *
     * @param integer $attaque
     * @return Carte
     */
    public function setAttaque($attaque) {
        $this->attaque = $attaque;

        return $this;
    }

    /**
     * Get attaque
     *
     * @return integer 
     */
    public function getAttaque() {
        return $this->attaque;
    }

    /**
     * Set personnageChamber
     *
     * @param string $personnageChamber
     * @return Carte
     */
    public function setPersonnageChamber($personnageChamber) {
        $this->personnageChamber = $personnageChamber;

        return $this;
    }

    /**
     * Get personnageChamber
     *
     * @return string 
     */
    public function getPersonnageChamber() {
        return $this->personnageChamber;
    }

    /**
     * Add traitCarte
     *
     * @param \jeus\QuickstrikeBundle\Entity\TraitCarte $traitCarte
     * @return Carte
     */
    public function addTraitCarte(\jeus\QuickstrikeBundle\Entity\TraitCarte $traitCarte) {
        $this->traitCartes[] = $traitCarte;

        return $this;
    }

    /**
     * Remove traitCarte
     *
     * @param \jeus\QuickstrikeBundle\Entity\TraitCarte $traitCarte
     * @return Carte
     */
    public function removeTraitCarte(\jeus\QuickstrikeBundle\Entity\TraitCarte $traitCarte) {
        $this->traitCartes->removeElement($traitCarte);

        return $this;
    }

    /**
     * Get traitCartes
     *
     * @return \jeus\QuickstrikeBundle\Entity\TraitCarte
     */
    public function getTraitCartes() {
        return $this->traitCartes;
    }

    /**
     * Set extension
     *
     * @param \jeus\QuickstrikeBundle\Entity\Extension $extension
     * @return Carte
     */
    public function setExtension(\jeus\QuickstrikeBundle\Entity\Extension $extension) {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return \jeus\QuickstrikeBundle\Entity\Extension
     */
    public function getExtension() {
        return $this->extension;
    }

    /**
     * Set typeCarte
     *
     * @param \jeus\QuickstrikeBundle\Entity\TypeCarte $typeCarte
     * @return Carte
     */
    public function setTypeCarte(\jeus\QuickstrikeBundle\Entity\TypeCarte $typeCarte) {
        $this->typeCarte = $typeCarte;

        return $this;
    }

    /**
     * Get typeCarte
     *
     * @return \jeus\QuickstrikeBundle\Entity\TypeCarte
     */
    public function getTypeCarte() {
        return $this->typeCarte;
    }

    /**
     * Set effet
     *
     * @param \jeus\QuickstrikeBundle\Entity\Effet $effet
     * @return Carte
     */
    public function setEffet(\jeus\QuickstrikeBundle\Entity\Effet $effet) {
        $this->effet = $effet;

        return $this;
    }

    /**
     * Get effet
     *
     * @return \jeus\QuickstrikeBundle\Entity\Effet
     */
    public function getEffet() {
        return $this->effet;
    }

    public function getLien() {
        $lien = '';
        if ($this->getTypeCarte()->getTag() === 'CHAMBER') {
            $lien = 'recto-' . $this->getPersonnageChamber() . '.png';
        } else {
            $lien = $this->getImage();
        }
        $lien = str_replace(' ', '-', $lien);
        return $lien;
    }

    public function getLienAgrandi() {
        $imageAgrandi = '';
        if ($this->getTypeCarte()->getTag() === 'CHAMBER') {
            $imageAgrandi = str_replace('.png', 'rv.png', $this->getImage());
        } else {
            $imageAgrandi = $this->getImage();
        }
        return $imageAgrandi;
    }

}
