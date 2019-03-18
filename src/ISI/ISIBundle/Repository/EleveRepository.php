<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\Eleve;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class EleveRepository extends \Doctrine\ORM\EntityRepository
{
  // Récuperation des élèves qui sont inscrits en fonction du regime
  public function elevesInscrits($as, $regime)
  {
    // $elevesAcademie = $this->_em->createQuery('SELECT * FROM ISIBundle:Eleve e WHERE e.matricule LIKE '*A*'');
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->addSelect('f')
       ->join('f.anneeScolaire', 'an')
       ->addSelect('an')
       ->join('f.classe', 'cl')
       ->addSelect('cl')
       ->where('an.anneeScolaireId = :as AND e.regime = :regime AND e.renvoye = 0')
       ->setParameter('regime', $regime)
       ->setParameter('as', $as)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    return $qb->getQuery()
              ->getResult();
  }

  // Récuperation des élèves du centre de formation qui sont inscrits
  public function tousLesElevesInscrits($as)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->addSelect('f')
       ->join('f.anneeScolaire', 'an')
       ->addSelect('an')
       ->join('f.classe', 'cl')
       ->addSelect('cl')
       ->where('an.anneeScolaireId = :as AND e.renvoye = 0')
       ->setParameter('as', $as)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    return $qb->getQuery()
              ->getResult();
  }

  // Récuperation des élèves appartenant à un même niveau
  public function elevesDuMemeNiveau($as, $niveauId)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->addSelect('f')
       ->join('f.anneeScolaire', 'an')
       ->addSelect('an')
       ->join('f.classe', 'cl')
       ->addSelect('cl')
       ->join('cl.niveau', 'n')
       ->addSelect('n')
       ->where('an.anneeScolaireId = :as AND n.id = :niveauId AND e.renvoye = 0')
       ->setParameter('as', $as)
       ->setParameter('niveauId', $niveauId)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    // return $qb->getDql();
    return $qb->getQuery()
              ->getResult();
  }

  // Récuperation des élèves d'un regime
  public function elevesDUnRegime($regime)
  {
    // $elevesAcademie = $this->_em->createQuery('SELECT * FROM ISIBundle:Eleve e WHERE e.matricule LIKE '*A*'');
    $qb = $this->createQueryBuilder('e');
    $qb->where('e.regime = :regime')
       ->setParameter('regime', $regime)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    return $qb->getQuery()
              ->getResult();
  }

  // Récupération du dernier matricule enregistré
  public function dernierMatricule()
  {
	  //REPLACE(SUBSTRING(SUBSTRING(e.matricule, 5), 1, LENGTH(SUBSTRING(e.matricule, 5)) - 4), 'F', '')
    // $dernierMatricule = $this->_em->createQuery(
      // 'SELECT
      // MAX(DISTINCT(SUBSTRING(SUBSTRING(e.matricule, 1, LENGTH(e.matricule) - 4), 5))) AS plus_grand_matricule
      // FROM ISIBundle:Eleve e');
	$dernierMatricule = $this->_em->createQuery(
      'SELECT
      DISTINCT(SUBSTRING(SUBSTRING(e.matricule, 5), 1, LENGTH(SUBSTRING(e.matricule, 5)) - 4)) AS mat
      FROM ISIBundle:Eleve e');

      return $dernierMatricule->getResult();
      //return $dernierMatricule->getScalarResult();
  }

  // Récupération des matricules des élèves d'un regime
  public function getMatricules($regime)
  {
    $matricules = $this->_em->createQuery(
      'SELECT
      e.matricule
      FROM ISIBundle:Eleve e
      WHERE e.regime = :regime');
    $matricules->setParameter('regime', $regime);

    return $matricules->getArrayResult();
      //return $dernierMatricule->getScalarResult();
  }

  // Cette fonction renvoie la liste des élève d'une classe donnée. On l'utilisera ici pour la
  // génération de la liste de classe en pdf
  public function lesElevesDeLaClasse($as, $classeId)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->join('f.classe', 'c')
       ->join('c.anneeScolaire', 'an')
       ->where('an.anneeScolaireId = :as AND c.classeId = :classeId AND e.renvoye = 0')
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC')
       ->setParameter('as', $as)
       ->setParameter('classeId', $classeId);

    return $qb->getQuery()
              ->getResult();
  }

  // Sélection des membres d'une halaqa
  public function laHalaqaDUnEleve($as, $eleveId)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->join('e.memoriser', 'm')
            ->join('m.halaqa', 'h')
            ->join('c.anneeScolaire', 'an')
            ->where('an.anneeScolaireId = :as AND m.eleve = :eleveId AND e.renvoye = 0')
            ->orderBy('e.nomFr', 'ASC')
            ->addOrderBy('e.pnomFr', 'ASC')
            ->setParameter('as', $as)
            ->setParameter('eleveId', $eleveId);
    
        return $qb->getQuery()
                    ->getResult();
    }

  public function elevesDuNiveau($niveauId, $as)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.frequenter', 'f')
       ->join('f.classe', 'c')
       ->join('c.anneeScolaire', 'an')
       ->join('c.niveau', 'n')
       ->where('an.anneeScolaireId = :as AND n.id = :niveauId AND e.renvoye = 0')
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC')
       ->setParameter('as', $as)
       ->setParameter('niveauId', $niveauId);

    return $qb->getQuery()
              ->getResult();
  }

  // Récuperation des élèves d'un regime qui ont été renvoyés
  public function elevesRenvoyes($regime)
  {
    // $elevesAcademie = $this->_em->createQuery('SELECT * FROM ISIBundle:Eleve e WHERE e.matricule LIKE '*A*'');
    $qb = $this->createQueryBuilder('e');
    $qb->where('e.regime = :regime AND e.renvoye = 1')
       ->setParameter('regime', $regime)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');
    // return $elevesAcademie->getResult();
    return $qb->getQuery()
              ->getResult();
  }

  // Sélection des élèves de l'internat
  public function elevesInternes($as)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.interner', 'i')
       ->addSelect('i')
       ->join('i.anneeScolaire', 'an')
       ->addSelect('an')
       ->join('i.chambre', 'ch')
       ->addSelect('ch')
       ->where('an.anneeScolaireId = :as AND e.renvoye = 0 AND i.renvoye = 0')
       ->setParameter('as', $as)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');

       return $qb->getQuery()
                 ->getResult();
  }

  // Sélection des élèves de l'internat
  public function elevesInternesRenvoyes($as)
  {
    $qb = $this->createQueryBuilder('e');
    $qb->join('e.interner', 'i')
       ->addSelect('i')
       ->join('i.anneeScolaire', 'an')
       ->addSelect('an')
       ->join('i.chambre', 'ch')
       ->addSelect('ch')
       ->where('an.anneeScolaireId = :as AND e.renvoye = 0 AND i.renvoye = 1')
       ->setParameter('as', $as)
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC');

       return $qb->getQuery()
                 ->getResult();
  }
}

//MAX(DISTINCT(CAST(SUBSTRING(SUBSTRING(e.matricule, 1, LENGTH(e.matricule) - 4), 5) AS SIGNED INTEGER))) AS plus_grand_matricule


?>
