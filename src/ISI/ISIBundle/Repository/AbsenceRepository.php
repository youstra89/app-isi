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
       ->where('e.id IN (:ids) AND a.annee = :an')
       //->addSelect('e')
       ->setParameter('ids', $ids)
       ->setParameter('an', $as)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }

  public function absenceDuMoisDesElevesDeLaClasse($classeId, $moisId, $as)
  {
    $qb = $this->createQueryBuilder('a');

    $qb->select('a')
       ->join('a.eleve', 'e')
       ->addSelect('e')
       ->join('e.frequenter', 'f')
       ->addSelect('f')
       ->join('f.classe', 'c')
       ->addSelect('c')
       ->join('a.mois', 'm')
       ->addSelect('m')
       ->where('c.id = :classeId AND a.mois = :moisId AND a.annee = :an')
       //->addSelect('e')
       ->setParameter('classeId', $classeId)
       ->setParameter('moisId', $moisId)
       ->setParameter('an', $as)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
}
