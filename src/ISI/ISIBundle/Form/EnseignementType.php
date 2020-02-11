<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ISI\ISIBundle\Repository\NiveauRepository;
use ISI\ISIBundle\Repository\MatiereRepository;


class EnseignementType extends AbstractType
{

  /**
   * {@inheritdoc}
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
      $em       = $options["entity_manager"];
      $as       = $options["as"];
      $niveau   = $options["niveau"];
      $niveauId = $options["niveauId"];
      $builder
        // ->add('niveau',        HiddenType::class, ['data' => $niveau])
        // ->add('anneeScolaire', HiddenType::class, ['data' => $as])
        ->add('coefficient',     NumberType::class)
        ->add('nombre_heure_cours',     NumberType::class)
        ->add('statu',    CheckboxType::class, ['required' => false])
        ->add('livre', EntityType::class, [
            'class'         => 'ISIBundle:Livre',
            'placeholder'   => 'Sélectionner un livre',
            'choice_label'  => 'nom',
            'multiple'      => FALSE
        ])
      ;

      //Ecoute de l'évènement
      $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($as, $niveauId, $em)
      {
          $enseignement = $event->getData();
          $form = $event->getForm();

          if (!$enseignement || null === $enseignement->getId()) {
            $form
              ->add('save', SubmitType::class, array('label' => 'Enregistrer'))
              ->add('matiere',         EntityType::class,
                [
                  'class'         => 'ISIBundle:Matiere',
                  'placeholder'   => 'Sélectionner une matière',
                  'query_builder' => function (MatiereRepository $matiere) use ($as, $niveauId, $em)
                                    {
                                      $subquery = $matiere->lesMatieresDuNiveau($as, $niveauId);
                                      $qb = $matiere->createQueryBuilder('m');
                                      $qb->where($qb->expr()->notIn('m.id', '$subquery'));
                                    },
                  'choice_label'  => 'libelle',
                  'multiple'      => false,
                  // 'attr'          => 
                  //   ['readonly'      => true,]
                ]
            );
          }
          else{
            $form->add('update', SubmitType::class, array('label' => 'Modifier'));
          }
      });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ISI\ISIBundle\Entity\Enseignement'
        ));

        $resolver->setRequired([
          'as',
          'niveauId',
          'niveau',
          'entity_manager',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isibundle_enseignement';
    }

}