<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\ProgrammeGroupeComposition;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProgrammeGroupeCompositionRepository extends \Doctrine\ORM\EntityRepository
{
    
    public function programme_d_examen_de_la_classe(int $classeId, int $examenId)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->join('p.groupeComposition', 'gc')
           ->join('gc.classe', 'c')
           ->join('gc.examen', 'e')
           ->where('c.id = :classeId AND e.id = :examenId AND p.isDeleted = false')
           ->setParameter('classeId', $classeId)
           ->setParameter('examenId', $examenId)
        ;

        $result = $qb->getQuery()->getResult();

        return $result;
    }
    
    public function programme_de_l_examen_du_regime(int $examenId, string $regime)
    {
        return $this->createQueryBuilder('p')
           ->join('p.groupeComposition', 'gc')
           ->join('gc.classe', 'c')
           ->join('c.niveau', 'n')
           ->join('n.groupeFormation', 'grpF')
           ->join('gc.examen', 'e')
           ->where('grpF.reference = :regime AND e.id = :examenId AND p.isDeleted = false')
           ->setParameter('regime', $regime)
           ->setParameter('examenId', $examenId)
           ->getQuery()
           ->getResult()
        ;
    }

    public function programme_de_composition_d_un_groupe_dans_une_matiere(int $groupeId, int $examenId, int $matiereId)
    {
        return $this->createQueryBuilder('p')
           ->join('p.groupeComposition', 'gc')
           ->join('gc.examen', 'e')
           ->join('p.programmeexamenniveau', 'pen')
           ->join('pen.matiere', 'm')
           ->where('gc.id = :groupeId AND m.id = :matiereId AND e.id = :examenId AND p.isDeleted = false')
           ->setParameter('groupeId', $groupeId)
           ->setParameter('matiereId', $matiereId)
           ->setParameter('examenId', $examenId)
           ->getQuery()
           ->getResult()
        ;
    }
}
