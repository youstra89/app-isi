<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Dompdf\Options;
use Dompdf\Dompdf;

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
use ISI\ISIBundle\Repository\AppreciationRepository;

use ISI\ISIBundle\Form\NoteType;
use ISI\ISIBundle\Form\NotesType;
use ISI\ISIBundle\Form\ExamenType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Doctrine\ORM\EntityRepository;

class ExamenController extends Controller
{
  // Pour tous ce qui concernent les examens

  // Cette fonction servira à afficher les classes pour pouvoir ensuite tirer (imprimer) les fiches de notes
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function afficherLesClassesPourTirerLesFichesDeNotesAction($as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoClasse = $em->getRepository('ISIBundle:Classe');

    $niveaux = $repoNiveau->niveauxDuGroupe($regime);
    $classes = $repoClasse->classeGrpFormation($as, $regime);
    $annee   = $repoAnnee->find($as);

    return $this->render('ISIBundle:Examen:afficher-classes-pour-tirer-fiche-de-notes.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'niveaux' => $niveaux,
      'classes' => $classes,
    ]);
  }

  // On affiche une classe et les différentes matières dispensées dans la classe en question afin de
  // pouvoir tirer la fiche de note pour la matière qu'on désire
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function lesFichesDeNotesDeLaClasseAction($as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoAnnee = $em->getRepository('ISIBundle:Annee');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    // La liste des élèves de la classe
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    // On sélectionne l'id du niveau en fonction de l'id de la classe pour la transmettre en paramètre
    // pour la sélection des matières du niveau
    $classe = $repoClasse->find($classeId);

    $niveauId = $classe->getNiveau()->getId();

    // Les matières de la classe
    $matieres = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    return $this->render('ISIBundle:Examen:fiche-de-notes-de-la-classe.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'classe'   => $classe,
      'matieres' => $matieres
    ]);
  }

  // Affichage de la vue pdf
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function laFichesDeNotesDeLaClassePourUneMatiereAction(Request $request, $as, $regime, $classeId, $matiereId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');

    $annee   = $repoAnnee->find($as);
    $classe  = $repoClasse->find($classeId);
    $matiere = $repoMatiere->find($matiereId);
    $eleves  = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $filename = "fiche-de-note-de-".$classe->getLibelleFr()."-".$matiere->getLibelle();

    $html = $this->renderView('ISIBundle:Examen:fiche-de-notes-pour-une-matiere.html.twig', [
      "as"      => $as,
      "classe"  => $classe,
      'annee'   => $annee,
      "eleves"  => $eleves,
      "regime"  => $regime,
      "matiere" => $matiere,
      'server'   => $_SERVER["DOCUMENT_ROOT"],   
      ]);


    // Je mets knp en commentaire pour pouvoir utiliser dom KNP
    return new Response(
        $snappy->getOutputFromHtml($html),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"'
        ]
    );
  }

  public static function dateToFrench($date, $format) 
  {
      $english_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
      $french_days = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
      $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
      $french_months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
      return str_replace($english_months, $french_months, str_replace($english_days, $french_days, date($format, strtotime($date) ) ) );
  }

  // Page d'accueil pour la saisie des notes
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function accueilSaisieDeNoteAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoExamen = $em->getRepository('ISIBundle:Examen');

    // Sélection de l'examen pour lequel on va saisir les notes
    $examen  = $repoExamen->dernierExamen($as);
    $examens = $repoExamen->lesExamensDeLAnnee($as);
    $annee   = $repoAnnee->find($as);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    if(empty($examen))
    {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas saisir de notes car il n\'a aucun examen en cours. Demandez au bureau des études et des examens d\'enregistrer un examen.');
      return $this->redirect($this->generateUrl('isi_affaires_scolaires', ['as' => $as, 'regime' => $regime]));
    }

    //
    $classes = $repoClasse->classeGrpFormation($as, $regime);
    return $this->render('ISIBundle:Examen:afficher-classes-pour-saisir-de-notes.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'classes'  => $classes,
      'examen'   => $examen,
      'annee'    => $annee,
      'niveaux'  => $niveaux,
      'examens'  => $examens,
    ]);
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function saisieDesNotesDeLaClasseAction($as, $regime, $classeId, $examenId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    // Sélection de l'examen
    $examen = $repoExamen->find($examenId);

    // La liste des élèves de la classe
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    // On sélectionne l'id du niveau en fonction de l'id de la classe pour la transmettre en paramètre
    // pour la sélection des matières du niveau
    $classe = $repoClasse->find($classeId);

    $niveauId = $classe->getNiveau()->getId();

    // Les matières de la classe
    $matieres = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    $idsMatieres = [];
    foreach ($matieres as $mat) {
      $idsMatieres[] = $mat->getId();
    }

    // Je sélectionne ici les notes d'un élèves donné et je les envoie au template pour que si l'on se rend compte
    // qu'il existe déjà une note, on grise la matière de de ne plus permettre la saisie de notes
    $notesDUnEleve   = $repoNote->findBy(['examen' => $examenId, 'eleve' => reset($eleves)['id']]);

    $idsMatiereAvecNote = [];

    $matiereSansNote    = [];
    $idsMatiereSansNote = [];
    /*********        Début de la sélection des matières ou les élèves on déjà eu une note        **********/
    foreach ($notesDUnEleve as $noteMat) {
      $idsMatiereAvecNote[] = $noteMat->getMatiere()->getId();
    }
    /**********        Fin de la sélection des matières ou les élèves on déjà eu une note        **********/
    /*********     Début de la sélection des matières ou les élèves n'on pas encore eu de note    **********/
    $idsMatiereSansNote = array_diff($idsMatieres, $idsMatiereAvecNote);
    foreach ($idsMatiereSansNote as $mat) {
      $matiereSansNote[$mat] = $repoMatiere->find($mat);
      // $matiereSansNote[] = $repoNote->findOneBy(['examen' => $examenId, 'eleve' => reset($eleves)['id'], 'matiere' => $mat]);
    }
    /********* *    Fin de la sélection des matières ou les élèves n'on pas encore eu de note    **********/

    // return new Response(var_dump($notesDUnEleve, $matiereSansNote, $idsMatiereSansNote));

    return $this->render('ISIBundle:Examen:liste-des-matieres-pour-la-saisie-des-notes.html.twig', [
      'asec'            => $as,
      'regime'          => $regime,
      'annee'           => $annee,
      'classe'          => $classe,
      'examen'          => $examen,
      'matieres'        => $matieres,
      'matiereSansNote' => $matiereSansNote,
    ]);
  }

  public function calculMoyenneExamen(array $eleves, $examen, $classe, array $enseignements)
  {
    $em                = $this->getDoctrine()->getManager();
    $repoAnnee         = $em->getRepository('ISIBundle:Annee');
    $repoNote          = $em->getRepository('ISIBundle:Note');
    $repoExamen        = $em->getRepository('ISIBundle:Examen');
    $repoClasse        = $em->getRepository('ISIBundle:Classe');
    $repoMatiere       = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne       = $em->getRepository('ISIBundle:Moyenne');
    $repoEnseignement  = $em->getRepository('ISIBundle:Enseignement');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');
    $examenId = $examen->getId();
    $classeId = $classe->getId();
    $admis   = 0;
    $recales = 0;
    foreach($eleves as $key => $value)
    {
      $eleveId = $value->getId();
      $moyenne = $repoMoyenne->findOneBy(["eleve" => $eleveId, "examen" => $examenId]);
      $moy = $this->calculMoyenneDUnEleve($eleveId, $examenId, $enseignements);
      if(empty($moyenne))
      {
        $moyenne = new Moyenne();
        $moyenne->setEleve($value);
        $moyenne->setExamen($examen);
        $moyenne->setTotalPoints($moy["totalPoints"]);
        $moyenne->setMoyenne($moy["moyenne"]);
        $moyenne->setCreatedBy($this->getUser());
        $moyenne->setCreatedAt(new \Datetime());
        $em->persist($moyenne);
      }
      else{
        $moyenne->setTotalPoints($moy["totalPoints"]);
        $moyenne->setMoyenne($moy["moyenne"]);
        $moyenne->setUpdatedBy($this->getUser());
        $moyenne->setUpdatedAt(new \Datetime());
      }
      $moyennesDeTousLesEleves[$eleveId] = $moyenne;
      if($moy >= 5.5)
        $admis++;
      else
        $recales++;
    }

    /** On va enregistrer la moyenne de la classe */
    $moyenneClasse = $repoMoyenneclasse->findOneBy(["classe" => $classeId, "examen" => $examenId]);
    if(empty($moyenneClasse))
    {
      $moyenneClasse = new Moyenneclasse();
      $moyenneClasse->setClasse($classe);
      $moyenneClasse->setExamen($examen);
      $moyenneClasse->setAdmis($admis);
      $moyenneClasse->setRecales($recales);
      $moyenneClasse->setCreatedBy($this->getUser());
      $moyenneClasse->setCreatedAt(new \Datetime());
      $em->persist($moyenneClasse);
    }
    else{
      $moyenneClasse->setAdmis($admis);
      $moyenneClasse->setRecales($recales);
      $moyenneClasse->setCreatedBy($this->getUser());
      $moyenneClasse->setCreatedAt(new \Datetime());
    }

    $rangs = $this->classementSemestriel($moyennesDeTousLesEleves);

    foreach($moyennesDeTousLesEleves as $key => $value)
    {
      $eleveId = $value->getEleve()->getId();
      $value->setRang($rangs[$eleveId]);
      $value->setUpdatedBy($this->getUser());
      $value->setUpdatedAt(new \Datetime());
    }

    try{
      $em->flush();
      return $rangs;
    } 
    catch(\Doctrine\ORM\ORMException $e){
      $this->addFlash('error', $e->getMessage());
      $this->get('logger')->error($e->getMessage());
      return false;
    } 
    catch(\Exception $e){
      $this->addFlash('error', $e->getMessage());
      return false;
    }
    
  }

  public function classementSemestriel($moyennes)
  {
      /** ****************  1 - Première étape - 1   *****************
       * A ce niveau toutes les moyennes sont enregistrées. On boucle encore sur les moyenne pour
       * les ranger du plus grand au plus petit. Al hamdoulillah la fonction array_multisort permet
       * de gérer cela efficacement */
      foreach ($moyennes as $key => $moyenne) {
        if(!empty($moyenne))
        {
          $valeur_moyenne[$key] = $moyenne->getMoyenne();
          $id[$key]             = $moyenne->getId();
        }
        else{
          unset($moyennes[array_search($moyenne, $moyennes)]);
        }
      }
      // array_multisort() permet de trier un tableau multidimensionnel
      array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);
      // return new Response(var_dump($moyennes));

      // ****************  2 - Deuxième étape - 2   ***************** //
      // On va maintenant persister le rang de chaque élève
      /**
       * Mais attention!!! Il faudra gérer les ex aequo
       * Pour faire cela, je vais initialiser des variables:
       * $ex : une booléenne qui vaut TRUE si l'on rencontre des valeurs identiques lors du parcours du tableau des moyennes
       * $nbrEx: une entière qui compte le nombre d'ex aequo, c'est-à-dire le nombre de moyennes identiques
       * $r quant à elle, elle ne sera pas initialisée, elle prendra la valeur du rang à sauvegarder en bd en fonction des ex aequo
       */
      $nbrEx      = 0;
      $ex         = FALSE;
      $continuer  = FALSE;
      $classement = [];
      foreach($moyennes as $key => $rang)
      {
        $eleveId = $rang->getEleve()->getId();
        // On va définir la valeur de la clé pour laquelle le test de comparaison s'arrête.
        // Si la clé de parcours du tableau prend la dernière clé, on arrête le test
        $limit = count($moyennes) - 1;
        if($key < $limit && $continuer == FALSE) {
          /**
           * Lorsque je parcours le tableau, je compare la moyenne en cours à la moyenne suivante et
           * je compare aussi la valeur de la $ex
           */
           if($moyennes[$key]->getMoyenne() == $moyennes[$key + 1]->getMoyenne() && $ex == TRUE)
           {
             /**
              * Si la moyenne en cours est identique à la moyenne suivante et qu'il y a eu un ex aequo, on va
              * incrémenter la valeur de ex aequo ($nbrEx) et on donne toujours la valeur TRUE à $ex.
              * Le rang prend alors la valeur de la clé ($key) plus un moins le nombre d'ex aequo ($nbrEx)
              */
              $r     = $key + 1 - $nbrEx;
              $ex    = TRUE;
              $nbrEx = $nbrEx + 1;
           }
           elseif($moyennes[$key]->getMoyenne() == $moyennes[$key + 1]->getMoyenne() && $ex == FALSE)
           {
             /**
              * Si la moyenne en cours est identique à la moyenne suivante et qu'il n'y a pas eu d'ex aequo, alors
              * on incrémente le nombre d'ex aequo ($nbrEx), $ex prend TRUE (car les valeur testées sont identiques).
              * Le rang prend alors la valeur de la clé de parcours ($key) plus un
              */
              $r     = $key + 1;
              $ex    = TRUE;
              $nbrEx = $nbrEx + 1;
           }
           elseif($moyennes[$key]->getMoyenne() != $moyennes[$key + 1]->getMoyenne() && $ex == TRUE)
           {
             /**
              * Si la moyenne en cours diffère de la moyenne suivante et qu'il y a eu un ex aequo alors, la valeur de
              * ex aequo prendra FALSE, le nombre d'ex aequo sera écrasé ($ex = 0) et le rang aura comme valeur
              * la valeur de la clé de parcours plus un moins le nombre d'ex aequo
              */
              $r     = $key + 1 - $nbrEx;
              $ex    = FALSE;
              $nbrEx = 0;
           }
           else {
             # Et enfin, si les deux moyennes testées ne sont pas identiques et qu'il n'y pas d'ex aequo...
             # Beuh... le calcul se déroule normalement
             $r     = $key + 1;
             $ex    = FALSE;
             $nbrEx = $nbrEx;
           }
        }
        else {
          // A ce stade, le dernier rang du tableau n'est pas renseigné.
          // On va donc faire un petit test
          if($ex == TRUE){
            $r = $key + 1 - $nbrEx;
          }
          else {
            $r = $key + 1;
          }
          $continuer = TRUE;
        }

        $classement[$eleveId] = $r;
      }// Fin de la boucle foreach

      return $classement;
  }

  public function findAppreciation($noteEleve, int $total)
  {
    if($total == 20){
      $noteEleve = $noteEleve / 2;
    }

    if(empty($noteEleve)){
      $noteEleve = 0;
    }

    switch ($noteEleve) {
      case $noteEleve < 5.5:
        $appreciation = 1;
        break;
      
      case $noteEleve >= 5.5 && $noteEleve < 6:
        $appreciation = 2;
        break;
      
      case $noteEleve >= 6 && $noteEleve < 8:
        $appreciation = 3;
        break;
      
      case $noteEleve >= 8 && $noteEleve < 9:
        $appreciation = 4;
        break;
        
      default:
        $appreciation = 5;
        break;
    }

    return $appreciation;
  }
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function enregistrerNotesAction(Request $request, $as, $regime, $classeId, $examenId, $matiereId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoEleve        = $em->getRepository('ISIBundle:Eleve');
    $repoExamen       = $em->getRepository('ISIBundle:Examen');
    $repoClasse       = $em->getRepository('ISIBundle:Classe');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne      = $em->getRepository('ISIBundle:Moyenne');
    $repoNote         = $em->getRepository('ISIBundle:Note');
    $repoAppreciation = $em->getRepository('ISIBundle:Appreciation');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');

    $annee         = $repoAnnee->find($as);
    $eleves        = $repoEleve->elevesDeLaClasse($as, $classeId);
    $examen        = $repoExamen->findOneBy(['id'     => $examenId]);
    $matiere       = $repoMatiere->findOneBy(['id'    => $matiereId]);
    $classe        = $repoClasse->findOneBy(['id'     => $classeId]);
    $elevesIds     = $this->recupererLesIdsDesEleves($eleves);
    $niveauId      = $classe->getNiveau()->getId();
    $moyenne       = $repoMoyenne->findOneBy(['eleve' => $eleves[0]->getId(), 'examen' => $examenId]);
    $moyennes      = $repoMoyenne->lesMoyennesDesElevesDeLaClasse($examenId, $elevesIds);
    $enseignements = $repoEnseignement->findBy(['annee' => $as, 'niveau' => $niveauId]);
    /**************************---------------------------********************************
     * Je vais créer un variable booléen et la passé en paramètre à url. Sa valeur initial
     * est 0. Son rôle sera de tester la première visite de cette page. Alors, si nous
     * sommes à la prmière visite de la page (cette page) on génère les notes. Sinon on
     * passe directement à la saisie des notes.
     **************************---------------------------********************************/
    $notesGenerees = $request->query->get('note');
    // Si $notesGenerees vaut 0 alors on génère les notes et les moyennes des élèves

    // Quand on soumet le formulaire
    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      foreach($data['note'] as $key => $noteEleve)
      {
        // On vérifie qu'aucune des notes saisies n'est pas supérieures à 10.
        if($noteEleve > 10)
        {
          $eleveNoteSup = $eleve;
          $request->getSession()->getFlashBag()->add('error', 'Vérifiez bien les notes saisies. Aucune note ne doit être supérieur à 10');
          return new Response('La note de cet élève est supérieur à 10: '.$noteEleve);
        }
        $eleve = $repoEleve->find($key);

        $note = new Note();
        $note->setExamen($examen);
        $note->setEleve($eleve);
        $note->setMatiere($matiere);
        $note->setCreatedBy($this->getUser());
        $note->setCreatedAt(new \Datetime());
        // On va déterminer l'appréciation de la note obtenue
        $appreciationId = $this->findAppreciation($noteEleve, 10);
        $appreciation = $repoAppreciation->find($appreciationId);
        $note->setAppreciation($appreciation);
        
        // Cette condition me permet de savoir si l'élève à composé dans la matière ou non
        if($noteEleve == 0 && strlen($noteEleve) <= 1)
        {
          $note->setNote(0);
          $note->setParticipation(TRUE);
        }
        elseif($noteEleve > 0 && $noteEleve <= 10)
        {
          $note->setNote($noteEleve);
          $note->setParticipation(TRUE);
        }
        elseif(empty($noteEleve))
        {
          $note->setNote(0);
          $note->setParticipation(FALSE);
        }
        $note->setCreatedBy($this->getUser());
        $note->setCreatedAt(new \Datetime());
        // return new Response("C'est OK");
        $em->persist($note);
      } // Fin foreach $data['note']
      
      try{
        $em->flush();
        $this->calculMoyenneExamen($eleves, $examen, $classe, $enseignements);
        $request->getSession()->getFlashBag()->add('info', 'Les notes  des élèves de '.$classe->getNiveau()->getLibelleFr().' - '.$classe->getLibelleFr().' en '.$matiere->getLibelle().' ont été bien enregistrées.');
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }
      return $this->redirect($this->generateUrl('isi_saisie_de_notes_de_la_classe', [
        'as'       => $as,
        'regime'   => $regime,
        'classeId' => $classeId,
        'examenId' => $examenId
      ]));
    }

    return $this->render('ISIBundle:Examen:formulaire-de-saisie-de-notes.html.twig', [
      'asec'      => $as,
      'regime'    => $regime,
      'annee'     => $annee,
      'classe'    => $classe,
      'eleves'    => $eleves,
      'matiere'   => $matiere,
      'examen'    => $examen,
      // 'form'      => $form->createView(),
    ]);
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function editerNotesAction(Request $request, $as, $regime, $classeId, $examenId, $matiereId)
  {
    $em                = $this->getDoctrine()->getManager();
    $repoAppreciation  = $em->getRepository('ISIBundle:Appreciation');
    $repoAnnee         = $em->getRepository('ISIBundle:Annee');
    $repoEleve         = $em->getRepository('ISIBundle:Eleve');
    $repoExamen        = $em->getRepository('ISIBundle:Examen');
    $repoClasse        = $em->getRepository('ISIBundle:Classe');
    $repoMatiere       = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne       = $em->getRepository('ISIBundle:Moyenne');
    $repoNote          = $em->getRepository('ISIBundle:Note');
    $repoFM            = $em->getRepository('ISIBundle:FrequenterMatiere');
    $repoFrequenter    = $em->getRepository('ISIBundle:Frequenter');
    $repoEnseignement  = $em->getRepository('ISIBundle:Enseignement');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');
    
    $annee         = $repoAnnee->find($as);
    $eleves        = $repoEleve->elevesDeLaClasse($as, $classeId);
    $examen        = $repoExamen->find($examenId);
    $matiere       = $repoMatiere->find($matiereId);
    $moyenne       = $repoMoyenne->findOneBy(['eleve'        => $eleves[0]->getId(), 'examen' => $examenId]);
    $classe        = $repoClasse->find($classeId);
    $niveauId      = $classe->getNiveau()->getId();
    $moyenneClasse = $repoMoyenneclasse->findOneBy(["classe" => $classeId, "examen"           => $examenId]);
    $enseignements = $repoEnseignement->findBy(['annee' => $as, 'niveau' => $niveauId]);

    /**
     * Quand l'année scolaire est finie, on doit plus faire de
     * mofication au niveau des notes
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des notes car l\'année scolaire '.$annee->getLibelle().' est achevée.');
      return $this->redirect($this->generateUrl('isi_saisie_de_notes', ['as' => $as, 'regime' => $regime]));
    }

    // Pour chaque élève on va sélectionner la note
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);

    // Sélection des notes pour les envoyer en mode édition
    // $notes = $repoNote->notesEnEdition($examenId, $matiereId, $elevesIds);
    $notes = [];
    foreach ($eleves as $eleve) {
      $eleveId = $eleve->getId();
      $notes[$eleveId] = $repoNote->findOneBy(['examen' => $examenId, 'matiere' => $matiere, 'eleve' => $eleveId]);
    }

    // Quand on soumet le formulaire
    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      $noteMAJ = 0;
      foreach($data['note'] as $key => $noteEleve)
      {
        // On vérifie qu'aucune des notes saisies n'est pas supérieures à 10.
        if($noteEleve > 10)
        {
          $eleveNoteSup = $eleve;
          $request->getSession()->getFlashBag()->add('error', 'Vérifiez bien les notes saisies. Aucune note ne doit être supérieur à 10');
          return new Response('La note de cet élève est supérieur à 10: '.$noteEleve);
        }

        foreach($eleves as $eleve)
        {
          $eleveId = $eleve->getId();
          if($eleveId == $key)
          {
            $note = $repoNote->findOneBy(['examen' => $examenId, 'eleve' => $key, 'matiere' => $matiereId]);
            if(empty($note))
            {
              $noteMAJ = $noteMAJ + 1;
              $note = new Note();
              $note->setExamen($examen);
              $note->setEleve($eleve);
              $note->setMatiere($matiere);
              $note->setCreatedBy($this->getUser());
              $note->setCreatedAt(new \Datetime());

              // On va déterminer l'appréciation de la note obtenue
              $appreciationId = $this->findAppreciation($noteEleve, 10);
              $appreciation = $repoAppreciation->find($appreciationId);
              $note->setAppreciation($appreciation);

              // Cette condition me permet de savoir si l'élève à composé dans la matière ou non
              if($noteEleve == 0 && strlen($noteEleve) <= 1)
              {
                $note->setNote(0);
                $note->setParticipation(TRUE);
              }
              elseif($noteEleve > 0 && $noteEleve <= 10)
              {
                $note->setNote($noteEleve);
                $note->setParticipation(TRUE);
              }
              elseif(empty($noteEleve))
              {
                $note->setNote(0);
                $note->setParticipation(FALSE);
              }
              $em->persist($note);
            }
            /**
             * Très bien, ici on va s'amuser un peu. Prêt ? On y va!
             * Après avoir sélectionner une note, on va comparer sa valeur (qui est en réalité l'ancienne valeur)
             * à la valeur saisie (la nouvelle valeur donc). S'il y a une différence, on fait l'update.
             * Beuh sinon, on ne fait rien.
             */
            if($note->getNote() != $noteEleve)
            {
              $noteMAJ = $noteMAJ + 1;
              $appreciationId = $this->findAppreciation($noteEleve, 10);
              $appreciation = $repoAppreciation->find($appreciationId);
              $note->setAppreciation($appreciation);
              // Cette condition me permet de savoir si l'élève à composé dans la matière ou non
              if($noteEleve == 0 && strlen($noteEleve) == 1)
              {
                $note->setNote(0);
                $note->setParticipation(TRUE);
              }
              elseif($noteEleve > 0 && $noteEleve <= 10)
              {
                $note->setNote($noteEleve);
                $note->setParticipation(TRUE);
              }
              elseif(empty($noteEleve))
              {
                $note->setNote(0);
                $note->setParticipation(FALSE);
              }
              $note->setUpdatedBy($this->getUser());
              $note->setUpdatedAt(new \Datetime());

              // Si la session de l'examen en cours vaut 2, alors il faudra calculer la moyenne annuelle pour la matière
              if($examen->getSession() == 2)
              {
                $matiereId = $note->getMatiere()->getId();
                $fq = $repoFrequenter->findOneBy(["eleve" => $eleveId, "annee" => $as]);
                $fm = $repoFM->findOneBy(["frequenter" => $fq->getId(), "matiere" => $matiereId]);
                $nt = $repoNote->findOneBy(["eleve" => $eleveId, "examen" => $examenId - 1, "matiere" => $matiereId]);
                $moyenneDeLaMatiere = $note->getNote() + $nt->getNote();
                $validation = ($moyenneDeLaMatiere >= 11) ? TRUE : FALSE ;
                if(!empty($fm))
                {
                  $fm->setMoyenne($moyenneDeLaMatiere);
                  $fm->setValidation($validation);
                  $fm->setUpdatedBy($this->getUser());
                  $fm->setUpdatedAt(new \Datetime());
                }
                // return new Response("C'est OK");
              }
            } // Fin de if ($note->getNote() != $noteEleve)
          }
        }
      }
      try{
        $em->flush();
        $res = $this->calculMoyenneExamen($eleves, $examen, $classe, $enseignements);
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      if($noteMAJ != 0)
      {
        // return new Response('Nombre de note modifiées : '.$noteMAJ);
        $request->getSession()->getFlashBag()->add('info', 'La mise à jour des notes des élèves de '.$classe->getNiveau()->getLibelleFr().' - '.$classe->getLibelleFr().' en '.$matiere->getLibelle().' s\'est terminée avec succès. N\'oubliez de recalculer les moyennes.');
      }
      else{
        // return new Response('Aucune note modifiée : '.$noteMAJ);
        $request->getSession()->getFlashBag()->add('info', 'Aucune n\'a été modifié.');
      }

      return $this->redirect($this->generateUrl('isi_saisie_de_notes_de_la_classe', [
        'as'       => $as,
        'regime'   => $regime,
        'notes'    => $notes,
        'classeId' => $classeId,
        'examenId' => $examenId
      ]));
    }


    return $this->render('ISIBundle:Examen:formulaire-d-edition-de-notes.html.twig', [
      'asec'      => $as,
      'regime'    => $regime,
      'annee'     => $annee,
      'classe'    => $classe,
      'eleves'    => $eleves,
      'matiere'   => $matiere,
      'examen'    => $examen,
      'notes'     => $notes
    ]);
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function accueilResultatsDExamenAction($as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoExamen = $em->getRepository('ISIBundle:Examen');

    $classes = $repoClasse->classeGrpFormation($as, $regime);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);
    $examens = $repoExamen->lesExamensDeLAnnee($as);
    $examen  = $repoExamen->dernierExamen($as);
    $annee   = $repoAnnee->find($as);
    return $this->render('ISIBundle:Examen:resultats-d-examen.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classes' => $classes,
      'niveaux' => $niveaux,
      'examen'  => $examen
    ]);
  }

  // On va se permettre d'afficher quelques données statistiques d'examen
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function statistiquesAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoExamen = $em->getRepository('ISIBundle:Examen');

    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    $defaultData = ['message' => 'Rien du tout'];
    $form = $this->createFormBuilder($defaultData);

    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      // return new Response(var_dump($data));
      return $this->redirect($this->generateUrl('isi_generation_donnees_statiqtiques', [
        'as'       => $as,
        'regime'   => $regime,
        'examenId' => $data['examen']
      ]));
    }

    $examens = $repoExamen->lesExamensDeLAnnee($as);
    $annee   = $repoAnnee->find($as);
    return $this->render('ISIBundle:Examen:statistiques-examen-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'examens' => $examens,
      'niveaux' => $niveaux,
    ]);
  }

  // On va se permettre d'afficher quelques données statistiques d'examen
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function resultatsAnnuelsAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');

    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    $defaultData = ['message' => 'Rien du tout'];
    $form = $this->createFormBuilder($defaultData);

    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      // return new Response(var_dump($data));
      $classe   = $data['classe'];
      $resultat = $data['resultat'];
      switch ($resultat) {
        case 'notes':
          return $this->redirect($this->generateUrl('isi_notes_des_deux_examens', [
            'as'       => $as,
            'regime'   => $regime,
            'classeId' => $classe,
          ]));
          break;

        case 'bulletins':
          return $this->redirect($this->generateUrl('isi_bulletins_moyennes_annuelles', [
            'as'       => $as,
            'regime'   => $regime,
            'classeId' => $classe,
          ]));
          break;

        default:
          return $this->redirect($this->generateUrl('isi_statistiques_generaux', [
            'as'       => $as,
            'regime'   => $regime,
            'classeId' => $classe,
          ]));
          break;
      }
    }

    $examens = $repoExamen->lesExamensDeLAnnee($as);
    if (count($examens) < 2) {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels pour le moment.');
      return $this->redirect($this->generateUrl('isi_affaires_scolaires', ['as' => $as, 'regime' => $regime]));
    }
    $annee   = $repoAnnee->find($as);
    $classes = $repoClasse->classeGrpFormation($as, $regime);
    // Les moyennes des classes à l'examen
    $mc = [];
    foreach ($classes as $classe) {
      # code...
      $classeId = $classe->getId();
      $moyenne = $repoMoyenneclasse->findOneBy(["classe" => $classeId, "examen" => $examens[1]->getId()]);
      if(is_null($moyenne) or is_null($moyenne->getAdmis()))
        $mc[$classeId] = FALSE;
      else
        $mc[$classeId] = TRUE;
    }
    return $this->render('ISIBundle:Examen:resultats-annuels-home.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'classes'  => $classes,
      'niveaux'  => $niveaux,
      'moyenneC' => $mc,
    ]);
  }

  // Function qui détermine le passage (l'admission) en classe supérieure
  public function admission($nmsr, $nmr)
  {
    if ($nmsr >= 3) {
      # code...
      $decision = 0;
    }
    elseif ($nmsr == 2 && $nmr > 0) {
      # code...
      $decision = 0;
    }
    elseif ($nmsr == 1 && $nmr >= 3) {
      # code...
      $decision = 0;
    }
    elseif ($nmr >= 5) {
      # code...
      $decision = 0;
    }
    else {
      # code...
      $decision = 1;
    }

    return $decision;
  }

  // Page d'accueil des résultats annuels d'une seule classe
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function resultatsAnnuelsDeLaClasseAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoFM      = $em->getRepository('ISIBundle:FrequenterMatiere');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');

    $moyennesPretes = FALSE;

    $annee    = $repoAnnee ->find($as);
    $classe   = $repoClasse->find($classeId);
    $niveauId = $classe->getNiveau()->getId();
    $matieres = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    // On va sélectionner l'examen en cours, qui est en réalité le dernier examen de l'année
    $examen   = $repoExamen->dernierExamen($as);
    $examenId = $examen->getId();

    // Calcul des moyennes de la seconde session
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    // On va sélectionner les entités enseignement du niveau pour les compter. Cela va nous permettre de
    // savoir si on peut commencer le calcul des moyennes ou pas. Car le nombre de notes saisies pour une
    // un élève donné doit être égal au nombre de matière du niveau
    $ens = $repoEnseignement->findBy(['annee' => $as, 'niveau' => $niveauId]);
    // return new Response(var_dump($fq));
    // On va selectionner les notes d'un élève de la classe
    /**
     * S'il y a une note vide alors on refuse le calcul des moyennes
     * et on génère une alerte pour demander à l'utilisateur d'aller
     * saisir toutes les notes des élèves de la classe.
     */
    // Pour cela je crée une variable qui compte le nombre de note null.
    $noteNull  = 0;
    $tableNote = [];
    $notesDUnEleve1 = $repoNote->findBy(['examen' => $examenId - 1, 'eleve' => reset($eleves)['id']]);
    $notesDUnEleve2 = $repoNote->findBy(['examen' => $examenId, 'eleve' => reset($eleves)['id']]);
    /**
     * Lorsque la valeur de $noteNull vaut 0, on pourra commencer le calcul des moyennes.
     * Sinon, on refuse le calcul
     */
    foreach ($notesDUnEleve2 as $note) {
      $tableNote[] = $note->getNote();
      if(is_null($note->getNote()))
        $noteNull++;
    }

    // Je teste maintenant la valeur de $noteNull
    if($noteNull != 0)
    {
      $request->getSession()->getFlashBag()->add('error', 'Toutes les notes n\'ont pas encore été saisies.');
      // return new Response(var_dump($tableNote, reset($eleves)['id'], $eleves[0]->getNomFr()));
      return $this->redirect($this->generateUrl('isi_resultats_d_examens', ['as' => $as, 'regime' => $regime]));
    }

    // return new Response(var_dump($ens));

    if(count($notesDUnEleve1) < count($ens))
    {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels de la classe '.$classe->getNiveau()->getLibelleFr().' '.$classe->getLibelleFr().', car toutes les notes de la session 1 n\'ont pas été saisies.');
      // return new Response('Cool');

      return $this->redirect($this->generateUrl('isi_resultats_annuels', [
        'as'       => $as,
        'regime'   => $regime,
      ]));
    }
    elseif(count($notesDUnEleve2) < count($ens))
    {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels de la classe '.$classe->getNiveau()->getLibelleFr().' '.$classe->getLibelleFr().', car toutes les notes de la session 2  n\'ont pas été saisies.');
      // return new Response('Cool');

      return $this->redirect($this->generateUrl('isi_resultats_annuels', [
        'as'       => $as,
        'regime'   => $regime,
      ]));
    }

    /* Je sélectionne ici la moyenne d'un élève donné. Si le rang et la moyenne sont différents de NULL, alors
     * cela signifie que les calculs n'ont pas enore été effectués: à savoir la moyenne et le rang.
     * Donc dans ce cas on procède aux calculs des moyennes et ensuite à celui des rangs
     */
    $moyenneDUnEleve = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleves[1]['id']]);

    /* Quand on arrive sur cette page pour la première fois, les moyennes ne sont pas encore caldulées
     // C'est pour cela que je fais une sérification, si la moyenne d'un élève vaut null, on procède au calcul des moyennes
     // Lors de la deuxième visite de la page, la moyenne sera différente de null, on passe alors au classement.
     // Cela se fait dans le else if de ce if
     ****************************************************************/

    // if($moyenneDUnEleve->getMoyenne() == null)
    if(empty($moyenneDUnEleve))
    {
      // Le calcul peut commencer
      /**
       * Le calcul se fera pour chaque élève mais aussi on va profiter pour calculer la
       * moyenne de la classe. nombre d'admis, nombre de recalés, pourcentage d'admis
       */
      $admis   = 0;
      $recales = 0;
      // On prend les élèves un par un, pour cela on fait un foreach
      foreach($eleves as $eleve)
      {
        // Initialisation des variables
        $session1 = 1;
        $session2 = 2;

        // Séquence 1:
        // On sélectionne ensuite les notes (toutes les notes) de l'élève en cours, c'est-à-dire dans la boucle
        $eleveId    = $eleve['id'];
        $notes1     = $repoNote->notesDUnEleveLorsDUnExamen($eleveId, $as, $session1);
        $notes2     = $repoNote->notesDUnEleveLorsDUnExamen($eleveId, $as, $session2);
        $frequenter = $repoFrequenter->findOneBy(['eleve' => $eleveId, 'annee' => $as]);

        // Une petite parenthèse pour tester $notes2. Si sa valeur est 0

        // Séquence 2:
        /* On boucle sur les notes de l'élève pour calculer la moyenne.
         * Mais avant, il faut initialiser le total des notes
         */
         $totalNote = 0;
         $moyenneDeLaMatiere = 0;
         $pointsTotalAnnuels = 0;

         /* Soit $nmsr le nombre de matières spécifiques où l'élève est recalés
          * (c'est-à-dire que sa moyenne annuelle est inférieure à 11)
          * et $nmr le nombre de matières (non spécifiques)
          */
         $nmr  = 0;
         $nmsr = 0;

         foreach ($notes2 as $note2) {
           $totalNote = $totalNote + $note2->getNote();

           // On va calculer la moyenne annuelle dans toutes les matières
           foreach ($notes1 as $note1) {
             if($note1->getMatiere()->getId() == $note2->getMatiere()->getId())
             {
               $matiereId = $note1->getMatiere()->getId();
               $matiere   = $repoMatiere->find($matiereId);
               $moyenneDeLaMatiere = $note1->getNote() + $note2->getNote();
               $validation = ($moyenneDeLaMatiere >= 10) ? TRUE : FALSE ;

               foreach ($ens as $en) {
                 if ($en->getMatiere()->getId() == $note1->getMatiere()->getId() and $en->getStatuMatiere() == TRUE)
                 {
                   # Cas des matières spécifiques
                   if ($moyenneDeLaMatiere < 10) {
                     $nmsr++;
                   }
                 }
                 elseif($en->getMatiere()->getId() == $note1->getMatiere()->getId() and $en->getStatuMatiere() == FALSE)
                 {
                   # Cas des matières non spécifiques
                   if ($moyenneDeLaMatiere < 10) {
                     $nmr++;
                   }
                 }
               }

               $fm = new FrequenterMatiere();
               $fm->setFrequenter($frequenter);
               $fm->setMatiere($matiere);
               $fm->setMoyenne($moyenneDeLaMatiere);
               $fm->setValidation($validation);
               $fm->setCreatedBy($this->getUser());
               $fm->setCreatedAt(new \Datetime());
               $em->persist($fm);
             }
           }// Fin de foreach $notes1
         }// Fin de foreach $notes2
        // $totalNote = array_sum($notes->getNote());

        // Maintenant on va déternimer l'admission de l'élève es classe supérieure
        $decision = $this->admission($nmsr, $nmr);
        $frequenter->setAdmission($decision);
        $frequenter->setUpdatedBy($this->getUser());
        $frequenter->setUpdatedAt(new \Datetime());

        // Séquence 3:
        // On calcul alors la moyenne
        $moyenne = $totalNote / count($notes2);
        $moy     = round($moyenne, 2);

        // Séquence 4:
        // On crée la moyenne de l'élève on la persiste
        $moyenne = new Moyenne();
        // $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve->getId()]);;

        // Séquence 5:
        // On renseigne les propriétés de la moyenne
        $moyenne->setEleve($eleve);
        $moyenne->setExamen($examen);
        $moyenne->setTotalPoints($totalNote);
        $moyenne->setMoyenne($moy);
        $moyenne->setCreatedBy($this->getUser());
        $moyenne->setCreatedAt(new \Datetime());

        // On va flusher l'entité moyenne
        $em->persist($moyenne);
        // $em->flush();

        if($moy > 5)
          $admis++;
        else
          $recales++;
      }

      // On crée la moyenne de la classe et on la persiste
      $moyenneClasse = new Moyenneclasse();
      $moyenneClasse->setClasse($classe);
      $moyenneClasse->setExamen($examen);
      $moyenneClasse->setAdmis($admis);
      $moyenneClasse->setRecales($recales);
      $moyenneClasse->setCreatedBy($this->getUser());
      $moyenneClasse->setCreatedAt(new \Datetime());
      // return new Response(var_dump($admis, $recales, $moyenneClasse));
      $em->persist($moyenneClasse);
      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Les résultats sont disponibles.');
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      // $moyennes = [];
      // foreach($eleves as $eleve)
      // {
      //   $moyennes[] = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve->getId()]);
      // }

      // On va créé une variable qui nous permettra de savoir si les moyennes et classements annuels ont été faits
      return $this->render('ISIBundle:Examen:resultats-annuels-de-la-classe.html.twig', [
        'asec'   => $as,
        'annee'  => $annee,
        'regime' => $regime,
        'classe' => $classe,
        'moyennesPretes' => $moyennesPretes,
      ]);
    }
    // Fin de la condition qui vérifie que les moyennes ont été calculées
    /**
     * Nous allons nous retrouver dans cette condition si et seulement si on avait déjà saisie les notes
     * et qu'on avait déjà calculé les moyennes. Et suite à cela, il y a eu une modification des notes.
     * Alors on revient ici pour reclaculer les moyennes
     */
    elseif(is_null($moyenneDUnEleve->getMoyenne())) {
      // Le calcul peut commencer
      /**
       * Le calcul se fera pour chaque élève mais aussi on va profiter pour calculer la
       * moyenne de la classe. nombre d'admis, nombre de recalés, pourcentage d'admis
       */
      $admis   = 0;
      $recales = 0;
      // On prend les élèves un par un, pour cela on fait un foreach
      foreach($eleves as $eleve)
      {
        // Séquence 1:
        // On sélectionne ensuite les notes (toutes les notes) de l'élève en cours, c'est-à-dire dans la boucle
        // $notes = $repoNote->findBy(['examen' => $examenId, 'eleve' => $eleve->getId()]);
        $nmr      = 0;
        $nmsr     = 0;
        $session1 = 1;
        $session2 = 2;
        $eleveId  = $eleve['id'];
        $notes1     = $repoNote->notesDUnEleveLorsDUnExamen($eleveId, $as, $session1);
        $notes2     = $repoNote->notesDUnEleveLorsDUnExamen($eleveId, $as, $session2);
        $frequenter = $repoFrequenter->findOneBy(['eleve' => $eleveId, 'annee' => $as]);

        // Séquence 2:
        /* On boucle sur les notes2 de l'élève pour calculer la moyenne de la seconde session.
         * Mais avant, il faut initialiser le total des notes et le nombre de
         */
         $totalNote = 0;
         $moyenneDeLaMatiere = 0;
         foreach ($notes2 as $note2) {
           $totalNote = $totalNote + $note2->getNote();

           // On va calculer la moyenne annuelle dans toutes les matières
           foreach ($notes1 as $note1) {
             if($note1->getMatiere()->getId() == $note2->getMatiere()->getId())
             {
               $matiereId = $note1->getMatiere()->getId();
               $matiere   = $repoMatiere->find($matiereId);
               $moyenneDeLaMatiere = $note1->getNote() + $note2->getNote();
               $validation = ($moyenneDeLaMatiere >= 10) ? TRUE : FALSE ;

               foreach ($ens as $en) {
                 if ($en->getMatiere()->getId() == $note1->getMatiere()->getId() and $en->getStatuMatiere() == TRUE) {
                   # Cas des matières spécifiques
                   if ($moyenneDeLaMatiere < 10) {
                     $nmsr++;
                   }
                 }
                 elseif($en->getMatiere()->getId() == $note1->getMatiere()->getId() and $en->getStatuMatiere() == FALSE) {
                   # Cas des matières non spécifiques
                   if ($moyenneDeLaMatiere < 10) {
                     $nmr++;
                   }
                 }
               }

               $fm = $repoFM->findOneBy(['frequenter' => $frequenter->getId(), 'matiere' => $matiereId]);
               if(empty($fm))
               {
                $validation = ($moyenneDeLaMatiere >= 10) ? TRUE : FALSE ;
                $fm = new FrequenterMatiere();
                $fm->setFrequenter($frequenter);
                $fm->setMatiere($matiere);
                $fm->setMoyenne($moyenneDeLaMatiere);
                $fm->setValidation($validation);
                $fm->setCreatedBy($this->getUser());
                $fm->setCreatedAt(new \Datetime());
                $em->persist($fm);
               }
               else{
                $fm->setMoyenne($moyenneDeLaMatiere);
                $fm->setValidation($validation);
                $fm->setUpdatedBy($this->getUser());
                $fm->setUpdatedAt(new \Datetime());
               }
             }
           }// Fin de foreach $notes1
         }// Fin de foreach $notes2
        // $totalNote = array_sum($notes->getNote());

        // Maintenant on va déternimer l'admission de l'élève es classe supérieure
        $decision = $this->admission($nmsr, $nmr);
        $frequenter->setAdmission($decision);
        $frequenter->setUpdatedBy($this->getUser());
        $frequenter->setUpdatedAt(new \Datetime());

        // Séquence 3:
        // On calcul alors la moyenne
        $moyenne = $totalNote / count($notes2);
        $moy     = round($moyenne, 2);

        // Séquence 4:
        // On crée la moyenne de l'élève on la persiste
        $moyenne = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleveId]);
        // $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve->getId()]);

        // Séquence 5:
        // On renseigne les propriétés de la moyenne
        $moyenne->setEleve($eleve);
        $moyenne->setExamen($examen);
        $moyenne->setTotalPoints($totalNote);
        $moyenne->setMoyenne($moy);
        $moyenne->setUpdatedBy($this->getUser());
        $moyenne->setUpdatedAt(new \Datetime());

        if($moy > 5)
          $admis++;
        else
          $recales++;
      }

      // On crée la moyenne de la classe et on la persiste
      $moyenneClasse = $repoMoyenneclasse->findOneBy(['classe' => $classeId, 'examen' => $examenId]);
      if(empty($moyenneClasse))
      {
        $moyenneClasse = new Moyenneclasse();
        $moyenneClasse->setClasse($classe);
        $moyenneClasse->setExamen($examen);
        $moyenneClasse->setAdmis($admis);
        $moyenneClasse->setRecales($recales);
        $moyenneClasse->setCreatedBy($this->getUser());
        $moyenneClasse->setCreatedAt(new \Datetime());
        //return new Response(var_dump($admis, $recales, $moyenneClasse));
        $em->persist($moyenneClasse);
      }
      $moyenneClasse->setClasse($classe);
      $moyenneClasse->setExamen($examen);
      $moyenneClasse->setAdmis($admis);
      $moyenneClasse->setRecales($recales);
      $moyenneClasse->setUpdatedBy($this->getUser());
      $moyenneClasse->setUpdatedAt(new \Datetime());
      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Vous devez maintenant calculer les résultats annuels.');
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      // $moyennes = [];
      // foreach($eleves as $eleve)
      // {
      //   $moyennes[] = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve->getId()]);
      // }

      return $this->render('ISIBundle:Examen:resultats-annuels-de-la-classe.html.twig', [
        'asec'   => $as,
        'annee'  => $annee,
        'regime' => $regime,
        'classe' => $classe,
        'moyennesPretes' => $moyennesPretes,
      ]);
    }

    // Séquence 6:
    // On va profiter pour calculer la moyenne annuelle de chaque élève
    // Ici, il faudra faire un test pour voir si les moyennes annuelles ont été calculées ou pas.
    // Si elles sont calculées, alors la valeur de $moyennesPretes passera à TRUE
    $moyenneAnnuelleDUnEleve = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => reset($eleves)['id']]);
    $moyennesPretes = (is_null($moyenneAnnuelleDUnEleve->getMoyenneAnnuelle())) ? FALSE : TRUE ;
    return $this->render('ISIBundle:Examen:resultats-annuels-de-la-classe.html.twig', [
      'asec'   => $as,
      'annee'  => $annee,
      'regime' => $regime,
      'classe' => $classe,
      'moyennesPretes' => $moyennesPretes,
    ]);
  }

  // Voir les résultats annuels avec un template php
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function resultYearAction()
  {
    return $this->render('ISIBundle:Examen:results-year.html.php');
  }

  // Calcul des moyennes annuelles et classement annuels
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function moyennesClassementAnnuelAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoFM      = $em->getRepository('ISIBundle:FrequenterMatiere');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    $annee    = $repoAnnee->find($as);
    $classe   = $repoClasse->find($classeId);
    $examens  = $repoExamen->lesExamensDeLAnnee($as);
    $examenId = $examens[1]->getId();

    // On va calculer les moyennes annuelles et faire le classement annuel
    // Séance 1:
    // On sélectionne d'abord les élèves de la classe
    
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    // return new Response(var_dump(json_encode($eleves)));

    $moyenneDUnEleve = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => reset($eleves)['id']]);
    // return new Response(var_dump($moyenneDUnEleve));
    // Séance 2:
    // Pour chaque élève, on va sélectionner les notes de la première et de la session de l'année et les entités Frequenter
    if($moyenneDUnEleve->getRang() == NULL) {
      // On va initialiser un tableau qui va récupérer, pour chaque élève, la moyenne
      $moyennes = [];
      foreach($eleves as $eleve)
      {
        $eleveId = $eleve['id'];
        $totalMoyennesDesDifferentesMatieres = 0;
        $moyenne = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleveId]);
        // On va calculer les moyennes annuelles ici
        $frequenter = $repoFrequenter->findOneBy(['annee' => $as, 'eleve' => $eleveId]);
        $fms = $repoFM->findBy(['frequenter' => $frequenter->getId()]);
        foreach ($fms as $fq) {
          $totalMoyennesDesDifferentesMatieres = $totalMoyennesDesDifferentesMatieres + $fq->getMoyenne();
        }
        $moyenneAnnuelle = $totalMoyennesDesDifferentesMatieres / count($fms);
        $moyenneAnnuelle = round($moyenneAnnuelle, 2);

        $moyenne->setMoyenneAnnuelle($moyenneAnnuelle);
        $moyennes[] = $moyenne;
      }
      // return new Response(var_dump($moyenneAnnuelle));
      // return new Response('Cool');
      // $em->flush();
      // return new Response(var_dump($moyennes[0]));
      // ****************  1 - Première étape - 1   ***************** //
      // A ce niveau toutes les moyennes sont enregistrées. On boucle encore sur les moyenne pour
      // les ranger du plus grand au plu petit. Al hamdoulillah la fonction array_multisort permet
      // de gérer cela efficacement
      foreach ($moyennes as $key => $moyenne) {
        if(!empty($moyenne))
        {
          $valeur_moyenne[$key] = $moyenne->getMoyenne();
          $id[$key]             = $moyenne->getId();
        }
        else{
          unset($moyennes[array_search($moyenne, $moyennes)]);
        }
      }

      // array_multisort() permet de trier un tableau multidimensionnel
      array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);
      // return new Response(var_dump($moyennes));

      // ****************  2 - Deuxième étape - 2   ***************** //
      // On va maintenant persister le rang de chaque élève
      /**
       * Mais attention!!! Il faudra gérer les ex aequo
       * Pour faire cela, je vais initialiser des variables:
       * $ex : une booléenne qui vaut TRUE si l'on rencontre des valeurs identiques lors du parcours du tableau des moyennes
       * $nbrEx: une entière qui compte le nombre d'ex aequo, c'est-à-dire le nombre de moyennes identiques
       * $r quant à elle, elle ne sera pas initialisée, elle prendra la valeur du rang à sauvegarder en bd en fonction des ex aequo
       */
       $ex = FALSE;
       $continuer = FALSE;
       $nbrEx = 0;
      foreach($moyennes as $key => $rang)
      {
        // On va définir la valeur de la clé pour laquelle le test de comparaison s'arrête.
        // Si la clé de parcours du tableau prend la dernière clé, on arrête le test
        $limit = count($moyennes) - 1;
        if($key < $limit && $continuer == FALSE) {
          /**
           * Lorsque je parcours le tableau, je compare la moyenne en cours à la moyenne suivante et
           * je compare aussi la valeur de la $ex
           */
           if($moyennes[$key]->getMoyenne() == $moyennes[$key + 1]->getMoyenne() && $ex == TRUE)
           {
             /**
              * Si la moyenne en cours est identique à la moyenne suivante et qu'il y a eu un ex aequo, on va
              * incrémenter la valeur de ex aequo ($nbrEx) et on donne toujours la valeur TRUE à $ex.
              * Le rang prend alors la valeur de la clé ($key) plus un moins le nombre d'ex aequo ($nbrEx)
              */
              $r     = $key + 1 - $nbrEx;
              $ex    = TRUE;
              $nbrEx = $nbrEx + 1;
           }
           elseif($moyennes[$key]->getMoyenne() == $moyennes[$key + 1]->getMoyenne() && $ex == FALSE)
           {
             /**
              * Si la moyenne en cours est identique à la moyenne suivante et qu'il n'y a pas eu d'ex aequo, alors
              * on incrémente le nombre d'ex aequo ($nbrEx), $ex prend TRUE (car les valeur testées sont identiques).
              * Le rang prend alors la valeur de la clé de parcours ($key) plus un
              */
              $r     = $key + 1;
              $ex    = TRUE;
              $nbrEx = $nbrEx + 1;
           }
           elseif($moyennes[$key]->getMoyenne() != $moyennes[$key + 1]->getMoyenne() && $ex == TRUE)
           {
             /**
              * Si la moyenne en cours diffère de la moyenne suivante et qu'il y a eu un ex aequo alors, la valeur de
              * ex aequo prendra FALSE, le nombre d'ex aequo sera écrasé ($ex = 0) et le rang aura comme valeur
              * la valeur de la clé de parcours plus un moins le nombre d'ex aequo
              */
              $r     = $key + 1 - $nbrEx;
              $ex    = FALSE;
              $nbrEx = 0;
           }
           else {
             # Et enfin, si les deux moyennes testées ne sont pas identiques et qu'il n'y pas d'ex aequo...
             # Beuh... le calcul se déroule normalement
             $r     = $key + 1;
             $ex    = FALSE;
             $nbrEx = $nbrEx;
           }
          // return new Response("Le rang de ".$rang['eleve']->getNomFr()." est ".$key." avec ".$rang['moyenne']." de moyenne.");
          $rang->setUpdatedBy($this->getUser());
          $rang->setUpdatedAt(new \Datetime());
          $rang->setRang($r);
        }
        else {
          // A ce stade, le dernier rang du tableau n'est pas renseigné.
          // On va donc faire un petit test
          if($ex == TRUE){
            $r = $key + 1 - $nbrEx;
          }
          else {
            $r = $key + 1;
          }
          $rang->setUpdatedBy($this->getUser());
          $rang->setUpdatedAt(new \Datetime());
          $rang->setRang($r);
          $request->getSession()->getFlashBag()->add("info", "Le classement de la deuxième session à été fait.");
          $continuer = TRUE;
        }
      }// Fin de la boucle foreach
      try{
        $em->flush();
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      // Début du classement annuel
      foreach ($moyennes as $key => $moyenne) {
        if(!empty($moyenne))
        {
          $valeur_moyenne[$key] = $moyenne->getMoyenneAnnuelle();
          $id[$key]             = $moyenne->getId();
        }
        else{
          unset($moyennes[array_search($moyenne, $moyennes)]);
        }
      }
      array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);
      // return new Response(var_dump($moyennes));

      // ****************  2 - Deuxième étape - 2   ***************** //
       $ex = FALSE;
       $continuer = FALSE;
       $nbrEx = 0;
      foreach($moyennes as $key => $rang)
      {
        // On va définir la valeur de la clé pour laquelle le test de comparaison s'arrête.
        // Si la clé de parcours du tableau prend la dernière clé, on arrête le test
        $limit = count($moyennes) - 1;
        if($key < $limit && $continuer == FALSE) {
          /**
           * Lorsque je parcours le tableau, je compare la moyenne en cours à la moyenne suivante et
           * je compare aussi la valeur de la $ex
           */
           if($moyennes[$key]->getMoyenneAnnuelle() == $moyennes[$key + 1]->getMoyenneAnnuelle() && $ex == TRUE)
           {
              $r     = $key + 1 - $nbrEx;
              $ex    = TRUE;
              $nbrEx = $nbrEx + 1;
           }

           elseif($moyennes[$key]->getMoyenneAnnuelle() == $moyennes[$key + 1]->getMoyenneAnnuelle() && $ex == FALSE)
           {
              $r     = $key + 1;
              $ex    = TRUE;
              $nbrEx = $nbrEx + 1;
           }
           elseif($moyennes[$key]->getMoyenneAnnuelle() != $moyennes[$key + 1]->getMoyenneAnnuelle() && $ex == TRUE)
           {
              $r     = $key + 1 - $nbrEx;
              $ex    = FALSE;
              $nbrEx = 0;
           }
           else {
             $r     = $key + 1;
             $ex    = FALSE;
             $nbrEx = $nbrEx;
           }
          // return new Response("Le rang de ".$rang['eleve']->getNomFr()." est ".$key." avec ".$rang['moyenne']." de moyenne.");
          $rang->setUpdatedBy($this->getUser());
          $rang->setUpdatedAt(new \Datetime());
          $rang->setClassementAnnuel($r);
        }
        else {
          // A ce stade, le dernier rang du tableau n'est pas renseigné.
          // On va donc faire un petit test
          if($ex == TRUE){
            $r = $key + 1 - $nbrEx;
          }
          else {
            $r = $key + 1;
          }
          $rang->setUpdatedBy($this->getUser());
          $rang->setUpdatedAt(new \Datetime());
          $rang->setClassementAnnuel($r);
          $request->getSession()->getFlashBag()->add("info", "Le classement annuel aussi à été fait.");
          $continuer = TRUE;
        }
      }// Fin de la boucle foreach
      try{
        $em->flush();
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }
      // Fin du classement annuel
      // return new Response(var_dump($moyennes));
      return $this->render('ISIBundle:Examen:classement-annuel-de-la-classe.html.twig', [
        'asec'     => $as,
        'regime'   => $regime,
        'annee'    => $annee,
        'classe'   => $classe,
        'eleves'   => $eleves,
        'moyennes' => $moyennes,
        'examenId' => $examenId,
      ]);
    }
    /*
     * A partir de la troisième visite de cette même page, on ne fait qu'afficher les résultats
     * En effet, les moyennes sont déjà calculées (voir le if de cette condition)
     * Et le classement est déjà fait (voir le else if de cette condition)
     * Il ne reste donc plus qu'à afficher les résultats
     */
    else{
      $moyennes = [];
      foreach($eleves as $eleve)
      {
        $moyennes[] = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve['id']]);
      }

      // On fait encore un arrangement dans l'ordre décroissant des moyennes
      foreach ($moyennes as $key => $moyenne) {
        if(!empty($moyenne))
        {
          $valeur_moyenne[$key] = $moyenne->getMoyenne();
          $id[$key]             = $moyenne->getId();
        }
        else{
          unset($moyennes[array_search($moyenne, $moyennes)]);
        }
      }
      array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);

      // return new Response(var_dump($moyennes));
      return $this->render('ISIBundle:Examen:classement-annuel-de-la-classe.html.twig', [
        'asec'     => $as,
        'regime'   => $regime,
        'annee'    => $annee,
        'classe'   => $classe,
        'eleves'   => $eleves,
        'moyennes' => $moyennes,
        'examenId' => $examenId,
      ]);
    }
  }

  // On pourra afficher toutes les notes des élèves de la classe au cours d'une année
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function notesTousLesExamensAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    // return new Response("C'est cool");

    // Sélection des entités
    $annee     = $repoAnnee->find($as);
    $classe    = $repoClasse->find($classeId);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $examens   = $repoExamen->lesExamensDeLAnnee($as);
    $matieres  = $repoMatiere->lesMatieresDuNiveau($as, $classe->getNiveau()->getId());
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);

    $notes1 = $repoNote->toutesLesNotesDesElevesDeLaClasse($examens[0]->getId(), $elevesIds);
    $notes2 = $repoNote->toutesLesNotesDesElevesDeLaClasse($examens[1]->getId(), $elevesIds);
    if (empty($notes1[0]) || empty($notes2[0])) {
      $request->getSession()->getFlashBag()->add('error', 'Il n\'est pas possible de calculer les moyennes annuelles pour l\'heure.');
      return $this->redirect($this->generateUrl('isi_resultats_annuels', [
        'as'     => $as,
        'regime' => $regime,
      ]));
    }

    $notes = [];
    foreach ($eleves as $eleve) {
      # code...
      $notesEleve['eleve'] = $eleve['nomFr'].' '.$eleve['pnomFr'];
      $tableauNotes = [];
      foreach ($matieres as $matiere) {
        # code...
        $note1 = $repoNote->findOneBy(['eleve' => $eleve['id'], 'examen' => $examens[0]->getId(), 'matiere' => $matiere->getId()]);
        $note2 = $repoNote->findOneBy(['eleve' => $eleve['id'], 'examen' => $examens[1]->getId(), 'matiere' => $matiere->getId()]);
        if(empty($note1) or empty($note2))
          dump($note1, $note2);
        if ($note1->getParticipation() == FALSE && $note2->getParticipation() == FALSE) {
          # code...
          $participation = 1;
        }
        elseif($note1->getParticipation() == TRUE && $note2->getParticipation() == FALSE) {
          # code...
          $participation = 2;
        }
        elseif($note1->getParticipation() == FALSE && $note2->getParticipation() == TRUE) {
          # code...
          $participation = 3;
        }
        else{
          $participation = 4;
        }
        $notesMatiere['participation'] = $participation;
        $notesMatiere['matiere']       = $matiere->getLibelle();
        $notesMatiere['note']          = $note1->getNote() + $note2->getNote();
        $tableauNotes[]                = $notesMatiere;
        $nosotes[]                     = $notesEleve;
      }
    }

    // return new Response(var_dump($notes[0]));
    // return new Response('cool');
    return $this->render('ISIBundle:Examen:notes-des-deux-examens.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'notes'    => $notes,
      'classe'   => $classe,
      'matieres' => $matieres
    ]);
  }

  // Les données  stiques obtenues pour toute l'année
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function statistiquesAnnuellesDUneClasseAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoFrequenter  = $em->getRepository('ISIBundle:Frequenter');

    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $classe    = $repoClasse->find($classeId);
    $examens   = $repoExamen->lesExamensDeLAnnee($as);
    $niveauId  = $classe->getNiveau()->getId();
    $matieres  = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);

    //On va récupérer les ids des élèves
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);
    $moyennes1  = [];
    $moyennes2  = [];
    $frequenter = [];

    foreach ($eleves as $eleve) {
      $eleveId = $eleve['id'];
      $moyennes1[]   = $repoMoyenne->findOneBy(['examen' => $examens[0]->getId(), 'eleve' => $eleveId]);
      $moyennes2[]   = $repoMoyenne->findOneBy(['examen' => $examens[1]->getId(), 'eleve' => $eleveId]);
      $frequenter[]  = $repoFrequenter->findOneBy(['annee' => $as, 'eleve' => $eleveId]);
    }
    // dump($frequenter);
    // On fait encore un arrangement dans l'ordre décroissant des moyennes
    foreach ($moyennes2 as $key => $moyenne) {
      if(!empty($moyenne))
      {
        $moyenne_annuelle[$key] = $moyenne->getMoyenneAnnuelle();        
        $id[$key]             = $moyenne->getId();
      }
      else{
        unset($moyennes2[array_search($moyenne, $moyennes2)]);
      }
    }
    array_multisort($moyenne_annuelle, SORT_DESC, $id, SORT_ASC, $moyennes2);
    // dump($eleves);

    return $this->render('ISIBundle:Examen:statistiques-annuelles-d-une-classe.html.twig', [
      'asec'       => $as,
      'regime'     => $regime,
      'annee'      => $annee,
      'classe'     => $classe,
      'matieres'   => $matieres,
      'moyennes1'  => $moyennes1,
      'moyennes2'  => $moyennes2,
      'frequenter' => $frequenter,
    ]);
  }

  // Bulletin affichant les résultats annuels
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function bulletinsMoyennesAnnuellesAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    
    // Sélection des entités
    $partie    = $request->query->get('partie');
    $annee     = $repoAnnee->find($as);
    $classe    = $repoClasse->find($classeId);
    $niveauId  = $classe->getNiveau()->getId();
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $examens   = $repoExamen->lesExamensDeLAnnee($as);
    $matieres  = $repoMatiere->lesMatieresDuNiveau($as, $classe->getNiveau()->getId());
    $ens       = $repoEnseignement->findBy(['annee'      => $as, 'niveau'       => $niveauId]);
    $examenId  = $examens[1]->getId();
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);

    $session1 = 1;
    $session2 = 2;
    $notes1 = $repoNote->toutesLesNotesDesElevesDeLaClasse($examens[0]->getId(), $elevesIds);
    $notes2 = $repoNote->toutesLesNotesDesElevesDeLaClasse($examens[1]->getId(), $elevesIds);
    if (empty($notes1[0]) || empty($notes2[0])) {
      $request->getSession()->getFlashBag()->add('error', 'Il n\'est pas possible de calculer les moyennes annuelles pour l\'heure.');
      return $this->redirect($this->generateUrl('isi_resultats_annuels', [
        'as'     => $as,
        'regime' => $regime,
      ]));
    }

    /**
     * On va faire quelque chose ici.
     * On va commencer par tester les entités frequenter. Si les moyennes annuelles sont déjà calculées
     *
     *
     ***********/
    foreach ($eleves as $eleve) {
      $moyennes[]   = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve['id']]);
      // $frequenter[] = $repoFrequenter->findOneBy(['annee' => $as, 'eleve' => $eleve->getId()]);
    }
    $frequenter = $repoFrequenter->findBy(['annee' => $as, 'classe' => $classeId]);
    // On fait encore un arrangement dans l'ordre décroissant des moyennes
    foreach ($moyennes as $key => $moyenne) {
      if(!empty($moyenne))
      {
        $valeur_moyenne[$key] = $moyenne->getMoyenneAnnuelle();
        $id[$key]             = $moyenne->getId();
      }
      else{
        unset($moyennes[array_search($moyenne, $moyennes)]);
      }
    }
    array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);

    $effectif = count($moyennes);

    if (!is_null($partie)) {
      $nbr = count($moyennes) / 2;
      $halfEleves = [];
      foreach ($moyennes as $key => $eleve) {
        if($key <= $nbr && $partie == 1)
          $halfEleves[] = $eleve;
        elseif($key > $nbr && $partie == 2)
          $halfEleves[] = $eleve;
      }
      $moyennes = $halfEleves;
    }

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    // $snappy->setOption("orientation", "Landscape");
    $filename = "moyenne-annuelle-des-eleves-".$classe->getLibelleFr();

    $date = $examens[1]->getDateProclamationResultats();
    $dt = strftime("%A %d %B %G", strtotime(date_format($date, 'd F Y')));
    $dt = $this->dateToFrench($dt, "l j F Y");

    $html = $this->renderView('ISIBundle:Examen:bulletins-moyennes-annuelles.html.twig', [
      'dt'         => $dt,
      'ens'        => $ens,
      'asec'       => $as,
      'regime'     => $regime,
      'examen'     => $examens[1],
      'annee'      => $annee,
      'notes1'     => $notes1,
      'notes2'     => $notes2,
      'classe'     => $classe,
      'moyennes'   => $moyennes,
      'matieres'   => $matieres,
      'effectif'   => $effectif,
      'frequenter' => $frequenter,
      'server'   => $_SERVER["DOCUMENT_ROOT"],   
      ]);

    // Tcpdf
    // $this->returnPDFResponseFromHTML($html);

    return new Response(
        $snappy->getOutputFromHtml($html),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"'
        ]
    );
    // return new Response(var_dump($notes));
    // return new Response('cool');
  }
  // Bulletin affichant les résultats annuels
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function bulletinAnnuelIndividuelAction(Request $request, $as, $regime, $classeId, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    
    
    // Sélection des entités
    $fq            = $repoFrequenter->findOneBy(["eleve"     => $eleveId, "annee"             => $as]);
    $annee         = $repoAnnee->find($as);
    $eleve         = $repoEleve->find($eleveId);
    $classe        = $repoClasse->find($classeId);
    $niveauId      = $classe->getNiveau()->getId();
    $examens       = $repoExamen->lesExamensDeLAnnee($as);
    $ens           = $repoEnseignement->findBy(['annee'      => $as, 'niveau'                 => $niveauId]);
    $moyenne       = $repoMoyenne->findOneBy(['examen'       => $examens[1]->getId(), 'eleve' => $eleveId]);
    $moyenneclasse = $repoMoyenneclasse->findOneBy(['examen' => $examens[1]->getId(), 'classe' => $classeId]);

    $notes1   = $repoNote->findBy(['examen' => $examens[0]->getId(), 'eleve' => $eleveId]);
    $notes2   = $repoNote->findBy(['examen' => $examens[1]->getId(), 'eleve' => $eleveId]);
    $effectif = $moyenneclasse->getAdmis() + $moyenneclasse->getRecales();

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    // $snappy->setOption("orientation", "Landscape");
    $filename = $classe->getLibelleFr()." --- moyenne-annuelle-de-".$eleve->getNomFr()."-".$eleve->getPnomFr();
    
    $date = $examens[1]->getDateProclamationResultats();
    $dt = strftime("%A %d %B %G", strtotime(date_format($date, 'd F Y')));
    $dt = $this->dateToFrench($dt, "l j F Y");

    $html = $this->renderView('ISIBundle:Examen:bulletin-moyenne-annuelle-d-un-eleve.html.twig', [
      'dt'       => $dt,
      'asec'     => $as,
      'regime'   => $regime,
      'moyenne'  => $moyenne,
      'eleve'    => $eleve,
      'examen'   => $examens[1],
      'annee'    => $annee,
      'ens'      => $ens,
      'notes1'   => $notes1,
      'notes2'   => $notes2,
      'classe'   => $classe,
      'effectif' => $effectif,
      'fq'       => $fq,
      'server'   => $_SERVER["DOCUMENT_ROOT"],   
      ]);

    return new Response(
        $snappy->getOutputFromHtml($html),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"'
        ]
    );
  }

  /**
   * Cette function de mon controller sera appeler et donc elle  devra retourner
   * les données attendues
   */
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function generationDonneesStatistiquesAction(Request $request, $as, $regime, $examenId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');

    $classes  = $repoClasse->classeGrpFormation($as, $regime);
    $niveaux  = $repoNiveau->niveauxDuGroupe($regime);
    $examen   = $repoExamen->find($examenId);
    $annee    = $repoAnnee->find($as);
    $moyennesClasses = [];
    foreach ($classes as $classe) {
      $moyenneDeLaClasse = $repoMoyenneclasse->findOneBy(['classe' => $classe->getId(), 'examen' => $examenId]);
      is_null($moyenneDeLaClasse) ? :
      $moyennesClasses[] = $repoMoyenneclasse->findOneBy(['classe' => $classe->getId(), 'examen' => $examenId]);
    }

    // return new Response(var_dump($frequentation));

    return $this->render('ISIBundle:Examen:statistiques-examen.html.twig', [
      'asec'            => $as,
      'regime'          => $regime,
      'annee'           => $annee,
      'niveaux'         => $niveaux,
      'examen'          => $examen,
      'moyennesClasses' => $moyennesClasses
    ]);
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function resultatsDeLaClasseAction(Request $request, $as, $regime, $classeId, $examenId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);
    $examen = $repoExamen->find($examenId);

    // La liste des élèves de la classe
    $eleves = $repoEleve->elevesDeLaClasse($as, $classeId);
    if(empty($eleves))
    {
      $request->getSession()->getFlashBag()->add('error', 'Aucun élèle inscrit dans cette classe');
      return $this->redirect($this->generateUrl('isi_saisie_de_notes', ['as' => $as, 'regime' => $regime]));
    }

    // On sélectionne l'id du niveau en fonction de l'id de la classe pour la transmettre en paramètre
    // pour la sélection des matières du niveau
    $classe   = $repoClasse->find($classeId);
    $niveauId = $classe->getNiveau()->getId();

    /**
     * On va compter le nombre de notes d'un des élèves de la classe ($notesDUnEleve).
     * Après cela, on va compter le nombre de matières dispensées dans le niveau ($ens).
     * Si $notesDUnEleve < $ens alors on refuse de les résultats.
    */
    $ens           = $repoEnseignement->findBy(['annee' => $as, 'niveau' => $niveauId]);
    $notesDUnEleve = $repoNote->findBy(['examen' => $examenId, 'eleve' => $eleves[0]->getId()]);
    // return new Response(var_dump(count($notesDUnEleve), count($ens)));
    if(count($notesDUnEleve) < count($ens))
    {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats. Toutes les notes de la session '.$examen->getSession().' n\'ont pas encore été saisies en '.$classe->getLibelleFr().'.');
      // return new Response(var_dump($tableNote, reset($eleves)['id'], $eleves[0]->getNomFr()));
      return $this->redirect($this->generateUrl('isi_saisie_de_notes', ['as' => $as, 'regime' => $regime]));
    }

    if($examen->getSession() == 2)
    {
      // return new Response("Retour 2");
      $request->getSession()->getFlashBag()->add('error', 'Vous devez calculer les résultats dans la "Résultats annuels".');
      return $this->redirect($this->generateUrl('isi_resultats_annuels', ['as' => $as, 'regime' => $regime]));
    }

    $elevesIds = $this->recupererLesIdsDesEleves($eleves);
    $moyennes = $repoMoyenne->lesMoyennesDesElevesDeLaClasse($examenId, $elevesIds);

    // On fait encore un arrangement dans l'ordre décroissant des moyennes
    foreach ($moyennes as $key => $moyenne) {
      if(!empty($moyenne))
      {
        $valeur_moyenne[$key] = $moyenne->getMoyenne();
        $id[$key]             = $moyenne->getId();
      }
      else{
        unset($moyennes[array_search($moyenne, $moyennes)]);
      }
    }
    array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);

    return $this->render('ISIBundle:Examen:resultats-de-la-classe.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'examen'   => $examen,
      'classe'   => $classe,
      'eleves'   => $eleves,
      'moyennes' => $moyennes,
    ]);
  }

  // On imprime (ou si vous voulez on affiche) le bulletin d'un seul élève de la classe
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function bulletinUniqueAction(Request $request, $as, $regime, $classeId, $examenId, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMoyenneclasse  = $em->getRepository('ISIBundle:Moyenneclasse');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    
    /*********** - Etape 1: Sélection des données- ***********/
    $annee         = $repoAnnee->find($as);
    $eleve         = $repoEleve->find($eleveId);
    $classe        = $repoClasse->find($classeId);
    $examen        = $repoExamen->find($examenId);
    $niveauId      = $classe->getNiveau()->getId();
    $ens           = $repoEnseignement->findBy(['annee'      => $as, 'niveau'       => $niveauId]);
    $moyenne       = $repoMoyenne->findOneBy(['examen'       => $examenId, 'eleve'  => $eleveId]);
    $moyenneclasse = $repoMoyenneclasse->findOneBy(['examen' => $examenId, 'classe' => $classeId]);
    $effectif      = $moyenneclasse->getAdmis() + $moyenneclasse->getRecales();

    $date = $examen->getDateProclamationResultats();
    $dt = strftime("%A %d %B %G", strtotime(date_format($date, 'd F Y')));
    $dt = $this->dateToFrench($dt, "l j F Y");

    $notes     = $repoNote->findBy(['examen' => $examenId, 'eleve' => $eleveId]);

    $template = 'ISIBundle:Examen:bulletin.html.twig';
    if($as < 3)
      $template = 'ISIBundle:Examen:bulletin-old.html.twig';
    $html = $this->renderView($template, [
      'dt'       => $dt,
      'ens'      => $ens,
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'eleve'    => $eleve,
      'classe'   => $classe,
      'examen'   => $examen,
      'notes'    => $notes,
      'effectif' => $effectif,
      'moyenne'  => $moyenne,
      'server'   => $_SERVER["DOCUMENT_ROOT"],
    ]);


    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    // $snappy->setOption("orientation", "Landscape");
    $filename = "bulletin-des-eleves-".$classe->getNiveau()->getLibelleFr()."-".$classe->getLibelleFr();

    return new Response(
        $snappy->getOutputFromHtml($html),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"'
        ]
    );
  }

  // Page qui va afficher les bulletins (Mais ici la génération se fait individuellement)
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function bulletinIndividuelAction(Request $request, $as, $regime, $classeId, $examenId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    
    
    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $classe    = $repoClasse->find($classeId);
    $examen    = $repoExamen->find($examenId);
    $niveauId      = $classe->getNiveau()->getId();
    $ens           = $repoEnseignement->findBy(['annee'      => $as, 'niveau'       => $niveauId]);

    foreach ($eleves as $eleve) {
      $moyennes[] = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve['id']]);
    }
    // On fait encore un arrangement dans l'ordre décroissant des moyennes
    foreach ($moyennes as $key => $moyenne) {
      if(!empty($moyenne))
      {
        $valeur_moyenne[$key] = $moyenne->getMoyenne();
        $id[$key]             = $moyenne->getId();
      }
      else{
        unset($moyennes[array_search($moyenne, $moyennes)]);
      }
    }
    // array_multisort() permet de trier un tableau multidimensionnel
    array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);

    $date = $examen->getDateProclamationResultats();
    $dt = strftime("%A %d %B %G", strtotime(date_format($date, 'd F Y')));
    $dt = $this->dateToFrench($dt, "l j F Y");
    $data = [];
    foreach($moyennes as $moy)
    {
      $notes     = $repoNote->findBy(['examen' => $examenId, 'eleve' => $moy->getEleve()->getId()]);
      $html = $this->renderView('ISIBundle:Examen:bulletin.html.twig', [
        'dt'       => $dt,
        'asec'     => $as,
        'regime'   => $regime,
        'annee'    => $annee,
        'ens'      => $ens,
        'classe'   => $classe,
        'examen'   => $examen,
        'notes'    => $notes,
        'effectif' => count($moyennes),
        'moyenne'  => $moy,
        'server'   => $_SERVER["DOCUMENT_ROOT"],
      ]);
      $data[] = $html;
    }

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    // $snappy->setOption("orientation", "Landscape");
    $filename = "bulletin-des-eleves-".$classe->getNiveau()->getLibelleFr()."-".$classe->getLibelleFr();

    return new Response(
        $snappy->getOutputFromHtml($data),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"'
        ]
    );
  }

  // Les bulletins des élèves la de classe
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function bulletinsAction(Request $request, $as, $regime, $classeId, $examenId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoEnseignement  = $em->getRepository('ISIBundle:Enseignement');
    
    /*********** - Etape 1: Sélection des données- ***********/
    $partie    = $request->query->get('partie');
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $classe    = $repoClasse->find($classeId);
    $niveauId  = $classe->getNiveau()->getId();
    $examen    = $repoExamen->find($examenId);
    $niveauId  = $classe->getNiveau()->getId();
    $matieres  = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    $ens       = $repoEnseignement->findBy(['annee'      => $as, 'niveau'       => $niveauId]);
    foreach($ens as $item){
      $enseignements[$item->getMatiere()->getId()] = $item;
    }

    foreach($matieres as $item){
      $matieresNiveau[$item->getId()] = $item;
    }


    //On va récupérer les ids des élèves
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);
    /***************** Changement d'approche *****************/
    // On va sélectionner les notes et les moyennes des élèves et les mettre directement dans un tableau
    $notes     = $repoNote->lesNotesDesElevesDeLaClasse($examenId, $elevesIds);

    foreach ($eleves as $eleve) {
      $moyennes[] = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve['id']]);
    }
    // On fait encore un arrangement dans l'ordre décroissant des moyennes
    foreach ($moyennes as $key => $moyenne) {
      if(!empty($moyenne))
      {
        $valeur_moyenne[$key] = $moyenne->getMoyenne();
        $id[$key]             = $moyenne->getId();
      }
      else{
        unset($moyennes[array_search($moyenne, $moyennes)]);
      }
    }
    array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);

    $effectif = count($moyennes);

    if (!is_null($partie)) {
      $nbr = count($moyennes) / 2;
      $halfEleves = [];
      foreach ($moyennes as $key => $eleve) {
        if($key <= $nbr && $partie == 1)
          $halfEleves[] = $eleve;
        elseif($key > $nbr && $partie == 2)
          $halfEleves[] = $eleve;
      }
      $moyennes = $halfEleves;
    }
    $date = $examen->getDateProclamationResultats();
    $dt = strftime("%A %d %B %G", strtotime(date_format($date, 'd F Y')));
    $dt = $this->dateToFrench($dt, "l j F Y");

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    // $snappy->setOption("orientation", "Landscape");
    $filename = "bulletin-des-eleves-".$classe->getNiveau()->getLibelleFr()."-".$classe->getLibelleFr();


    $template = 'ISIBundle:Examen:bulletins-des-eleves.html.twig';
    if($as < 3)
      $template = 'ISIBundle:Examen:bulletins-des-eleves-old.html.twig';
    $html = $this->renderView($template, [
      // "title" => "Titre de mon document",
      'dt'       => $dt,
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'ens'      => $enseignements,
      'classe'   => $classe,
      'examen'   => $examen,
      'notes'    => $notes,
      'matieres' => $matieresNiveau,
      'moyennes' => $moyennes,
      'effectif' => $effectif,
      'server'   => $_SERVER["DOCUMENT_ROOT"],   
      ]);

    // Tcpdf
    // $this->returnPDFResponseFromHTML($html);

    return new Response(
        $snappy->getOutputFromHtml($html),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"'
        ]
    );
  }

  // Cette page affichera les résultas de façon générale
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function resultatsgenerauxDUneClasseAction(Request $request, $as, $regime, $classeId, $examenId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');

    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $classe    = $repoClasse->find($classeId);
    $examen    = $repoExamen->find($examenId);
    $niveauId  = $classe->getNiveau()->getId();

    //On va récupérer les ids des élèves
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);

    foreach ($eleves as $eleve) {
      $moyennes[] = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve['id']]);
    }
    // On fait encore un arrangement dans l'ordre décroissant des moyennes
    foreach ($moyennes as $key => $moyenne) {
      if(!empty($moyenne))
      {
        $valeur_moyenne[$key] = $moyenne->getMoyenne();
        $id[$key]             = $moyenne->getId();
      }
      else{
        unset($moyennes[array_search($moyenne, $moyennes)]);
      }
    }
    array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);

    return $this->render('ISIBundle:Examen:resultats-generaux-des-eleves-de-la-classe.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'classe'   => $classe,
      'examen'   => $examen,
      'moyennes' => $moyennes,
    ]);
  }


  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function classementAction(Request $request, $as, $regime, $classeId, $examenId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoInformations = $em->getRepository('ISIBundle:Informations');
    
    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $classe    = $repoClasse->find($classeId);
    $examen    = $repoExamen->find($examenId);
    $niveauId  = $classe->getNiveau()->getId();
    $informations = $repoInformations->find(1);
    
    //On va récupérer les ids des élèves
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);
    
    foreach ($eleves as $eleve) {
      $moyennes[] = $repoMoyenne->findOneBy(['examen' => $examenId, 'eleve' => $eleve['id']]);
    }
    // On fait encore un arrangement dans l'ordre décroissant des moyennes
    foreach ($moyennes as $key => $moyenne) {
      if(!empty($moyenne))
      {
        $valeur_moyenne[$key] = $moyenne->getMoyenne();
        $id[$key]             = $moyenne->getId();
      }
      else{
        unset($moyennes[array_search($moyenne, $moyennes)]);
      }
    }
    array_multisort($valeur_moyenne, SORT_DESC, $id, SORT_ASC, $moyennes);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $filename = "classement-de-".$classe->getLibelleFr();
    
    
    $html = $this->renderView('ISIBundle:Examen:classement.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'classe'   => $classe,
      'examen'   => $examen,
      'moyennes' => $moyennes,
      "informations" => $informations,
      'server'   => $_SERVER["DOCUMENT_ROOT"],      
    ]);

    $header = $this->renderView( '::header.html.twig' );
    // $footer = $this->renderView( '::footer.html.twig' );

    $options = [
        'header-html' => $header,
        // 'footer-html' => $footer,
    ];

    // Tcpdf
    // $this->returnPDFResponseFromHTML($html);

    return new Response(
        $snappy->getOutputFromHtml($html, $options),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"'
        ]
    );
  }

  // Bilan annuel
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function bilanAnnuelAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoExamen     = $em->getRepository('ISIBundle:Examen');

    $examens = $repoExamen->lesExamensDeLAnnee($as);
    if (count($examens) < 2) {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels pour le moment.');
      return $this->redirect($this->generateUrl('isi_parametres', ['as' => $as]));
    }

    $annee       = $repoAnnee->find($as);

    return $this->render('ISIBundle:Examen:bilan-annuel-home.html.twig', [
      'asec' => $as,
      'annee' => $annee,
    ]);

  }

  // Bilan annuel
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function bilanAnnuelDUnRegimeAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEleve     = $em->getRepository('ISIBundle:Eleve');
    $repoNiveau     = $em->getRepository('ISIBundle:Niveau');
    $repoClasse     = $em->getRepository('ISIBundle:Classe');
    $repoExamen     = $em->getRepository('ISIBundle:Examen');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    $examens = $repoExamen->lesExamensDeLAnnee($as);
    if (count($examens) < 2) {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels pour le moment.');
      return $this->redirect($this->generateUrl('isi_bilan_annuel_home', ['as' => $as]));
    }

    $annee      = $repoAnnee->find($as);
    $niveaux    = $repoNiveau->niveauxDuGroupe($regime);
    $classes    = $repoClasse->classeGrpFormation($as, $regime);
    $frequenter = $repoFrequenter->elevesDuRegime($as, $regime);

    /**
     * On va compter le nombre de d'occurence de l'entité frequenter où l'attribut admission vaut NULL.
     * S'il vaut 0 alors, on peut passer au bilan. Sinon, beuh...
     */
    $nbr = 0;
    foreach ($frequenter as $fq) {
      # code...
      if ($fq->getAdmission() == NULL) {
        # code...
        $nbr++;
        // return new Response(var_dump($fq->getEleve()));
      }
    }
    // return new Response(var_dump($nbr));
    if ($nbr != 0) {
      # code...
      $request->getSession()->getFlashBag()->add('error', 'Le bilan annuel ne peut être fait car tous les résultats annuels ne sont pas disponibles.');
      return $this->redirect($this->generateUrl('isi_bilan_annuel_home', ['as' => $as]));
    }

    // On fera les traitements de ce côté pour alléger l'affichage des données au niveau des vues
    // On d'abord besoin de savoir le nombre total d'élèves
    // Ensuite, le nombre d'admis et le nombre de recalés.
    // Dans un premier temps, tout ceci se fera de façon globale
    // Par la suite on vera cela par niveau et par classe, par sexe aussi peut être
    // Initialisation des variables
    $i = 0;
    $nbrTotalAdmis = 0;
    $nbrTotalRecales = 0;
    $nbrTotalAdmisFeminin = 0;
    $nbrTotalAdmisMasculin = 0;
    $nbrTotalRecalesFeminin = 0;
    $nbrTotalRecalesMasculin = 0;
    $statistiquesClasses = [];
    $statistiquesNiveaux = [];
    foreach ($niveaux as $niveau) {

      # code...
      $effectifNiveau = 0;
      $totalFeminin = 0;
      $totalMasculin = 0;
      $totalAdmisNiveau = 0;
      $totalFemininAdmisNiveau = 0;
      $totalMasculinAdmisNiveau = 0;

      $totalRecalesNiveau = 0;
      $totalFemininRecalesNiveau = 0;
      $totalMasculinRecalesNiveau = 0;
      foreach ($classes as $classe) {
        # code...
        $statClasse  = [];
        $admisClasse = 0;
        $recalesClasse = 0;
        $effectifClasse = 0;
        foreach ($frequenter as $fq) {
          # code...
          if ($fq->getClasse()->getId() == $classe->getId() and $classe->getNiveau()->getId() == $niveau->getId())
          {
            # code...
            $effectifClasse++;
            $effectifNiveau++;
            if ($fq->getEleve()->getSexe() == 1 and $fq->getAdmission() == 1) {
              # code...
              $admisClasse++;
              $nbrTotalAdmisMasculin++;
              $totalMasculinAdmisNiveau++;
            } elseif ($fq->getEleve()->getSexe() == 2 and $fq->getAdmission() == 1) {
              # code...
              $admisClasse++;
              $nbrTotalAdmisFeminin++;
              $totalFemininAdmisNiveau++;
            }

            if ($fq->getEleve()->getSexe() == 1 and $fq->getAdmission() == 0) {
              # code...
              $recalesClasse++;
              $nbrTotalRecalesMasculin++;
              $totalMasculinRecalesNiveau++;
            } elseif ($fq->getEleve()->getSexe() == 2 and $fq->getAdmission() == 0) {
              # code...
              $recalesClasse++;
              $nbrTotalRecalesFeminin++;
              $totalFemininRecalesNiveau++;
            }

            $fq->getEleve()->getSexe() == 1 ? $totalMasculin++ : $totalFeminin++ ;
          }

        }// Fin de foreach $frequenterF
        /* Il faut créer un tableau à cet enfroit pour enregistrer pour chaque classe les stats */
        $recalesClasse = $effectifClasse - $admisClasse;
        $statistiquesClasses[$classe->getId()] = ["admis" => $admisClasse, "recales" => $recalesClasse, "effectif" => $effectifClasse] ;
        // return new Response(var_dump($statistiquesClassesF));
      }// Fin de foreach $classesF
      // return new Response(var_dump($statistiquesClassesF));

      /* Il faut créer un tableau à cet enfroit pour enregistrer pour chaque niveau les stats */
      $totalAdmisNiveau = $totalMasculinAdmisNiveau + $totalFemininAdmisNiveau;
      $recalesNiveau = $effectifNiveau - $totalAdmisNiveau;
      // On va déterminer ici le pourcentage d'admis de chaque niveau
      if ($effectifNiveau != 0) {
        # code...
        $pourcentage = ($totalAdmisNiveau * 100) / $effectifNiveau;
        $statistiquesNiveaux[$i++] = [
          "id"               => $niveau->getId(),
          "niveau"           => $niveau->getLibelleFr(),
          "totalM"           => $totalMasculin,
          "totalF"           => $totalFeminin,
          "totalAdmisM"      => $totalMasculinAdmisNiveau,
          "totalAdmisF"      => $totalFemininAdmisNiveau,
          "totalRecalesM"    => $totalMasculinRecalesNiveau,
          "totalRecalesF"    => $totalFemininRecalesNiveau,
          "effectif"         => $effectifNiveau,
          "admis"            => $totalAdmisNiveau,
          "recales"          => $recalesNiveau,
          "pourcentageAdmis" => $pourcentage ] ;
      }
      
    }// Fin de foreach $niveaux
    // return new Response(var_dump($statistiquesClassesF));

    $nbrTotalAdmis   = $nbrTotalAdmisMasculin + $nbrTotalAdmisFeminin;
    $nbrTotalRecales = count($frequenter) - $nbrTotalAdmis;

    $statRegime = [];
    foreach ($statistiquesNiveaux as $key => $stat) {
      $pc = 0;
      // $pc = round((($stat[$key]['totalAdmisM'] + $stat[$key]['totalAdmisF'] * 100) / ($nbrTotalAdmis)), 2);
      $pc = round((($stat['admis'] * 100) / ($nbrTotalAdmis)), 2);
      if($key == 0)
        $statRegime[] = ["name" => $stat['niveau'], "y" => $pc, "sliced" => TRUE, "selected" => TRUE];
      else
        $statRegime[] = ["name" => $stat['niveau'], "y" => $pc];
    }
    // return new Response(var_dump($statF));
    return $this->render('ISIBundle:Examen:bilan-annuel.html.twig', [
      'asec'         => $as,
      'annee'        => $annee,
      'niveaux'      => $niveaux,
      'classes'      => $classes,
      'totalAdmis'   => $nbrTotalAdmis,
      'totalAdmisM'  => $nbrTotalAdmisMasculin,
      'totalAdmisF'  => $nbrTotalAdmisFeminin,
      'totalRecales' => $nbrTotalRecales,
      'statRegime'   => $statRegime,
      'statNiveaux'  => $statistiquesNiveaux,
      // 'frequenterA' => $frequenterA,
      // 'frequenterF' => $frequenterF,
    ]);
  }

  // Cette page affichera les notes des élèves d'une classe donnée après les examens
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function notesDesElevesDUneClasseAction(Request $request, $as, $regime, $classeId, $examenId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');

    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $classe    = $repoClasse->find($classeId);
    $examen    = $repoExamen->find($examenId);
    $niveauId  = $classe->getNiveau()->getId();
    $matieres  = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);

    //On va récupérer les ids des élèves
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);

    // On va sélectionner les notes et les moyennes des élèves et les mettre directement dans un tableau
    $toutesLesNotes = $repoNote->lesNotesDesElevesDeLaClasse($examenId, $elevesIds);
    $notes = [];
    foreach($toutesLesNotes as $key => $note)
    {
      $eleveId = $note->getEleve()->getId();
      foreach($matieres as $k => $matiere)
      {
        $matiereId = $matiere->getId();
        if($matiereId == $note->getMatiere()->getId())
          $notes[$matiereId][$eleveId] = $note;
      }
    }

    return $this->render('ISIBundle:Examen:notes-des-eleves-d-une-classe.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'classe'   => $classe,
      'eleves'   => $eleves,
      'examen'   => $examen,
      'notes'    => $notes,
      'matieres' => $matieres,
    ]);
  }

  /************** - ça, une une fonction personnelle. Elle sert à récupérer les ids des éleves à partir d'une liste d'élèves */
  public function recupererLesIdsDesEleves($eleves)
  {
    $lesIdsEleves = [];
    foreach($eleves as $eleve)
    {
      if (is_object($eleve)) {
        $lesIdsEleves[] = $eleve->getId();
      }
      else{
        $lesIdsEleves[] = $eleve['id'];
      }
    }
    // $lesIdsEleves = $lesIdsEleves.'0';

    return $lesIdsEleves;
  }
  /************** - elle finie ici */

  public function calculMoyenneDUnEleve(int $eleveId, int $examenId, $enseignements)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoNote         = $em->getRepository('ISIBundle:Note');
    // Séquence 1:
    // On sélectionne ensuite les notes (toutes les notes) de l'élève en cours, c'est-à-dire dans la boucle
    $notes = $repoNote->findBy(['examen' => $examenId, 'eleve' => $eleveId]);

    if(count($notes) == count($enseignements))
    {
      // Séquence 2:
      /* On boucle sur les notes de l'élève pour calculer la moyenne.
       * Mais avant, il faut initialiser le total des notes
       */
      $totalNote = 0;
      $totalcoeff = 0;
      foreach ($notes as $note) {
        foreach($enseignements as $key => $value){
          if($note->getMatiere()->getId() == $value->getMatiere()->getId()){
            $totalcoeff = $totalcoeff + $value->getCoefficient();
            $totalNote = $totalNote + $note->getNote() * $value->getCoefficient();
          }
        }
      }
  
      // Séquence 3:
      // On calcul alors la moyenne
      $moyenne = $totalNote / $totalcoeff;
      $moy     = round($moyenne, 2);
    }
    else{
      $moy       = null;
      $totalNote =  null;
    }
    return ["moyenne" => $moy, "totalPoints" => $totalNote];
  }
}

?>
