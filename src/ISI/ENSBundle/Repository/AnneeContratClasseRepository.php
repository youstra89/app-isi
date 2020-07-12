<?php

namespace ISI\ENSBundle\Repository;

/**
 * AnneeContratClasseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnneeContratClasseRepository extends \Doctrine\ORM\EntityRepository
{
    public function lesCoursDeLEnseignant(int $as, int $enseignantId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb ->join('c.anneeContrat', 'ac')
            ->join('ac.contrat', 'cont')
            ->join('cont.enseignant', 'e')
            ->join('ac.annee', 'an')
            ->where('an.id = :as AND e.id = :enseignantId AND e.enseignant = true')
            ->setParameter('as', $as)
            ->setParameter('enseignantId', $enseignantId)
            ->setParameter('as', $as);

        return $qb->getQuery()
                ->getResult();
    }
    
    public function tousLesCoursDeLAnnee(int $anneeId, int $annexeId, string $regime)
    {
        $qb = $this->createQueryBuilder('c');
        $qb ->join('c.anneeContrat', 'ac')
            ->join('ac.contrat', 'cont')
            ->join('cont.enseignant', 'e')
            ->join('ac.annee', 'an')
            ->join('c.classe', 'cl')
            ->join('cl.niveau', 'n')
            ->join('n.groupeFormation', 'grpF')
            ->where('an.id = :anneeId AND e.annexe = :annexeId AND e.enseignant = true AND grpF.reference = :regime')
            ->setParameter('annexeId', $annexeId)
            ->setParameter('anneeId', $anneeId)
            ->setParameter('regime', $regime);

        return $qb->getQuery()
                ->getResult();
    }
}
