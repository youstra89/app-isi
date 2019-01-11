<?php

namespace ISI\ORGBundle\Form;

use ISI\ORGBundle\Entity\Commune;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommuneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',              TextType::class)
            ->add('taux_musulmans',   TextType::class)
            ->add('nombre_habitants', TextType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
          $commune= $event->getData();
          $form   = $event->getForm();

          if (!$commune || null === $commune->getId()) {
              $form->add('save', SubmitType::class, array('label' => 'Enregister la classe'));
          }
          else{
              $form->add('update', SubmitType::class, array('label' => 'Modifier la classe'));
          }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Commune::class,
        ]);
    }
}
