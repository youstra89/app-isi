<?php

namespace ISI\ORGBundle\Form;
use ISI\ORGBundle\Entity\Mosquee;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MosqueeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('nom',                TextType::class, ['label' => 'Nom de la mosquée'])
          ->add('quartier',           TextType::class, ['label' => 'Quartier'])
          ->add('responsable',        TextType::class, ['label' => 'Nom du responsable',    'required' => false])
          ->add('numero_responsable', TextType::class, ['label' => 'Numéro du responsable', 'required' => false])
          ->add('djoumoua',           CheckboxType::class, ['required' => false])
          ->add('annee_construction')
          ->add('description',        TextType::class, ['label' => 'Description'])
          ->add('commune',            EntityType::class, [
              'required' => true,
              'label' => 'Commune',
              'placeholder' => 'Commune',
              'class' => 'ORGBundle:Commune',
              'choice_label' => 'nom',
              'multiple' => false
          ])
          ->add('imam', EntityType::class, [
              'required' => false,
              'label' => 'Imam',
              'choice_label' => function ($imam) {
                    return $imam->getPnom() . ' ' . $imam->getNom();
              },
              'class' => 'ORGBundle:Imam',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('i');
              },
              'multiple' => false
            ])
            ->add('options', ChoiceType::class, [
              'choices' => $this->getChoices(Mosquee::OPTIONS),
              'multiple' => true
            ]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ORGBundle\Entity\Mosquee'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_orgbundle_mosquee';
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
