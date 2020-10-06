<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\GroupeComposition;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class GroupeCompositionRepository extends \Doctrine\ORM\EntityRepository
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

  public function groupeDeCompositionDeLAnnexePourUnExamen($examenId, $annexeId)
  {
    $groupes = $this->createQueryBuilder('gc');
    $groupes->join('gc.classe', 'c')
             ->join('gc.examen', 'e')
             ->join('c.niveau', 'n')
             ->join('c.annexe', 'a')
             ->orderBy('n.id', 'ASC')
             ->orderBy('c.libelleFr', 'ASC')
             ->where('e.id = :examenId AND a.id = :annexeId');

    $groupes->setParameter('examenId', $examenId)->setParameter('annexeId', $annexeId);

    return $groupes->getQuery()
                    ->getResult();
  }
}
