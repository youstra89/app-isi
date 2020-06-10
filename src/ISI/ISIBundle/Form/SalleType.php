<?php

namespace ISI\ISIBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ISI\ISIBundle\Repository\BatimentRepository;

class SalleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom',          TextType::class, ['required' => true])
                ->add('nombrePlace')
                ->add('localisation', TextType::class, ['required' => true])
                ->add('batiment', EntityType::class, [
                    'class'         => 'ISIBundle:Batiment',
                    'placeholder'   => 'Choisir le bâtiment',
                    'query_builder' => function (BatimentRepository $er)
                    {
                      return $er->createQueryBuilder('b')
                                ->where('b.utilisation = :used')
                                ->setParameter('used', 1);
                    },
                    'choice_label'  => 'nom',
                    'multiple'      => false,
                    'required'      => true
                  ])
        ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ISIBundle\Entity\Salle'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isi_isibundle_salle';
    }


}
