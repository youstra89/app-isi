<?php

namespace ISI\ORGBundle\Form;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class VendrediType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('mosquee', EntityType::class, [
              'required' => true,
              'label' => 'Mosquee',
              'placeholder' => 'Mosquee',
              'class' => 'ORGBundle:Mosquee',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('m');
              },
              'choice_label' => 'nom',
              'multiple' => false
          ])
          ->add('imam', EntityType::class, [
              'required' => true,
              'label' => 'Imam',
              'placeholder' => 'Imam',
              'choice_label' => function ($imam) {
                    return $imam->getPnom() . ' ' . $imam->getNom();
              },
              'class' => 'ORGBundle:Imam',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('i');
              },
              'multiple' => false
            ]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ORGBundle\Entity\Vendredi'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_orgbundle_vendredi';
    }


}
