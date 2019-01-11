<?php

namespace ISI\ENSBundle\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class AnneeContratAbsenceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateDepart',   DateType::class,     ['required' => TRUE])
            ->add('dateRetour',   DateType::class,     ['required' => TRUE])
            ->add('motif',        TextareaType::class, ['required' => TRUE])
            ->add('autorisation', CheckboxType::class, ['required' => FALSE])
            ->add('dateDemande',  DateType::class,     ['required' => FALSE])
            // ->add('dateSave')
            // ->add('dateUpdate')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $absence = $event->getData();
            $form = $event->getForm();
    
            if (!$absence || null === $absence->getId()) {
                $form->add('save', SubmitType::class, array('label' => 'Enregistrer l\'absence'));
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
            'data_class' => 'ISI\ENSBundle\Entity\AnneeContratAbsence'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_ensbundle_anneecontratabsence';
    }


}
