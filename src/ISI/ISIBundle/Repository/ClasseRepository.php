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
       ->join('c.annee', 'a')
       ->where('a.id = :as')
       ->andWhere('grp.reference = :regime')
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
              ->join('c.annee', 'a')
              ->where('c.niveau = :niveau')
              ->andWhere('a.id = :annee')
              ->setParameter('niveau', $niveau)
              ->setParameter('annee', $as);

    return $qb->getQuery()
              ->getResult();
  }

  public function classeInscription()
  {
    $dql = $this->_em->createQuery(
                    "SELECT c.id, CONCAT(n.libelleFr, ' ', c.libelleFr) nomClasse
                    FROM ISIBundle:Classe c
                    JOIN c.niveau n"
    );

    // $dql->setParameter("as", $as);
    return $dql;
  }

  public function myFindGrpFormationClasse($classeId)
  {
    $qb = $this->createQueryBuilder('c');
    $qb->select('grpF.reference')
       ->join('c.niveau', 'n')
       ->join('n.groupeFormation', 'grpF')
       ->where('c.id = :classe')
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
       ->join('c.annee', 'an')
       ->where('grpF.reference = :regime AND an.id = :an')
       ->setParameter('regime', $regime)
       ->setParameter('an', $as)
    ;
    return $qb->getQuery()
              ->getResult();
  }

  public function classesSuperieures($as, $regime, $niveauId, $succession)
  {
    $qb = $this->createQueryBuilder('c');
    $qb->join('c.niveau', 'n')
       ->join('n.groupeFormation', 'grpF')
       ->join('c.annee', 'an')
       ->where('grpF.reference = :regime AND an.id = :an AND n.succession = :succession')
       ->setParameter('succession', $succession)
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
       ->join('c.annee', 'an')
       ->where('an.id = :an')
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
        ->join('c.annee', 'an')
        ->where('an.id = :as AND exam.id = :examen AND grpF.reference = :regime')
        ->setParameter('as', $as)
        ->setParameter('examen', $examen)
        ->setParameter('regime', $regime);

    return $qb->getQuery()
            ->getResult();
  }

  public function lesClassesDeLEnseignant(int $as, int $enseignantId)
  {
    $qb = $this->createQueryBuilder('c');
    $qb->join('c.cours', 'crs')
        ->join('crs.anneeContrat', 'ac')
        ->join('ac.contrat', 'cont')
        ->join('cont.enseignant', 'e')
        ->join('ac.annee', 'an')
        ->where('an.id = :as AND e.id = :enseignantId')
        ->setParameter('as', $as)
        ->setParameter('enseignantId', $enseignantId)
        ->setParameter('as', $as);

    return $qb->getQuery()
            ->getResult();
  }

  public function classesAyantEmploiDuTemps(int $as, string $regime)
  {
      $qb = $this->createQueryBuilder('c');
      $qb ->join('c.cours', 'ac')
          ->join('c.niveau', 'n')
          ->join('n.groupeFormation', 'grpF')
          ->join('ac.annee', 'an')
          ->where('an.id = :as AND grpF.reference = :regime')
          ->setParameter('as', $as)
          ->setParameter('regime', $regime);

      return $qb->getQuery()
              ->getResult();
  }
}
?>
