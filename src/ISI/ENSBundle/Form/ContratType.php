<?php

namespace ISI\ENSBundle\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContratType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('debut',      DateType::class, ['required' => TRUE])
            ->add('duree',      IntegerType::class, ['required' => FALSE])
            ->add('anneeDebut', EntityType::class, [
                'class'         => 'ISIBundle:Annee',
                'placeholder'   => 'Choisir l\'année de début',
                'choice_label'  => 'libelle',
                'multiple'      => FALSE
              ], ['required' => TRUE])
            ->add('commentaire', TextareaType::class, ['required' => TRUE])
            ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $contrat = $event->getData();
            $form = $event->getForm();
    
            if (!$contrat || null === $contrat->getId()) {
                $form->add('save', SubmitType::class, array('label' => 'Enregistrer la prise de fonction'));
            }
            else{
                $form->add('update', SubmitType::class, array('label' => 'Enregistrer les modifications'));
            }
        });
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ENSBundle\Entity\Contrat'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_ensbundle_contrat';
    }


}
