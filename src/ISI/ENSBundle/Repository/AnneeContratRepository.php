<?php

namespace ISI\ENSBundle\Repository;

/**
 * AnneeContratRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnneeContratRepository extends \Doctrine\ORM\EntityRepository
{
    public function fonctionDeLAnnee($anneeId)
    {
        $qb = $this->createQueryBuilder('a');
        $offset = 0;
        $limit = 1;
        $qb->select('a')
           ->join('a.annee', 'an')
           ->addSelect('an')
           ->join('a.contrat', 'c')
           ->addSelect('c')
           ->join('c.enseignant', 'e')
           ->addSelect('e')
           ->where('an.id = :anneeId AND c.fini = :bool AND e.enseignant = true')
           ->orderBy('e.nomFr', 'ASC')
           ->addOrderBy('e.pnomFr', 'ASC')
           ->setParameter('anneeId', $anneeId)
           ->setParameter('bool', false)
        ;
        return $qb
            ->getQuery()
            ->getResult()
            // ->getResult()
        ;
    }
}
