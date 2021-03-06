<?php

namespace ISI\ISIBundle\Repository;

/**
 * TacheRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TacheRepository extends \Doctrine\ORM\EntityRepository
{
    public function appelsDuJours($date, $regime)
    {
        $date = $date->format('Y-m-d');
        return  $this
                    ->createQueryBuilder('t')
                    ->select('t')
                    ->join('t.cours', 'crs')
                    ->addSelect('crs')
                    ->join('crs.classe', 'c')
                    ->addSelect('c')
                    ->join('c.niveau', 'n')
                    ->addSelect('n')
                    ->join('n.groupeFormation', 'grpF')
                    ->addSelect('grpF')
                    ->where('grpF.reference = :regime AND t.createdAt LIKE :date')
                    ->setParameter('regime', $regime)
                    ->setParameter('date', $date.'%')
                    ->getQuery()
                    ->getResult()
        ;
    }


    public function appelsCoranDuJours($date)
    {
        $date = $date->format('Y-m-d');
        return $this
                    ->createQueryBuilder('t')
                    ->select('t')
                    ->join('t.cours', 'c')
                    ->addSelect('c')
                    ->join('c.halaqa', 'h')
                    ->addSelect('h')
                    ->where('t.createdAt LIKE :date AND c.halaqa IS NOT NULL')
                    ->setParameter('date', $date.'%')
                    ->getQuery()
                    ->getResult()
        ;
    }
}
