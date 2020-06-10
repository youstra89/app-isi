<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Interner;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class InternerRepository extends \Doctrine\ORM\EntityRepository
{
  public function elevesInternesRenvoyes($as)
  {
    $internes = $this->createQueryBuilder('i');
    $internes->join('i.eleve', 'e')
             ->addSelect('e')
             ->join('i.annee', 'an')
             ->addSelect('an')
             ->where('an.id = :as AND i.renvoye = 1');

    $internes->setParameter('as', $as);

    return $internes->getQuery()
                    ->getResult();
  }

  public function litseDesInternes($as)
  {
    $internes = $this->createQueryBuilder('i');
    $internes->join('i.eleve', 'e')
             ->join('e.frequenter', 'f')
             ->addSelect('e')
             ->join('i.annee', 'an')
             ->addSelect('an')
             ->where('an.id = :as AND e.renvoye = FALSE');

    $internes->setParameter('as', $as);

    return $internes->getQuery()
                    ->getResult();

  }
}
