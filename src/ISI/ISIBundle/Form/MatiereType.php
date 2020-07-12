<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ISI\ISIBundle\Repository\MatiereRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MatiereType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('libelle',  TextType::class);

          $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
          {
              $matiere = $event->getData();
              $form = $event->getForm();

              if (!$matiere || null === $matiere->getId()) {
                $form
                  ->add('referenceLangue', EntityType:: class, array(
                    'class'        => 'ISIBundle:Languematiere',
                    'choice_label' => 'libelle',
                    'multiple'     => false
                  ))
                  ->add('matiere_mere', EntityType::class, [
                    'class'         => 'ISIBundle:Matiere',
                    'placeholder'   => 'Choisir la matière mère',
                    'query_builder' => function (MatiereRepository $er)
                    {
                      return $er->createQueryBuilder('m')
                                ->where('m.matiere_mere IS NULL');
                    },
                    'choice_label'  => 'libelle',
                    'multiple'      => false,
                    'required'      => false
                  ])
                  ->add('save', SubmitType::class, array('label' => 'Enregistrer la matière'));
              }
              else{
                $form->add('update', SubmitType::class, array('label' => 'Modifier la matière'));
              }
          });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ISIBundle\Entity\Matiere'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isibundle_matiere';
    }


}
