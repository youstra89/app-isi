<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class HalaqaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class)
            ->add('genre', ChoiceType::class, array(
                'choices' => array(
                    'Choisir le genre de la classe' => '',
                    'Homme'                         => 'H',
                    'Femme'                         => 'F',
                    'Mixte'                         => 'M'
                    )
                )
            )
            ->add('save', SubmitType::class, ['label' => 'Enregistrer la halaqa'])
            // ->add('dateSave')
            // ->add('dateUpdate')
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ISIBundle\Entity\Halaqa'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isibundle_halaqa';
    }


}
