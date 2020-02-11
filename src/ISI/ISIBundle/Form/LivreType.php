<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class LivreType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom',         TextType::class)
                ->add('auteur',      TextType::class)
                ->add('description', TextType::class, ['required' => FALSE])
                ;
                
                $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
                {
                    $livre = $event->getData();
                    $form = $event->getForm();
                    
                    if (!$livre || null === $livre->getId()) {
                        
                $form
                ->add('matiere', EntityType::class, [
                    'class'         => 'ISIBundle:Matiere',
                    'placeholder'   => 'Sélectionner une matière',
                    'choice_label'  => 'libelle',
                    'multiple'      => FALSE
                  ])
                // ->add('instance', ChoiceType::class, [
                //     'choices' => [
                //     'Instance' => '',
                //     'Conseil'                   => 'Conseil',
                //     'Direction Générale'        => 'Direction Générale',
                //     'Direction des enseignants' => 'Direction des enseignants',
                //     ]
                // ], ['required' => TRUE])
                ->add('support',     FileType::class, ['required' => FALSE, 'label' => 'Support numérique', 'data_class' => null])
                ->add('save',        SubmitType::class, array('label' => 'Enregistrer le livre'));
            }
            else{
                $form->add('update', SubmitType::class, array('label' => 'Modifier le livre'));
            }
        });
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ISIBundle\Entity\Livre'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isibundle_livre';
    }


}
