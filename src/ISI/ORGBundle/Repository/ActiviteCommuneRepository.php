<?php

namespace ISI\ORGBundle\Repository;

/**
 * ActiviteCommuneRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ActiviteCommuneRepository extends \Doctrine\ORM\EntityRepository
{

  public function activitesDeTournee($id)
  {
      $qb = $this->createQueryBuilder('ac');

      $qb->select('ac')
         ->join('ac.activite', 'a')
         ->addSelect('a')
         ->join('a.communes', 'c')
         ->addSelect('c')
         ->join('c.tournees', 't')
         ->addSelect('t')
         ->where('t.id = :id')
         ->setParameter(':id', $id)
      ;
      return $qb
          ->getQuery()
          ->getResult()
      ;
  }

}
