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
      ->add('statu',    CheckboxType::class, ['required' => false])
      ->add('matiere',         EntityType::class,
          array(
            'class'         => 'ISIBundle:Matiere',
            'placeholder'   => 'Sélectionner une matière',
            'query_builder' => function (MatiereRepository $matiere) use ($as, $niveauId, $em)
                              {
                                $subquery = $matiere->lesMatieresDuNiveau($as, $niveauId);
                                $qb = $matiere->createQueryBuilder('m');
                                $qb->where($qb->expr()->notIn('m.id', '$subquery'));
                              },
            'choice_label'  => 'libelle',
            'multiple'      => false
        ))
        ->add('livre', EntityType::class, [
            'class'         => 'ISIBundle:Livre',
            'placeholder'   => 'Sélectionner un livre',
            'choice_label'  => 'nom',
            'multiple'      => FALSE
        ])
        ->add('save',          SubmitType::class, array('label' => 'Ajouter la metière'))
      ;
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

/*SELECT
  *
FROM matiere m
WHERE m.matiere_id NOT IN (
    SELECT m2.matiere_id
      FROM matiere m2
          JOIN enseignement e
              ON e.matiere_id = m2.matiere_id
          JOIN classe c
              ON e.classe_id = c.classe_id
          JOIN anneescolaire a
              ON e.annee_scolaire_id = a.annee_scolaire_id
      WHERE a.annee_scolaire_id = 1
      AND c.classe_id = 1
    )*/
