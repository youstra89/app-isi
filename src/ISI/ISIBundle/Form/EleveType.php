<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EleveType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('nomFr',  TextType::class)
          ->add('nomAr',  TextType::class, ['required' => false])
          ->add('pnomFr', TextType::class)
          ->add('pnomAr', TextType::class, ['required' => false])
          ->add('sexe', ChoiceType::class, array(
            'choices' => array(
              'Selectionner le genre' => '',
              'Homme'                 => 1,
              'Femme'                 => 2
            )
          ))
          // ->add('sexeAr')
          ->add('refExtrait', TextType::class)
          ->add('profession', TextType::class, ['required' => false])
          ->add('grpSanguin', ChoiceType::class, array(
            'required' => false,
            'choices'  => array(
              'Le groupe sanguin' => '',
              'Groupe A'          => 'A',
              'Groupe B'          => 'B',
              'Groupe AB'         => 'AB',
              'Groupe O'          => 'O'
            )
          ))
          // ->add('dateNaissance', BirthdayType::class, ['required' => true])
          ->add('lieuNaissance', TextType::class, ['required' => true])
          ->add('lieuNaissanceAr', TextType::class, ['required' => true])
          ->add('nationalite', TextType::class, ['required' => true])
          ->add('nationaliteAr', TextType::class, ['required' => true])
          ->add('commune', TextType::class, ['required' => true])
          ->add('residence', TextType::class, ['required' => true])
          ->add('contact', TextType::class, ['required' => false])
          ->add('etsOrigine', TextType::class, ['required' => false])
          ->add('etatSante', TextType::class, ['required' => false])
          ->add('photo', FileType::class, [
              'label' => 'Sélectionner une image',

              // unmapped means that this field is not associated to any entity property
              'mapped' => true,

              // make it optional so you don't have to re-upload the PDF file
              // everytime you edit the Product details
              'required' => false,
          ])
          ->add('nomPere', TextType::class)
          ->add('contactPere', TextType::class, ['required' => false])
          ->add('professionPere', TextType::class, ['required' => false])
          ->add('situationPere', ChoiceType::class, [
            'placeholder' => 'Situation du père',
            'choices' => [
              'Vivant' => 1,
              'Décédé' => 2
            ],
          ])
          ->add('nomMere', TextType::class)
          ->add('contactMere', TextType::class, ['required' => false])
          ->add('professionMere', TextType::class)
          ->add('situationMere', ChoiceType::class, array(
            'placeholder' => 'Situation de la mère',
            'choices' => [
                'Vivante' => 1,
                'Décédée' => 2
              ]
          ))
          ->add('nomTuteur', TextType::class, ['required' => false])
          ->add('contactTuteur', TextType::class, ['required' => false])
          ->add('professionTuteur', TextType::class, ['required' => false]);

        //Ecoute de l'évènement
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $eleve = $event->getData();
            $form = $event->getForm();

            if (!$eleve || null === $eleve->getId()) {
              $form->add('save', SubmitType::class, array('label' => 'Préinscrire l\'élève'));
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
