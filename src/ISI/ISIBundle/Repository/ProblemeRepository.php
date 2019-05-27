<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Probleme;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProblemeRepository extends \Doctrine\ORM\EntityRepository
{
  public function problemesDUnEleveLorsDUneAnnee($eleve, $as)
  {
    $problemes = $this->createQueryBuilder('p');
    $problemes->join('p.commettre', 'c')
              ->addSelect('c')
              ->join('c.annee', 'an')
              ->addSelect('an')
              ->where('c.eleve = :eleve AND c.annee = :as');

    $problemes->setParameter('eleve', $eleve);
    $problemes->setParameter('as', $as);

    return $problemes->getQuery()
                     ->getResult();
  }
}
