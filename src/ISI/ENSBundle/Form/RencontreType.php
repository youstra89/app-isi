<?php

namespace ISI\ENSBundle\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RencontreType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type',  ChoiceType::class, [
                    'choices' => [
                        'Réunion' => 'Réunion',
                        'Séminaire' => 'Séminaire',
                        'Autre' => 'Autre'
                    ],
                    'placeholder' => 'Choisir le type de la rencontre'
                ], ["required" => true])
            // ->add('date',  Date::class, ["required" => TRUE])
            ->add('theme', TextType::class, ["required" => true])
            ->add('duree', TextType::class, ["required" => true])
            ->add('lieu',  ChoiceType::class, [
                    'choices' => [
                        'Salle des enseignants' => 'Salle des enseignants',
                        'Salle de réunion' => 'Salle de réunion',
                        'Bibliothèque' => 'Bibliothèque',
                        'Mosquée' => 'Mosquée',
                        'Autre' => 'Autre'
                    ],
                    'placeholder' => 'Choisir le lieu de la rencontre'
                ], ["required" => true])
            // ->add('dateSave')
            // ->add('dateUpdate')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $rencontre = $event->getData();
            $form = $event->getForm();
    
            if (!$rencontre || null === $rencontre->getId()) {
                $form->add('save', SubmitType::class, array('label' => 'Enregistrer la rencontre'));
            }
            else{
                $form->add('update', SubmitType::class, array('label' => 'Enregistrer les modifications'));
            }
        });
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ENSBundle\Entity\Rencontre'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_ensbundle_rencontre';
    }


}
