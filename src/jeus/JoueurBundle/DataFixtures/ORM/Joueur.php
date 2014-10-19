<?php

namespace jeus\JoueurBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use jeus\JoueurBundle\Entity\Joueur;

class LoadJoueurData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $Joueur = new Joueur();

        $Joueur->setNom("SMAGGHE");
        $Joueur->setPrenom("julien");
        $Joueur->setEmail("jsmagghe@gmail.com");
        $Joueur->setRoles(array('ROLE_ADMIN'));
        $Joueur->setUsername("jeus");
        $Joueur->setActif(true);

        $encoder = $this->container->get('security.encoder_factory')->getEncoder($Joueur);
        $passwordEncode = $encoder->encodePassword('juliens', $Joueur->getSalt());

        $Joueur->setPassword($passwordEncode);

        $manager->persist($Joueur);
        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }

}
