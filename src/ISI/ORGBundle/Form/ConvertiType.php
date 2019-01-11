<?php

namespace ISI\ORGBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

class ConvertiType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('nom',                TextType::class,     ['label' => 'Nom', 'required' => true])
          ->add('pnom',               TextType::class,     ['label' => 'Prénom', 'required' => true])
          ->add('residence',          TextType::class,     ['label' => 'Résidence', 'required' => true])
          ->add('age',                TextType::class,     ['label' => '', 'required' => true])
          ->add('sexe', ChoiceType::class, array(
            'choices' => array(
              'Selectionner le genre' => '',
              'Homme'                 => 1,
              'Femme'                 => 2
            )
          ))
          ->add('profession',         TextType::class,     ['label' => 'Profession', 'required' => true])
          ->add('ancienneConfession', TextType::class,     ['label' => 'Ancienne confession', 'required' => true])
          ->add('numero',             TextType::class,     ['label' => 'Numéro de téléphone', 'required' => false])
          ->add('commentaire',        TextareaType::class, ['label' => 'Commentaire', 'required' => false]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ORGBundle\Entity\Converti'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_orgbundle_converti';
    }


}
