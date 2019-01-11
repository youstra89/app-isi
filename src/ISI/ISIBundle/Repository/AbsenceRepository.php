<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Absence;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class AbsenceRepository extends \Doctrine\ORM\EntityRepository
{
  public function absencesDesEleveDeLaClasse($as, $ids)
  {
    $qb = $this->createQueryBuilder('a');

    $qb->select('a')
       ->join('a.eleve', 'e')
       ->addSelect('e')
       ->where('e.eleveId IN (:ids) AND a.anneeScolaire = :an')
       //->addSelect('e')
       ->setParameter('ids', $ids)
       ->setParameter('an', $as)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
}
