<?php

namespace ISI\ISIBundle\Repository;


class EleveRepository extends \Doctrine\ORM\EntityRepository
{
  // Récuperation des élèves qui sont inscrits en fonction du regime
  public function elevesInscrits(int $as, int $annexeId, $regime)
  {
    // $elevesAcademie = $this->_em->createQuery('SELECT * FROM ISIBundle:Eleve e WHERE e.matricule LIKE '*A*'');
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->addSelect('f')
       ->join('f.annee', 'an')
       ->addSelect('an')
       ->join('f.classe', 'cl')
       ->addSelect('cl')
       ->where('an.id = :as AND e.regime = :regime AND e.renvoye = 0 AND e.annexe = :annexeId')
       ->setParameter('regime', $regime)
       ->setParameter('as', $as)
       ->setParameter('annexeId', $annexeId)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    return $qb->getQuery()
              ->getResult();
  }

  // Récuperation des élèves du centre de formation qui sont inscrits
  public function tousLesElevesInscrits(int $as, int $annexeId)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->addSelect('f')
       ->join('f.annee', 'an')
       ->addSelect('an')
       ->join('f.classe', 'cl')
       ->addSelect('cl')
       ->where('an.id = :as AND e.renvoye = 0 AND e.annexe = :annexeId')
       ->setParameter('annexeId', $annexeId)
       ->setParameter('as', $as)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    return $qb->getQuery()
              ->getResult();
  }

  // Récuperation des élèves appartenant à un même niveau
  public function elevesDuMemeNiveau(int $as, int $annexeId, $niveauId)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->addSelect('f')
       ->join('f.annee', 'an')
       ->addSelect('an')
       ->join('f.classe', 'cl')
       ->addSelect('cl')
       ->join('cl.niveau', 'n')
       ->addSelect('n')
       ->where('an.id = :as AND n.id = :niveauId AND e.renvoye = 0 AND e.annexe = :annexeId')
       ->setParameter('as', $as)
       ->setParameter('annexeId', $annexeId)
       ->setParameter('niveauId', $niveauId)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    // return $qb->getDql();
    return $qb->getQuery()
              ->getResult();
  }

  // Récuperation des élèves d'un regime
  public function elevesDUnRegime(int $annexeId, $regime)
  {
    // $elevesAcademie = $this->_em->createQuery('SELECT * FROM ISIBundle:Eleve e WHERE e.matricule LIKE '*A*'');
    $qb = $this->createQueryBuilder('e');
    $qb->where('e.regime = :regime AND e.annexe = :annexeId')
       ->setParameter('annexeId', $annexeId)
       ->setParameter('regime', $regime)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    return $qb->getQuery()
              ->getResult();
  }

  // Récupération du dernier matricule enregistré
  public function dernierMatricule(int $annexeId)
  {
	  //REPLACE(SUBSTRING(SUBSTRING(e.matricule, 5), 1, LENGTH(SUBSTRING(e.matricule, 5)) - 4), 'F', '')
    // $dernierMatricule = $this->_em->createQuery(
      // 'SELECT
      // MAX(DISTINCT(SUBSTRING(SUBSTRING(e.matricule, 1, LENGTH(e.matricule) - 4), 5))) AS plus_grand_matricule
      // FROM ISIBundle:Eleve e');
	$dernierMatricule = $this->_em->createQuery(
      'SELECT
      DISTINCT(SUBSTRING(SUBSTRING(e.matricule, 5), 1, LENGTH(SUBSTRING(e.matricule, 5)) - 4)) AS mat
      FROM ISIBundle:Eleve e WHERE e.annexe = :annexeId')->setParameter('annexeId', $annexeId);

      return $dernierMatricule->getResult();
      //return $dernierMatricule->getScalarResult();
  }

  // Récupération des matricules des élèves d'un regime
  public function getMatricules($annexeId, $regime)
  {
    $matricules = $this->_em->createQuery(
      'SELECT
      e.matricule
      FROM ISIBundle:Eleve e
      WHERE e.regime = :regime AND e.annexe = :annexeId');
    $matricules->setParameter('regime', $regime)->setParameter('annexeId', $annexeId);

    return $matricules->getArrayResult();
      //return $dernierMatricule->getScalarResult();
  }

  // Cette fonction renvoie la liste des élève d'une classe donnée. On l'utilisera ici pour la
  // génération de la liste de classe en pdf
  public function lesElevesDeLaClasse(int $as, int $annexeId, int $classeId)
  {
    $em = $this->getEntityManager();
    $sql = 'SELECT e.id AS id, e.matricule AS matricule, e.nom_fr AS nomFr, e.pnom_fr AS pnomFr, e.nom_ar AS nomAr, e.pnom_ar AS pnomAr, e.sexe AS sexe, IF(er.eleve_id IS NULL OR e.renvoye = 0, FALSE, TRUE) AS renvoye, e.photo AS photo, e.date_naissance AS dateNaissance, e.lieu_naissance AS lieuNaissance FROM eleve_renvoye er RIGHT JOIN eleve e ON er.eleve_id = e.id AND er.annee_id = :an JOIN frequenter f ON f.eleve_id = e.id JOIN classe c ON c.id = f.classe_id WHERE f.annee_id = :an AND c.id = :classeId AND e.annexe_id = :annexeId;';
    $statement = $em->getConnection()->prepare($sql);
    $statement->bindValue('classeId', $classeId);
    $statement->bindValue('annexeId', $annexeId);
    $statement->bindValue('an', $as);
    $statement->execute();
    $eleves = $statement->fetchAll();
    if(!empty($eleves)){
      foreach ($eleves as $key => $value) {
        if ($value['renvoye'] == true) {
          unset($eleves[array_search($value, $eleves)]);
        }
        else {
          # code...
          $nom[$key]  = $value['nomFr'];
          $pnom[$key]  = $value['pnomFr'];
        }
      }
      array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $eleves);
    }
    return $eleves;
  }


  public function elevesDeLaClasse(int $as, int $annexeId, $classeId)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->join('f.classe', 'c')
       ->join('c.annee', 'an')
       ->where('an.id = :as AND c.id = :classeId AND e.renvoye = 0 AND e.annexe = :annexeId')
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC')
       ->setParameter('annexeId', $annexeId)
       ->setParameter('as', $as)
       ->setParameter('classeId', $classeId);

      $eleves = $qb->getQuery()
              ->getResult();
      if(!empty($eleves)){
        foreach ($eleves as $key => $value) {
          if ($value->getRenvoye() == true) {
            unset($eleves[array_search($value, $eleves)]);
          }
          else {
            # code...
            $nom[$key]  = $value->getNomFr();
            $pnom[$key] = $value->getPnomFr();
          }
        }
        array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $eleves);
      }

      return $eleves;
  }

  // Sélection des membres d'une halaqa
  public function laHalaqaDUnEleve(int $as, int $annexeId, $eleveId)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->join('e.memoriser', 'm')
            ->join('m.halaqa', 'h')
            ->join('c.annee', 'an')
            ->where('an.id = :as AND m.eleve = :eleveId AND e.renvoye = 0')
            ->orderBy('e.nomFr', 'ASC')
            ->addOrderBy('e.pnomFr', 'ASC')
            ->setParameter('as', $as)
            ->setParameter('annexeId', $annexeId)
            ->setParameter('eleveId', $eleveId);
    
        return $qb->getQuery()
                    ->getResult();
    }

  public function elevesDuNiveau(int $niveauId, int $as, int $annexeId)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->join('f.classe', 'c')
       ->join('c.annee', 'an')
       ->join('c.niveau', 'n')
       ->where('an.id = :as AND n.id = :niveauId AND e.renvoye = 0 AND e.annexe = :annexeId')
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC')
       ->setParameter('as', $as)
       ->setParameter('annexeId', $annexeId)
       ->setParameter('niveauId', $niveauId);

    return $qb->getQuery()
              ->getResult();
  }

  // Récuperation des élèves d'un regime qui ont été renvoyés
  public function elevesRenvoyes(int $annexeId, $regime)
  {
    // $elevesAcademie = $this->_em->createQuery('SELECT * FROM ISIBundle:Eleve e WHERE e.matricule LIKE '*A*'');
    $qb = $this->createQueryBuilder('e');
    $qb->where('e.regime = :regime AND e.renvoye = 1 AND e.annexe = :annexeId')
       ->setParameter('annexeId', $annexeId)
       ->setParameter('regime', $regime)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    return $qb->getQuery()
              ->getResult();
  }

  // Sélection des élèves de l'internat
  public function elevesInternes(int $as, int $annexeId)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.interner', 'i')
       ->addSelect('i')
       ->join('i.annee', 'an')
       ->addSelect('an')
       ->join('i.chambre', 'ch')
       ->addSelect('ch')
       ->where('an.id = :as AND e.renvoye = 0 AND i.renvoye = 0 AND e.annexe = :annexeId')
       ->setParameter('annexeId', $annexeId)
       ->setParameter('as', $as)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');

       return $qb->getQuery()
                 ->getResult();
  }

  // Sélection des élèves de l'internat
  public function elevesInternesRenvoyes(int $as, int $annexeId)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.interner', 'i')
       ->addSelect('i')
       ->join('i.annee', 'an')
       ->addSelect('an')
       ->join('i.chambre', 'ch')
       ->addSelect('ch')
       ->where('an.id = :as AND e.renvoye = 0 AND i.renvoye = 1 AND e.annexe = :annexeId')
       ->setParameter('annexeId', $annexeId)
       ->setParameter('as', $as)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');

       return $qb->getQuery()
                 ->getResult();
  }
}

//MAX(DISTINCT(CAST(SUBSTRING(SUBSTRING(e.matricule, 1, LENGTH(e.matricule) - 4), 5) AS SIGNED INTEGER))) AS plus_grand_matricule


?>
