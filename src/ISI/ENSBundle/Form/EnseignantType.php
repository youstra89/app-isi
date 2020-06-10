<?php

namespace ISI\ENSBundle\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EnseignantType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nomFr',  TextType::class, ['required' => true]) 
                ->add('nomAr',  TextType::class, ['required' => true])
                ->add('pnomFr', TextType::class, ['required' => true])
                ->add('pnomAr', TextType::class, ['required' => true])
                ->add('sexe',   ChoiceType::class, array(
                    'choices' => array(
                      'Selectionner le genre' => '',
                      'Homme'                 => 1,
                      'Femme'                 => 2
                    )
                  ), ['required' => true])
                // ->add('dateNaissance',  BirthdayType::class, ['required' => TRUE])
                ->add('lieuNaissance',  TextType::class,     ['required' => true])
                ->add('referenceCni',   TextType::class,     ['required' => true])
                ->add('contact',        TextType::class,     ['required' => true])
                ->add('email',          TextType::class,     ['required' => true])
                ->add('nationalite',    TextType::class,     ['required' => true])
                ->add('niveauEtude',    TextType::class,     ['required' => true])
                ->add('diplomeObtenu',  TextType::class,     ['required' => true])
                ->add('autresCompetences',      TextareaType::class,     ['required' => true])
                ->add('situationMatrimoniale',  ChoiceType::class,   [
                    'choices' => array(
                        'Faites un choix' => '',
                        'Célibataire'     => 'Célibataire',
                        'Marié(e)'        => 'Marié(e)',
                        'Divorcéé(e)'     => 'Divorcéé(e)'
                      )
                    ], ['required' => true])
                ->add('residence',              TextType::class,     ['required' => true])
                ->add('expProfessionnelle',     IntegerType::class,  ['required'    => true])
                ->add('languesParlees',         TextType::class,     ['required' => true])
                ->add('anneeObtention',         TextType::class,     ['required' => true])
                ->add('enseignant',             CheckboxType::class, ['required' => false])
                ->add('arabe',                  CheckboxType::class, ['required' => false])
                ->add('francais',               CheckboxType::class, ['required' => false])
                ->add('administrateur',         CheckboxType::class, ['required' => false])
                ->add('autre',                  CheckboxType::class, ['required' => false])
        ;

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
