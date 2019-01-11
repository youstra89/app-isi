<?php

namespace UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom field
        $builder->add('nom');
        // $builder->add('pnomUser', TextType::class);
    }

    public function getNom()
    {
        return 'user_registration';
    }

    // public function getPnomUser()
    // {
    //     return 'user_registration';
    // }
}