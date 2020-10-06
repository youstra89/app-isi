<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\ProgrammeExamenNiveau;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProgrammeExamenNiveauRepository extends \Doctrine\ORM\EntityRepository
{
    
    public function programme_examen_niveau(int $niveauId, int $examenId, int $annexeId)
    {
        $qb = $this->createQueryBuilder('p')
           ->join('p.niveau', 'n')
           ->join('p.annexe', 'a')
           ->join('p.examen', 'e')
           ->where('n.id = :niveauId AND e.id = :examenId AND a.id = :annexeId AND p.isDeleted = false')
           ->setParameter('niveauId', $niveauId)
           ->setParameter('examenId', $examenId)
           ->setParameter('annexeId', $annexeId)
        ;

        $result = $qb->getQuery()->getResult();

        return $result;
    }


    public function programme_examen_d_un_enseignant(int $enseignantId, int $examenId, int $annexeId)
    {
        $qb = $this->createQueryBuilder('p')
           ->join('p.programmegroupecomposition', 'pgc')
           ->join('pgc.surveillances', 's')
           ->join('p.annexe', 'a')
           ->join('p.examen', 'e')
           ->join('s.anneeContrat', 'ac')
           ->join('ac.contrat', 'c')
           ->join('c.enseignant', 'ens')
           ->where('ens.id = :enseignantId AND e.id = :examenId AND a.id = :annexeId AND p.isDeleted = false')
           ->setParameter('enseignantId', $enseignantId)
           ->setParameter('examenId', $examenId)
           ->setParameter('annexeId', $annexeId)
        ;

        $result = $qb->getQuery()->getResult();

        return $result;
    }
    
    
    public function programme_d_examen_du_regime(int $examenId, string $regime, int $annexeId)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->join('p.niveau', 'n')
           ->join('n.groupeFormation', 'grpF')
           ->join('p.annexe', 'a')
           ->join('p.examen', 'e')
           ->where('grpF.reference = :regime AND a.id = :annexeId AND e.id = :examenId AND p.isDeleted = false')
           ->setParameter('regime', $regime)
           ->setParameter('annexeId', $annexeId)
           ->setParameter('examenId', $examenId)
        ;

        
        return $qb->getQuery()->getResult();
    }
}
