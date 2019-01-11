<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Anneescolaire;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class AnneescolaireRepository extends \Doctrine\ORM\EntityRepository
{
  public function anneeEnCours()
  {
    $qb = $this->createQueryBuilder('asec');

    $offset = 0;
    $limit = 1;
    $qb->select('asec')
       ->orderBy('asec.anneeScolaireId', 'DESC')
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
       ->orderBy('asec.anneeScolaireId', 'DESC')
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
      SUBSTRING(a.libelleAnneeScolaire, 3, LENGTH(a.libelleAnneeScolaire) - 7) AS annee
      FROM ISIBundle:Anneescolaire a WHERE a.anneeScolaireId = :idAnnee');
    $anneeMatricule->setParameter('idAnnee', $as)->getSingleScalarResult();
    return $anneeMatricule;
  }
}
