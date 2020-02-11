<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Note;
use ISI\ISIBundle\Entity\Annee;
use ISI\ISIBundle\Entity\Niveau;
use ISI\ISIBundle\Entity\Classe;
use ISI\ISIBundle\Entity\Examen;
use ISI\ISIBundle\Entity\Matiere;
use ISI\ISIBundle\Entity\Moyenne;
use ISI\ISIBundle\Entity\Enseignement;
use ISI\ISIBundle\Entity\Appreciation;
use ISI\ISIBundle\Entity\Moyenneclasse;
use ISI\ISIBundle\Entity\FrequenterMatiere;
use ISI\ISIBundle\Repository\NoteRepository;
use ISI\ISIBundle\Repository\ExamenRepository;
use ISI\ISIBundle\Repository\NiveauRepository;
use ISI\ISIBundle\Repository\MoyenneRepository;
use ISI\ISIBundle\Repository\MatiereRepository;
use ISI\ISIBundle\Repository\AnneeRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Doctrine\ORM\EntityRepository;

class ImportExportController extends Controller
{
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function envoi_des_notes_et_moyennes_sur_le_netAction(Request $request, $as, $regime, $classeId, $examenId)
  {
    $em = $this->getDoctrine()->getManager();
    return $this->render('ISIBundle:ImportExport:envoi-des-resultats-en-ligne.html.php');
    // $customerEm = $this->getDoctrine()->getManager('customer');
    // $customerEm = $this->get('doctrine.orm.customer_entity_manager');

    // $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    // $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    // $repoClasse  = $em->getRepository('ISIBundle:Classe');
    // $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    // $repoExamen  = $em->getRepository('ISIBundle:Examen');

    // /*********** - Etape 1: Sélection des données- ***********/
    // $annee     = $repoAnnee->find($as);
    // $eleves    = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    // $classe    = $repoClasse->find($classeId);
    // $examen    = $repoExamen->find($examenId);
    // $niveauId  = $classe->getNiveau()->getId();

    $requetes = "SELECT * FROM note_abidjan n WHERE n.examen_id = :examenId AND n.eleve_id IN (SELECT f.eleve_id FROM frequenter_abidjan WHERE f.classe_id = :classeId);";   
    $statement = $customerEm->getConnection()->prepare($requetes);
    $statement->bindValue('examenId', $examenId);
    $statement->bindValue('classeId', $classeId);
    $statement->execute();
    $notes = $statement->fetchAll();
    return new Response(var_dump($notes));
  }
}
