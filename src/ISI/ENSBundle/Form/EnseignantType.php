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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

class EnseignantType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nomFr',  TextType::class, ['required' => TRUE]) 
                ->add('nomAr',  TextType::class, ['required' => TRUE])
                ->add('pnomFr', TextType::class, ['required' => TRUE])
                ->add('pnomAr', TextType::class, ['required' => TRUE])
                ->add('sexe',   ChoiceType::class, array(
                    'choices' => array(
                      'Selectionner le genre' => '',
                      'Homme'                 => 1,
                      'Femme'                 => 2
                    )
                  ), ['required' => TRUE])
                // ->add('dateNaissance',  BirthdayType::class, ['required' => TRUE])
                ->add('lieuNaissance',  TextType::class,     ['required' => TRUE])
                ->add('referenceCni',   TextType::class,     ['required' => TRUE])
                ->add('contact',        TextType::class,     ['required' => TRUE])
                ->add('email',          TextType::class,     ['required' => TRUE])
                ->add('nationalite',    TextType::class,     ['required' => TRUE])
                ->add('niveauEtude',    TextType::class,     ['required' => TRUE])
                ->add('diplomeObtenu',  TextType::class,     ['required' => TRUE])
                ->add('autresCompetences',      TextareaType::class,     ['required' => TRUE])
                ->add('situationMatrimoniale',  ChoiceType::class,   [
                    'choices' => array(
                        'Faites un choix' => '',
                        'Célibataire'     => 'Célibataire',
                        'Marié(e)'        => 'Marié(e)',
                        'Divorcéé(e)'     => 'Divorcéé(e)'
                      )
                    ], ['required' => TRUE])
                ->add('residence',              TextType::class,     ['required' => TRUE])
                ->add('expProfessionnelle',     IntegerType::class,  ['required' => TRUE])
                ->add('languesParlees',         TextType::class,     ['required' => TRUE])
                ->add('anneeObtention',         TextType::class,     ['required' => TRUE]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $enseignant = $event->getData();
            $form = $event->getForm();

            if (!$enseignant || null === $enseignant->getId()) {
                $form->add('save', SubmitType::class, array('label' => 'Enregistrer l\'enseignant'));
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
            'data_class' => 'ISI\ENSBundle\Entity\Enseignant'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ENSBundle_enseignant';
    }


}
