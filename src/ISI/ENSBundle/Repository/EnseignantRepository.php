<?php

namespace ISI\ENSBundle\Repository;

use ENSBundle\Repository;
use ENSBundle\Entity\Examen;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class EnseignantRepository extends \Doctrine\ORM\EntityRepository
{
  // Récupération du dernier matricule enregistré
  public function dernierMatricule()
  {
	$dernierMatricule = $this->_em->createQuery(
      'SELECT
      DISTINCT(SUBSTRING(SUBSTRING(e.matricule, 5), 1, LENGTH(SUBSTRING(e.matricule, 5)) - 4)) AS mat
      FROM ENSBundle:Enseignant e');

      return $dernierMatricule->getResult();
      //return $dernierMatricule->getScalarResult();
  }
}
