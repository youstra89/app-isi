<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Moyenne;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class MoyenneRepository extends \Doctrine\ORM\EntityRepository
{
  // La fonction me permet de sélectionner les moyennes des élèves d'une classe donnée.
  // La variable $elevesIds contient les ids des élèves de la classe en cours de traitement
  public function lesMoyennesDesElevesDeLaClasse($examenId, $elevesIds)
  {
    $qb = $this->createQueryBuilder('m');
    $qb->where('m.examen = :examenId')
       ->andWhere($qb->expr()->in('m.eleve', ':ids'))
       ->setParameter('ids', $elevesIds)
       ->setParameter('examenId', $examenId)
    ;

    $moyennes = $qb->getQuery()->getResult();
    return $moyennes;
  }

  // On ve sélectionner toutes les moyennes des élèves d'un regime pour faire les statistiques
  public function moyenneElevesRegime($as, $examenId)
  {
    $qb = $this->createQueryBuilder('m')
               ->join('m.eleve', 'e')
               ->addSelect('e')
               ->join('m.examen', 'ex')
               ->addSelect('ex') 
               ->join('ex.anneeScolaire', 'an')
               ->addSelect('an')
               ->where('an.anneeScolaireId = :asec')
               ->andWhere('ex.examenId = :examenId')
               ->andWhere('e.matricule NOT LIKE :mat')
               ->setParameter('asec', $as) 
               ->setParameter('examenId', $examenId) 
               ->setParameter('mat', '%A___');

    $moyennes = $qb->getQuery()->getResult();
    return $moyennes;
  }
}
