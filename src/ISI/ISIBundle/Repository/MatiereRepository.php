<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Matiere;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class MatiereRepository extends \Doctrine\ORM\EntityRepository
{
  // Cette fonction me permet de récupérer la liste des matières d'un niveau donné
  public function lesMatieresDuNiveau($as, $niveauId)
  {
    $qb = $this->createQueryBuilder('m');
    $qb->join('m.enseignements', 'ens')
       ->addSelect('ens')
       ->join('ens.niveau', 'n')
       ->addSelect('n')
       ->join('ens.annee', 'an')
       ->addSelect('an')
       ->where('an.id = :annee AND n.id = :niveau')
       ->orderBy('m.id', 'ASC');
    $qb->setParameters(['annee' => $as, 'niveau' => $niveauId]);
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
}
