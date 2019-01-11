<?php

namespace ISI\ENSBundle\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AnneeContratType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('annee')
            // ->add('contrat')
            ->add('nombreHeuresCours',  IntegerType::class, ['required' => TRUE])
            ->add('nombreHeuresCoran',  IntegerType::class, ['required' => TRUE])
            ->add('nombreHeuresSamedi', IntegerType::class, ['required' => TRUE])
            // ->add('dateSave')
            // ->add('dateUpdate')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $anneeContrat = $event->getData();
            $form = $event->getForm();
    
            if (!$anneeContrat || null === $anneeContrat->getId()) {
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
            'data_class' => 'ISI\ENSBundle\Entity\AnneeContrat'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_ensbundle_anneecontrat';
    }


}
