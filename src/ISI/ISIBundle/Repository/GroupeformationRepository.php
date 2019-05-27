<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Groupeformation;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class GroupeformationRepository extends \Doctrine\ORM\EntityRepository
{
  public function groupeDuRegime($regime)
  {
    $query = $this->_em->createQuery(
      'SELECT grp FROM ISIBundle:Groupeformation grp
      JOIN grp.reference r
      WHERE r.regimeRef = :regime');
    $query->setParameter('regime', $regime);

    return $query->getResult();
  }

}
