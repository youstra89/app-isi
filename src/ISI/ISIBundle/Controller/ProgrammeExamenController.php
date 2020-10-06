<?php

namespace ISI\ISIBundle\Controller;

use ISI\ISIBundle\Entity\Correction;
use ISI\ISIBundle\Entity\Surveillance;
use ISI\ISIBundle\Entity\GroupeComposition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use ISI\ISIBundle\Entity\ProgrammeExamenNiveau;
use ISI\ISIBundle\Entity\EleveGroupeComposition;
use Symfony\Component\HttpFoundation\JsonResponse;
use ISI\ISIBundle\Entity\ProgrammeGroupeComposition;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ProgrammeExamenController extends Controller
{
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/accueil-programme-examen-{as}-{regime}-{examenId}-{annexeId}", name="accueil_programme_examen")
   */
  public function accueil_programme_examen(Request $request, int $as, string $regime, int $examenId, int $annexeId)
  {
    $em                       = $this->getDoctrine()->getManager();
    $repoAnnee                = $em->getRepository('ISIBundle:Annee');
    $repoExamen               = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe               = $em->getRepository('ISIBundle:Annexe');
    $repoProgrammeComposition = $em->getRepository('ISIBundle:ProgrammeGroupeComposition');
    $annexe                   = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);
    $examen = $repoExamen->find($examenId);
    $programmes = $repoProgrammeComposition->programme_de_l_examen_du_regime($examenId, $regime);
    // dump($programmes[2]->getGroupeComposition());

    return $this->render('ISIBundle:ProgrammeExamen:index.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      'regime'     => $regime,
      'examen'     => $examen,
      'annexe'     => $annexe,
      'programmes' => $programmes,
    ]);
  }
  
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/groupes-de-composition-{as}-{regime}-{examenId}-{annexeId}", name="groupes_composition")
   */
  public function groupes_composition(Request $request, int $as, string $regime, int $examenId, int $annexeId)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $repoGroupe = $em->getRepository('ISIBundle:GroupeComposition');
    $annexe     = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee   = $repoAnnee->find($as);
    $examen  = $repoExamen->find($examenId);
    $groupes = $repoGroupe->groupeDeCompositionDeLAnnexePourUnExamen($examenId, $annexeId);
    // dump($groupes);

    return $this->render('ISIBundle:ProgrammeExamen:groupes-composition.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'examen'  => $examen,
      'regime'  => $regime,
      'annexe'  => $annexe,
      'groupes' => $groupes,
    ]);
  }
  
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/liste-des-classe-pour-programme-d-examen-{as}-{regime}-{examenId}-{annexeId}", name="liste_des_classes_programme_examen")
   */
  public function liste_des_classes_programme_examen(Request $request, int $as, string $regime, int $examenId, int $annexeId)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $annexe     = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee   = $repoAnnee->find($as);
    $examen  = $repoExamen->find($examenId);
    $classes = $repoClasse->classeGrpFormation($as, $annexeId, $regime);
    dump($classes);

    return $this->render('ISIBundle:ProgrammeExamen:liste-des-classes-programme-examen.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'regime'  => $regime,
      'examen'  => $examen,
      'annexe'  => $annexe,
      'classes' => $classes,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/programme-d-examen-d-un-regime-{as}-{regime}-{examenId}-{annexeId}", name="programme_d_examen_du_regime")
   */
  public function programme_d_examen_du_regime(Request $request, int $as, string $regime, int $examenId, int $annexeId)
  {
    $em                        = $this->getDoctrine()->getManager();
    $repoAnnee                 = $em->getRepository('ISIBundle:Annee');
    $repoExamen                = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe                = $em->getRepository('ISIBundle:Annexe');
    $repoNiveau                = $em->getRepository('ISIBundle:Niveau');
    $repoProgrammeExamenNiveau = $em->getRepository('ISIBundle:ProgrammeExamenNiveau');
    $annexe                    = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee         = $repoAnnee->find($as);
    $examen        = $repoExamen->find($examenId);
    $niveaux       = $repoNiveau->niveauxDuGroupe($regime);
    $programmes    = $repoProgrammeExamenNiveau->programme_d_examen_du_regime($examenId, $regime, $annexeId);
    // dump($programmes);

    return $this->render('ISIBundle:ProgrammeExamen:programme-d-examen-des-niveaux.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      'regime'     => $regime,
      'niveaux'    => $niveaux,
      'examen'     => $examen,
      'annexe'     => $annexe,
      'programmes' => $programmes,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/liste-des-niveaux-pour-programme-d-examen-{as}-{regime}-{examenId}-{annexeId}", name="liste_des_niveaux_programme_examen")
   */
  public function liste_des_niveaux_programme_examen(Request $request, int $as, string $regime, int $examenId, int $annexeId)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $annexe     = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee   = $repoAnnee->find($as);
    $examen  = $repoExamen->find($examenId);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);

    return $this->render('ISIBundle:ProgrammeExamen:liste-des-niveaux-programme-examen.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'regime'  => $regime,
      'examen'  => $examen,
      'annexe'  => $annexe,
      'niveaux' => $niveaux,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/enregistrement-du-programme-d-examen-d-un-niveau-{as}-{regime}-{examenId}-{niveauId}-{annexeId}", name="enregistrement_du_programme_d_examen_d_un_niveau")
   */
  public function enregistrement_du_programme_d_examen_d_un_niveau(Request $request, int $as, string $regime, int $examenId, int $niveauId, int $annexeId)
  {
    $em                        = $this->getDoctrine()->getManager();
    $repoAnnee                 = $em->getRepository('ISIBundle:Annee');
    $repoExamen                = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe                = $em->getRepository('ISIBundle:Annexe');
    $repoNiveau                = $em->getRepository('ISIBundle:Niveau');
    $repoEnseignement          = $em->getRepository('ISIBundle:Enseignement');
    $repoProgrammeExamenNiveau = $em->getRepository('ISIBundle:ProgrammeExamenNiveau');
    $annexe                    = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee         = $repoAnnee->find($as);
    $examen        = $repoExamen->find($examenId);
    $niveau        = $repoNiveau->find($niveauId);
    $matieresAr    = $repoEnseignement->enseignementDuNiveau($as, $niveauId, false);
    $matieresFr    = $repoEnseignement->enseignementDuNiveau($as, $niveauId, true);
    $matieres      = array_merge($matieresAr, $matieresFr);
    $programmes    = $repoProgrammeExamenNiveau->programme_examen_niveau($niveauId, $examenId, $annexeId);

    // dump($programmes);

    if($request->isMethod("post"))
    {
      /** On va enregistrer les groupes de composition et aussi enregistrer l'appartenance d'un élève à un groupe de composition */
      $data    = $request->request->all();
      $dates   = $data["dates"];
      $heuresD = $data["heuresD"];
      $heuresF = $data["heuresF"];
      foreach ($matieres as $matiere) {
        $matiereId = $matiere->getMatiere()->getId();
        
        if(count($matiere->getMatiere()->getMatiereEnfants()) == 0){
          $programme = $repoProgrammeExamenNiveau->findOneBy(["niveau" => $niveauId, "examen" => $examenId, "matiere" => $matiereId]);
          if(empty($programme) and !empty($dates[$matiereId]) and !empty($heuresD[$matiereId])){
            $programme = new ProgrammeExamenNiveau();
            $programme->setExamen($examen);
            $programme->setAnnexe($annexe);
            $programme->setNiveau($niveau);
            $programme->setMatiere($matiere->getMatiere());
            $programme->setDate(new \DateTime($dates[$matiereId]));
            $programme->setHeureDebut(new \DateTime($heuresD[$matiereId]));
            $programme->setHeureFin(new \DateTime($heuresF[$matiereId]));
            $programme->setCreatedBy($this->getUser());
            $programme->setCreatedAt(new \Datetime());
            $em->persist($programme);
          }
          elseif(!empty($programme) and !empty($dates[$matiereId]) and !empty($heuresD[$matiereId])){
            $programme->setDate(new \DateTime($dates[$matiereId]));
            $programme->setHeureDebut(new \DateTime($heuresD[$matiereId]));
            $programme->setHeureFin(new \DateTime($heuresF[$matiereId]));
            $programme->setUpdatedBy($this->getUser());
            $programme->setUpdatedAt(new \Datetime());
          }
        }

        $tab[] = $programme;
        // $programme = null;
      }

      // dump($tab);
      // die();
      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Le programme de composition du niveau <strong>'.$niveau->getLibelleFr().'</strong> a été sauvegardé avec succès.');
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }
      return $this->redirect($this->generateUrl('calendier_de_composition_du_niveau', ['as' => $as, 'examenId' => $examenId, 'regime' => $regime, 'annexeId' => $annexeId, 'niveauId' => $niveauId]));
    }

    return $this->render('ISIBundle:ProgrammeExamen:enregistrement-du-programme-d-examen-d-un-niveau.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      'regime'     => $regime,
      'niveau'     => $niveau,
      'examen'     => $examen,
      'annexe'     => $annexe,
      'matieres'   => $matieres,
      'programmes' => $programmes,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/calendier-de-composition-d-un-niveau-{as}-{regime}-{examenId}-{niveauId}-{annexeId}", name="calendier_de_composition_du_niveau")
   */
  public function calendier_de_composition_du_niveau(Request $request, int $as, string $regime, int $examenId, int $niveauId, int $annexeId)
  {
    $em                        = $this->getDoctrine()->getManager();
    $repoAnnee                 = $em->getRepository('ISIBundle:Annee');
    $repoExamen                = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe                = $em->getRepository('ISIBundle:Annexe');
    $repoNiveau                = $em->getRepository('ISIBundle:Niveau');
    $repoEnseignement          = $em->getRepository('ISIBundle:Enseignement');
    $repoProgrammeExamenNiveau = $em->getRepository('ISIBundle:ProgrammeExamenNiveau');
    $annexe                    = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee         = $repoAnnee->find($as);
    $examen        = $repoExamen->find($examenId);
    $niveau        = $repoNiveau->find($niveauId);
    $matieresAr    = $repoEnseignement->enseignementDuNiveau($as, $niveauId, false);
    $matieresFr    = $repoEnseignement->enseignementDuNiveau($as, $niveauId, true);
    $matieres      = array_merge($matieresAr, $matieresFr);
    $programmes    = $repoProgrammeExamenNiveau->programme_examen_niveau($niveauId, $examenId, $annexeId);
    $dates         = [];
    foreach ($programmes as $value) {
      $jour = $this->dateToFrench(date("l", strtotime($value->getDate()->format("d-m-Y"))));
      $dates[$value->getDate()->format("d-m-Y")] = $jour;
    }
    $dates = array_unique($dates);
    // dump($dates);

    return $this->render('ISIBundle:ProgrammeExamen:calendier-de-composition-d-un-niveau.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      'regime'     => $regime,
      'niveau'     => $niveau,
      'examen'     => $examen,
      'annexe'     => $annexe,
      'matieres'   => $matieres,
      'dates'      => $dates,
      'programmes' => $programmes,
    ]);
  }

  public function dateToFrench($english) 
  {
    switch ($english) {
        case 'Monday':
            $jour = 'Lundi';
            break;
        
        case 'Tuesday':
            $jour = 'Mardi';
            break;
        case 'Wednesday':
            $jour = 'Mercredi';
            break;
        case 'Thursday':
            $jour = 'Jeudi';
            break;
        case 'Friday':
            $jour = 'Vendredi';
            break;
        case 'Saturday':
            $jour = 'Samedi';
            break;
        default:
            $jour = 'Dimanche';
            break;
    }
    return $jour;
  }
  
  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/enregistrement-de-groupe-de-composition-{as}-{regime}-{examenId}-{classeId}-{annexeId}", name="enregistrement_de_groupe_de_composition")
   */
  public function enregistrement_de_groupe_de_composition(Request $request, int $as, string $regime, int $examenId, int $classeId, int $annexeId)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoGroupe = $em->getRepository('ISIBundle:GroupeComposition');
    $repoEleveGroupe = $em->getRepository('ISIBundle:EleveGroupeComposition');
    $repoEleve = $em->getRepository('ISIBundle:Eleve');
    $annexe     = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee  = $repoAnnee->find($as);
    $examen = $repoExamen->find($examenId);
    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
    $elevesGroupe = $repoEleveGroupe->groupe_de_composition_des_eleves_d_une_classe($examen, $classe);
    // dump($elevesGroupe);

    if($request->isMethod("post"))
    {
      /** On va enregistrer les groupes de composition et aussi enregistrer l'appartenance d'un élève à un groupe de composition */
      $data    = $request->request->all();
      $groupes = $data["groupes"];
      $differentsGroupes = array_unique($groupes);
      $goupesCompositionClasse = [];
      foreach ($differentsGroupes as $value) {
        if(null != $value){
          $groupeClasse = $repoGroupe->findBy(["examen" => $examen, "classe" => $classe, "libelle" => $value]);
          if (null == $groupeClasse) {
            $groupe = new GroupeComposition();
            $groupe->setClasse($classe);
            $groupe->setLibelle($value);
            $groupe->setExamen($examen);
            $groupe->setCreatedBy($this->getUser());
            $groupe->setCreatedAt(new \Datetime());
            $em->persist($groupe);
            $goupesCompositionClasse[$value] = $groupe;
          }
          else{
            $goupesCompositionClasse[$value] = $groupeClasse[0];
          }
        }
      }

      foreach ($groupes as $key => $value) {
        if(null != $value){
          $eleveGroupeComposition = $repoEleveGroupe->groupe_de_composition_eleve($examen, $key);
          if (null == $eleveGroupeComposition) {
            $eleve = $repoEleve->find($key);
            $eleveGroupeComposition = new EleveGroupeComposition();
            $eleveGroupeComposition->setEleve($eleve);
            $eleveGroupeComposition->setGroupeComposition($goupesCompositionClasse[$value]);
            $eleveGroupeComposition->setCreatedBy($this->getUser());
            $eleveGroupeComposition->setCreatedAt(new \Datetime());
            $em->persist($eleveGroupeComposition);
          } 
          else {
            // $groupe = 
            $eleveGroupeComposition[0]->setGroupeComposition($goupesCompositionClasse[$value]);
            $eleveGroupeComposition[0]->setUpdatedBy($this->getUser());
            $eleveGroupeComposition[0]->setUpdatedAt(new \Datetime());
          }
        }
      }

      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Les groupes de composition en <strong>'.$classe->getLibelleFr().'</strong> ont été sauvegardés avec succès.');
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }
      return $this->redirect($this->generateUrl('groupes_composition', ['as' => $as, 'examenId' => $examenId, 'regime' => $regime, 'annexeId' => $annexeId]));
    }

    return $this->render('ISIBundle:ProgrammeExamen:enregistrement-de-groupe-de-composition.html.twig', [
      'asec'         => $as,
      'annee'        => $annee,
      'regime'       => $regime,
      'classe'       => $classe,
      'examen'       => $examen,
      'annexe'       => $annexe,
      'eleves'       => $eleves,
      'elevesGroupe' => $elevesGroupe,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/enregistrement-du-programme-d-examen-{as}-{regime}-{examenId}-{classeId}-{annexeId}", name="enregistrement_du_programme_d_examen_d_une_classe")
   */
  public function enregistrement_du_programme_d_examen_d_une_classe(Request $request, int $as, string $regime, int $examenId, int $classeId, int $annexeId)
  {
    $em                        = $this->getDoctrine()->getManager();
    $repoAnnee                 = $em->getRepository('ISIBundle:Annee');
    $repoExamen                = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe                = $em->getRepository('ISIBundle:Annexe');
    $repoClasse                = $em->getRepository('ISIBundle:Classe');
    $repoSalle                 = $em->getRepository('ISIBundle:Salle');
    $repoAnneeContrat          = $em->getRepository('ENSBundle:AnneeContrat');
    $repoGroupe                = $em->getRepository('ISIBundle:GroupeComposition');
    $repoProgrammeExamenNiveau = $em->getRepository('ISIBundle:ProgrammeExamenNiveau');
    $repoProgrammeComposition  = $em->getRepository('ISIBundle:ProgrammeGroupeComposition');
    $annexe                    = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee            = $repoAnnee->find($as);
    $examen           = $repoExamen->find($examenId);
    $classe           = $repoClasse->find($classeId);
    $niveauId         = $classe->getNiveau()->getId();
    $groupeClasse     = $repoGroupe->findBy(["examen" => $examen, "classe" => $classe]);
    $salles           = $repoSalle->findBy(["annexe"  => $annexeId]);
    $anneeContrats    = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);
    $programmesNiveau = $repoProgrammeExamenNiveau->programme_examen_niveau($niveauId, $examenId, $annexeId);
    $programmes       = $repoProgrammeComposition->programme_d_examen_de_la_classe($classeId, $examenId);
    // dump($programmesNiveau, $programmes);
    if($request->isMethod("post"))
    {
      /** On va enregistrer les groupes de composition et aussi enregistrer l'appartenance d'un élève à un groupe de composition */
      $data                     = $request->request->all();
      $repoSurveillance         = $em->getRepository('ISIBundle:Surveillance');
      // On va ouvrir une boucle sur les groupes de formations d'abord
      foreach ($groupeClasse as $groupe) {
        $groupeId     = $groupe->getId();
        $sallesCompo  = $data["salles$groupeId"];
        $surveillants = isset($data["surveillants$groupeId"]) ? $data["surveillants$groupeId"] : [];
        foreach ($programmesNiveau as $unProgrammeNiveau) {
          $matiereId = $unProgrammeNiveau->getMatiere()->getId();
          if(!empty($sallesCompo[$matiereId])){
            $programme = $repoProgrammeComposition->programme_de_composition_d_un_groupe_dans_une_matiere($groupeId, $examenId, $matiereId);
            $salleId   = (int) $sallesCompo[$matiereId];
            $salle     = $this->trouveSalleSelonSalleId($salles, $salleId);
            if(empty($programme)){
              $programme = new ProgrammeGroupeComposition();
              $programme->setExamen($examen);
              $programme->setGroupeComposition($groupe);
              $programme->setProgrammeexamenniveau($unProgrammeNiveau);
              // $programme->setMatiere($unProgrammeNiveau->getMatiere());
              $programme->setSalle($salle);
              $programme->setCreatedBy($this->getUser());
              $programme->setCreatedAt(new \Datetime());
              $em->persist($programme);
            }
            else{
              $programme = $programme[0];
              $programme->setSalle($salle);
              $programme->setUpdatedBy($this->getUser());
              $programme->setUpdatedAt(new \Datetime());
            }
            
            if (isset($surveillants[$matiereId])) {
              if(empty($programme->getSurveillances())){
                foreach ($surveillants[$matiereId] as $surveillantId) {
                  $anneeContrat = $this->trouveAnneeContratSelonAnneeContratId($anneeContrats, $surveillantId);
                  $surveillance = new Surveillance();
                  $surveillance->setDisabled(false);
                  $surveillance->setAnneeContrat($anneeContrat);
                  $surveillance->setProgrammegroupecomposition($programme);
                  $surveillance->setCreatedBy($this->getUser());
                  $surveillance->setCreatedAt(new \Datetime());
                  $em->persist($surveillance);
                }
                // $surveillance = $repoSurveillance->findOneBy(["surveillant" => $surveillantId, "programmegroupecomposition"]);
              }
              else{
                $oldSurveillancesId = $this->trouveIdDesSurveillants($programme->getSurveillances());
                $newIds = [];
                foreach ($surveillants[$matiereId] as $surveillantId) {
                  $anneeContrat = $this->trouveAnneeContratSelonAnneeContratId($anneeContrats, $surveillantId);
                  $surveillance = $repoSurveillance->surveillance_groupe_matiere($surveillantId, $groupeId, $matiereId);
                  $newIds[$surveillantId] = $surveillantId;
                  if(empty($surveillance)){
                    $surveillance = new Surveillance();
                    $surveillance->setDisabled(false);
                    $surveillance->setAnneeContrat($anneeContrat);
                    $surveillance->setProgrammegroupecomposition($programme);
                    $surveillance->setCreatedBy($this->getUser());
                    $surveillance->setCreatedAt(new \Datetime());
                    $em->persist($surveillance);
                    $oldEnabledSurveillances[$anneeContrat->getContrat()->getEnseignant()->getNom()] = $surveillance;
                    $disable[] = true;
                  }
                  elseif(!empty($surveillance) and $surveillance[0]->getDisabled() == true){
                    $surveillance[0]->setDisabled(false);
                    $surveillance[0]->setUpdatedBy($this->getUser());
                    $surveillance[0]->setUpdatedAt(new \Datetime());    
                    $oldDisabledSurveillances[$anneeContrat->getContrat()->getEnseignant()->getNom()] = $surveillance;
                    $disable[] = false;  
                  }
                }
  
                foreach ($oldSurveillancesId as $key => $surveillance) {
                  if(!in_array($key, $newIds)){
                    $surveillance->setDisabled(true);
                    $surveillance->setUpdatedBy($this->getUser());
                    $surveillance->setUpdatedAt(new \Datetime());   
                  }
                }
              }
            }
          }
        }
      }

      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Le programme de composition en <strong>'.$classe->getLibelleFr().'</strong> ont été sauvegardés avec succès.');
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }
      return $this->redirect($this->generateUrl('accueil_programme_examen', ['as' => $as, 'examenId' => $examenId, 'regime' => $regime, 'annexeId' => $annexeId]));
    }

    return $this->render('ISIBundle:ProgrammeExamen:enregistrement-du-programme-d-examen-d-une-classe.html.twig', [
      'asec'             => $as,
      'annee'            => $annee,
      'regime'           => $regime,
      'classe'           => $classe,
      'examen'           => $examen,
      'annexe'           => $annexe,
      'salles'           => $salles,
      'groupeClasse'     => $groupeClasse,
      'anneeContrats'    => $anneeContrats,
      'programmes'       => $programmes,
      'programmesNiveau' => $programmesNiveau,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/enregistrement-des-correcteurs-{as}-{regime}-{examenId}-{classeId}-{annexeId}", name="enregistrement_des_correcteurs")
   */
  public function enregistrement_des_correcteurs(Request $request, int $as, string $regime, int $examenId, int $classeId, int $annexeId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoExamen       = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe       = $em->getRepository('ISIBundle:Annexe');
    $repoClasse       = $em->getRepository('ISIBundle:Classe');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $repoCorrection   = $em->getRepository('ISIBundle:Correction');
    $annexe           = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee            = $repoAnnee->find($as);
    $examen           = $repoExamen->find($examenId);
    $classe           = $repoClasse->find($classeId);
    $niveauId         = $classe->getNiveau()->getId();
    $anneeContrats    = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);
    $matieres         = $repoMatiere->lesMatieresDeCompositionDuNiveau($as, $niveauId);
    $corrections      = $repoCorrection->findBy(["classe" => $classeId, "examen" => $examenId]);
    $oldCorrectionsIds = [];
    foreach ($corrections as $key => $value) {
      $oldCorrectionsIds[$value->getAnneeContrat()->getId()] = $value;
    }

    if($request->isMethod("post"))
    {
      /** On va enregistrer les groupes de composition et aussi enregistrer l'appartenance d'un élève à un groupe de composition */
      $data = $request->request->all();
      $correctionsDataPosted = isset($data["correcteurs"]) ? $data["correcteurs"] : [];
      foreach ($correctionsDataPosted as $key => $value) {
        foreach ($matieres as $mat) {
          if($mat->getId() == $key){
            $matiere = $mat;
          }
        }
        foreach ($value as $correcteurId) {
          $newIds[$correcteurId]  = $correcteurId;
          $correction   = $repoCorrection->findOneBy(["matiere" => $key, "classe" => $classeId, "examen" => $examenId, "anneeContrat" => $correcteurId]);
          $anneeContrat = $this->trouveAnneeContratSelonAnneeContratId($anneeContrats, $correcteurId);
  
          if(empty($correction)){
            $correction = new Correction();
            $correction->setAnneeContrat($anneeContrat);
            $correction->setExamen($examen);
            $correction->setClasse($classe);
            $correction->setMatiere($matiere);
            $correction->setDisabled(false);
            $correction->setCreatedBy($this->getUser());
            $correction->setCreatedAt(new \Datetime());
            $em->persist($correction);
            $test = "yes";
          }
          else{
            $correction->setDisabled(false);
            $correction->setUpdatedBy($this->getUser());
            $correction->setUpdatedAt(new \Datetime()); 
            $test = "no";
          }
        }

      }
      
      $tab = [];
      foreach ($oldCorrectionsIds as $key => $correction) {
        if(!in_array($key, $newIds)){
          $correction->setDisabled(true);
          $correction->setUpdatedBy($this->getUser());
          $correction->setUpdatedAt(new \Datetime());   
          $tab[$key] = $correction;
        }
      }
      // dump($oldCorrectionsIds, $newIds, $tab);
      // die();

      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Les correcteurs en <strong>'.$classe->getLibelleFr().'</strong> ont été sauvegardés avec succès.');
      } 
      catch(\Doctrine\ORM\ORMException $e){
        $this->addFlash('error', $e->getMessage());
        $this->get('logger')->error($e->getMessage());
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }
      return $this->redirect($this->generateUrl('liste_des_classes_programme_examen', ['as' => $as, 'examenId' => $examenId, 'regime' => $regime, 'annexeId' => $annexeId]));
    }

    return $this->render('ISIBundle:ProgrammeExamen:enregistrement-des-correcteurs.html.twig', [
      'asec'          => $as,
      'annee'         => $annee,
      'regime'        => $regime,
      'classe'        => $classe,
      'examen'        => $examen,
      'annexe'        => $annexe,
      'matieres'      => $matieres,
      'anneeContrats' => $anneeContrats,
      'corrections'   => $corrections,
    ]);
  }


  /**
   * @Security("has_role('ROLE_ETUDE')")
   * @Route("/fiche-de-controle-de-presence-a-l-examen-{as}-{examenId}-{groupeId}-{annexeId}", name="fiche_de_controle_de_presence_examen")
   */
  public function fiche_de_controle_de_presence_examen(Request $request, int $as, $examenId, int $groupeId, int $annexeId)
  {
    $em                        = $this->getDoctrine()->getManager();
    $repoEleveGC               = $em->getRepository('ISIBundle:EleveGroupeComposition');
    $repoAnnee                 = $em->getRepository('ISIBundle:Annee');
    $repoExamen                = $em->getRepository('ISIBundle:Examen');
    $repoAnnexe                = $em->getRepository('ISIBundle:Annexe');
    $repoGroupeComposition     = $em->getRepository('ISIBundle:GroupeComposition');
    $repoProgrammeExamenNiveau = $em->getRepository('ISIBundle:ProgrammeExamenNiveau');
    $annexe = $repoAnnexe->find($annexeId);
    $examen = $repoExamen->find($examenId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $matiereSelectionnes = $request->get('matiereSelectionnes');
    
    $annee            = $repoAnnee->find($as);
    $groupe           = $repoGroupeComposition->find($groupeId);
    $niveauId         = $groupe->getClasse()->getNiveau()->getId();
    $eleves           = $repoEleveGC->elevesDuGroupeDeComposition($examenId, $annexeId, $groupeId);
    $programmesNiveau = $repoProgrammeExamenNiveau->programme_examen_niveau($niveauId, $examenId, $annexeId);
    $programmesNiveau = $this->selectionDesMatieresAAfficher($programmesNiveau, $matiereSelectionnes);
    
    // return new Response(var_dump($listeHalaqa));

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("page-size", "A3");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "fiche-de-controle-de-".$groupe->getClasse()->getLibelleFr()."-".$groupe->getLibelle();
    

    $html = $this->renderView('ISIBundle:ProgrammeExamen:fiche-de-controle-de-presence-examen.html.twig', [
      // "title" => "Titre de mon document",
      "eleves"           => $eleves,
      "examen"           => $examen,
      "annee"            => $annee,
      'annexe'           => $annexe,
      "eleves"           => $eleves,
      "groupe"           => $groupe,
      'server'           => $_SERVER["DOCUMENT_ROOT"],   
      'programmesNiveau' => $programmesNiveau,   
    ]);

    $header = $this->renderView( '::header.html.twig' );
    // $footer = $this->renderView( '::footer.html.twig' );

    $options = [
        'header-html' => $header,
        // 'footer-html' => $footer,
    ];


    return new Response(
        $snappy->getOutputFromHtml($html, $options),
        200,
        [
          'Content-Type'        => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"',
        ]
    );
  }

  /**
   * @Route("/presence-des-eleves-lors-d-une-epreuve-{as}-{regime}-{examenId}-{classeId}-{annexeId}", name="presence_des_eleves_lors_d_une_epreuve_a_l_examen")
   */
  public function presence_des_eleves_lors_d_une_epreuve_a_l_examen(Request $request, int $as, string $regime, int $classeId, int $examenId, int $annexeId)
  {
    $em                      = $this->getDoctrine()->getManager();
    $repoAnnee               = $em->getRepository('ISIBundle:Annee');
    $repoEleve               = $em->getRepository('ISIBundle:Eleve');
    $repoExamen              = $em->getRepository('ISIBundle:Examen');
    $repoClasse              = $em->getRepository('ISIBundle:Classe');
    $repoAnnexe              = $em->getRepository('ISIBundle:Annexe');
    $repoEnseignement        = $em->getRepository('ISIBundle:Enseignement');
    $repoParticipationExamen = $em->getRepository('ISIBundle:ParticipationExamen');
    
    $eleves   = $repoEleve->lesElevesDeLaClasse($as, $annexeId, $classeId);
    $classe   = $repoClasse->find($classeId);
    $niveauId = $classe->getNiveau()->getId();
    $repoProgrammeExamenNiveau = $em->getRepository('ISIBundle:ProgrammeExamenNiveau');
    $programmesNiveau = $repoProgrammeExamenNiveau->programme_examen_niveau($niveauId, $examenId, $annexeId);
    $programmesNiveau = $this->selectionDesMatieresAAfficher($programmesNiveau);
    $examen   = $repoExamen->find($examenId);
    $annee    = $repoAnnee->find($as);
    $annexe   = $repoAnnexe->find($annexeId);
    $appels   = $repoParticipationExamen->appels_des_eleves_d_une_classe($classeId, $examenId);

    // dump($appels, $eleves);
    return $this->render('ISIBundle:ProgrammeExamen:presence-lors-des-epreuves-examen.html.twig', [
        'asec'     => $as,
        'annee'    => $annee, 
        'annexe'   => $annexe,      
        'regime'   => $regime,             
        'examen'   => $examen,           
        'classe'   => $classe,           
        'appels'   => $appels,        
        'eleves'   => $eleves,        
        'programmesNiveau' => $programmesNiveau,        
    ]);
  }

  public function selectionDesMatieresAAfficher($programmesNiveau, $matiereSelectionnes = null){
    foreach ($programmesNiveau as $key => $value) {
      if($matiereSelectionnes     == "ar" and $value->getMatiere()->getReferenceLangue()->getReference() == "fr"){
        unset($programmesNiveau[$key]);
      }
      elseif($matiereSelectionnes == "fr" and $value->getMatiere()->getReferenceLangue()->getReference() == "ar"){
        unset($programmesNiveau[$key]);
      }
    }

    return $programmesNiveau;
  }

  public function trouveSalleSelonSalleId(array $salles, int $salleId){
    $salle = null;
    foreach ($salles as $item) {
      if($item->getId() == $salleId)
        $salle = $item;
    }

    return $salle;
  }

  public function trouveAnneeContratSelonAnneeContratId(array $anneeContrats, int $anneeContratId){
    $anneeContrat = null;
    foreach ($anneeContrats as $item) {
      if($item->getId() == $anneeContratId)
        $anneeContrat = $item;
    }

    return $anneeContrat;
  }

  public function trouveIdDesSurveillants($surveillances){
    $ids = [];
    foreach ($surveillances as $value) {
      $ids[$value->getAnneeContrat()->getId()] = $value;
    }

    return $ids;
  }
}


?>