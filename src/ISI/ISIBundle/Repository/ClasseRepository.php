<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Classe;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ClasseRepository extends \Doctrine\ORM\EntityRepository
{
  public function classesDeLAnnee($as, $regime)
  {
    $qb = $this->createQueryBuilder('c');
    $qb->select('c')
       ->join('c.niveau', 'n')
       ->join('n.groupeFormation', 'grp')
      //  ->join('grp.referenceRegime', 'r')
       ->join('c.anneeScolaire', 'a')
       ->where('a.anneeScolaireId = :as')
       ->andWhere('grp.referenceGrp = :regime')
       ->setParameter('as', $as)
       ->setParameter('regime', $regime)
    ;

    return $qb
       ->getQuery()
       ->getResult();
  }

  // Je récupère les classes d'un niveau avec cette function pour une année scolaire donnée
  public function lesClassesDuNiveau($as, $niveau)
  {
    $qb = $this->createQueryBuilder('c')
              ->join('c.anneeScolaire', 'a')
              ->where('c.niveau = :niveau')
              ->andWhere('a.anneeScolaireId = :annee')
              ->setParameter('niveau', $niveau)
              ->setParameter('annee', $as);

    return $qb->getQuery()
              ->getResult();
  }

  public function classeInscription()
  {
    $dql = $this->_em->createQuery(
                    "SELECT c.classeId, CONCAT(n.libelleFr, ' ', c.libelleClasseFr) nomClasse
                    FROM ISIBundle:Classe c
                    JOIN c.niveau n"
    );

    // $dql->setParameter("as", $as);
    return $dql;
  }

  public function myFindGrpFormationClasse($classeId)
  {
    $qb = $this->createQueryBuilder('c');
    $qb->select('grpF.referenceGrp')
       ->join('c.niveau', 'n')
       ->join('n.groupeFormation', 'grpF')
       ->where('c.classeId = :classe')
       ->setParameter('classe', $classeId);

    return $qb->getQuery()
              ->getResult();
  }

  // Pour connaitre toutes les classes d'un regime ou groupe de formation une année donnée
  public function classeGrpFormation($as, $regime)
  {
    $qb = $this->createQueryBuilder('c');
    $qb->join('c.niveau', 'n')
       ->join('n.groupeFormation', 'grpF')
       ->join('c.anneeScolaire', 'an')
       ->where('grpF.referenceGrp = :regime AND an.anneeScolaireId = :an')
       ->setParameter('regime', $regime)
       ->setParameter('an', $as)
    ;
    return $qb->getQuery()
              ->getResult();
  }

  // Sélection de toutes les classes existantes lors d'une année scolaire
  public function lesClasseDeLAnnee($as)
  {
    $qb = $this->createQueryBuilder('c');
    $qb->join('c.niveau', 'n')
       ->join('c.anneeScolaire', 'an')
       ->where('an.anneeScolaireId = :an')
       ->setParameter('an', $as)
    ;
    return $qb->getQuery()
              ->getResult();
  }

  // Cette fonction me permet d'afficher la liste de toutes les classe d'un même regime pour un examen donné
  public function listeDesClassesPourExamen($as, $regime, $examen)
  {
    $qb = $this->createQueryBuilder('c');
    $qb->join('c.etatFicheDeNote', 'fiche')
        ->join('c.niveau', 'n')
        ->join('n.groupeFormation', 'grpF')
        ->join('fiche.examen', 'exam')
        ->join('c.anneeScolaire', 'an')
        ->where('an.anneeScolaireId = :as AND exam.examenId = :examen AND grpF.referenceGrp = :regime')
        ->setParameter('as', $as)
        ->setParameter('examen', $examen)
        ->setParameter('regime', $regime);

    return $qb->getQuery()
            ->getResult();
  }
}
?>
