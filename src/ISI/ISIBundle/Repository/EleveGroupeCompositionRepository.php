<?php

namespace ISI\ISIBundle\Repository;

use ISI\ISIBundle\Repository;
use ISI\ISIBundle\Entity\EleveGroupeComposition;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class EleveGroupeCompositionRepository extends \Doctrine\ORM\EntityRepository
{
  public function groupe_de_composition_eleve($examen, $eleveId)
  {
    $internes = $this->createQueryBuilder('egc');
    $internes->join('egc.eleve', 'e')
             ->join('egc.groupeComposition', 'gc')
             ->join('gc.examen', 'ex')
             ->where('e.renvoye = 0 AND e.id = :eleveId AND ex = :examen')
             ->setParameter('examen', $examen)
             ->setParameter('eleveId', $eleveId);

    return $internes->getQuery()
                    ->getResult();
  }
  
  public function groupe_de_composition_des_eleves_d_une_classe($examen, $classe)
  {
    $internes = $this->createQueryBuilder('egc');
    $internes->join('egc.eleve', 'e')
             ->join('egc.groupeComposition', 'gc')
             ->join('gc.classe', 'c')
             ->join('gc.examen', 'ex')
             ->where('e.renvoye = 0 AND c = :classe AND ex = :examen')
             ->setParameter('examen', $examen)
             ->setParameter('classe', $classe);

    return $internes->getQuery()
                    ->getResult();
  }

  public function elevesDuGroupeDeComposition(int $examenId, int $annexeId, int $groupeId)
  {
    $qb = $this->createQueryBuilder('egc');
    $qb->join('egc.groupeComposition', 'g')
       ->join('egc.eleve', 'e')
       ->join('g.examen', 'ex')
       ->where('ex.id = :examenId AND g.id = :groupeId AND e.renvoye = 0 AND e.annexe = :annexeId')
       ->orderBy('e.nomFr', 'ASC')
       ->addOrderBy('e.pnomFr', 'ASC')
       ->setParameter('annexeId', $annexeId)
       ->setParameter('examenId', $examenId)
       ->setParameter('groupeId', $groupeId);

      $eleves = $qb->getQuery()
              ->getResult();
              
      if(!empty($eleves)){
        foreach ($eleves as $key => $value) {
          if ($value->getEleve()->getRenvoye() == true) {
            unset($eleves[array_search($value, $eleves)]);
          }
          else {
            # code...
            $nom[$key]  = $value->getEleve()->getNomFr();
            $pnom[$key] = $value->getEleve()->getPnomFr();
          }
        }
        array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $eleves);
      }

      return $eleves;
  }

}
