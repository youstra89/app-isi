<?php

namespace ISI\ENSBundle\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ConvocationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('date',     DateType::class, ['required' => TRUE])
            ->add('instance', ChoiceType::class, [
                'choices' => [
                  'Instance' => '',
                  'Conseil'                   => 'Conseil',
                  'Direction Générale'        => 'Direction Générale',
                  'Direction des enseignants' => 'Direction des enseignants',
                ]
              ], ['required' => TRUE])
            ->add('motif',    TextareaType::class, ['required' => TRUE])
            // ->add('dateSave')
            // ->add('dateUpdate')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $convocation = $event->getData();
            $form = $event->getForm();
    
            if (!$convocation || null === $convocation->getId()) {
                $form->add('save', SubmitType::class, array('label' => 'Enregistrer la convocation'));
            }
            else{
                $form->add('update', SubmitType::class, array('label' => 'Enregistrer les modifications'));
            }
        });
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ENSBundle\Entity\Convocation'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_ensbundle_convocation';
    }


}
