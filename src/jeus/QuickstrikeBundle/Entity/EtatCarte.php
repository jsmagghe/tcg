<?php

namespace jeus\QuickstrikeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtatCarte
 *
 * @ORM\Table(name="quickstrike_etatcarte")
 * @ORM\Entity
 */
class EtatCarte
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
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible;

    /**
     * @ORM\ManyToOne(targetEntity="jeus\QuickstrikeBundle\Entity\Emplacement")
     */
    protected $emplacement;


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
     * @param \jeus\QuickstrikeBundle\Entity\Emplacement $emplacement
     * @return Partie
     */
    public function setEmplacementJoueur(\jeus\QuickstrikeBundle\Entity\Emplacement $emplacement)
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    /**
     * Get emplacement
     *
     * @return \jeus\QuickstrikeBundle\Entity\Emplacement
     */
    public function getEmplacementJoueur()
    {
        return $this->emplacement;
    }

    function __construct($emplacement) {
        $this->visible = false;
        $this->emplacement = $emplacement;
    }

}
