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
use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ISIBundle\Entity\Groupeformation;

use ISI\ISIBundle\Repository\MoisRepository;
use ISI\ISIBundle\Repository\AbsenceRepository;
use ISI\ISIBundle\Repository\EleveRepository;
use ISI\ISIBundle\Repository\ClasseRepository;
use ISI\ISIBundle\Repository\NiveauRepository;
use ISI\ISIBundle\Repository\AnneescolaireRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AffairesScolairesController extends Controller
{
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function indexAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
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
    $repoAnnee = $em->getRepository('ISIBundle:Anneescolaire');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');

    $annee = $repoAnnee->anneeEnCours();
    // $examens = $repoExamen->lesExamensDeLAnnee($as);

    return $this->render('ISIBundle:Default:index.html.twig', array(
      'asec'   => $annee->getAnneeScolaireId(),
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
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
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
      $classeId = $classe->getClasseId();
      $niveauId = $classe->getNiveau()->getId();
      $effectif = $repoFrequenter->findBy(['anneeScolaire' => $as, 'classe' =>$classeId]);
      $nbrG = 0;
      $nbrR = 0;
      foreach ($effectif as $eff) {
        if($eff->getRedouble() == 1)
          $nbrR++;
        if($eff->getEleve()->getSexeFr() == 1)
          $nbrG++;
      }
      $stat['niveau'] = $niveauId;
      $stat['classe'] = $classeId;
      $stat['libelle'] = $classe->getLibelleClasseAr();
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
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
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
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
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
    $repoAnnee     = $em->getRepository('ISIBundle:Anneescolaire');

    $annee  = $repoAnnee->find($as);
    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $filename = "liste-de-classe-de-".$classe->getLibelleClasseFr();


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
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
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
    $repoAnnee     = $em->getRepository('ISIBundle:Anneescolaire');

    $annee  = $repoAnnee->find($as);

    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $listeHalaqa = [];
    foreach ($eleves as $eleve) {
      $listeHalaqa[] = $repoMemoriser->findOneBy(['anneeScolaire' => $as, 'eleve' => $eleve->getEleveId()]) ;
    }

    // return new Response(var_dump($listeHalaqa));

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "liste-d-appel-de-".$classe->getLibelleClasseFr();


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
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve  = $em->getRepository('ISIBundle:Eleve');
    $repoClasse = $em->getRepository('ISIBundle:Classe');

    $annee  = $repoAnnee->find($as);
    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "liste-d-appel-de-".$classe->getNiveau()->getLibelleFr()."-".$classe->getLibelleClasseFr();


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
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoHalaqa  = $em->getRepository('ISIBundle:Halaqa');
    $annee = $repoAnnee->find($as);
    $halaqas = $repoHalaqa->findBy(['anneeScolaire' => $as, 'regime' => $regime]);



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
    $repoAnnee = $em->getRepository('ISIBundle:Anneescolaire');
    $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');
    $repoClasse = $em->getRepository('ISIBundle:Classe');

    $annee  = $repoAnnee->find($as);
    $halaqa = $repoHalaqa->find($halaqaId);
    $memoriser = $repoMemoriser->findBy(['halaqa' => $halaqaId]);

    $classes = [];
    foreach ($memoriser as $memo) {
      $classes[] = $repoFrequenter->findOneBy(['eleve' => $memo->getEleve(), 'anneeScolaire' => $as]);
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
    $filename = "liste-de-halaqa-".$halaqa->getLibelleHalaqa();


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
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
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
    $repoAnnee = $em->getRepository('ISIBundle:Anneescolaire');
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
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
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
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
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
      $request->getSession()->getFlashBag()->add('error', 'Impossible de saisir des heures d\'absence car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
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
    $absenceDuMois = $repoAbsence->findBy(['eleve' => $eleves[0]->getEleveId(), 'mois' => $moisId, 'anneeScolaire' => $as]);
    if(empty($absenceDuMois))
    {
      // Création de l'entité absence pour chaque élève
      foreach ($eleves as $eleve) {
        $entiteAbsence = new Absence();
        $entiteAbsence->setAnneeScolaire($annee);
        $entiteAbsence->setEleve($eleve);
        $entiteAbsence->setMois($mois);
        $entiteAbsence->setDateSave(new \Datetime());
        $entiteAbsence->setDateUpdate(new \Datetime());

        $em->persist($entiteAbsence);
        $em->flush();
      }
    }

    // Ne pas confondre $absenceDuMois (sans s) avec le $absencesDuMois
    $absencesDuMois = [];
    foreach($eleves as $eleve)
    {
      $absencesDuMois[] = $repoAbsence->findOneBy(['eleve' => $eleve->getEleveId(), 'mois' => $moisId, 'anneeScolaire' => $as]);
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
          if($eleve->getEleveId() == $key)
          {
            $abs = $repoAbsence->findOneBy(['mois' => $moisId, 'eleve' => $key, 'anneeScolaire' => $as]);
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

            $abs->setDateUpdate(new \Datetime());
            $em->flush();
          }
        } // Fin foreach $eleves
      } // Fin foreach $data['note']

      $request->getSession()->getFlashBag()->add('info', 'Les heures d\'absence du mois de '.$mois->getMois().' de la classe '.$classe->getNiveau()->getLibelleFr().' '.$classe->getLibelleClasseFr().' ont été bien enregistrées.');
      return $this->redirect($this->generateUrl('isi_heures_absences_cours_d_une_classe_home',[
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

  // Page d'accueil des heures d'absences enregistrées
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function absenceEnregistreeHomeAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Anneescolaire');
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
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoMois    = $em->getRepository('ISIBundle:Mois');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoAbsence = $em->getRepository('ISIBundle:Absence');
    $repoExamen  = $em->getRepository('ISIBundle:Examen');
    $examens = $repoExamen->lesExamensDeLAnnee($as);

    // Sélection des informations à envoyer vers le formulaire
    $annee  = $repoAnnee->find($as);

    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $classe = $repoClasse->find($classeId);
    $absence == 'Cours' ? $absence = 'Cours' : $absence = 'Coran';

    // On va sélectionner les mois où les absences on été enregistrées
    $moisAbsences = $repoAbsence->findBy(['eleve' => $eleves[0]->getEleveId(), 'anneeScolaire' => $as]);

    // On va sélectionner les absences de tous les élèves de la classe
    $ids = $this->recupererLesIdsDesEleves($eleves);
    $absences = $repoAbsence->absencesDesEleveDeLaClasse($as, $ids);
    //foreach ($eleves as $eleve) {
      # code...
      //$absences[] = $repoAbsence->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);
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
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
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
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
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

    // return new Response(var_dump($eleves));
    foreach ($classes as $cl) {
      if($cl->getClasseId() == $classeId)

        unset($classes[array_search($classe, $classes)]);
    }

    return $this->render('ISIBundle:Scolarite:reamenager-une-classe.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'eleves'  => $eleves,
      'classe'  => $classe,
      'classes' => $classes,
      'examens' => $examens,
    ]);
  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function appliquerReamenagementAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
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

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de faire le réaménagement car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
      return $this->redirect($this->generateUrl('isi_reamenager_classes_home', ['as' => $as, 'regime' => $regime]));
    }

      /**
       * On va créer une variable qui s'appelle $bool. Sa valeur initiale est false.
       * Dès qu'il y a un élève pour qui on fait une réaménagement, sa valeur passera à true.
       * A la fin de notre boucle, on teste sa valeur et on affiche un message getFlashBag
       */
      $bool = false;
      foreach ($classes as $key => $cl) {
        if(!empty($classes[$key]))
        {
          $bool = true;
          $newClasse  = $repoClasse->find($classes[$key]);
          $eleve      = $repoEleve->find($key);

          // On va signifier que l'élève à changer de classe dans son historique
          $probleme     = new Probleme();
          $appreciation = 'Réaménagement';
          $description  = 'Changement de classe de '.$classe->getNiveau()->getLibelleFr().' - '.$classe->getLibelleClasseFr().' à '.$newClasse->getNiveau()->getLibelleFr().' - '.$newClasse->getLibelleClasseFr();
          $tab[] = $description;
          $probleme->setAppreciation($appreciation);
          $probleme->setDescription($description);
          $probleme->setDateSave(new \Datetime());
          $probleme->setDateUpdate(new \Datetime());

          $commettre = new Commettre();
          $commettre->setEleve($eleve);
          $commettre->setProbleme($probleme);
          $commettre->setAnneescolaire($annee);

          $em->persist($probleme);
          $em->persist($commettre);

          $frequenter = $repoFrequenter->findBy(['eleve' => $key, 'anneeScolaire' => $as, 'classe' => $classe]);
          foreach ($frequenter as $fq) {
            $fq->setClasse($newClasse);
            $fq->setDateUpdate(new \Datetime());
          }
        }
      }
      $em->flush();

      // Une message getFlashBag pour notifier l'utilisateur du résultat des réaménagements
      if ($bool == false) {
        $request->getSession()->getFlashBag()->add('info', 'Vous n\'avez effectué aucun changement dans la classe '.$classe->getNiveau()->getLibelleFr().' - '.$classe->getLibelleClasseFr());
      } else {
        $request->getSession()->getFlashBag()->add('info', 'Les réaménagements se sont bien effectués dans la classe '.$classe->getNiveau()->getLibelleFr().' - '.$classe->getLibelleClasseFr());
      }

      return $this->redirect($this->generateUrl('isi_reamenager_classes_home', [
        'as'     => $as,
        'regime' => $regime
      ]));
    }
  }

  /************** - ça, une une fonction personnelle. Elle sert à récupérer les ids des éleves à partir d'une liste d'élèves */
  public function recupererLesIdsDesEleves($eleves)
  {
    $lesIdsEleves = [];
    foreach($eleves as $eleve)
    {
        $lesIdsEleves[] = $eleve->getEleveId();
    }
    // $lesIdsEleves = $lesIdsEleves.'0';

    return $lesIdsEleves;
  }
  /************** - elle finie ici */
}

?>
