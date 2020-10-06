<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\ParticipationExamen;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ParticipationExamenRepository extends \Doctrine\ORM\EntityRepository
{
  public function appels_des_eleves_du_groupe($groupeId, $examenId, $matiereId){
    $qb = $this->createQueryBuilder('p');

    $qb->select('p')
       ->join('p.eleve', 'e')
       ->join('p.examen', 'ex')
       ->join('p.matiere', 'm')
       ->join('e.elevegroupecomposition', 'egc')
       ->join('egc.groupeComposition', 'gc')
       ->where('gc.id = :groupeId AND m.id = :matiereId AND ex.id = :examenId')
       ->setParameter('groupeId', $groupeId)
       ->setParameter('examenId', $examenId)
       ->setParameter('matiereId', $matiereId)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
  
  public function appels_des_eleves_d_une_classe($classeId, $examenId){
    $qb = $this->createQueryBuilder('p');

    $qb->select('p')
       ->join('p.eleve', 'e')
       ->join('p.examen', 'ex')
       ->join('p.matiere', 'm')
       ->join('e.elevegroupecomposition', 'egc')
       ->join('egc.groupeComposition', 'gc')
       ->join('gc.classe', 'c')
       ->where('c.id = :classeId AND ex.id = :examenId')
       ->setParameter('classeId', $classeId)
       ->setParameter('examenId', $examenId)
    ;
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
}
