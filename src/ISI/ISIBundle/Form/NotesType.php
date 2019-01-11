<?php

namespace ISI\ISIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\CallbackTransformer;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use ISI\ISIBundle\DataTransformer\NumberIdTransformer;

use ISI\ISIBundle\Form\Notetype;

class NotesType extends AbstractType
{
    // // Ajout du constructeur pour dataTransformer
    // protected $em;
 
    // public function __construct(EntityManager $em) {
    //     $this->em = $em;
    // }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notes', CollectionType::class, [
                'entry_type'   => NoteType::class,
                'entry_options'  => array(
                    'attr'      => array('class' => 'notes')
                ),
            ])
            ->add('submit', SubmitType::class, array('label' => 'Enregistrer les notes'))
        ;

        // My transformer
        $builder->get('notes')
            ->addModelTransformer(new CallbackTransformer(
                function ($notesAsArray) {
                    // transform the array to a string
                    return implode(', ', $notesAsArray);
                },
                function ($tagsAsString) {
                    // transform the string back to an array
                    return explode(', ', $tagsAsString);
                }
            ))
        ;

        // Ajout du data transformer
        // $builder->get('notes')
        //         ->addModelTransformer(new NumberIdTransformer($this->em, 'ISIBundle:Note'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            // 'data_class' => null
            // 'data_class' => 'ISI\ISIBundle\Entity\Note'
            'data_class' => Note::class
        ));
    }

    // // DataTranformer
    public function getName() {
        return 'form_note';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'isibundle_note';
    }


}
