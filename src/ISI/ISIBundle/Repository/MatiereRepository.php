<?php

namespace ISI\ISIBundle\Repository;



class MatiereRepository extends \Doctrine\ORM\EntityRepository
{
  // Cette fonction me permet de récupérer la liste des matières d'un niveau donné
  public function lesMatieresDuNiveau($as, $niveauId, $matiereEnfant = false)
  {
    $qb = $this->createQueryBuilder('m');
    $qb->join('m.enseignements', 'ens')
       ->addSelect('ens')
       ->join('ens.niveau', 'n')
       ->addSelect('n')
       ->join('ens.annee', 'an')
       ->addSelect('an')
       ->orderBy('m.id', 'ASC');
    if($matiereEnfant == false)
      $qb->where('an.id = :annee AND n.id = :niveau AND m.matiere_mere IS NULL');
    else
      $qb->where('an.id = :annee AND n.id = :niveau');
    $qb->setParameters(['annee' => $as, 'niveau' => $niveauId]);
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }

  public function lesMatieresDeCompositionDuNiveau($as, $niveauId, $matiereEnfant = false)
  {
    $qb = $this->createQueryBuilder('m');
    $qb->join('m.enseignements', 'ens')
       ->addSelect('ens')
       ->join('ens.niveau', 'n')
       ->addSelect('n')
       ->join('ens.annee', 'an')
       ->addSelect('an')
       ->orderBy('m.id', 'ASC');
    if($matiereEnfant == false)
      $qb->where('an.id = :annee AND n.id = :niveau AND ens.matiereExamen = true AND m.matiere_mere IS NULL');
    else
      $qb->where('an.id = :annee AND n.id = :niveau AND ens.matiereExamen = true');
    $qb->setParameters(['annee' => $as, 'niveau' => $niveauId]);
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }

  public function lesMatieresEnfantsDuNiveau($as, $niveauId)
  {
    $qb = $this->createQueryBuilder('m');
    $qb->join('m.enseignements', 'ens')
       ->addSelect('ens')
       ->join('ens.niveau', 'n')
       ->addSelect('n')
       ->join('ens.annee', 'an')
       ->addSelect('an')
       ->where('an.id = :annee AND n.id = :niveau AND m.matiere_mere IS NOT NULL')
       ->orderBy('m.id', 'ASC');
    $qb->setParameters(['annee' => $as, 'niveau' => $niveauId]);
    return $qb
      ->getQuery()
      ->getResult()
    ;
  }
}
