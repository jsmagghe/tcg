<?php

namespace jeus\QuickstrikeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Effet
 *
 * @ORM\Table(name="quickstrike_effet")
 * @ORM\Entity
 */
class Effet
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
     * @ORM\Column(name="numero", type="smallint")
     */
    private $numero;

    /**
     * @var string
     *
     * @ORM\Column(name="texte", type="text")
     */
    private $texte;

    /**
     * @var string
     *
     * @ORM\Column(name="texteFr", type="text", nullable=true)
     */
    private $texteFr;


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
     * Set numero
     *
     * @param integer $numero
     * @return Effet
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return integer 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set texte
     *
     * @param string $texte
     * @return Effet
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte
     *
     * @return string 
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Set texteFr
     *
     * @param string $texteFr
     * @return Effet
     */
    public function setTexteFr($texteFr)
    {
        $this->texteFr = $texteFr;

        return $this;
    }

    /**
     * Get texteFr
     *
     * @return string 
     */
    public function getTexteFr()
    {
        return $this->texteFr;
    }
}
