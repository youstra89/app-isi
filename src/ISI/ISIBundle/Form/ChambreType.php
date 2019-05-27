<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChambreType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('batiment', EntityType::class, [
                  'class'         => 'ISIBundle:Batiment',
                  'placeholder'   => 'Choisir le bâtiment',
                  'choice_label'  => 'nom',
                  'multiple'      => false
                ])
                ->add('libelle', TextType::class)
                ->add('genre', ChoiceType::class, [
                  'choices' => [
                    'Garçon' => 1,
                    'Fille'  => 2,
                  ],
                  'placeholder' => 'Quel est le genre de la chambre ?'
                ])
                ->add('nombreDePlaces', NumberType::class)
                // ->add('dateSave')
                // ->add('dateUpdate')
                ->add('save', SubmitType::class, array('label' => 'Enregistrer la chambre'));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ISIBundle\Entity\Chambre'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isibundle_chambre';
    }


}
