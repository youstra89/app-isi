<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Examen;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ExamenRepository extends \Doctrine\ORM\EntityRepository
{
  
  public function dernierExamen($as)
  {
    $offset = 0;
    $limit = 1;
    $examen  = $this->createQueryBuilder('e')
                    ->join('e.annee', 'a')
                    ->where('a.id = :as')
                    ->orderBy('e.id', 'DESC')
                    ->setFirstResult( $offset )
                    ->setMaxResults( $limit );

    $examen->setParameter('as', $as);

    return $examen->getQuery()
                  ->getOneOrNullResult();
  }

  public function lesExamensDeLAnnee($as)
  {
    $examens = $this->createQueryBuilder('e')
                    ->where('e.annee = :as')
                    ->orderBy('e.id', 'ASC');

    $examens->setParameter('as', $as);
    return $examens->getQuery()
                   ->getResult();
  }
}
