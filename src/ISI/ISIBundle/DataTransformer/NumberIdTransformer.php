<?php

namespace ISIBundle\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;

class NumberIdTransformer implements DataTransformerInterface {

    /**
     * EntityManager pour récupérer nos entités
     */
    private $manager;

    /**
     * Le nom de la class que l'on passe dans le constructor
     */
    private $note;

    public function __construct(ObjectManager $manager, $note) {
        $this->manager = $manager;
        $this->note = $note;
    }

    /**
     * Transforme l'entité passé à la méthode en retournant son ID
     * Return String/Integer
     */
    public function transform($entity) {
        if (null === $entity) {
            return '';
        }
        return $entity->getId();
    }

    /**
     * On passe l'ID pour retourner l'entité
     * Return Entity
     */
    public function reverseTransform($value) {
        if (!$value) {
            return;
        }
        // Recherche de l'entité corespondante
        $entity = $this->manager
                ->getRepository($this->note)
                ->find($value);
        return $entity;
    }

}