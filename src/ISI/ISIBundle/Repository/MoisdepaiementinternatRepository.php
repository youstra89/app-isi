<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Moisdepaiementinternat;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class MoisdepaiementinternatRepository extends \Doctrine\ORM\EntityRepository
{
  // On va sélectionner le dernier mois de paiement de l'année
  public function dernierMoisDePaiementDeLAnnee($as)
  {
    $offset = 0;
    $limit = 1;
    $qb = $this->createQueryBuilder('mdp');
    $qb->join('mdp.mois', 'm')
       ->addSelect('m')
       ->join('mdp.anneeScolaire', 'an')
       ->addSelect('an')
       ->where('an.anneeScolaireId = :as')
       ->orderBy('mdp.id', 'DESC')
       ->setFirstResult( $offset )
       ->setMaxResults( $limit );

    $qb->setParameter('as', $as);

    return $qb->getQuery()
              ->getResult();
  }
}
