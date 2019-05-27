<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Type;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use ISI\ISIBundle\Form\Regimeformation;
use ISI\ISIBundle\Repository\NiveauRepository;
use ISI\ISIBundle\Repository\RegimeformationRepository;

class ClasseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $regime = $options['regime'];
      $builder
        ->add('libellefr', TextType::class)
        ->add('libellear', TextType::class)
        ->add('genre', ChoiceType::class, array(
          'choices' => array(
            'Choisir le genre de la classe' => '',
            'رجال'                         => 'H',
            'نساء'                         => 'F',
            'مزدوج'                        => 'M'
          )
        ))
      ;

        //Ecoute de l'évènement
      $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
      {
        dump($event->getForm()->getConfig()->getOptions()['regime']);
        $regime = $event->getForm()->getConfig()->getOptions()['regime'];
        $classe = $event->getData();
        $form   = $event->getForm();

        if (!$classe || null === $classe->getId()) {
            $form
              ->add('niveau', EntityType::class, [
                'class'         => 'ISIBundle:Niveau',
                'placeholder'   => 'Choisir le niveau',
                'query_builder' => function (NiveauRepository $er) use ($regime)
                {
                  return $er->createQueryBuilder('n')
                            ->join('n.groupeFormation', 'grp')
                            ->where('grp.reference = :regime')
                            ->setParameter('regime', $regime);
                },
                'choice_label'  => 'libelleFr',
                'multiple'      => false
              ])
              ->add('save', SubmitType::class, array('label' => 'Enregister la classe'));
        }
        else{
          $form->add('update', SubmitType::class, array('label' => 'Modifier la classe'));
        }
      });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
      $resolver->setRequired(['regime']);
        
        $resolver->setDefaults(array(
          'data_class' => 'ISI\ISIBundle\Entity\Classe',
          'regime' => null
      ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isibundle_classe';
    }


}
