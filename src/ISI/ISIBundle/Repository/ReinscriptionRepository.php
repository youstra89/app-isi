<?php

namespace ISI\ISIBundle\Repository;

/**
 * BatimentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReinscriptionRepository extends \Doctrine\ORM\EntityRepository
{
    public function elevesReinscritsClasse(int $classeId, int $anneeId)
    {
        $renvoi = false;
        $qb = $this->createQueryBuilder('r');
        $qb->select('r')
           ->join('r.eleve', 'e')
           ->addSelect('e')
           ->join('e.frequenter', 'f')
           ->addSelect('f')
           ->join('f.classe', 'c')
           ->addSelect('c')
           ->where('e.renvoye = :renvoi AND c.id = :classeId AND r.annee = :anneeId') 
           ->setParameter('renvoi', $renvoi)
           ->setParameter('classeId', $classeId)
           ->setParameter('anneeId', $anneeId)
         ;

        return $qb->getQuery()
                  ->getResult();
    }
}