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

class AnneeContratSanctionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('annee')
            // ->add('contrat')
            // ->add('date', DateType::class, ['required' => TRUE])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Sélectionner le type de la sanction' => '',
                    'Blâme' => 'Blâme',
                    'Diminution de salaire' => 'Diminution de salaire',
                    'Retrait temporaire de l\'emploi du temps' => 'Retrait temporaire de l\'emploi du temps',
                ],
                'multiple'  => FALSE
            ], ['required' => TRUE])
            ->add('description', TextareaType::class, ['required' => TRUE])
            // ->add('dateSave')
            // ->add('dateUpdate')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $sanction = $event->getData();
            $form = $event->getForm();
    
            if (!$sanction || null === $sanction->getId()) {
                $form->add('save', SubmitType::class, array('label' => 'Enregistrer'));
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
            'data_class' => 'ISI\ENSBundle\Entity\AnneeContratSanction'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_ensbundle_anneecontratsanction';
    }


}
