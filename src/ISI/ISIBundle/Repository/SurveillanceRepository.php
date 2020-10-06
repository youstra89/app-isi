<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Surveillance;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class SurveillanceRepository extends \Doctrine\ORM\EntityRepository
{
    public function surveillance_groupe_matiere(int $anneeContratId, int $groupeId, int $matiereId)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->join('s.anneeContrat', 'a')
           ->join('s.programmegroupecomposition', 'p')
           ->join('p.programmeexamenniveau', 'pen')
           ->join('pen.matiere', 'm')
           ->join('p.groupeComposition', 'g')
           ->where('a.id = :anneeContratId AND m.id = :matiereId AND g.id = :groupeId AND p.isDeleted = false')
           ->setParameter('groupeId', $groupeId)
           ->setParameter('matiereId', $matiereId)
           ->setParameter('anneeContratId', $anneeContratId)
        ;

        return $qb->getQuery()->getResult();
    }
    
    public function lesCoursDeLEnseignant(int $examenId, string $regime, int $enseignantId)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->join('s.anneeContrat', 'a')
           ->join('a.contrat', 'c')
           ->join('c.enseignant', 'ens')
           ->join('s.programmegroupecomposition', 'p')
           ->join('p.programmeexamenniveau', 'pen')
           ->join('pen.niveau', 'n')
           ->join('n.groupeFormation', 'grpF')
           ->join('pen.examen', 'exa')
           ->where('ens.id = :enseignantId AND grpF.reference = :regime AND exa.id = :examenId AND p.isDeleted = false')
           ->setParameter('examenId', $examenId)
           ->setParameter('regime', $regime)
           ->setParameter('enseignantId', $enseignantId)
        ;

        return $qb->getQuery()->getResult();
    }
}
