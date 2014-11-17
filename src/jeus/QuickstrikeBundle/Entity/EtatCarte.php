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
     * @var string
     *
     * @ORM\Column(name="emplacement", type="string", length=50)
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

    function __construct($emplacement) {
        $this->visible = false;
        $this->emplacement = $emplacement;
    }

}
