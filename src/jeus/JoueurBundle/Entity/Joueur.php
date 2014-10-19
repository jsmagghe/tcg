<?php

namespace jeus\JoueurBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Joueur
 *
 * @ORM\Table(name="joueur")
 * @ORM\Entity(repositoryClass="jeus\JoueurBundle\Repository\JoueurRepository")
 */
class Joueur implements AdvancedUserInterface
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
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\Email(message = "'{{ value }}' n'est pas un email valide.", checkMX = true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     * @var string
     *
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * @var boolean
     *
     * @ORM\Column(name="actif", type="boolean", options={"default"=1})
     */
    protected $actif;

    /**
     * @var string
     *
     * @ORM\Column(name="jeuEnCours", type="string", length=255, nullable=true)
     */
    private $jeuEnCours;

    /**
     * @var string
     *
     * @ORM\Column(name="enAttenteBleach", type="boolean", options={"default"=0})
     */
    private $enAttenteBleach;

    /**
     * @var string
     *
     * @ORM\Column(name="enAttenteQuickstrike", type="boolean", options={"default"=0})
     */
    private $enAttenteQuickstrike;

    /**
     * @var string
     *
     * @ORM\Column(name="enAttenteSaintSeiya", type="boolean", options={"default"=0})
     */
    private $enAttenteSaintSeiya;

    /**
     * @ORM\OneToMany(targetEntity="jeus\QuickstrikeBundle\Entity\Deck", mappedBy="joueur", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $DeckQuickstrikes;
    
    
    public function __construct()
    {
        $this->roles = array();
        $this->DeckQuickstrikes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Joueur
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
     * Set prenom
     *
     * @param string $prenom
     * @return Joueur
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Joueur
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Joueur
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Joueur
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Joueur
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set jeuEnCours
     *
     * @param string $jeuEnCours
     * @return Joueur
     */
    public function setJeuEnCours($jeuEnCours)
    {
        $this->jeuEnCours = $jeuEnCours;

        return $this;
    }

    /**
     * Get jeuEnCours
     *
     * @return string 
     */
    public function getJeuEnCours()
    {
        return $this->jeuEnCours;
    }

    /**
     * Set roles
     *
     * @param array $roles
     * @return Joueur
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array 
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function setRole($role)
    {
        $this->roles = array($role);

        return $this;
    }

    public function getRole()
    {
        return $this->roles[0];
    }

    public function getRolePourVue()
    {
        switch ($this->roles[0]) {
            case "ROLE_ADMIN":
                return "administrateur";
                break;
            case "ROLE_USER":
                return "utilisateur";
                break;
            default:
                return $this->roles[0];
        }
    }

    public function eraseCredentials()
    {
        
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->actif;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return Joueur
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return boolean 
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Set enAttenteBleach
     *
     * @param boolean $enAttenteBleach
     * @return Joueur
     */
    public function setEnAttenteBleach($enAttenteBleach)
    {
        $this->enAttenteBleach = $enAttenteBleach;

        return $this;
    }

    /**
     * Get enAttenteBleach
     *
     * @return boolean 
     */
    public function getEnAttenteBleach()
    {
        return $this->enAttenteBleach;
    }

    /**
     * Set enAttenteQuickstrike
     *
     * @param boolean $enAttenteQuickstrike
     * @return Joueur
     */
    public function setEnAttenteQuickstrike($enAttenteQuickstrike)
    {
        $this->enAttenteQuickstrike = $enAttenteQuickstrike;

        return $this;
    }

    /**
     * Get enAttenteQuickstrike
     *
     * @return boolean 
     */
    public function getEnAttenteQuickstrike()
    {
        return $this->enAttenteQuickstrike;
    }

    /**
     * Set enAttenteSaintseiya
     *
     * @param boolean $enAttenteSaintseiya
     * @return Joueur
     */
    public function setEnAttenteSaintseiya($enAttenteSaintseiya)
    {
        $this->enAttenteSaintseiya = $enAttenteSaintseiya;

        return $this;
    }

    /**
     * Get enAttenteSaintseiya
     *
     * @return boolean 
     */
    public function getEnAttenteSaintseiya()
    {
        return $this->enAttenteSaintseiya;
    }

    public function getActifPourVue()
    {
        return $this->actif ? "Oui" : "Non";
    }

    public function __toString()
    {
        return $this->nom . " " . $this->prenom;
    }

    public function getValeursPourVue()
    {
        $values = array(
            $this->nom,
            $this->prenom,
            $this->username,
            '**********',
            $this->email,
            $this->getRolePourVue(),
            $this->getActifPourVue()
        );

        return $values;
    }

    public static function getFields()
    {
        return array('Nom', 'Prénom', 'Login', 'Mot de passe', 'Email', 'Role', 'Actif', 'Jeu en cours');
    }


    /**
     * Add DeckQuickstrike
     *
     * @param \jeus\QuickstrikeBundle\Entity\DeckQuickstrike $DeckQuickstrike
     * @return Joueur
     */
    public function addDeckQuickstrike(\jeus\QuickstrikeBundle\Entity\DeckQuickstrike $DeckQuickstrike)
    {
        $this->DeckQuickstrikes[] = $DeckQuickstrike;

        return $this;
    }

    /**
     * Remove DeckQuickstrike
     *
     * @param \jeus\QuickstrikeBundle\Entity\DeckQuickstrike $DeckQuickstrike
     */
    public function removeDeckQuickstrike(\jeus\QuickstrikeBundle\Entity\DeckQuickstrike $DeckQuickstrike)
    {
        $this->DeckQuickstrikes->removeElement($DeckQuickstrike);
    }

    /**
     * Get DeckQuickstrikes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDeckQuickstrikes()
    {
        return $this->DeckQuickstrikes;
    }
}
