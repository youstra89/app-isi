<?php

namespace ISI\ENSBundle\Repository;

/**
 * ContratRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContratRepository extends \Doctrine\ORM\EntityRepository
{
    public function dernierContrat($enseignantId)
    {
        $qb = $this->createQueryBuilder('c');
        $offset = 0;
        $limit = 1;
        $qb->select('c')
           ->join('c.enseignant', 'e')
           ->where('e.id = :id AND c.fini = false')
           ->orderBy('c.id', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($limit)
           ->setParameter('id', $enseignantId)
        ;
        return $qb
            ->getQuery()
            ->getOneOrNullResult()
            // ->getResult()
        ;
    }

    public function contratsEnCours()
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c')
           ->join('c.enseignant', 'e')
           ->where('c.fini = false AND e.enseignant = true')
           ->orderBy('c.id', 'DESC')
        ;
        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function agentsContratsEnCours()
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c')
           ->join('c.enseignant', 'e')
           ->where('c.fini = false')
           ->orderBy('c.id', 'DESC')
        ;
        return $qb
            ->getQuery()
            ->getResult()
        ;
    }
}
