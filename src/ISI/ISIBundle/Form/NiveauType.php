<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

use ISI\ISIBundle\Repository\GroupeformationRepository;

class NiveauType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $regime       = $options["regime"];
      $builder
        ->add('libelleFr', TextType::class)
        ->add('libelleAr', TextType::class)
        // ->add('dateSave')
        // ->add('dateUpdate')
        ;

      $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event)
        {
          $form = $event->getForm();
          $niveau = $event->getData();
          if($niveau->getId() === null)
          {
            $form->add('save', SubmitType::class, array('label' => 'Enregistrer le niveau'));
          }
          else {
            $form->add('update', SubmitType::class, array('label' => 'Modifier le niveau'));
          }
        }
      );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ISIBundle\Entity\Niveau'
        ));

        $resolver->setRequired([
          'regime'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isibundle_niveau';
    }


}
