<?php

namespace ISI\ORGBundle\Repository;

/**
 * RegionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VendrediRepository extends \Doctrine\ORM\EntityRepository
{
  public function disponibiliteImam($id, $date)
  {
      $qb = $this->createQueryBuilder('v');
      $qb->where('v.imam = :id AND v.date LIKE :date')
         ->setParameter('id', $id)
         ->setParameter('date', $date)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }

  public function disponibiliteMosquee($id, $date)
  {
      $qb = $this->createQueryBuilder('v');
      $qb->where('v.mosquee = :id AND v.date LIKE :date')
         ->setParameter('id', $id)
         ->setParameter('date', $date)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
}
