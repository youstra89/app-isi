<?php

namespace ISI\ENSBundle\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AnneeContratConduiteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('annee')
            // ->add('contrat')
            // ->add('date',         DateType::class, ['required' => TRUE])
            ->add('appreciation', ChoiceType::class, [
                'choices' => [
                  'Donner une appréciation' => '',
                  'Excellente'                 => 'Excellente',
                  'Très bonne'                 => 'Très bonne',
                  'Bonne'                      => 'Bonne',
                  'Mauvaise'                   => 'Mauvaise',
                  'Très mauvaise'              => 'Très mauvaise',
                ]
              ], ['required' => TRUE])
            ->add('description',  TextareaType::class, ['required' => TRUE])
            // ->add('dateSave')
            // ->add('dateUpdate')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $conduite = $event->getData();
            $form = $event->getForm();
    
            if (!$conduite || null === $conduite->getId()) {
                $form->add('save', SubmitType::class, array('label' => 'Enregistrer la conduite'));
            }
            else{
                $form->add('update', SubmitType::class, array('label' => 'Modifier'));
            }
        });
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ENSBundle\Entity\AnneeContratConduite'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_ensbundle_anneecontratconduite';
    }


}
