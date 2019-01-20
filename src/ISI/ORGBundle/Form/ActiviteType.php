<?php

namespace ISI\ORGBundle\Form;

use ISI\ORGBundle\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ActiviteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('lieu',  TextType::class,   ['required' => true, 'label' => 'Lieu/Quartier'])
          ->add('heure', TextType::class,   ['required' => true, 'label' => 'Heure'])
          ->add('type',  ChoiceType::class, ['required' => true, 'label' => 'Type', 'choices' => $this->getChoices(), 'placeholder' => 'Faites un choix'])
          ->add('theme', TextType::class,   ['required' => true, 'label' => 'Theme']);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ORGBundle\Entity\Activite'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_orgbundle_activite';
    }

    public function getChoices()
    {
      $choices = Activite::TYPE;
      $output = [];
      foreach($choices as $k => $v){
        $output[$v] = $k;
      }
      return $output;
    }
}
