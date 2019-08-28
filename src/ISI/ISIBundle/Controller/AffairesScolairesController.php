<?php

namespace ISI\ISIBundle\Controller;

use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\QueryBuilder;

use Dompdf\Options;
use Dompdf\Dompdf;

use ISI\ISIBundle\Entity\Eleve;
use ISI\ISIBundle\Entity\Classe;
use ISI\ISIBundle\Entity\Niveau;
use ISI\ISIBundle\Entity\Absence;
use ISI\ISIBundle\Entity\Probleme;
use ISI\ISIBundle\Entity\Commettre;
use ISI\ISIBundle\Entity\Annee;
use ISI\ISIBundle\Entity\Groupeformation;

use ISI\ISIBundle\Repository\MoisRepository;
use ISI\ISIBundle\Repository\AbsenceRepository;
use ISI\ISIBundle\Repository\EleveRepository;
use ISI\ISIBundle\Repository\ClasseRepository;
use ISI\ISIBundle\Repository\NiveauRepository;
use ISI\ISIBundle\Repository\AnneeRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AffairesScolairesController extends Controller
{
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function indexAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoExamen = $em->getRepository('ISIBundle:Examen');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    return $this->render('ISIBundle:Scolarite:index.html.twig', array(
      'asec'   => $as,
      'regime' => $regime,
      'annee'  => $annee,
      'examens' => $examens,
    ));
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function homeScolariteAction()
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');

    $annee = $repoAnnee->anneeEnCours();
    // $examens = $repoExamen->lesExamensDeLAnnee($as);

    return $this->render('ISIBundle:Default:index.html.twig', array(
      'asec'   => $annee->getId(),
      'annee'  => $annee,
      // 'examens' => $examens,
    ));
  }

  // On va se permettre de savoir un peu plus sur les données statistques (pourcentage des filles, des garçons, redoublants et autres)
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function statistiquesAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    $annee = $repoAnnee->anneeEnCours();
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    $classes = $repoClasse->classeGrpFormation($as, $regime);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    // On va créer un tableau pour la sauvegarde des données statistiques.
    $statClasses = [];
    foreach ($classes as $classe) {
      $stat = [];
      $classeId = $classe->getId();
      $niveauId = $classe->getNiveau()->getId();
      $effectif = $repoFrequenter->statistiquesClasse($classeId);
      $nbrG = 0;
      $nbrR = 0;
      foreach ($effectif as $eff) {
        if($eff->getRedouble() == 1)
          $nbrR++;
        if($eff->getEleve()->getSexe() == 1)
          $nbrG++;
      }
      $stat['niveau'] = $niveauId;
      $stat['classe'] = $classeId;
      $stat['libelle'] = $classe->getLibelleAr();
      $stat['nbrG']   = $nbrG;
      $stat['nbrR']   = $nbrR;
      $stat['effectif'] = count($effectif);

      $statClasses[] = $stat;
    }

    return $this->render('ISIBundle:Scolarite:statistiques.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classes' => $classes,
      'niveaux' => $niveaux,
      'examens' => $examens,
      'statClasses' => $statClasses,
    ]);
  }

  // Affichage des classe pour l'impression des liste de classe
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function listeDeClasseHomeAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);
    $examens = $repoExamen->lesExamensDeLAnnee($as);
    $classes = $repoClasse->classeGrpFormation($as, $regime);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    return $this->render('ISIBundle:Scolarite:liste-de-classe-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classes' => $classes,
      'niveaux' => $niveaux,
      'examens' => $examens,
    ]);
  }

  // La function de sélection les élèves d'une classe donnée
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function listeDeClasseAction(Request $request, $as, $regime, $classeId)
  {
    $repoEleve  = $this->getDoctrine()->getManager()->getRepository('ISIBundle:Eleve');
    $repoClasse = $this->getDoctrine()->getManager()->getRepository('ISIBundle:Classe');

    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    return $this->render('ISIBundle:Scolarite:liste-de-la-classe.html.twig', [
      'classe' => $classe,
      'eleves' => $eleves
    ]);
  }

  // Pour afficher simplement la liste de la classe sans l'imprimer
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function listeDeClasseEtatSansImpressionAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoEleve  = $em->getRepository('ISIBundle:Eleve');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    return $this->render('ISIBundle:Scolarite:liste-de-la-classe-edition-sans-impression.html.twig', [
      "asec"   => $as,
      "regime" => $regime,
      "annee"  => $annee,
      "classe" => $classe,
      "eleves" => $eleves,
      "examens" => $examens,
    ]);
  }

  // Pour tirer la liste de classe d'une classe bien donnée [La function pour la génération du fichier pdf]
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function tirerListeDeLaClasseAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve     = $em->getRepository('ISIBundle:Eleve');
    $repoClasse    = $em->getRepository('ISIBundle:Classe');
    $repoAnnee     = $em->getRepository('ISIBundle:Annee');

    $annee  = $repoAnnee->find($as);
    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $filename = "liste-de-classe-de-".$classe->getLibelleFr();


    // $html = $this->renderView('../../../../scr/ISIBundle/Resources/views/Scolarite/liste-de-la-classe.html.twig', [
    $html = $this->renderView('ISIBundle:Scolarite:liste-de-la-classe.html.twig', [
      // "title" => "Titre de mon document",
      "annee" => $annee,
      "classe" => $classe,
      "eleves" => $eleves,
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

  // Affichage des classes pour l'impression des listes d'appel
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function listeDAppelHomeAction(Request $request, $as, $regime)
  {
    $em = $this->getdoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $this->getDoctrine()->getManager()->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    $classes = $repoClasse->classeGrpFormation($as, $regime);
    return $this->render('ISIBundle:Scolarite:liste-d-appel-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classes' => $classes,
      'examens' => $examens
    ]);
  }

  // Pour tirer la liste d'appel d'une classe bien donnée [La function pour la génération du fichier pdf] Academie
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function tirerListeDAppelDeLaClasseAcademieAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve     = $em->getRepository('ISIBundle:Eleve');
    $repoClasse    = $em->getRepository('ISIBundle:Classe');
    $repoMemoriser = $em->getRepository('ISIBundle:Memoriser');
    $repoAnnee     = $em->getRepository('ISIBundle:Annee');

    $annee  = $repoAnnee->find($as);

    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $listeHalaqa = [];
    foreach ($eleves as $eleve) {
      $listeHalaqa[] = $repoMemoriser->findOneBy(['annee' => $as, 'eleve' => $eleve['id']]) ;
    }

    // return new Response(var_dump($listeHalaqa));

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "liste-d-appel-de-".$classe->getLibelleFr();


    // $html = $this->renderView('../../../../scr/ISIBundle/Resources/views/Scolarite/liste-de-la-classe.html.twig', [
    $html = $this->renderView('ISIBundle:Scolarite:liste-d-appel-de-la-classe-academie.html.twig', [
      // "title" => "Titre de mon document",
      "classe" => $classe,
      "eleves" => $eleves,
      "annee"  => $annee,
      "elevesH" => $listeHalaqa,
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
          // 'orientation'         => 'Landscape',
          // 'default-header'      => true,
          'Content-Type'        => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"',
        ]
    );
  }

  // Pour tirer la liste d'appel d'une classe bien donnée [La function pour la génération du fichier pdf] Formation
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function tirerListeDAppelDeLaClasseFormationAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoEleve  = $em->getRepository('ISIBundle:Eleve');
    $repoClasse = $em->getRepository('ISIBundle:Classe');

    $annee  = $repoAnnee->find($as);
    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "liste-d-appel-de-".$classe->getNiveau()->getLibelleFr()."-".$classe->getLibelleFr();


    // $html = $this->renderView('../../../../scr/ISIBundle/Resources/views/Scolarite/liste-de-la-classe.html.twig', [
    $html = $this->renderView('ISIBundle:Scolarite:liste-d-appel-de-la-classe-formation.html.twig', [
      // "title" => "Titre de mon document",
      "annee" => $annee,
      "classe" => $classe,
      "eleves" => $eleves,
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
          // 'orientation'         => 'Landscape',
          // 'default-header'      => true,
          'Content-Type'        => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"',
        ]
    );
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function listeDesHalaqasAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoHalaqa  = $em->getRepository('ISIBundle:Halaqa');
    $annee = $repoAnnee->find($as);
    $halaqas = $repoHalaqa->findBy(['annee' => $as, 'regime' => $regime]);



    return $this->render('ISIBundle:Scolarite:liste-de-halaqa-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'halaqas' => $halaqas,
    ]);

  }

  // Edition de la liste des élèves d'une halaqa
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function listeDesElevesDUneHalaqaAction(Request $request, $as, $regime, $halaqaId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoMemoriser  = $em->getRepository('ISIBundle:Memoriser');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');
    $repoClasse = $em->getRepository('ISIBundle:Classe');

    $annee  = $repoAnnee->find($as);
    $halaqa = $repoHalaqa->find($halaqaId);
    $memoriser = $repoMemoriser->findBy(['halaqa' => $halaqaId]);

    $classes = [];
    foreach ($memoriser as $memo) {
      $classes[] = $repoFrequenter->findOneBy(['eleve' => $memo->getEleve(), 'annee' => $as]);
    }

    // return new Response(var_dump($classes));

    foreach ($memoriser as $key => $memo) {
      $nom[$key]   = $memo->getEleve()->getNomFr();
      $pnom[$key]  = $memo->getEleve()->getPnomFr();
    }

    // array_multisort() permet de trier un tableau multidimensionnel
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $memoriser);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "liste-de-halaqa-".$halaqa->getLibelle();


    // $html = $this->renderView('../../../../scr/ISIBundle/Resources/views/Scolarite/liste-de-la-classe.html.twig', [
    $html = $this->renderView('ISIBundle:Scolarite:liste-des-eleves-de-halaqa.html.twig', [
      // "title" => "Titre de mon document",
      "annee"     => $annee,
      "regime"    => $regime,
      "halaqa"    => $halaqa,
      "classes"   => $classes,
      "memoriser" => $memoriser,
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
          // 'orientation'         => 'Landscape',
          // 'default-header'      => true,
          'Content-Type'        => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"',
        ]
    );
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
    return $this->render('ISIBundle:Scolarite:afficher-classes-pour-saisir-de-notes.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'classes'  => $classes,
      'examen'   => $examen,
      'annee'    => $annee,
      'niveaux'  => $niveaux,
      'examens'  => $examens,
    ]);
  }

  // Page d'accueil de la gestion des absences
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function absenceHomeAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    $annee   = $repoAnnee->find($as);
    $classes = $repoClasse->classeGrpFormation($as, $regime);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    return $this->render('ISIBundle:Scolarite:gestion-des-absences-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classes' => $classes,
      'niveaux' => $niveaux,
      'examens' => $examens
    ]);
  }

  // Page d'accueil de la gestion des heures d'absence de cours (les cours en classe) de la classe passée en paramètre
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function heuresAbsenceCoursHomeAction(Request $request, $as, $regime, $classeId, $absence)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoMois   = $em->getRepository('ISIBundle:Mois');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    $absence == 'Cours' ? $absence = 'Cours' : $absence = 'Coran';

    $annee  = $repoAnnee->find($as);
    $mois   = $repoMois->findAll();
    $classe = $repoClasse->find($classeId);

    return $this->render('ISIBundle:Scolarite:heures-d-absence-cours-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classe'  => $classe,
      'absence' => $absence,
      'examens' => $examens,
      'mois'    => $mois,
    ]);
  }

  // On affiche un formulaire pour permettre à l'utilisateur de saisir les heures d'absence des élèves d'une classe donnée
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function saisieDesHeuresDAbsenceAction(Request $request, $as, $regime, $classeId, $moisId, $absence)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoMois    = $em->getRepository('ISIBundle:Mois');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoAbsence = $em->getRepository('ISIBundle:Absence');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    // Sélection des informations à envoyer vers le formulaire
    $annee  = $repoAnnee->find($as);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de saisir des heures d\'absence car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('isi_accueil_gestion_absences', ['as' => $as, 'regime' => $regime]));
    }

    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $classe = $repoClasse->find($classeId);
    $mois   = $repoMois->find($moisId);
    $absence == 'Cours' ? $absence = 'Cours' : $absence = 'Coran';

    // Je sélectionne les absences du mois de tous les élèves de la classe. Ces absences se trouvent dans $absencesDuMois
    /**
     * Si $absencesDuMois est vide, alors on va générer les entités absence du mois pour chaque élève
     * Sinon, on affiche directement le formulaire pour la saisie des heures d'absences.
     */
    $absenceDuMois = $repoAbsence->findBy(['eleve' => $eleves[0]['id'], 'mois' => $moisId, 'annee' => $as]);
    if(empty($absenceDuMois))
    {
      // Création de l'entité absence pour chaque élève
      foreach ($eleves as $eleve) {
        $eleveAbsent = $repoEleve->find($eleve['id']);
        $entiteAbsence = new Absence();
        $entiteAbsence->setAnnee($annee);
        $entiteAbsence->setEleve($eleveAbsent);
        $entiteAbsence->setMois($mois);
        $entiteAbsence->setCreatedBy($this->getUser());
        $entiteAbsence->setCreatedAt(new \Datetime());

        $em->persist($entiteAbsence);
        $em->flush();
      }
    }
    else{
      if((!is_null($absenceDuMois[0]->getHeureCours()) and $absence == 'Cours') or (!is_null($absenceDuMois[0]->getHeureCoran()) and $absence == 'Coran'))
      {
        $request->getSession()->getFlashBag()->add('error', 'Les absences en "'.$absence.'" de ce mois ont déjà été saisies pour cette classe.');
        return $this->redirect($this->generateUrl('isi_accueil_gestion_absences',[
          'as'       => $as,
          'regime'   => $regime,
          'classeId' => $classeId,
          'absence'  => $absence,
          'moisId'   => $moisId
        ]));
      }
    }

    // Ne pas confondre $absenceDuMois (sans s) avec le $absencesDuMois
    $absencesDuMois = [];
    foreach($eleves as $eleve)
    {
      $absencesDuMois[] = $repoAbsence->findOneBy(['eleve' => $eleve['id'], 'mois' => $moisId, 'annee' => $as]);
    }

    $defaultData = array('message' => 'Type your message here');
    $form        = $this->createFormBuilder($defaultData);
    $form->getForm();

    // Quand on soumet le formulaire
    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      // return new Response(var_dump($data));
      foreach($data['abs'] as $key => $absenceEleve)
      {
        // On vérifie qu'aucune des notes saisies n'est pas supérieures à 10.
        if(!is_numeric($absenceEleve))
        {
          $eleveNoteSup = $eleve;
          $request->getSession()->getFlashBag()->add('error', 'Les heures sont des données numériques.');

          return $this->redirect($this->generateUrl('isi_saisie_des_heures_d_absence',[
            'as'       => $as,
            'regime'   => $regime,
            'classeId' => $classeId,
            'absence'  => $absence,
            'moisId'   => $moisId
          ]));
        }

        foreach($eleves as $eleve)
        {
          if($eleve['id'] == $key)
          {
            $abs = $repoAbsence->findOneBy(['mois' => $moisId, 'eleve' => $key, 'annee' => $as]);
            // Cette condition me permet de savoir si l'élève à composé dans la matière ou non
            if($absence == 'Cours')
            {
              if(empty($absenceEleve))
                $abs->setHeureCours(0);
              else
                $abs->setHeureCours($absenceEleve);
            }
            else{
              if(empty($absenceEleve))
                $abs->setHeureCoran(0);
              else
                $abs->setHeureCoran($absenceEleve);
            }

            $abs->setUpdatedBy($user);
            $abs->setUpdatedAt(new \Datetime());
          }
        } // Fin foreach $eleves
      } // Fin foreach $data['note']

      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Les heures d\'absence du mois de <strong>'.$mois->getMois().'</strong> de la classe <strong>'.$classe->getLibelleFr().'</strong> ont été bien enregistrées.');
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      return $this->redirect($this->generateUrl('isi_heures_absences_d_une_classe_home',[
        'as' => $as,
        'regime' => $regime,
        'classeId' => $classeId,
        'absence' => $absence
      ]));
    }

    return $this->render('ISIBundle:Scolarite:formulaire-de-saisie-des-heures-d-absence.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classe'  => $classe,
      'mois'    => $mois,
      'eleves'  => $eleves,
      'absence' => $absence,
      'examens' => $examens,
      'absencesDuMois' => $absencesDuMois
    ]);
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function modificationDesHeuresDAbsenceAction(Request $request, $as, $regime, $classeId, $moisId, $absence)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoMois    = $em->getRepository('ISIBundle:Mois');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoAbsence = $em->getRepository('ISIBundle:Absence');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    // Sélection des informations à envoyer vers le formulaire
    $annee  = $repoAnnee->find($as);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de modifier des heures d\'absence car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('isi_accueil_gestion_absences', ['as' => $as, 'regime' => $regime]));
    }

    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $classe = $repoClasse->find($classeId);
    $mois   = $repoMois->find($moisId);
    $absence == 'Cours' ? $absence = 'Cours' : $absence = 'Coran';


    $absencesDuMois = $repoAbsence->absenceDuMoisDesElevesDeLaClasse($classeId, $moisId, $as);

    $defaultData = array('message' => 'Type your message here');
    $form        = $this->createFormBuilder($defaultData);
    $form->getForm();

    // Quand on soumet le formulaire
    if($request->isMethod('post')){
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      // return new Response(var_dump($data));
      foreach($data['abs'] as $key => $absenceEleve)
      {
        // On vérifie qu'aucune des notes saisies n'est pas supérieures à 10.
        if(!is_numeric($absenceEleve))
        {
          $eleveNoteSup = $eleve;
          $request->getSession()->getFlashBag()->add('error', 'Les heures sont des données numériques.');

          return $this->redirect($this->generateUrl('isi_modification_des_heures_d_absence',[
            'as'       => $as,
            'regime'   => $regime,
            'classeId' => $classeId,
            'absence'  => $absence,
            'moisId'   => $moisId
          ]));
        }
        $user = $this->getUser();

        foreach($eleves as $eleve)
        {
          if($eleve['id'] == $key)
          {
            $abs = $repoAbsence->findOneBy(['mois' => $moisId, 'eleve' => $key, 'annee' => $as]);
            // Cette condition me permet de savoir si l'élève à composé dans la matière ou non
            if($absence == 'Cours')
            {
              if(empty($absenceEleve))
                $abs->setHeureCours(0);
              else
                $abs->setHeureCours($absenceEleve);
            }
            else{
              if(empty($absenceEleve))
                $abs->setHeureCoran(0);
              else
                $abs->setHeureCoran($absenceEleve);
            }

            $abs->setUpdatedBy($user);
            $abs->setUpdatedAt(new \Datetime());
          }
        } // Fin foreach $eleves
      } // Fin foreach $data['note']

      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Les heures d\'absence du mois de <strong>'.$mois->getMois().'</strong> de la classe <strong>'.$classe->getLibelleFr().'</strong> ont été bien enregistrées.');
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      return $this->redirect($this->generateUrl('isi_heures_absences_enregistrees',[
        'as'       => $as,
        'regime'   => $regime,
        'classeId' => $classeId,
        'absence'  => $absence
      ]));
    }

    return $this->render('ISIBundle:Scolarite:formulaire-de-modification-des-heures-d-absence.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classe'  => $classe,
      'mois'    => $mois,
      'eleves'  => $eleves,
      'absence' => $absence,
      'examens' => $examens,
      'absencesDuMois' => $absencesDuMois
    ]);
  }

  // Page d'accueil des heures d'absences enregistrées
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function absenceEnregistreeHomeAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    $annee   = $repoAnnee->find($as);
    $classes = $repoClasse->classeGrpFormation($as, $regime);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    return $this->render('ISIBundle:Scolarite:heures-d-absences-enregistrees-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classes' => $classes,
      'niveaux' => $niveaux,
      'examens' => $examens
    ]);
  }

  // Cette page affichera les heures d'absences enregistrées d'une classe
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function heuresAbsenceEnregistreesAction(Request $request, $as, $regime, $classeId, $absence)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoMois    = $em->getRepository('ISIBundle:Mois');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoAbsence = $em->getRepository('ISIBundle:Absence');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    // Sélection des informations à envoyer vers le formulaire
    $annee  = $repoAnnee->find($as);

    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    // return new Response(var_dump($eleves));
    $classe = $repoClasse->find($classeId);
    $absence == 'Cours' ? $absence = 'Cours' : $absence = 'Coran';

    // On va sélectionner les mois où les absences on été enregistrées
    $moisAbsences = $repoAbsence->findBy(['eleve' => reset($eleves)['id'], 'annee' => $as]);

    // On va sélectionner les absences de tous les élèves de la classe
    $ids = $this->recupererLesIdsDesEleves($eleves);
    $absences = $repoAbsence->absencesDesEleveDeLaClasse($as, $ids);
    //foreach ($eleves as $eleve) {
      # code...
      //$absences[] = $repoAbsence->findOneBy(['eleve' => $eleve->getId(), 'annee' => $as]);
    //}

    return $this->render('ISIBundle:Scolarite:heures-d-absence-enregistrees.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classe'  => $classe,
      'eleves'  => $eleves,
      'absence' => $absence,
      'absences' => $absences,
      'moisAbsences' => $moisAbsences,
      // 'examens' => $examens,
      // 'absencesDuMois' => $absencesDuMois
    ]);
  }

  // Accueil pour le réaménagement des classes
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function reamenagerClassesHomeAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    // Sélection des entités
    $annee = $repoAnnee->find($as);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);
    $classes = $repoClasse->classesDeLAnnee($as, $regime);

    return $this->render('ISIBundle:Scolarite:reamenager-les-classes-home.html.twig', [
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'niveaux'  => $niveaux,
      'classes'  => $classes,
      'examens'  => $examens,
    ]);
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function reamenagerUneClasseAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    // Sélection des entités
    $annee    = $repoAnnee ->find($as);
    $classe   = $repoClasse->find($classeId);
    $niveauId = $classe    ->getNiveau()->getId();
    $classes  = $repoClasse->lesClassesDuNiveau($as, $niveauId);
    $eleves   = $repoEleve ->lesElevesDeLaClasse($as, $classeId);
    $halaqas  = [];
    if ($regime == 'A') {
      # code...
      $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');
      $halaqas    = $repoHalaqa->findBy(['annee' => $as, 'regime' => $regime]);
    }

    // return new Response(var_dump($eleves));
    foreach ($classes as $cl) {
      if($cl->getId() == $classeId)

        unset($classes[array_search($classe, $classes)]);
    }

    return $this->render('ISIBundle:Scolarite:reamenager-une-classe.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'eleves'  => $eleves,
      'classe'  => $classe,
      'classes' => $classes,
      'halaqas' => $halaqas,
      'examens' => $examens,
    ]);
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function appliquerReamenagementAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoClasse     = $em->getRepository('ISIBundle:Classe');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    // Sélection des entités
    $annee    = $repoAnnee ->find($as);
    $classe   = $repoClasse->find($classeId);
    $niveauId = $classe    ->getNiveau()->getId();
    $classes  = $repoClasse->lesClassesDuNiveau($as, $niveauId);
    $eleves   = $repoEleve ->lesElevesDeLaClasse($as, $classeId);

    if($request->isMethod('post'))
    {
      $em = $this->getDoctrine()->getManager();
      $data    = $request->request->all();
      $classes = $data['classe'];
      if ($regime == 'A') {
        $halaqas = $data['halaqa'];
        $repoHalaqa    = $em->getRepository('ISIBundle:Halaqa');
        $repoMemoriser = $em->getRepository('ISIBundle:Memoriser');
      }
      $check_recording = false;
      $classe_recording = false;
      $halaqa_recording = false;

      /**
       * Quand l'année scolaire est finie, on doit plus faire des
       * mofications, des mises à jour
       **/
      if($annee->getAchevee() == TRUE)
      {
        $request->getSession()->getFlashBag()->add('error', 'Impossible de faire le réaménagement car l\'année scolaire <strong>'.$annee->getLibelleAnnee().'</strong> est achevée.');
        return $this->redirect($this->generateUrl('isi_reamenager_classes_home', ['as' => $as, 'regime' => $regime]));
      }
      $user = $this->getUser();

      foreach ($classes as $key => $cl) {
        if(!empty($cl)){
          if(($regime == 'A' && array_key_exists($key, $halaqas) && $halaqas[$key] != null) || $regime == 'F'){
            $eleve = $repoEleve->find($key);
            $nvoClasse = $repoClasse->find((int) $cl);
            // On va signifier que l'élève à changer de classe dans son historique
            $probleme     = new Probleme();
            $appreciation = 'Réaménagement';
            $description  = 'Changement de classe de '.$classe->getLibelleFr().' à '.$nvoClasse->getLibelleFr();
            $frequenter = $repoFrequenter->findOneBy(['eleve' => $key, 'annee' => $as, 'classe' => $classeId]);
            
            $tab[] = $description;
            $probleme->setAppreciation($appreciation);
            $probleme->setDescription($description);
            $probleme->setDate(new \Datetime());
            $probleme->setCreatedBy($user);
            $probleme->setCreatedAt(new \Datetime());

            $commettre = new Commettre();
            $commettre->setEleve($eleve);
            $commettre->setProbleme($probleme);
            $commettre->setAnnee($annee);
            $commettre->setCreatedBy($user);

            $em->persist($probleme);
            $em->persist($commettre);

            $frequenter->setClasse($nvoClasse);
            $frequenter->setUpdatedBy($user);
            $frequenter->setUpdatedAt(new \Datetime());
            
            $check_recording = true;
          }
          else{
            $classe_recording = true;
          }
        }
      }

      if($regime == 'A'){
        foreach ($halaqas as $key => $hal) {
          if(!empty($hal) && array_key_exists($key, $classes) && $classes[$key] != null){
              $eleve = $repoEleve->find($key);
              $halaqa = $repoHalaqa->find((int) $hal);
              $memoriser = $repoMemoriser->findOneBy(['eleve' => $key, 'annee' => $as]);
              $memoriser->setHalaqa($halaqa);
              $memoriser->setUpdatedBy($user);
              $memoriser->setUpdatedAt(new \Datetime());
          }
          else{
            $halaqa_recording = true;
          }
        }
      }

      if ($check_recording == false && $classe_recording == false) {
        # On entre dans cette condition s'il n'y a pas eu d'enregistrement
        $request->getSession()->getFlashBag()->add('error', 'Aucun changement constaté. Veuillez essayer à nouveau.');
        return $this->redirect($this->generateUrl('isi_reamenager_classes_home', ['as' => $as, 'regime' => $regime]));
      } else {
        # Beuh, sinon on flush les entités nouvellement créées
        if ($classe_recording) {
          $request->getSession()->getFlashBag()->add('error', 'Les classes de tous les élèves n\'ont pas été précisées.');
        }
        if ($halaqa_recording) {
          $request->getSession()->getFlashBag()->add('error', 'Il y a au moins un élève pour qui aucune halaqa n\'a été présicée');
        }
        if($check_recording == true){
          try{
            $em->flush();
            $this->addFlash('info', 'Flash inscription de la classe <strong>'.$classe->getLibelleFr().'</strong> de l\'année précédente effectué avec succès.');
          } 
          catch(\Doctrine\ORM\ORMException $e){
            $this->addFlash('error', $e->getMessage());
            $this->get('logger')->error($e->getMessage());
          } 
          catch(\Exception $e){
            $this->addFlash('error', $e->getMessage());
          }
        }
      }

      return $this->redirect($this->generateUrl('isi_reamenager_classes_home', [
        'as'     => $as,
        'regime' => $regime
      ]));
    }
  }

  public function dispositionDesSallesDeCoursAction(Request $request, int $as, string $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $repoSalle = $em->getRepository('ISIBundle:Salle');
    $repoSC    = $em->getRepository('ISIBundle:SalleClasse');
    $annee     = $repoAnnee->find($as);
    $salles    = $repoSalle->findAll();
    $sallesClasses = $repoSC->findBy(['annee' => $as]);
    $sallesAOQP = [];
    $sallesFOQP = [];
    foreach ($sallesClasses as $key => $value) {
      if ($value->getClasse()->getNiveau()->getGroupeFormation()->getId() == 1) {
        # code...
        $sallesAOQP[] = $value->getSalle();
      } 
      elseif ($value->getClasse()->getNiveau()->getGroupeFormation()->getId() == 2) {
        # code...
        $sallesFOQP[] = $value->getSalle();
      }
      
    }
    // dump($sallesClasses, $salles);

    return $this->render('ISIBundle:Scolarite:disposition.html.twig', [
      'asec'          => $as,
      'regime'        => $regime,
      'annee'         => $annee,
      'salles'        => $salles,
      'sallesAOQP'    => $sallesAOQP,
      'sallesFOQP'    => $sallesFOQP,
      'sallesClasses' => $sallesClasses,
    ]);
  }

  /************** - ça, une une fonction personnelle. Elle sert à récupérer les ids des éleves à partir d'une liste d'élèves */
  public function recupererLesIdsDesEleves($eleves)
  {
    $lesIdsEleves = [];
    foreach($eleves as $eleve)
    {
        $lesIdsEleves[] = $eleve['id'];
    }
    // $lesIdsEleves = $lesIdsEleves.'0';

    return $lesIdsEleves;
  }
  /************** - elle finie ici */
}

?>
