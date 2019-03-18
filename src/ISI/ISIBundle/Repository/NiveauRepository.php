<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Niveau;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class NiveauRepository extends \Doctrine\ORM\EntityRepository
{
  public function niveauxDuGroupe($regime)
  {
    // $regime = 'A' ? 'AC' : 'CF';
    $query = $this->_em->createQuery(
      'SELECT n FROM ISIBundle:Niveau n
      JOIN n.groupeFormation grp
      WHERE grp.referenceGrp = :regime');
    $query->setParameter('regime', $regime);

    return $query->getResult();
  }

  public function niveauDeClasse($regime)
  {
    // $regime = 'A' ? 'AC' : 'CF';
    $query = $this->_em->createQuery(
      'SELECT n FROM ISIBundle:Niveau n
      JOIN n.groupeFormation grp
      WHERE grp.referenceGrp = :regime');
    $query->setParameter('regime', $regime);
    return $query->getResult();
  }

  public function dernierNiveauDuRegime($regime)
  {
    $qb = $this->createQueryBuilder('n');

    $grp = ($regime == 'A') ? 1 : 2 ;
    $offset = 0;
    $limit  = 1;
    $qb->select('n')
       ->where('n.groupeFormation = :regime')
       ->orderBy('n.succession', 'DESC')
       ->setParameter('regime', $grp)
       ->setFirstResult( $offset )
       ->setMaxResults( $limit )
    ;
    return $qb
      ->getQuery()
      ->getOneOrNullResult()
      // ->getResult()
    ;
  }

}
