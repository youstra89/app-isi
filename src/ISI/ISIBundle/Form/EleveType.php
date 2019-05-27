<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

class EleveType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('nomFr',  TextType::class)
          ->add('nomAr',  TextType::class, ['required' => FALSE])
          ->add('pnomFr', TextType::class)
          ->add('pnomAr', TextType::class, ['required' => FALSE])
          ->add('sexe', ChoiceType::class, array(
            'choices' => array(
              'Selectionner le genre' => '',
              'Homme'                 => 1,
              'Femme'                 => 2
            )
          ))
          // ->add('sexeAr')
          ->add('refExtrait', TextType::class)
          ->add('grpSanguin', ChoiceType::class, array(
            'required' => FALSE,
            'choices'  => array(
              'Le groupe sanguin' => '',
              'Groupe A'          => 'A',
              'Groupe B'          => 'B',
              'Groupe AB'         => 'AB',
              'Groupe O'          => 'O'
            )
          ))
          // ->add('dateNaissance', BirthdayType::class, ['required' => TRUE])
          ->add('lieuNaissance', TextType::class, ['required' => TRUE])
          ->add('nationalite', TextType::class, ['required' => TRUE])
          ->add('commune', TextType::class, ['required' => TRUE])
          ->add('residence', TextType::class, ['required' => TRUE])
          ->add('contact', TextType::class, ['required' => FALSE])
          ->add('etsOrigine', TextType::class, ['required' => FALSE])
          ->add('etatSante', TextType::class, ['required' => FALSE])
          // ->add('photo', TextType::class)
          ->add('nomPere', TextType::class)
          ->add('contactPere', TextType::class, ['required' => FALSE])
          ->add('professionPere', TextType::class, ['required' => FALSE])
          ->add('situationPere', ChoiceType::class, [
            'placeholder' => 'Situation du père',
            'choices' => [
              'Vivant' => 1,
              'Décédé' => 2
            ],
          ])
          ->add('nomMere', TextType::class)
          ->add('contactMere', TextType::class, ['required' => FALSE])
          ->add('professionMere', TextType::class)
          ->add('situationMere', ChoiceType::class, array(
            'placeholder' => 'Situation de la mère',
            'choices' => [
                'Vivante' => 1,
                'Décédée' => 2
              ]
          ))
          ->add('nomTuteur', TextType::class, ['required' => FALSE])
          ->add('contactTuteur', TextType::class, ['required' => FALSE])
          ->add('professionTuteur', TextType::class, ['required' => FALSE]);

        //Ecoute de l'évènement
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $eleve = $event->getData();
            $form = $event->getForm();

            if (!$eleve || null === $eleve->getId()) {
              $form
                ->add('save', SubmitType::class, array('label' => 'Préinscrire l\'élève'));
            }
            else{
              $form->add('update', SubmitType::class, array('label' => 'Modifier l\'élève'));
            }
        });
      }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ISIBundle\Entity\Eleve'
        ));

        // $resolver->setRequired([
        //   'matricule',
        //   'lettre',
        //   'annee'
        // ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isibundle_eleve';
    }
}
