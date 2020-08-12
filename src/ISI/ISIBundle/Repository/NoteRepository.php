<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Note;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class NoteRepository extends \Doctrine\ORM\EntityRepository
{
  // $ids contient les id des élèves sous forme de chaîne de caractères
  public function notesEnEdition($examenId, $matiereId, $elevesIds)
  {
      $qb = $this->createQueryBuilder('n');
      $qb->where('n.examen = :examenId AND n.matiere = :matiereId AND n.eleve IN (:ids)')
          // ->andWhere($qb->expr()->in('n.eleve', ':ids'))
          ->setParameter('ids', $elevesIds)
          ->setParameter('examenId', $examenId)
          ->setParameter('matiereId', $matiereId)
      ;

      return $qb->getQuery()->getResult();
  }

  // La fonction me permet de sélectionner les notes des élèves d'une classe donnée.
  // La variable $elevesIds contient les ids des élèves de la classe en cours de traitement
  public function lesNotesDesElevesDeLaClasse($examenId, $elevesIds)
  {
    $query = $this->_em->createQuery(
      'SELECT n FROM ISIBundle:Note n
      WHERE n.examen = :examenId AND n.eleve IN (:ids)');
    $query->setParameter('examenId', $examenId);
    $query->setParameter('ids', $elevesIds);

    return $query->getResult();
  }

  // Les notes des élèves pour une matière donnée
  public function lesNotesDesElevesDeLaClasseEnUneMatiere($examenId, $matiereId, $elevesIds)
  {
    $query = $this->_em->createQuery(
      'SELECT n FROM ISIBundle:Note n
      WHERE n.examen = :examenId AND n.matiere = :matiereId AND n.eleve IN (:ids)');
    $query->setParameter('examenId', $examenId);
    $query->setParameter('matiereId', $matiereId);
    $query->setParameter('ids', $elevesIds);

    return $query->getResult();
  }

  // Les élèves qui n'ont pas de notes dans une classe donnée
  public function elevesSansNotes($classeId, $examenId)
  {
    $query = $this->_em->createQuery(
      'SELECT DISTINCT(e1.id)
        FROM ISIBundle:Eleve e1
        JOIN ISIBundle:Frequenter f
        JOIN ISIBundle:Classe c
        WHERE c.id = :classeId AND e1.id NOT IN
          (
            SELECT DISTINCT(e.eleveId)
            FROM ISIBundle:Eleve e
            JOIN ISIBundle:Note n
            JOIN ISIBundle:Frequenter fq
            JOIN ISIBundle:Classe cl
            WHERE cl.id = :classeId AND n.eleve = fq.eleve AND fq.classe = cl.id AND n.examen = :examenId
          )'
        );
    $query->setParameter('classeId', $classeId);
    $query->setParameter('examenId', $examenId);

  // return $query->getDql();
  return $query->getResult();
  }

  // En sélectionne toutes les notes (ceux de la première et de la seuxième session) d'un élève
  public function notesDUnEleveLorsDUnExamen($id, $as, $session)
  {
    $qb = $this->createQueryBuilder('n');
    $qb->join('n.eleve', 'e')
       ->join('n.examen', 'ex')
       ->join('ex.annee', 'an')
       ->where('e.id = :id AND an.id = :an AND ex.session = :session')
       ->setParameter('id', $id)
       ->setParameter('an', $as)
       ->setParameter('session', $session);

    // return $qb->getDql();

    return $qb->getQuery()
              ->getResult();
  }

  public function notesDUnEleveLorsDesDeuxExamens($eleveId, $as)
  {
    $qb = $this->createQueryBuilder('n');
    $qb->join('n.eleve', 'e')
       ->join('n.examen', 'ex')
       ->join('ex.annee', 'an')
       ->where('e.id = :eleveId AND an.id = :an')
       ->setParameter('eleveId', $eleveId)
       ->setParameter('an', $as);

    // return $qb->getDql();

    return $qb->getQuery()
              ->getResult();
  }

  // Toutes les notes des élèves d'une classe pour un examen donné
  public function toutesLesNotesDesElevesDeLaClasse($examenId, $elevesIds)
  {
    $query = $this->_em->createQuery(
      'SELECT n FROM ISIBundle:Note n
      WHERE n.examen = :examenId AND n.eleve IN (:ids)');
    $query->setParameter('examenId', $examenId);
    $query->setParameter('ids', $elevesIds);

    return $query->getResult();
  }

  // Toutes les notes des élèves d'une classe pour un examen donné
  public function test($eleveId, $anneeId)
  {
    $query = $this->_em->createQuery(
      'SELECT SUM(n.note) FROM ISIBundle:Note n
      JOIN ISIBundle:Examen e ON n.examen = e
      JOIN ISIBundle:Annee a ON e.annee = a
      WHERE n.examen = :anneeId AND n.eleve = :eleveId');
    $query->setParameter('anneeId', $anneeId);
    $query->setParameter('ids', $eleveId);

    return $query->getResult();
  }
}

/***
'SELECT DISTINCT(e1.eleveId)
        FROM ISIBundle:Eleve e1
        JOIN ISIBundle:Frequenter f
        JOIN ISIBundle:Classe c WITH c.classeId = '.$classeId.'
        AND e1.eleveId NOT IN
          (
            SELECT DISTINCT(e.eleveId)
            FROM ISIBundle:Eleve e
            JOIN ISIBundle:Note n WITH n.eleve = fq.eleve
            JOIN ISIBundle:Frequenter fqr WITH fqr.eleve = n.eleve
            JOIN ISIBundle:Classe cl WITH cl.classeId = '.$classeId.'
            AND fqr.classe = cl.classeId AND n.examen = '.$examenId.'
          )'
***/
