<?php

namespace ISI\ISIBundle\Controller;

use ISI\ISIBundle\Entity\Note;
use ISI\ISIBundle\Entity\Eleve;
use ISI\ISIBundle\Entity\Moyenne;
use ISI\ISIBundle\Entity\NoteFrancais;

use ISI\ISIBundle\Entity\Moyenneclasse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ExamenController extends Controller
{
  // Pour tous ce qui concernent les examens

  // Cette fonction servira à afficher les classes pour pouvoir ensuite tirer (imprimer) les fiches de notes
  /**
   * @Security("has_role('ROLE_DIRECTION_ETUDE')")
   * @Route("/examen/tirer-fiches-de-notes-{as}-{regime}-{annexeId}", name="isi_afficher_fiches_de_notes")
   */
  public function afficherLesClassesPourTirerLesFichesDeNotesAction(Request $request, int $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $niveaux = $repoNiveau->niveauxDuGroupe($regime);
    $classes = $repoClasse->classeGrpFormation($as, $annexeId, $regime);
    $annee   = $repoAnnee->find($as);

    return $this->render('ISIBundle:Examen:afficher-classes-pour-tirer-fiche-de-notes.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee, 'annexe'   => $annexe,
      'niveaux' => $niveaux,
      'classes' => $classes,
    ]);
  }

  // On affiche une classe et les différentes matières dispensées dans la classe en question afin de
  // pouvoir tirer la fiche de note pour la matière qu'on désire
  /**
   * @Security("has_role('ROLE_NOTE' or 'ROLE_ETUDE')")
   * @Route("/examen/les-fiches-de-notes-de-la-classe-{as}-{regime}-{classeId}-{annexeId}", name="isi_tirer_fiches_de_notes")
   */
  public function lesFichesDeNotesDeLaClasseAction(Request $request, int $as, $regime, int $classeId, int $annexeId)
  {
    $em          = $this->getDoctrine()->getManager();
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoAnnexe  = $em->getRepository('ISIBundle:Annexe');
    $annexe      = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    // On sélectionne l'id du niveau en fonction de l'id de la classe pour la transmettre en paramètre
    // pour la sélection des matières du niveau
    $classe = $repoClasse->find($classeId);

    $niveauId = $classe->getNiveau()->getId();

    // Les matières de la classe
    // $matieres = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    $matieres = $repoMatiere->lesMatieresDeCompositionDuNiveau($as, $niveauId);

    return $this->render('ISIBundle:Examen:fiche-de-notes-de-la-classe.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee, 
      'annexe'   => $annexe,
      'classe'   => $classe,
      'matieres' => $matieres
    ]);
  }

  // Affichage de la vue pdf
  /**
   * @Security("has_role('ROLE_NOTE' or 'ROLE_ETUDE')")
   * @Route("/examen/la-fiche-de-notes-de-la-classe-pour-une-matiere-{as}-{regime}-{classeId}-{matiereId}-{annexeId}", name="isi_fiche_de_notes_d_une_matiere")
   */
  public function laFichesDeNotesDeLaClassePourUneMatiereAction(Request $request, int $as, $regime, int $classeId, int $matiereId, int $annexeId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoEleve        = $em->getRepository('ISIBundle:Eleve');
    $repoClasse       = $em->getRepository('ISIBundle:Classe');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoAnnexe       = $em->getRepository('ISIBundle:Annexe');
    $repoExamen       = $em->getRepository('ISIBundle:Examen');
    $repoCorrection   = $em->getRepository('ISIBundle:Correction');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $annexe           = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee       = $repoAnnee->find($as);
    $examen      = $repoExamen->dernierExamen($as);
    $classe      = $repoClasse->find($classeId);
    $niveauId    = $classe->getNiveau()->getId();
    $examenId   = $examen->getId();

    $corrections = $repoCorrection->findBy(["classe" => $classeId, "examen" => $examenId, "matiere" => $matiereId, "disabled" => false]);
    // $corrections = $repoCorrection->findBy(["niveau" => $niveauId, "annee" => $as, "matiere" => $matiereId]);
    
    $eleves  = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
    
    $matiere    = $repoMatiere->find($matiereId);
    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $filename = "fiche-de-note-de-".$classe->getLibelleFr()."-".$matiere->getLibelle();

    if(count($matiere->getMatiereEnfants()) != 0){
      $matieresFr = $repoEnseignement->enseignementDuNiveau($as, $niveauId, true);

      $html = $this->renderView('ISIBundle:Examen:fiche-de-notes-francais.html.twig', [
        "as"          => $as,
        "classe"      => $classe,
        'annee'       => $annee, 
        'annexe'      => $annexe,
        "eleves"      => $eleves,
        "regime"      => $regime,
        "matiere"     => $matiere,
        "corrections" => $corrections,
        "matieresFr"  => $matieresFr,
        'server'      => $_SERVER["DOCUMENT_ROOT"],   
      ]);
    }
    else{
      $html = $this->renderView('ISIBundle:Examen:fiche-de-notes-pour-une-matiere.html.twig', [
        "as"      => $as,
        "classe"  => $classe,
        'annee'   => $annee, 
        'annexe'  => $annexe,
        "eleves"  => $eleves,
        "regime"  => $regime,
        "matiere" => $matiere,
        "corrections" => $corrections,
        'server'  => $_SERVER["DOCUMENT_ROOT"],   
      ]);
    }



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
      $english_days   = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
      $french_days    = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
      $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
      $french_months  = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
      return str_replace($english_months, $french_months, str_replace($english_days, $french_days, date($format, strtotime($date) ) ) );
  }

  // Page d'accueil pour la saisie des notes
  /**
   * @Security("has_role('ROLE_NOTE' or 'ROLE_ETUDE')")
   * @Route("/accueil-saisie-de-notes-{as}-{regime}-{annexeId}", name="isi_saisie_de_notes")
   */
  public function accueilSaisieDeNote(Request $request, $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    // Sélection de l'examen pour lequel on va saisir les notes
    $examen  = $repoExamen->dernierExamen($as);
    $examens = $repoExamen->lesExamensDeLAnnee($as);
    $annee   = $repoAnnee->find($as);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    if(empty($examen))
    {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas saisir de notes car il n\'a aucun examen en cours. Demandez au bureau des études et des examens d\'enregistrer un examen.');
      return $this->redirect($this->generateUrl('isi_affaires_scolaires', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $regime]));
    }

    //
    $classes = $repoClasse->classeGrpFormation($as, $annexeId, $regime);
    return $this->render('ISIBundle:Examen:afficher-classes-pour-saisir-de-notes.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'classes'  => $classes,
      'examen'   => $examen,
      'annee'   => $annee, 'annexe'   => $annexe,
      'niveaux'  => $niveaux,
      'examens'  => $examens,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/liste-des-matieres-pour-la-saisie-des-notes-de-la-classe-{as}-{regime}-{classeId}-{examenId}-{annexeId}", name="isi_saisie_de_notes_de_la_classe")
   */
  public function saisieDesNotesDeLaClasse(Request $request, int $as, $regime, int $classeId, int $examenId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoAnnexe  = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    // Sélection de l'examen
    $examen = $repoExamen->find($examenId);

    // La liste des élèves de la classe
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);

    // On sélectionne l'id du niveau en fonction de l'id de la classe pour la transmettre en paramètre
    // pour la sélection des matières du niveau
    $classe = $repoClasse->find($classeId);

    $niveauId = $classe->getNiveau()->getId();

    // Les matières de la classe
    $matieres = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    $matieres = $repoMatiere->lesMatieresDeCompositionDuNiveau($as, $niveauId);
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
      'annexe'          => $annexe,
      'classe'          => $classe,
      'examen'          => $examen,
      'matieres'        => $matieres,
      'matiereSansNote' => $matiereSansNote,
    ]);
  }

  public function calculMoyenneExamen(array $eleves, $examen, $classe, array $enseignements)
  {
    $em                = $this->getDoctrine()->getManager();
    $repoEleve         = $em->getRepository('ISIBundle:Eleve');
    $repoMoyenne       = $em->getRepository('ISIBundle:Moyenne');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');
    $examenId = $examen->getId();
    $classeId = $classe->getId();
    $admis   = 0;
    $recales = 0;
    foreach($eleves as $value)
    {
      $eleveId = $value instanceof Eleve ? $value->getId() : $value["id"];
      $eleve = $repoEleve->find($eleveId);
      $moyenne = $repoMoyenne->findOneBy(["eleve" => $eleveId, "examen" => $examenId]);
      $moy = $this->calculMoyenneDUnEleve($eleveId, $examenId, $enseignements);
      if(empty($moyenne))
      {
        $moyenne = new Moyenne();
        $moyenne->setEleve($eleve);
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
      if($moy["moyenne"] >= 5.5)
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

  public function calculMoyennesAnnuelles(array $eleves, $examen, $classe, array $enseignements)
  {
    $em             = $this->getDoctrine()->getManager();
    $repoNote       = $em->getRepository('ISIBundle:Note');
    $repoMoyenne    = $em->getRepository('ISIBundle:Moyenne');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $anneeId        = $examen->getAnnee()->getId();
    $examenId       = $examen->getId();
    $admis          = 0;
    $recales        = 0;
    foreach($eleves as $key => $value)
    {
      $eleveId = $value instanceof Eleve ? $value->getId() : $value["id"];
      $notesDUnEleve1 = $repoNote->findBy(['examen' => $examenId - 1, 'eleve' => reset($eleves)['id']]);
      $notesDUnEleve2 = $repoNote->findBy(['examen' => $examenId, 'eleve' => reset($eleves)['id']]);  
      foreach ($notesDUnEleve2 as $note2) {
        // On va calculer la moyenne annuelle dans toutes les matières
        foreach ($notesDUnEleve1 as $note1) {
          if($note1->getMatiere()->getId() == $note2->getMatiere()->getId())
          {
            $moyenneDeLaMatiere = $note1->getNote() + $note2->getNote();
            $moyennesDeTousLesEleves[$eleveId] = $moyenneDeLaMatiere;
            $moyenne = $repoMoyenne->findOneBy(["examen" => $examenId, "eleve" => $eleveId]);
            $frequenter = $repoFrequenter->findOneBy(["annee" => $anneeId, "eleve" => $eleveId]);
            $moyAnnuelle = $this->calculMoyenneAnnuelleDUnEleve($frequenter, $enseignements); 
            $moyenne->setMoyenneAnnuelle($moyAnnuelle["moyenne"]);
            $admission = $moyAnnuelle["admission"];
            $admission == true ? $admis++ : $recales++;
            $moyennesDeTousLesEleves[$eleveId] = $moyenne;
            $frequenter->setAdmission($admission);
          }
        }// Fin de foreach $notes1
      }// Fin de foreach $notes2
    }

    $rangs = $this->classementSemestriel($moyennesDeTousLesEleves);

    foreach($moyennesDeTousLesEleves as $key => $value)
    {
      $eleveId = $value->getEleve()->getId();
      $value->setClassementAnnuel($rangs[$eleveId]);
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
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/saisie-des-notes-sauvegarde-en-db-{as}-{regime}-{classeId}-{examenId}-{matiereId}-{annexeId}", name="isi_saisie_de_notes_de_la_classe_pour_une_matiere")
   */
  public function enregistrerNotes(Request $request, $as, $regime, $classeId, $examenId, $matiereId, int $annexeId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoEleve        = $em->getRepository('ISIBundle:Eleve');
    $repoExamen       = $em->getRepository('ISIBundle:Examen');
    $repoClasse       = $em->getRepository('ISIBundle:Classe');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoAppreciation = $em->getRepository('ISIBundle:Appreciation');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $eleves   = $repoEleve->elevesDeLaClasse($as, $annexeId, $classeId);
    $examen   = $repoExamen->find($examenId);
    $matiere  = $repoMatiere->find($matiereId);
    $classe   = $repoClasse->find($classeId);
    $niveauId = $classe->getNiveau()->getId();
    // $enseignements = $repoEnseignement->findBy(['annee' => $as, 'niveau' => $niveauId, 'matiereExamen' => true]);
    $enseignements = $repoEnseignement->matieresDeCompositionDuNiveau($as, $niveauId);
    // dump($enseignements);

    if(count($matiere->getMatiereEnfants()) != 0){
      return $this->redirect($this->generateUrl('saisie_des_notes_francais', ['as' => $as, 'regime' => $regime, 'classeId' => $classeId, 'examenId' => $examenId, 'matiereId' => $matiereId, 'annexeId' => $annexeId]));
    }

    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      foreach($data['note'] as $key => $noteEleve)
      {
        // On vérifie qu'aucune des notes saisies n'est pas supérieures à 10.
        if($noteEleve > 10)
        {
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
        $appreciation   = $repoAppreciation->find($appreciationId);
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
        // return new Response("C'est OK");
        $em->persist($note);
      } // Fin foreach $data['note']
      
      try{
        $em->flush();
        $this->calculMoyenneExamen($eleves, $examen, $classe, $enseignements);
        $request->getSession()->getFlashBag()->add('info', 'Les notes  des élèves de <strong>'.$classe->getLibelleFr().'</strong> en <strong>'.$matiere->getLibelle().'</strong> ont été bien enregistrées.');
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
        'annexeId' => $annexeId,
        'classeId' => $classeId,
        'examenId' => $examenId
      ]));
    }

    return $this->render('ISIBundle:Examen:formulaire-de-saisie-de-notes.html.twig', [
      'asec'      => $as,
      'regime'    => $regime,
      'annee'     => $annee, 
      'annexe'    => $annexe,
      'classe'    => $classe,
      'eleves'    => $eleves,
      'matiere'   => $matiere,
      'examen'    => $examen,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/edition-des-notes-sauvegarde-en-db-{as}-{regime}-{classeId}-{examenId}-{matiereId}-{annexeId}", name="isi_edition_des_notes")
   */
  public function editerNotesAction(Request $request, $as, $regime, $classeId, $examenId, $matiereId, int $annexeId)
  {
    $em                = $this->getDoctrine()->getManager();
    $repoAppreciation  = $em->getRepository('ISIBundle:Appreciation');
    $repoAnnee         = $em->getRepository('ISIBundle:Annee');
    $repoEleve         = $em->getRepository('ISIBundle:Eleve');
    $repoExamen        = $em->getRepository('ISIBundle:Examen');
    $repoClasse        = $em->getRepository('ISIBundle:Classe');
    $repoMatiere       = $em->getRepository('ISIBundle:Matiere');
    $repoNote          = $em->getRepository('ISIBundle:Note');
    $repoEnseignement  = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    
    $annee         = $repoAnnee->find($as);
    $eleves        = $repoEleve->elevesDeLaClasse($as, $annexeId, $classeId);
    $examen        = $repoExamen->find($examenId);
    $matiere       = $repoMatiere->find($matiereId);
    $classe        = $repoClasse->find($classeId);
    $niveauId      = $classe->getNiveau()->getId();
    $enseignements = $repoEnseignement->matieresDeCompositionDuNiveau($as, $niveauId);

    /**
     * Quand l'année scolaire est finie, on doit plus faire de
     * mofication au niveau des notes
     **/
    if($annee->getAchevee() == true)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des notes car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('isi_saisie_de_notes', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $regime]));
    }

    if(count($matiere->getMatiereEnfants()) != 0){
      return $this->redirect($this->generateUrl('mise_a_jour_des_notes_francais', ['as' => $as, 'regime' => $regime, 'classeId' => $classeId, 'examenId' => $examenId, 'matiereId' => $matiereId, 'annexeId' => $annexeId]));
    }


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
                $note->setParticipation(true);
              }
              elseif($noteEleve > 0 && $noteEleve <= 10)
              {
                $note->setNote($noteEleve);
                $note->setParticipation(true);
              }
              elseif(empty($noteEleve))
              {
                $note->setNote(0);
                $note->setParticipation(false);
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
                $note->setParticipation(true);
              }
              elseif($noteEleve > 0 && $noteEleve <= 10)
              {
                $note->setNote($noteEleve);
                $note->setParticipation(true);
              }
              elseif(empty($noteEleve))
              {
                $note->setNote(0);
                $note->setParticipation(false);
              }
              $note->setUpdatedBy($this->getUser());
              $note->setUpdatedAt(new \Datetime());
            } // Fin de if ($note->getNote() != $noteEleve)
          }
        }
      }
      $this->calculMoyenneExamen($eleves, $examen, $classe, $enseignements);
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

      if($noteMAJ != 0)
      {
        // return new Response('Nombre de note modifiées : '.$noteMAJ);
        $request->getSession()->getFlashBag()->add('info', 'La mise à jour des notes des élèves de <strong>'.$classe->getLibelleFr().'</strong> en <strong>'.$matiere->getLibelle().'</strong> s\'est terminée avec succès. N\'oubliez de recalculer les moyennes.');
      }
      else{
        // return new Response('Aucune note modifiée : '.$noteMAJ);
        $request->getSession()->getFlashBag()->add('info', 'Aucune n\'a été modifié.');
      }

      return $this->redirect($this->generateUrl('isi_saisie_de_notes_de_la_classe', [
        'as'       => $as,
        'regime'   => $regime, 
        'annexeId' => $annexeId,
        'notes'    => $notes,
        'classeId' => $classeId,
        'examenId' => $examenId
      ]));
    }


    return $this->render('ISIBundle:Examen:formulaire-d-edition-de-notes.html.twig', [
      'asec'      => $as,
      'regime'    => $regime,
      'annee'     => $annee, 
      'annexe'    => $annexe,
      'classe'    => $classe,
      'eleves'    => $eleves,
      'matiere'   => $matiere,
      'examen'    => $examen,
      'notes'     => $notes
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/saisie-des-notes-de-francais-{as}-{regime}-{classeId}-{examenId}-{matiereId}-{annexeId}", name="saisie_des_notes_francais")
   */
  public function saisie_des_notes_francais(Request $request, $as, $regime, $classeId, $examenId, $matiereId, int $annexeId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoEleve        = $em->getRepository('ISIBundle:Eleve');
    $repoExamen       = $em->getRepository('ISIBundle:Examen');
    $repoClasse       = $em->getRepository('ISIBundle:Classe');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoAppreciation = $em->getRepository('ISIBundle:Appreciation');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $eleves   = $repoEleve->elevesDeLaClasse($as, $annexeId, $classeId);
    $examen   = $repoExamen->find($examenId);
    $matiere  = $repoMatiere->find($matiereId);
    $classe   = $repoClasse->find($classeId);
    $niveauId = $classe->getNiveau()->getId();
    $matieresFr = $repoEnseignement->enseignementDuNiveau($as, $niveauId, true);
    $enseignements = $repoEnseignement->matieresDeCompositionDuNiveau($as, $niveauId);
    // dump($enseignements);

    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      foreach ($eleves as $eleve) {
        $eleveId = $eleve->getId();
        $notes = $data['notes'.$eleveId];
        $total = 0;
        foreach($notes as $key => $noteEleve)
        {
          $noteEleve = (float) $noteEleve;
          // On vérifie qu'aucune des notes saisies n'est pas supérieures à 10.
          if($noteEleve > 10)
          {
            $request->getSession()->getFlashBag()->add('error', 'Vérifiez bien les notes saisies. Aucune note ne doit être supérieur à 10');
            return new Response('La note de cet élève est supérieur à 10: '.$noteEleve);
          }
          $matiereFrancais  = $repoMatiere->find($key);
          $total = $total + $noteEleve;
  
          $noteF = new NoteFrancais();
          $noteF->setExamen($examen);
          $noteF->setEleve($eleve);
          $noteF->setMatiere($matiereFrancais);
          $noteF->setCreatedBy($this->getUser());
          $noteF->setCreatedAt(new \Datetime());
          // On va déterminer l'appréciation de la note obtenue
          $appreciationId = $this->findAppreciation($noteEleve, 10);
          $appreciation   = $repoAppreciation->find($appreciationId);
          $noteF->setAppreciation($appreciation);
          
          // Cette condition me permet de savoir si l'élève à composé dans la matière ou non
          $participation = 0;
          if($noteEleve == 0 && strlen($noteEleve) <= 1)
          {
            $noteF->setNote(0);
            $noteF->setParticipation(TRUE);
            $participation++;
          }
          elseif($noteEleve > 0 && $noteEleve <= 10)
          {
            $noteF->setNote($noteEleve);
            $noteF->setParticipation(TRUE);
            $participation++;
          }
          elseif(empty($noteEleve))
          {
            $noteF->setNote(0);
            $noteF->setParticipation(FALSE);
          }
          $noteF->setCreatedBy($this->getUser());
          $noteF->setCreatedAt(new \Datetime());
          // return new Response("C'est OK");
          $em->persist($noteF);
        } // Fin foreach $data['note']


        $moyenne = $total / count($matieresFr);
        $note = new Note();
        $note->setExamen($examen);
        $note->setEleve($eleve);
        $note->setMatiere($matiere);
        $note->setCreatedBy($this->getUser());
        $note->setCreatedAt(new \Datetime());
        // On va déterminer l'appréciation de la note obtenue
        $appreciationId = $this->findAppreciation($moyenne, 10);
        $appreciation   = $repoAppreciation->find($appreciationId);
        $note->setAppreciation($appreciation);
        $note->setNote($moyenne);
        // Cette condition me permet de savoir si l'élève à composé dans la matière ou non
        $participation == 0 ? $note->setParticipation(false) : $note->setParticipation(true);

        $note->setCreatedBy($this->getUser());
        $note->setCreatedAt(new \Datetime());
        $em->persist($note);

        // dump($notes, $total);
        // die();
      }
      
      try{
        $em->flush();
        $this->calculMoyenneExamen($eleves, $examen, $classe, $enseignements);
        $request->getSession()->getFlashBag()->add('info', 'Les notes  des élèves de <strong>'.$classe->getLibelleFr().'</strong> en <strong>'.$matiere->getLibelle().'</strong> ont été bien enregistrées.');
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
        'annexeId' => $annexeId,
        'classeId' => $classeId,
        'matiereId' => $matiereId,
        'examenId' => $examenId
      ]));
    }

    return $this->render('ISIBundle:Examen:formulaire-de-saisie-des-notes-francais.html.twig', [
      'asec'       => $as,
      'regime'     => $regime,
      'annee'      => $annee, 
      'annexe'     => $annexe,
      'classe'     => $classe,
      'eleves'     => $eleves,
      'matiere'    => $matiere,
      'matieresFr' => $matieresFr,
      'examen'     => $examen,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/mise-a-jour-des-notes-de-francais-{as}-{regime}-{classeId}-{examenId}-{matiereId}-{annexeId}", name="mise_a_jour_des_notes_francais")
   */
  public function mise_a_jour_des_notes_francais(Request $request, $as, $regime, $classeId, $examenId, $matiereId, int $annexeId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoEleve        = $em->getRepository('ISIBundle:Eleve');
    $repoNote         = $em->getRepository('ISIBundle:Note');
    $repoNoteF        = $em->getRepository('ISIBundle:NoteFrancais');
    $repoExamen       = $em->getRepository('ISIBundle:Examen');
    $repoClasse       = $em->getRepository('ISIBundle:Classe');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoAppreciation = $em->getRepository('ISIBundle:Appreciation');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $eleves   = $repoEleve->elevesDeLaClasse($as, $annexeId, $classeId);
    $examen   = $repoExamen->find($examenId);
    $matiere  = $repoMatiere->find($matiereId);
    $classe   = $repoClasse->find($classeId);
    $niveauId = $classe->getNiveau()->getId();
    $matieresFr = $repoEnseignement->enseignementDuNiveau($as, $niveauId, true);
    $enseignements = $repoEnseignement->matieresDeCompositionDuNiveau($as, $niveauId);
    // $notes = $repoNote->notesDesElevesDeLaClasseDansUneMatiere($examenId, $matiereId, $classeId);notesEnEdition($examenId, $matiereId, $elevesIds);
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);
    $notes = $repoNote->notesEnEdition($examenId, $matiereId, $elevesIds);
    $notesFrancais = $repoNoteF->notesEnEdition($examenId, $matiereId, $elevesIds);


    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      foreach ($eleves as $eleve) {
        $eleveId = $eleve->getId();
        $notes = $data['notes'.$eleveId];
        $total = 0;
        foreach($notes as $key => $noteEleve)
        {
          $noteEleve = (float) $noteEleve;
          // On vérifie qu'aucune des notes saisies n'est pas supérieures à 10.
          if($noteEleve > 10)
          {
            $request->getSession()->getFlashBag()->add('error', 'Vérifiez bien les notes saisies. Aucune note ne doit être supérieur à 10');
            return new Response('La note de cet élève est supérieur à 10: '.$noteEleve);
          }
          $matiereFrancais  = $repoMatiere->find($key);
          $total = $total + $noteEleve;
  
          $noteF = $repoNoteF->findOneBy(["eleve" => $eleveId, "matiere" => $key, "examen" => $examenId]);
          if(empty($noteF)){
            $noteF = new NoteFrancais();
            $noteF->setExamen($examen);
            $noteF->setEleve($eleve);
            $noteF->setMatiere($matiereFrancais);
            $noteF->setCreatedBy($this->getUser());
            $noteF->setCreatedAt(new \Datetime());
            $em->persist($noteF);
          }
          else{
            $noteF->setUpdatedBy($this->getUser());
            $noteF->setUpdatedAt(new \Datetime());
          }
          // On va déterminer l'appréciation de la note obtenue
          $appreciationId = $this->findAppreciation($noteEleve, 10);
          $appreciation   = $repoAppreciation->find($appreciationId);
          $noteF->setAppreciation($appreciation);
          
          // Cette condition me permet de savoir si l'élève à composé dans la matière ou non
          $participation = 0;
          if($noteEleve == 0 && strlen($noteEleve) <= 1)
          {
            $noteF->setNote(0);
            $noteF->setParticipation(TRUE);
            $participation++;
          }
          elseif($noteEleve > 0 && $noteEleve <= 10)
          {
            $noteF->setNote($noteEleve);
            $noteF->setParticipation(TRUE);
            $participation++;
          }
          elseif(empty($noteEleve))
          {
            $noteF->setNote(0);
            $noteF->setParticipation(FALSE);
          }
        } // Fin foreach $data['note']


        $moyenne = $total / count($matieresFr);
        $note = $repoNote->findOneBy(["eleve" => $eleveId, "matiere" => $matiereId, "examen" => $examenId]);
        if(empty($note)){
          dump($note);
          die();
          $note = new Note();
          $note->setExamen($examen);
          $note->setEleve($eleve);
          $note->setMatiere($matiere);
          $note->setCreatedBy($this->getUser());
          $note->setCreatedAt(new \Datetime());
          $em->persist($note);
        }
        else{
          $note->setUpdatedBy($this->getUser());
          $note->setUpdatedAt(new \Datetime());
        }
        // On va déterminer l'appréciation de la note obtenue
        $appreciationId = $this->findAppreciation($moyenne, 10);
        $appreciation   = $repoAppreciation->find($appreciationId);
        $note->setAppreciation($appreciation);
        $note->setNote($moyenne);
        // Cette condition me permet de savoir si l'élève à composé dans la matière ou non
        $participation == 0 ? $note->setParticipation(false) : $note->setParticipation(true);


        // dump($notes, $total);
        // die();
      }
      
      try{
        $em->flush();
        $this->calculMoyenneExamen($eleves, $examen, $classe, $enseignements);
        $request->getSession()->getFlashBag()->add('info', 'Les notes  des élèves de <strong>'.$classe->getLibelleFr().'</strong> en <strong>'.$matiere->getLibelle().'</strong> ont été bien enregistrées.');
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
        'annexeId' => $annexeId,
        'classeId' => $classeId,
        'matiereId' => $matiereId,
        'examenId' => $examenId
      ]));
    }
    // dump($notes);
    return $this->render('ISIBundle:Examen:formulaire-de-mise-a-jour-des-notes-francais.html.twig', [
      'asec'          => $as,
      'regime'        => $regime,
      'annee'         => $annee, 
      'annexe'        => $annexe,
      'classe'        => $classe,
      'eleves'        => $eleves,
      'notes'         => $notes,
      'notesFrancais' => $notesFrancais,
      'matiere'       => $matiere,
      'matieresFr'    => $matieresFr,
      'examen'        => $examen,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   */
  public function accueilResultatsDExamenAction(Request $request, int $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $classes = $repoClasse->classeGrpFormation($as, $annexeId, $regime);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);
    $examen  = $repoExamen->dernierExamen($as);
    $annee   = $repoAnnee->find($as);
    return $this->render('ISIBundle:Examen:resultats-d-examen.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee, 'annexe'   => $annexe,
      'classes' => $classes,
      'niveaux' => $niveaux,
      'examen'  => $examen
    ]);
  }

  // On va se permettre d'afficher quelques données statistiques d'examen
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/donnees-statistiques-examen-{as}-{regime}-{annexeId}", name="isi_statiqtiques")
   */
  public function statistiquesAction(Request $request, $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    $defaultData = ['message' => 'Rien du tout'];

    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      // return new Response(var_dump($data));
      return $this->redirect($this->generateUrl('isi_generation_donnees_statiqtiques', [
        'as'       => $as,
        'regime'   => $regime, 'annexeId' => $annexeId,
        'examenId' => $data['examen']
      ]));
    }

    $examens = $repoExamen->lesExamensDeLAnnee($as);
    $annee   = $repoAnnee->find($as);
    return $this->render('ISIBundle:Examen:statistiques-examen-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee, 'annexe'   => $annexe,
      'examens' => $examens,
      'niveaux' => $niveaux,
    ]);
  }

  // On va se permettre d'afficher quelques données statistiques d'examen
  /**
   * @Security("has_role('ROLE_ETUDE' or 'ROLE_SCOLARITE')")
   * @Route("/examen/resultats-annuels/{as}-{regime}-{annexeId}", name="isi_resultats_annuels")
   */
  public function resultatsAnnuelsAction(Request $request, $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $niveaux = $repoNiveau->niveauxDuGroupe($regime);
    $examens = $repoExamen->lesExamensDeLAnnee($as);
    if (count($examens) < 2) {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels pour le moment.');
      return $this->redirect($this->generateUrl('isi_affaires_scolaires', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $regime]));
    }
    $annee   = $repoAnnee->find($as);
    $classes = $repoClasse->classeGrpFormation($as, $annexeId, $regime);
    // Les moyennes des classes à l'examen
    $mc = [];
    foreach ($classes as $classe) {
      # code...
      $classeId = $classe->getId();
      $moyenne = $repoMoyenneclasse->findOneBy(["classe" => $classeId, "examen" => $examens[1]->getId()]);
      if(empty($moyenne) or empty($moyenne->getAdmis()))
        $mc[$classeId] = FALSE;
      else
        $mc[$classeId] = TRUE;
    }

    $routeName = $request->attributes->get('_route');
    $routeParameters = $request->get('direction');
    // return new Response(var_dump($routeParameters));
    return $this->render('ISIBundle:Examen:resultats-annuels-home.html.twig', [
      'asec'      => $as,
      'regime'    => $regime,
      'annee'   => $annee, 'annexe'   => $annexe,
      'classes'   => $classes,
      'niveaux'   => $niveaux,
      'moyenneC'  => $mc,
      'direction' => $routeParameters,
    ]);
    if($routeParameters == 'etude')
    {
    }
    elseif($routeParameters == 'scolarite')
    {
      return $this->render('ISIBundle:Scolarite:resultats-annuels-home.html.twig', [
        'asec'     => $as,
        'regime'   => $regime,
        'annee'   => $annee, 'annexe'   => $annexe,
        'classes'  => $classes,
        'niveaux'  => $niveaux,
        'moyenneC' => $mc,
      ]);
    }
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
   * @Security("has_role('ROLE_ETUDE' or 'ROLE_SCOLARITE')")
   * @Route("/examen/resultats-annuels-de-la-classe-{as}-{regime}-{classeId}-{annexeId}", name="isi_resultats_annuels_d_une_classe")
   */
  public function resultatsAnnuelsDUneClasseAction(Request $request, $as, $regime, $classeId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $classe   = $repoClasse->find($classeId);
    $niveauId = $classe->getNiveau()->getId();
    // On va sélectionner l'examen en cours, qui est en réalité le dernier examen de l'année
    $examen   = $repoExamen->dernierExamen($as);
    $examenId = $examen->getId();

    // Calcul des moyennes de la seconde session
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);

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
      return $this->redirect($this->generateUrl('isi_resultats_d_examens', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $regime]));
    }

    // return new Response(var_dump($ens));

    if(count($notesDUnEleve1) < count($ens))
    {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels de la classe <strong>'.$classe->getLibelleFr().'</strong>, car toutes les notes de la session 1 n\'ont pas été saisies.');
      // return new Response('Cool');

      return $this->redirect($this->generateUrl('isi_resultats_annuels', [
        'as'       => $as,
        'annexeId' => $annexeId,
        'regime'   => $regime,
      ]));
    }
    elseif(count($notesDUnEleve2) < count($ens))
    {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels de la classe <strong>'.$classe->getLibelleFr().'</strong>, car toutes les notes de la session 2  n\'ont pas été saisies.');
      // return new Response('Cool');

      return $this->redirect($this->generateUrl('isi_resultats_annuels', [
        'as'       => $as,
        'regime'   => $regime, 
        'annexeId' => $annexeId,
      ]));
    }

    $routeParameters = $request->get('direction');
    $this->calculMoyenneExamen($eleves, $examen, $classe, $ens);
    $this->calculMoyennesAnnuelles($eleves, $examen, $classe, $ens);
    return $this->redirect($this->generateUrl('isi_classement_annuel', [
      'as'        => $as,
      'regime'    => $regime, 'annexeId' => $annexeId,
      'classeId'  => $classeId,
      'direction' => $routeParameters,
    ]));
  }

  // Voir les résultats annuels avec un template php
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/moyennes-annuelles-de-la-classe", name="moyenne_annuelle")
   */
  public function resultYearAction()
  {
    return $this->render('ISIBundle:Examen:results-year.html.php');
  }

  // Calcul des moyennes annuelles et classement annuels
  /**
   * @Security("has_role('ROLE_ETUDE' or 'ROLE_SCOLARITE')")
   * @Route("/examen/calcul-moyennes-annuelles-et-classement-annuel-{as}-{regime}-{classeId}-{annexeId}", name="isi_classement_annuel")
   */
  public function moyennesClassementAnnuelAction(Request $request, int $as, $regime, int $classeId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $classe   = $repoClasse->find($classeId);
    $examens  = $repoExamen->lesExamensDeLAnnee($as);
    $examenId = $examens[1]->getId();
    $routeParameters = $request->get('direction');

    // On va calculer les moyennes annuelles et faire le classement annuel
    // Séance 1:
    // On sélectionne d'abord les élèves de la classe
    
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
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
        // $fms = $repoFM->findBy(['frequenter' => $frequenter->getId()]);
        // $notesDUnEleve = $repoNote->notesDUnEleveLorsDesDeuxExamens($eleveId, $as);
        $notesDUnEleve = $repoNote->test($eleveId, $as);
        dump($notesDUnEleve);
        die();
        foreach ($notesDUnEleve as $fq) {
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
        'annee'   => $annee, 'annexe'   => $annexe,
        'classe'   => $classe,
        'eleves'   => $eleves,
        'moyennes' => $moyennes,
        'examenId' => $examenId,
        'direction' => $routeParameters,
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
        'annee'   => $annee, 'annexe'   => $annexe,
        'classe'   => $classe,
        'eleves'   => $eleves,
        'moyennes' => $moyennes,
        'examenId' => $examenId,
        'direction' => $routeParameters,
      ]);
    }
  }

  // On pourra afficher toutes les notes des élèves de la classe au cours d'une année
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/notes-de-tous-les-examens/{as}-{regime}-{classeId}-{annexeId}", name="isi_notes_des_deux_examens")
   */
  public function notesTousLesExamensAction(Request $request, int $as, $regime, int $classeId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    // return new Response("C'est cool");

    // Sélection des entités
    $annee     = $repoAnnee->find($as);
    $classe    = $repoClasse->find($classeId);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
    $examens   = $repoExamen->lesExamensDeLAnnee($as);
    $matieres  = $repoMatiere->lesMatieresDuNiveau($as, $classe->getNiveau()->getId());
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);

    $notes1 = $repoNote->toutesLesNotesDesElevesDeLaClasse($examens[0]->getId(), $elevesIds);
    $notes2 = $repoNote->toutesLesNotesDesElevesDeLaClasse($examens[1]->getId(), $elevesIds);
    if (empty($notes1[0]) || empty($notes2[0])) {
      $request->getSession()->getFlashBag()->add('error', 'Il n\'est pas possible de calculer les moyennes annuelles pour l\'heure.');
      return $this->redirect($this->generateUrl('isi_resultats_annuels', [
        'as'     => $as, 
        'annexeId' => $annexeId,
        'regime' => $regime,
      ]));
    }

    $notes = [];
    foreach ($eleves as $eleve) {
      # code...
      // $notesMatiere['eleve']         = $eleve['nomFr'].' '.$eleve['pnomFr'];
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
        $tableauNotes['notes']                = $notesMatiere;
        // $tableauNotes['eleve']                = $eleve['nomFr'].' '.$eleve['pnomFr'];
        $notes[]                       = $tableauNotes;
      }
    }
    return new Response(var_dump($notes));

    // return new Response(var_dump($notes[0]));
    // return new Response('cool');
    return $this->render('ISIBundle:Examen:notes-des-deux-examens.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'   => $annee, 'annexe'   => $annexe,
      'notes'    => $notes,
      'classe'   => $classe,
      'matieres' => $matieres
    ]);
  }

  // Les données  stiques obtenues pour toute l'année
  /**
   * @Security("has_role('ROLE_ETUDE' or 'ROLE_SCOLARITE')")
   * @Route("/examen/statistiques-annuelles-de-la-classe-{as}-{regime}-{classeId}-{annexeId}", name="isi_statistiques_annuelles")
   */
  public function statistiquesAnnuellesDUneClasseAction(Request $request, int $as, $regime, int $classeId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoFrequenter  = $em->getRepository('ISIBundle:Frequenter');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $routeParameters = $request->get('direction');

    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
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
      'annee'   => $annee, 'annexe'   => $annexe,
      'classe'     => $classe,
      'matieres'   => $matieres,
      'moyennes1'  => $moyennes1,
      'moyennes2'  => $moyennes2,
      'frequenter' => $frequenter,
      'direction' => $routeParameters,
    ]);
  }

  // Bulletin affichant les résultats annuels
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/bulletins-moyennes-annuelles/{as}-{regime}-{classeId}-{annexeId}", name="isi_bulletins_moyennes_annuelles")
   */
  public function bulletinsMoyennesAnnuellesAction(Request $request, $as, $regime, $classeId, int $annexeId)
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
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    
    // Sélection des entités
    $partie    = $request->query->get('partie');
    $annee     = $repoAnnee->find($as);
    $classe    = $repoClasse->find($classeId);
    $niveauId  = $classe->getNiveau()->getId();
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
    $examens   = $repoExamen->lesExamensDeLAnnee($as);
    $matieres  = $repoMatiere->lesMatieresDuNiveau($as, $classe->getNiveau()->getId());
    $enseignements       = $repoEnseignement->findBy(['annee'      => $as, 'niveau'       => $niveauId]);
    $examenId  = $examens[1]->getId();
    $elevesIds = $this->recupererLesIdsDesEleves($eleves);

    $ens = [];
    foreach ($enseignements as $value) {
      $ens[$value->getMatiere()->getId()] = $value;
    }
    $notes1 = $repoNote->toutesLesNotesDesElevesDeLaClasse($examens[0]->getId(), $elevesIds);
    $notes2 = $repoNote->toutesLesNotesDesElevesDeLaClasse($examens[1]->getId(), $elevesIds);
    if (empty($notes1[0]) || empty($notes2[0])) {
      $request->getSession()->getFlashBag()->add('error', 'Il n\'est pas possible de calculer les moyennes annuelles pour l\'heure.');
      return $this->redirect($this->generateUrl('isi_resultats_annuels', [
        'as'     => $as, 
        'annexeId' => $annexeId,
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
      'annexe'     => $annexe,
      'notes1'     => $notes1,
      'notes2'     => $notes2,
      'classe'     => $classe,
      'moyennes'   => $moyennes,
      'matieres'   => $matieres,
      'effectif'   => $effectif,
      'frequenter' => $frequenter,
      'server'     => $_SERVER["DOCUMENT_ROOT"],   
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
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/resultats-annuels-d-un-eleve-{as}-{regime}-{classeId}-{eleveId}-{annexeId}", name="isi_bulletin_annuel_individuel")
   */
  public function bulletinAnnuelIndividuelAction(Request $request, int $as, $regime, int $classeId, int $eleveId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    
    
    // Sélection des entités
    $fq            = $repoFrequenter->findOneBy(["eleve"     => $eleveId, "annee"             => $as]);
    $annee         = $repoAnnee->find($as);
    $eleve         = $repoEleve->find($eleveId);
    $classe        = $repoClasse->find($classeId);
    $niveauId      = $classe->getNiveau()->getId();
    $examens       = $repoExamen->lesExamensDeLAnnee($as);
    $enseignements = $repoEnseignement->findBy(['annee'      => $as,                  'niveau' => $niveauId]);
    $moyenne       = $repoMoyenne->findOneBy(['examen'       => $examens[1]->getId(), 'eleve'  => $eleveId]);
    $moyenneclasse = $repoMoyenneclasse->findOneBy(['examen' => $examens[1]->getId(), 'classe' => $classeId]);
    $ens = [];
    foreach ($enseignements as $value) {
      $ens[$value->getMatiere()->getId()] = $value;
    }

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
      'annee'   => $annee, 'annexe'   => $annexe,
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
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/donnees-statistiques-examen-{as}-{regime}-{examenId}-{annexeId}", name="isi_generation_donnees_statiqtiques")
   */
  public function generationDonneesStatistiquesAction(Request $request, $as, $regime, $examenId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMoyenneclasse = $em->getRepository('ISIBundle:Moyenneclasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $classes  = $repoClasse->classeGrpFormation($as, $annexeId, $regime);
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
      'annexe'          => $annexe,
      'niveaux'         => $niveaux,
      'examen'          => $examen,
      'moyennesClasses' => $moyennesClasses
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE' or 'ROLE_NOTE')")
   * @Route("/examen/resultats-de-la-classe-{as}-{regime}-{classeId}-{examenId}-{annexeId}", name="isi_resultats_de_la_classe")
   */
  public function resultatsDeLaClasseAction(Request $request, int $as, $regime, int $classeId, int $examenId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as, 'annexeId' => $annexeId]));
    }

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);
    $examen = $repoExamen->find($examenId);

    // La liste des élèves de la classe
    $eleves = $repoEleve->elevesDeLaClasse($as, $annexeId, $classeId);
    if(empty($eleves))
    {
      $request->getSession()->getFlashBag()->add('error', 'Aucun élèle inscrit dans cette classe');
      return $this->redirect($this->generateUrl('isi_saisie_de_notes', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $regime]));
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
    $ens           = $repoEnseignement->matieresDeCompositionDuNiveau($as, $niveauId);//findBy(['annee' => $as, 'niveau' => $niveauId]);
    $notesDUnEleve = $repoNote->findBy(['examen' => $examenId, 'eleve' => $eleves[0]->getId()]);
    // return new Response(var_dump(count($notesDUnEleve), count($ens)));
    if(count($notesDUnEleve) < count($ens))
    {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats. Toutes les notes de la session <strong>'.$examen->getSession().'</strong> n\'ont pas encore été saisies en <strong>'.$classe->getLibelleFr().'</strong>.');
      // return new Response(var_dump($tableNote, reset($eleves)['id'], $eleves[0]->getNomFr()));
      return $this->redirect($this->generateUrl('isi_saisie_de_notes', ['as' => $as, 'regime' => $regime, 'annexeId' => $annexeId]));
    }

    if($examen->getSession() == 2)
    {
      // return new Response("Retour 2");
      $request->getSession()->getFlashBag()->add('error', 'Vous devez calculer les résultats dans la "Résultats annuels".');
      return $this->redirect($this->generateUrl('isi_resultats_annuels', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $regime]));
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
      'annee'   => $annee, 'annexe'   => $annexe,
      'examen'   => $examen,
      'classe'   => $classe,
      'eleves'   => $eleves,
      'moyennes' => $moyennes,
    ]);
  }

  // On imprime (ou si vous voulez on affiche) le bulletin d'un seul élève de la classe
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/bulletin-d-un-eleve-{as}-{regime}-{classeId}-{examenId}-{eleveId}-{annexeId}", name="isi_bulletin_unique")
   */
  public function bulletinUniqueAction(Request $request, $as, $regime, $classeId, $examenId, $eleveId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoMemoriser  = $em->getRepository('ISIBundle:Memoriser');
    $repoCours   = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoMoyenneclasse  = $em->getRepository('ISIBundle:Moyenneclasse');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    
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
    $heuresAbsence = $this->total_des_heures_d_absence_d_un_eleve($eleveId, $as, $examenId);
    $cours         = $repoCours->enseignant_de_la_classe($as, $annexeId, $classeId);
    if($regime == "A"){
      $memoriser     = $repoMemoriser->findOneBy(["annee" => $as, "eleve" => $eleveId]);
      $halaqaId      = $memoriser->getHalaqa()->getId();
      $coursCoran    = $repoCours->findOneByHalaqa($halaqaId);
      $enseignantCoran = $coursCoran->getAnneeContrat()->getContrat()->getEnseignant()->getPnomAr()." ".$coursCoran->getAnneeContrat()->getContrat()->getEnseignant()->getNomAr();
    }
    else{
      foreach ($cours as $value) {
        if($value->getMatiere()->getId() == 1)
          $enseignantCoran = $value->getAnneeContrat()->getContrat()->getEnseignant()->getPnomAr()." ".$value->getAnneeContrat()->getContrat()->getEnseignant()->getNomAr();
      }
    }
    // dump($cours);
    // die();

    $date = $examen->getDateProclamationResultats();
    $dt = strftime("%A %d %B %G", strtotime(date_format($date, 'd F Y')));
    $dt = $this->dateToFrench($dt, "l j F Y");

    $notes     = $repoNote->findBy(['examen' => $examenId, 'eleve' => $eleveId]);

    $template = 'ISIBundle:Examen:bulletin.html.twig';
    if($as < 3)
      $template = 'ISIBundle:Examen:bulletin-old.html.twig';

    $html = $this->renderView($template, [
      'dt'              => $dt,
      'ens'             => $ens,
      'asec'            => $as,
      'regime'          => $regime,
      'annee'           => $annee, 
      'cours'           => $cours, 
      'enseignantCoran' => $enseignantCoran, 
      'annexe'          => $annexe,
      'eleve'           => $eleve,
      'classe'          => $classe,
      'examen'          => $examen,
      'notes'           => $notes,
      'effectif'        => $effectif,
      'moyenne'         => $moyenne,
      'server'          => $_SERVER["DOCUMENT_ROOT"],
      'heuresAbsence'   => $heuresAbsence,
    ]);


    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    // $snappy->setOption("orientation", "Landscape");
    $filename = "bulletin-de-".strtolower($eleve->getNomFr())."-".strtolower($eleve->getNomFr())."de-".$examen->getLibelleFr();

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
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/impression-des-bulletins-{as}-{regime}-{classeId}-{examenId}-{annexeId}", name="isi_bulletin_individuel")
   */
  public function bulletinIndividuelAction(Request $request, $as, $regime, $classeId, $examenId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    
    
    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
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
        'annee'   => $annee, 'annexe'   => $annexe,
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
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/impression-des-bulletins-d-une-classe-donnee-{as}-{regime}-{classeId}-{examenId}-{annexeId}", name="isi_bulletin")
   */
  public function bulletinsAction(Request $request, $as, $regime, $classeId, $examenId, int $annexeId)
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
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    
    /*********** - Etape 1: Sélection des données- ***********/
    $partie    = $request->query->get('partie');
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
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
      'annee'   => $annee, 'annexe'   => $annexe,
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
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/resultats-generaux-des-eleves-de-la-classe/{as}/{regime}/{classeId}/{examenId}-{annexeId}", name="isi_resultats_generaux_une_classe")
   */
  public function resultatsgenerauxDUneClasseAction(Request $request, int $as, $regime, $classeId, $examenId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
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
      'annexe'   => $annexe,
      'classe'   => $classe,
      'examen'   => $examen,
      'moyennes' => $moyennes,
    ]);
  }


  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/classement/{as}/{regime}/{classeId}/{examenId}-{annexeId}", name="isi_classement")
   */
  public function classementAction(Request $request, int $as, $regime, $classeId, $examenId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoMoyenne = $em->getRepository('ISIBundle:Moyenne');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoInformations = $em->getRepository('ISIBundle:Informations');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    
    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
    $classe    = $repoClasse->find($classeId);
    $examen    = $repoExamen->find($examenId);
    $informations = $repoInformations->find(1);
    
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
      'annee'   => $annee, 'annexe'   => $annexe,
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
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/bilan-annuel-{as}-{annexeId}", name="isi_bilan_annuel_home")
   */
  public function bilanAnnuelAction(Request $request, $as, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoExamen     = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $examens = $repoExamen->lesExamensDeLAnnee($as);
    if (count($examens) < 2) {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels pour le moment.');
      return $this->redirect($this->generateUrl('isi_saisie_de_notes', ['as' => $as, 'regime' => 'A']));
    }

    $annee       = $repoAnnee->find($as);

    return $this->render('ISIBundle:Examen:bilan-annuel-home.html.twig', [
      'asec' => $as,
      'annee' => $annee,
    ]);

  }

  // Bilan annuel
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/bilan-annuel-de-regime-{as}-{regime}-{annexeId}", name="isi_bilan_annuel_regime")
   */
  public function bilanAnnuelDUnRegimeAction(Request $request, $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoNiveau     = $em->getRepository('ISIBundle:Niveau');
    $repoClasse     = $em->getRepository('ISIBundle:Classe');
    $repoExamen     = $em->getRepository('ISIBundle:Examen');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $examens = $repoExamen->lesExamensDeLAnnee($as);
    if (count($examens) < 2) {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas voir les résultats annuels pour le moment.');
      return $this->redirect($this->generateUrl('isi_bilan_annuel_home', ['as' => $as]));
    }

    $annee      = $repoAnnee->find($as);
    $niveaux    = $repoNiveau->niveauxDuGroupe($regime);
    $classes    = $repoClasse->classeGrpFormation($as, $annexeId, $regime);
    $frequenter = $repoFrequenter->elevesDuRegime($as, $annexeId, $regime);
    // dump($frequenter);

    /**
     * On va compter le nombre de d'occurence de l'entité frequenter où l'attribut admission vaut NULL.
     * S'il vaut 0 alors, on peut passer au bilan. Sinon, beuh...
     */
    $nbr = 0;
    foreach ($frequenter as $fq) {
      # code...
      if ($fq->getAdmission() === NULL) {
        # code...
        $nbr++;
        // return new Response(var_dump($fq));
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
      'annee'   => $annee, 'annexe'   => $annexe,
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
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/examen/notes-des-eleves-d-une-classe-donnee-{as}-{regime}-{classeId}-{examenId}-{annexeId}", name="isi_notes_eleves_une_classe")
   */
  public function notesDesElevesDUneClasseAction(Request $request, $as, $regime, $classeId, $examenId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoNote    = $em->getRepository('ISIBundle:Note');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe  = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    /*********** - Etape 1: Sélection des données- ***********/
    $annee     = $repoAnnee->find($as);
    $eleves    = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
    $classe    = $repoClasse->find($classeId);
    $examen    = $repoExamen->find($examenId);
    $niveauId  = $classe->getNiveau()->getId();
    $matieres  = $repoMatiere->lesMatieresDeCompositionDuNiveau($as, $niveauId);

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
      'annexe'   => $annexe,
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

  public function total_des_heures_d_absence_d_un_eleve(int $eleveId, int $anneeId, int $examenId)
  {
    $em             = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoExamen     = $em->getRepository('ISIBundle:Examen');
    $repoPresence   = $em->getRepository('ISIBundle:Presence');
    $repoPermission = $em->getRepository('ISIBundle:Permission');
    $examen         = $repoExamen->find($examenId);
    $annee          = $repoAnnee->find($anneeId);
    $debut          = $examen->getSession()        == 1 ? $annee->getDebutPremierSemestre(): $annee->getDebutSecondSemestre();
    $fin            = $examen->getSession()        == 1 ? $annee->getFinPremierSemestre()  : $annee->getFinSecondSemestre();
    $presences      = $repoPresence->absence_eleve_periode($eleveId, $debut, $fin);
    $permissions    = $repoPermission->permission_eleve_periode($eleveId, $debut, $fin);
    $totalHeuresAbsences    = 0;
    $totalHeuresJustifiees = 0;
    foreach ($presences as $value) {
      if($value->getHeure1() == true)
        $totalHeuresAbsences++;
      if($value->getHeure2() == true)
        $totalHeuresAbsences++;
      if($value->getHeure3() == true)
        $totalHeuresAbsences++;
      if($value->getHeure4() == true)
        $totalHeuresAbsences++;
      if($value->getHeure5() == true)
        $totalHeuresAbsences++;
      if($value->getHeure6() == true)
        $totalHeuresAbsences++;
      if($value->getHeure7() == true)
        $totalHeuresAbsences++;
      if($value->getHeure8() == true)
        $totalHeuresAbsences++;

      foreach ($permissions as $permission) {
        if($value->getDate() >= $permission->getDepart() and $value->getDate() < $permission->getRetour()){
          if($value->getHeure1() == true)
            $totalHeuresJustifiees++;
          if($value->getHeure2() == true)
            $totalHeuresJustifiees++;
          if($value->getHeure3() == true)
            $totalHeuresJustifiees++;
          if($value->getHeure4() == true)
            $totalHeuresJustifiees++;
          if($value->getHeure5() == true)
            $totalHeuresJustifiees++;
          if($value->getHeure6() == true)
            $totalHeuresJustifiees++;
          if($value->getHeure7() == true)
            $totalHeuresJustifiees++;
          if($value->getHeure8() == true)
            $totalHeuresJustifiees++;
        }
      }
    }

    return ["totalHeuresAbsences" => $totalHeuresAbsences, "totalHeuresJustifiees" => $totalHeuresJustifiees];
  }

  public function calculMoyenneDUnEleve(int $eleveId, int $examenId, $enseignements)
  {
    $em       = $this->getDoctrine()->getManager();
    $repoNote = $em->getRepository('ISIBundle: Note');
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
        foreach($enseignements as $value){
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
      $totalNote = null;
    }
    return ["moyenne" => $moy, "totalPoints" => $totalNote];
  }

  public function calculMoyenneAnnuelleDUnEleve($frequenter, $enseignements)
  {
    $em     = $this->getDoctrine()->getManager();
    $repoFM = $em->getRepository('ISIBundle:FrequenterMatiere');
    $fm     = $repoFM->findBy(["frequenter" => $frequenter->getId()]);

    if(count($fm) == count($enseignements))
    {
      // Séquence 2:
      /* On boucle sur les notes de l'élève pour calculer la moyenne.
       * Mais avant, il faut initialiser le total des notes
       */
      $totalNote  = 0;
      $totalcoeff = 0;
      foreach ($fm as $moyenne) {
        foreach($enseignements as $value){
          if($moyenne->getMatiere()->getId() == $value->getMatiere()->getId()){
            $totalcoeff = $totalcoeff + $value->getCoefficient();
            $totalNote  = $totalNote + $moyenne->getMoyenne() * $value->getCoefficient();
          }
        }
      }
  
      // Séquence 3:
      // On calcul alors la moyenne
      $moyenne = $totalNote / $totalcoeff;
      $moy     = round($moyenne, 2);
      $admission = $moy >= 11 ? true : false;
    }
    else{
      $moy       = null;
      $totalNote = null;
      $admission = null;
    }
    return ["moyenne" => $moy, "totalPoints" => $totalNote, "admission" => $admission];
  }
}

?>
