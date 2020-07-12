<?php

namespace ISI\ISIBundle\Repository;


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
