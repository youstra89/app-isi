<?php

namespace ISI\ORGBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ImamType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('nom',           TextType::class, ['label' => 'Nom',                 'required' => true])
          ->add('pnom',          TextType::class, ['label' => 'Prénom',              'required' => true])
          ->add('niveau_etude',  TextType::class, ['label' => 'Niveau d\'étude',     'required' => false])
          ->add('specialite',    TextType::class, ['label' => 'Spécialité',          'required' => false])
          ->add('numero',        TextType::class, ['label' => 'Numéro de téléphone', 'required' => true])
          ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ORGBundle\Entity\Imam'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_orgbundle_imam';
    }


}
