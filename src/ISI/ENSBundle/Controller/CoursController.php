<?php
namespace ISI\ENSBundle\Controller;

use ISI\ISIBundle\Entity\Annee;
use ISI\ISIBundle\Entity\Classe;
use ISI\ISIBundle\Entity\Matiere;
use ISI\ENSBundle\Entity\Contrat;
use ISI\ISIBundle\Entity\Enseignement;

use ISI\ENSBundle\Entity\Enseignant;
use ISI\ENSBundle\Entity\AnneeContrat;
use ISI\ENSBundle\Entity\AnneeContratClasse;

use ISI\ISIBundle\Repository\AnneeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use ISI\ENSBundle\Repository\ContratRepository;
use ISI\ENSBundle\Repository\EnseignantRepository;
use ISI\ISIBundle\Repository\AnneeContratRepository;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class CoursController extends Controller
{
  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function listeDesClassesSaveCoursAction(int $as, string $regime)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $annee      = $repoAnnee->find($as);
    $classes    = $repoClasse->classesDeLAnnee($as, $regime);


    return $this->render('ENSBundle:Cours:liste-des-classes.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'regime'  => $regime,
      'classes' => $classes,
    ]);
  }


  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function enregistrerCoursDuneClasseAction(Request $request, int $as, string $regime, int $classeId)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoAnneeContrat  = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoCours   = $em->getRepository('ENSBundle:AnneeContratClasse');
    $annee       = $repoAnnee->find($as);
    $classe      = $repoClasse->find($classeId);
    $niveauId    = $classe->getNiveau()->getId();
    $matieres    = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as);
    $coursDeLaClasse = $repoCours->findBy(["annee" => $as, "classe" => $classeId]);
    $infoCours = [];
    foreach ($coursDeLaClasse as $key => $value) {
      $cle = $value->getMatiere()->getId();
      $infoCours[$cle] = $value;
    }

    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $enseignants = $data["enseignants"];
      $mode = null;
      foreach ($enseignants as $key => $value) {
        $anneeContrat = $repoAnneeContrat->find($value);
        $verificationCours = $repoCours->findBy(["annee" => $as, "classe" => $classeId, "matiere" => $key]);
        if(empty($value))
        {
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas précisé l\'enseignant pour une matière.');
          return $this->redirectToRoute('ens_enregistrer_cours_classe', ['as' => $as, 'regime' => $regime, 'classeId' => $classeId]);
        }
        // elseif(!empty($verificationCours)){
        elseif(!empty($verificationCours) and $verificationCours[0]->getAnneeContrat()->getId() !== (int) $value){
          // return new Response(var_dump($verificationCours[0]->getAnneeContrat()->getId(), (int) $value));
          $mode = "modification";
          $verificationCours[0]->setAnneeContrat($anneeContrat);
          $verificationCours[0]->setUpdatedAt(new \DateTime());
          $verificationCours[0]->setUpdatedBy($this->getUser());
        }
        elseif(empty($verificationCours)){
          $mode = "enregistrement";
          $matiere = $repoMatiere->find($key);
          $cours = new AnneeContratClasse();
          $cours->setAnnee($annee);
          $cours->setClasse($classe);
          $cours->setMatiere($matiere);
          $cours->setAnneeContrat($anneeContrat);
          $cours->setCreatedAt(new \DateTime());
          $cours->setCreatedBy($this->getUser());
          $em->persist($cours);
        }
      }
      try{
        $em->flush();
        if ($mode == "modification") {
          $request->getSession()->getFlashBag()->add('info', 'Les cours de la classe '.$classe->getLibelleFr().' ont été mis à jour avec succès.');
        } 
        elseif ($mode == "enregistrement") {
          $request->getSession()->getFlashBag()->add('info', 'Les cours de la classe '.$classe->getLibelleFr().' ont été enrégistrés avec succès.');
        }
        
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      return $this->redirectToRoute('ens_liste_des_classes_save_cours', ['as' => $as, 'regime' => $regime, 'classeId' => $classeId]);
    }

    return $this->render('ENSBundle:Cours:enregistrer-cours-classe.html.twig', [
      'asec'          => $as,
      'annee'         => $annee,
      'regime'        => $regime,
      'classe'        => $classe,
      'infoCours'     => $infoCours,
      'matieres'      => $matieres,
      'anneeContrats' => $anneeContrats,
      'coursDeLaClasse'  => $coursDeLaClasse,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function voirLesCoursDeLaClasseAction(int $as, string $regime, int $classeId)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoEns     = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoCours   = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoEns     = $em->getRepository('ISIBundle:Enseignement');
    $annee       = $repoAnnee->find($as);
    $classe      = $repoClasse->find($classeId);
    $niveauId    = $classe->getNiveau()->getId();
    $ens         = $repoEns->findBy(['annee' => $as, 'niveau' => $niveauId]);
    $cours = $repoCours->findBy(["annee" => $as, "classe" => $classeId]);

    return $this->render('ENSBundle:Cours:cours-enregistres.html.twig', [
      'asec'   => $as,
      'annee'  => $annee,
      'regime' => $regime,
      'classe' => $classe,
      'cours'  => $cours,
      'ens'    => $ens
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function nombreHeuresCoursEnseignantsAction(Request $request, $as)
  {
    $em                     = $this->getDoctrine()->getManager();
    $repoEns                = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee              = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat       = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnneeContratClasse = $em->getRepository('ENSBundle:AnneeContratClasse');

    $annee = $repoAnnee->find($as);
    $anneeContrats  = $repoAnneeContrat->findBy(["annee" => $as]);
    $enseignements  = $repoEns->findBy(['annee' => $as]);
    $cours = $repoAnneeContratClasse->findBy(["annee" => $as]);

    $heuresCours = [];
    foreach ($anneeContrats as $key => $value) {
      $anneeContratId = $value->getId();
      $heureAcademie = 0;
      $heureCentreFormation = 0;
      foreach ($cours as $k => $v) {
        $niveau = $v->getClasse()->getNiveau();
        $regimeClasse = $niveau->getGroupeFormation()->getReference();
        if ($anneeContratId == $v->getAnneeContrat()->getId()) {
          foreach ($enseignements as $keyEns => $valueEns) {
            // Etape 1: On va calculer les heures de cours à l'académie
            if ($regimeClasse == 'A' and $valueEns->getNiveau()->getId() === $niveau->getId() and $v->getMatiere()->getId()  === $valueEns->getMatiere()->getId()) {
              $heureAcademie += $valueEns->getNombreHeureCours();
            } 
            // Etape 2: Puis ceux du centre de formation
            elseif ($regimeClasse == 'F' and $valueEns->getNiveau()->getId() === $niveau->getId() and $v->getMatiere()->getId()  === $valueEns->getMatiere()->getId()) {
              $heureCentreFormation += $valueEns->getNombreHeureCours();
            }
          }
        }
      }
      $heuresCours[$anneeContratId] = ["ens" => $value->getContrat()->getEnseignant()->getNomFr().' '.$value->getContrat()->getEnseignant()->getPnomFr(), "academie" => $heureAcademie, "centre" => $heureCentreFormation];
    }
    return $this->render('ENSBundle:Cours:nombre-heures-cours-des-enseignants.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      'heuresCours' => $heuresCours,
      'anneeContrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function coursDUnEnseignantAction(Request $request, $as, int $anneeContratId)
  {
    $em                     = $this->getDoctrine()->getManager();
    $repoEns                = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee              = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat       = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnneeContratClasse = $em->getRepository('ENSBundle:AnneeContratClasse');

    $annee = $repoAnnee->find($as);
    $anneeContrat   = $repoAnneeContrat->find($anneeContratId);
    $enseignements  = $repoEns->findBy(['annee' => $as]);
    $cours = $repoAnneeContratClasse->findBy(["annee" => $as]);

    $anneeContratId = $anneeContrat->getId();
    $heureAcademie = 0;
    $coursAcademie = [];
    $ensAcademie = [];
    $heureCentreFormation = 0;
    $coursCentreFormation = [];
    $ensCentreFormation = [];
    foreach ($cours as $k => $v) {
      $niveau = $v->getClasse()->getNiveau();
      $regimeClasse = $niveau->getGroupeFormation()->getReference();
      if ($anneeContratId == $v->getAnneeContrat()->getId()) {
        foreach ($enseignements as $key => $value) {
          // Etape 1: On va calculer les heures de cours à l'académie
          if ($regimeClasse == 'A' and $value->getNiveau()->getId() === $niveau->getId() and $v->getMatiere()->getId()  === $value->getMatiere()->getId()) {
            $heureAcademie += $value->getNombreHeureCours();
            $coursAcademie[] = $v;
            $ensAcademie[] = $value;
          } 
          // Etape 2: Puis ceux du centre de formation
          elseif ($regimeClasse == 'F' and $value->getNiveau()->getId() === $niveau->getId() and $v->getMatiere()->getId()  === $value->getMatiere()->getId()) {
            $heureCentreFormation += $value->getNombreHeureCours();
            $coursCentreFormation[] = $v;
            $ensCentreFormation[] = $value;
          }
        }
      }
    }
    $heuresCours = ["ens" => $anneeContrat->getContrat()->getEnseignant()->getNomFr().' '.$anneeContrat->getContrat()->getEnseignant()->getPnomFr(), "academie" => $heureAcademie, "centre" => $heureCentreFormation];
    return $this->render('ENSBundle:Cours:cours-d-un-enseignant.html.twig', [
      'asec'                 => $as,
      'annee'                => $annee,
      'heuresCours'          => $heuresCours,
      'coursCentreFormation' => $coursCentreFormation,
      'ensCentreFormation'   => $ensCentreFormation,
      'coursAcademie'        => $coursAcademie,
      'ensAcademie'          => $ensAcademie,
      'anneeContrat'         => $anneeContrat,      

    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function priseDeFonctionHomeAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');

    $annee  = $repoAnnee->find($as);
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire '.$annee->getLibelle().' est achevée.');
      return $this->redirect($this->generateUrl('ens_initialisation_prise_de_fonction', ['as' => $as]));
    }

    if($request->isMethod('post'))
    {
      $data      = $request->request->all();
      $matricule = $data['matricule'];
      if(empty($matricule))
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas saisie de matricule.');
        return $this->redirectToRoute('ens_initialisation_prise_de_fonction', ['as' => $as]);
      }
      $enseignant = $repoEnseignant->findOneBy(['matricule' => $matricule]);
      if(empty($enseignant))
      {
        $request->getSession()->getFlashBag()->add('error', 'Le matricule saisi ne correspond à aucun enseignant');
        return $this->redirectToRoute('ens_initialisation_prise_de_fonction', ['as' => $as]);
      }

      return $this->redirect($this->generateUrl('ens_prise_de_fonction', ['as' => $as, 'enseignantId' => $enseignant->getId()]));
    }

    return $this->render('ENSBundle:Enseignant:initialisation-de-prise-de-fonction-enseignant.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      // 'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function priseDeFonctionAction(Request $request, $as, $enseignantId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoContrat    = $em->getRepository('ENSBundle:Contrat');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $enseignant = $repoEnseignant->find($enseignantId);

    $annee   = $repoAnnee->find($as);
    $contrat = $repoContrat->dernierContrat($enseignantId);
    if(!is_null($contrat) and $contrat->getFini() == FALSE)
    {
      $request->getSession()->getFlashBag()->add('error', $enseignant->getNomFr().' '.$enseignant->getPnomFr().' est déjà en fonction.');
      return $this->redirect($this->generateUrl('ens_initialisation_prise_de_fonction', ['as'=> $as]));
    }

    $contrat = new Contrat();
    $contrat->setEnseignant($enseignant);
    $contrat->setCreatedBy($this->getUser());
    $contrat->setCreatedAt(new \Datetime());
    $form  = $this->createForm(ContratType::class, $contrat);

    if($form->handleRequest($request)->isValid())
    {
      $em = $this->getDoctrine()->getManager();
      $em->persist($contrat);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La prise de fonction de l\'enseignant(e) '.$enseignant->getNomFr().' '.$enseignant->getPnomFr().' a été enregistrée avec succès.');

      return $this->redirect($this->generateUrl('ens_initialisation_prise_de_fonction', ['as'=> $as]));
    }

    return $this->render('ENSBundle:Enseignant:prise-de-fonction.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      'form'       => $form->createView(),
      'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function fonctionEnCoursAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');

    $annee    = $repoAnnee->find($as);
    $contrats = $repoContrat->findBy(['fini' => FALSE]);

    return $this->render('ENSBundle:Enseignant:fonctions-en-cours.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'contrats' => $contrats,
    ]);
  }


  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function arretDeFonctionHomeAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoContrat    = $em->getRepository('ENSBundle:Contrat');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');

    $annee  = $repoAnnee->find($as);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire '.$annee->getLibelle().' est achevée.');
      return $this->redirect($this->generateUrl('ens_initialisation_l_arret_de_fonction', ['as' => $as]));
    }

    /*
      Pour arrêter un contrat, on va demander au user d'enter le matricule de l'enseignant dont le contrat doit être
      arrêté. On cherche alors l'existence d'un éventuel contrat (le dernier dans notre cas) lié à cet enseignant.

      Si on en trouve, on teste son attribut fini.
        SI celui-ci vaut TRUE, un warning est alors générer pour dire que cet enseignant n'a pas de contrat en cours.
        SINON on peut poursuivre l'annulation du contrat
    */
    if($request->isMethod('post'))
    {
      $data      = $request->request->all();
      $matricule = $data['matricule'];
      if(empty($matricule))
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas saisie de matricule.');
        return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as]);
      }
      $enseignant = $repoEnseignant->findOneBy(['matricule' => $matricule]);
      if(empty($enseignant))
      {
        $request->getSession()->getFlashBag()->add('error', 'Le matricule saisi ne correspond à aucun enseignant');
        return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as]);
      }
      else
      {
        $contrat = $repoContrat->dernierContrat($enseignant->getId());
        if(is_null($contrat))
        {
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez jamais eu de relation de travail avec l\'enseignant(e) '.$enseignant->getNomFr().' '.$enseignant->getPnomFr().' ('.$matricule.').');
          return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as]);
        }
        elseif($contrat->getFini() == TRUE)
        {
          $request->getSession()->getFlashBag()->add('error', 'Il n y a pas de contrat en cours pour l\'enseignant(e) '.$enseignant->getNomFr().' '.$enseignant->getPnomFr().' ('.$matricule.').');
          return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as]);
        }
        else
        {
          return $this->redirect($this->generateUrl('ens_arret_de_fonction', ['as' => $as, 'contratId' => $contrat->getId()]));
        }
      }

    }

    return $this->render('ENSBundle:Enseignant:initialisation-de-l-arret-de-fonction-enseignant.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      // 'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function arretDeFonctionAction(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoContrat    = $em->getRepository('ENSBundle:Contrat');

    $annee   = $repoAnnee->find($as);
    $contrat = $repoContrat->find($contratId);

    // $form = $this->createForm(ContratType::class, $contrat);

    if($request->isMethod('post'))
    {
      $data  = $request->request->all();
      $date  = $data['date'];
      $motif = $data['motif'];
      $date = new \DateTime($date);
      // $date = $date->format('Y-m-d');

      $contrat->setAnneeFin($annee);
      $contrat->setFin($date);
      $contrat->setFini(TRUE);
      $contrat->setMotifFin($motif);
      $contrat->setUpdatedBy($this->getUser());
      $contrat->setUpdatedAt(new \Datetime());
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'L\'arrêt de fonction de l\'enseignant '.$contrat->getEnseignant()->getNomFr().' '.$contrat->getEnseignant()->getPnomFr().' a s\'est effectué avec succès.');

      return $this->redirect($this->generateUrl('ens_initialisation_l_arret_de_fonction', ['as'=> $as]));
    }

    return $this->render('ENSBundle:Enseignant:arret-de-fonction.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'contrat' => $contrat,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function enseignantsDeLAnneeAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeContrat   = $em->getRepository('ENSBundle:AnneeContrat');

    $annee    = $repoAnnee->find($as);
    $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);
    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as);

    return $this->render('ENSBundle:Enseignant:enseignants-de-l-annee.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'contrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function impressionListeDesEnseignantsDeLAnneeAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeContrat   = $em->getRepository('ENSBundle:AnneeContrat');

    $annee    = $repoAnnee->find($as);
    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as);

    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $filename = "liste-des-enseignants-".$annee->getLibelle();

    $html = $this->renderView('ENSBundle:Enseignant:impression-liste-des-enseignants.html.twig', [
      // "title" => "Titre de mon document",
      "annee"         => $annee,
      "anneeContrats" => $anneeContrats,
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

    return $this->render('ENSBundle:Enseignant:enseignants-de-l-annee.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'contrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function impressionListeDesEnseignantsDeLAnneeAvecDetailsAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeContrat   = $em->getRepository('ENSBundle:AnneeContrat');

    $annee    = $repoAnnee->find($as);
    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as);

    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "liste-des-enseignants-".$annee->getLibelle();

    $html = $this->renderView('ENSBundle:Enseignant:impression-liste-des-enseignants-avec-details.html.twig', [
      // "title" => "Titre de mon document",
      "annee"         => $annee,
      "anneeContrats" => $anneeContrats,
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

    return $this->render('ENSBundle:Enseignant:enseignants-de-l-annee.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'contrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function ajouterEnseignantAnneeAction(Request $request, $as, $contratId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle: Annee');
    $repoContrat      = $em->getRepository('ENSBundle: Contrat');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');

    $annee        = $repoAnnee->find($as);
    $contrat      = $repoContrat->find($contratId);
    $anneeContrat = $repoAnneeContrat->findOneBy(['annee' => $as, 'contrat' => $contratId]);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire '.$annee->getLibelle().' est achevée.');
      return $this->redirect($this->generateUrl('ens_fonctions_en_cours', ['as' => $as]));
    }

    if(!is_null($anneeContrat))
    {
      $request->getSession()->getFlashBag()->add('error', $anneeContrat->getContrat()->getEnseignant()->getNomFr().' '.$anneeContrat->getContrat()->getEnseignant()->getPnomFr().' est déjà utilisé(e) pour l\'année '.$annee->getLibelle().'.');
      return $this->redirectToRoute('ens_fonctions_en_cours', ['as' => $as]);
    }

    $newAnneeContrat = new AnneeContrat();
    $newAnneeContrat->setAnnee($annee);
    $newAnneeContrat->setContrat($contrat);
    $newAnneeContrat->setCreatedBy($this->getUser());
    $newAnneeContrat->setCreatedAt(new \Datetime());
    $form  = $this->createForm(AnneeContratType::class, $newAnneeContrat);

    if($form->handleRequest($request)->isValid())
    {
      $em = $this->getDoctrine()->getManager();
      $em->persist($newAnneeContrat);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'Les heures de cours de '.$contrat->getEnseignant()->getNomFr().' '.$contrat->getEnseignant()->getPnomFr().' ont été bien enregistrées pour l\'année '.$annee->getLibelle().'.');

      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as'=> $as]));
    }

    return $this->render('ENSBundle:Enseignant:nouveau-annee-contrat.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'form'    => $form->createView(),
      'contrat' => $contrat,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function modifierEnseignantAnneeAction(Request $request, $as, $anneeContratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat   = $em->getRepository('ENSBundle:AnneeContrat');

    $annee    = $repoAnnee->find($as);
    $anneeContrat    = $repoAnneeContrat->find($anneeContratId);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire '.$annee->getLibelle().' est achevée.');
      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as' => $as]));
    }

    $form  = $this->createForm(AnneeContratType::class, $anneeContrat);

    if($form->handleRequest($request)->isValid())
    {
      $em = $this->getDoctrine()->getManager();
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'Les heures de cours de '.$anneeContrat->getContrat()->getEnseignant()->getNomFr().' '.$anneeContrat->getContrat()->getEnseignant()->getPnomFr().' ont été mise à jour avec succès pour l\'année '.$annee->getLibelle().'.');

      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as'=> $as]));
    }

    return $this->render('ENSBundle:Enseignant:edit-annee-contrat.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'form'    => $form->createView(),
      'contrat' => $anneeContrat,
    ]);
  }
}


 ?>
