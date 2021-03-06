<?php

namespace ISI\ISIBundle\Repository;

/**
 * PermissionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PermissionRepository extends \Doctrine\ORM\EntityRepository
{
    public function listeDesPermissions(int $anneeId, int $annexeId)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->join('p.eleve', 'e')
           ->where('p.annee = :anneeId AND e.annexe = :annexeId')
           ->setParameter('anneeId', $anneeId)
           ->setParameter('annexeId', $annexeId)
        ;

        return $qb->getQuery()->getResult();
    }

    public function permission_eleve_periode($eleveId, $debut, $fin)
    {
        return $this->createQueryBuilder('p')
            ->join('p.eleve', 'e')
            ->where('e.id = :eleveId AND e.renvoye = 0 AND p.depart >= :debut AND p.retour <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('eleveId', $eleveId)
            ->getQuery()
            ->getResult();
    }
}
