<?php

namespace ISI\ISIBundle\Repository;

/**
 * PresenceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PresenceRepository extends \Doctrine\ORM\EntityRepository
{
    public function appels_des_eleves_de_la_classe($classeId, $date)
    {
        return $this->createQueryBuilder('p')
            ->join('p.eleve', 'e')
            ->join('e.frequenter', 'f')
            ->join('f.classe', 'c')
            ->where('c.id = :classeId AND e.renvoye = 0 AND p.date LIKE :date')
            ->orderBy('e.nomFr', 'ASC')
            ->addOrderBy('e.pnomFr', 'ASC')
            ->setParameter('date', $date.'%')
            ->setParameter('classeId', $classeId)
            ->getQuery()
            ->getResult();
    }

    public function appels_des_eleves_de_la_halaqa($halaqaId, $date)
    {
        return $this->createQueryBuilder('p')
            ->join('p.eleve', 'e')
            ->join('e.memoriser', 'm')
            ->join('m.halaqa', 'h')
            ->where('h.id = :halaqaId AND e.renvoye = 0 AND p.date LIKE :date')
            ->orderBy('e.nomFr', 'ASC')
            ->addOrderBy('e.pnomFr', 'ASC')
            ->setParameter('date', $date.'%')
            ->setParameter('halaqaId', $halaqaId)
            ->getQuery()
            ->getResult();
    }
    
    public function presences_d_une_periode($classeId, $debut, $fin)
    {
        return $this->createQueryBuilder('p')
            ->join('p.eleve', 'e')
            ->join('e.frequenter', 'f')
            ->join('f.classe', 'c')
            ->where('c.id = :classeId AND e.renvoye = 0 AND p.date >= :debut AND p.date <= :fin')
            ->orderBy('e.nomFr', 'ASC')
            ->addOrderBy('e.pnomFr', 'ASC')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('classeId', $classeId)
            ->getQuery()
            ->getResult();
    }
}
