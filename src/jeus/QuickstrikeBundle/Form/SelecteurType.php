<?php

namespace jeus\QuickstrikeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use jeus\QuickstrikeBundle\Entity\Extension;
use jeus\QuickstrikeBundle\Entity\TraitCarte;
use jeus\QuickstrikeBundle\Entity\TypeCarte;

class SelecteurType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('extension', 'entity', array(
                    'class' => 'jeusQuickstrikeBundle:Extension',
                    'property' => 'libelle',
                    'label' => 'Extension',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                ))
                ->add('typeCarte', 'entity', array(
                    'class' => 'jeusQuickstrikeBundle:TypeCarte',
                    'property' => 'libelle',
                    'label' => 'Type de carte',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                    'attr' => array(
                        'class' => 'type-carte-ajax'
                        ),
                ))
                ->add('traitCarte', 'entity', array(
                    'class' => 'jeusQuickstrikeBundle:TraitCarte',
                    'property' => 'libelle',
                    'label' => 'Trait',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false
                ))
                ->add('attaque', 'text', array(
                    'required' => false,
                    'mapped' => false
                ))
                ->add('intercept', 'text', array(
                    'required' => false,
                    'mapped' => false
                ))
                ->add('effet', 'text', array(
                    'required' => false,
                    'mapped' => false
                ))
                ->add('page', 'hidden', array(
                    'required' => false,
                    'mapped' => false
                ))
                /*->add('nombreCarte', 'choice', array(
                    'label' => 'Nombre de carte',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'mapped' => false,
                    'empty_value' => '8 cartes',
                    'choices' => array(
                        9 => '9 cartes',
                        20 => '20 cartes',
                        50 => '50 cartes',
                        100 => '100 cartes',
                        200 => '200 cartes',
                        0 => 'toutes les cartes',
                    ),
                ))*/
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
                //'data_class' => 'jeus\QuickstrikeBundle\Entity\Extension'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'jeus_quickstrikebundle_selecteur';
    }

}
