<?php

namespace ISI\ORGBundle\Form;

use ISI\ORGBundle\Entity\Cours;
use ISI\ORGBundle\Entity\Mosquee;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CoursType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('mosquee', EntityType::class, [
            'class' => 'ORGBundle:Mosquee',
            'query_builder' => function (EntityRepository $er) {
              return $er->createQueryBuilder('m');
            },
            'choice_label' => function ($mosquee) {
              return $mosquee->getNom() . ' - ' . $mosquee->getCommune()->getNom() . ' - ' . $mosquee->getQuartier();
            },
            'placeholder' => 'Nom de la mosquée',
            'required' => false,
            'multiple' => false
          ])
          // ->add('mosquee', EntityType::class, [
          //   'required' => false,
          //   'label' => false,
          //   'placeholder' => 'Fournisseur',
          //   'multiple' => false
          // ])
          ->add('discipline', TextType::class, ['label' => 'Discipline'])
          ->add('livre',      TextType::class, ['label' => 'Livre étudié'])
          ->add('heure', ChoiceType::class, [
            'placeholder' => 'Heure du cours',
            'choices' => $this->getChoices(Cours::HEURECOURS),
            'multiple' => false
          ])
          ->add('jour', ChoiceType::class, [
            'placeholder' => 'Jour du cours',
            'choices' => $this->getChoices(Cours::JOURCOURS),
            'multiple' => true
          ])
          ->add('anneeDebut', TextType::class, ['label' => 'Année de debut']);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ORGBundle\Entity\Cours'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_orgbundle_cours';
    }

    public function getChoices($entity)
    {
      $choices = $entity;
      $output = [];
      foreach($choices as $k => $v){
        $output[$v] = $k;
      }
      return $output;
    }


}
