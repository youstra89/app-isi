<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Interner;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class InternerRepository extends \Doctrine\ORM\EntityRepository
{
  public function elevesInternesRenvoyes(int $as, int $annexeId)
  {
    $internes = $this->createQueryBuilder('i');
    $internes->join('i.eleve', 'e')
             ->addSelect('e')
             ->join('i.annee', 'an')
             ->addSelect('an')
             ->where('an.id = :as AND i.renvoye = 1 AND e.annexe = :annexeId');

    $internes->setParameter('as', $as)->setParameter('annexeId', $annexeId);

    return $internes->getQuery()
                    ->getResult();
  }

  public function litseDesInternes(int $as, int $annexeId)
  {
    $internes = $this->createQueryBuilder('i');
    $internes->join('i.eleve', 'e')
             ->join('e.frequenter', 'f')
             ->addSelect('e')
             ->join('i.annee', 'an')
             ->addSelect('an')
             ->where('an.id = :as AND e.renvoye = 0 AND e.annexe = :annexeId');

    $internes->setParameter('as', $as)->setParameter('annexeId', $annexeId);

    return $internes->getQuery()
                    ->getResult();

  }
}
