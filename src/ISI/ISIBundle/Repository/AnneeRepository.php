<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Annee;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class AnneeRepository extends \Doctrine\ORM\EntityRepository
{
  public function anneeEnCours()
  {
    $qb = $this->createQueryBuilder('asec');

    $offset = 0;
    $limit = 1;
    $qb->select('asec')
       ->orderBy('asec.id', 'DESC')
       ->setFirstResult( $offset )
       ->setMaxResults( $limit )
    ;
    return $qb
      ->getQuery()
      ->getOneOrNullResult()
      // ->getResult()
    ;
  }

  public function toutesLesAnnees()
  {
    $qb = $this->createQueryBuilder('asec');

    $qb->select('asec')
       ->orderBy('asec.id', 'DESC')
    ;
    return $qb
      ->getQuery()
      ->getResult()
      // ->getResult()
    ;
  }

  public function anneeMatricule($as)
  {
    $anneeMatricule = $this->_em->createQuery('SELECT
      SUBSTRING(a.libelle, 3, LENGTH(a.libelle) - 7) AS annee
      FROM ISIBundle:Annee a WHERE a.id = :idAnnee');
    $anneeMatricule->setParameter('idAnnee', $as)->getSingleScalarResult();
    return $anneeMatricule;
  }
}
