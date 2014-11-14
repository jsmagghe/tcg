<?php

namespace jeus\JoueurBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class JoueurType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('nom', 'text', array(
                    'label' => 'nom',
                    'required' => true,
                ))
                ->add('prenom', 'text', array(
                    'label' => 'prenom',
                    'required' => true,
                ))
                ->add('username', 'text', array(
                    'label' => 'username',
                    'required' => true,
                    'error_bubbling' => true
                ))
                ->add('password', 'password', array(
                    'label' => 'password',
                    'required' => true,
                    'attr' => array(
                        'placeholder' => '**********'
                    )
                ))
                ->add('email', 'text', array(
                    'label' => 'email',
                    'required' => false,
                    'error_bubbling' => true
                ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
    }

    public function onPreSetData(FormEvent $event)
    {
        $Joueur = $event->getData();
        $form = $event->getForm();

        if (!is_null($Joueur->getId())) { /* Si on est en édition */
            $form->add('role', 'choice', array(
                        'mapped' => false,
                        'required' => true,
                        'label' => null,
                        'choices' => array(
                            'ROLE_ADMIN' => 'administrateur',
                            'ROLE_USER' => 'utilisateur'
                        ),
                        'expanded' => false,
                        'multiple' => false,
                        'empty_value' => '- Choisissez -',
                        'empty_data' => null,
                        'data' => $Joueur->getRole()
                    ))
                    ->add('password', 'password', array(
                        'label' => false,
                        'required' => false,
                        'attr' => array(
                            'placeholder' => '**********'
                        )
                    ))
            ;
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'jeus\JoueurBundle\Entity\Joueur'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'jeus_joueurbundle_joueur';
    }

}
