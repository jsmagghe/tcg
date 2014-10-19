<?php

namespace jeus\QuickstrikeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BlocMentionLegale
 *
 * @ORM\Table(name="quickstrike_cartedeck")
 * @ORM\Entity(repositoryClass="jeus\QuickstrikeBundle\Repository\CarteDeckRepository")
 */
class CarteDeck
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
     * @ORM\ManyToOne(targetEntity="\jeus\QuickstrikeBundle\Entity\Carte")
     */
    private $Carte;

    /**
     * @ORM\ManyToOne(targetEntity="\jeus\QuickstrikeBundle\Entity\Deck", inversedBy="Cartes")
     */
    private $Deck;



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
     * Set Carte
     *
     * @param \jeus\QuickstrikeBundle\Entity\Carte $carte
     * @return CarteDeck
     */
    public function setCarte(\jeus\QuickstrikeBundle\Entity\Carte $carte = null)
    {
        $this->Carte = $carte;

        return $this;
    }

    /**
     * Get Carte
     *
     * @return \jeus\QuickstrikeBundle\Entity\Carte 
     */
    public function getCarte()
    {
        return $this->Carte;
    }

    /**
     * Set Deck
     *
     * @param \jeus\QuickstrikeBundle\Entity\Deck $deck
     * @return CarteDeck
     */
    public function setDeck(\jeus\QuickstrikeBundle\Entity\Deck $deck = null)
    {
        $this->Deck = $deck;

        return $this;
    }

    /**
     * Get Deck
     *
     * @return \jeus\QuickstrikeBundle\Entity\Deck 
     */
    public function getDeck()
    {
        return $this->Deck;
    }
}
