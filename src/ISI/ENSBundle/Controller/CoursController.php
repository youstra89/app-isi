<?php
namespace ISI\ENSBundle\Controller;

use ISI\ENSBundle\Entity\Contrat;
use ISI\ENSBundle\Entity\AnneeContrat;
use ISI\ENSBundle\Entity\AnneeContratClasse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class CoursController extends Controller
{
  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/liste-des-classes/{as}/{regime}", name="ens_liste_des_classes_save_cours")
   */
  public function listeDesClassesSaveCours(Request $request, int $as, string $regime)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee      = $repoAnnee->find($as);
    $classes    = $repoClasse->classesDeLAnnee($as, $annexeId, $regime);
    $emplois    = $repoClasse->classesAyantEmploiDuTemps($as, $annexeId, $regime);
    // dump($emplois);
    $tab = [];
    if(!empty($emplois)){
      foreach ($emplois as $value) {
        $tab[] = $value->getId();
      }
    }

    return $this->render('ENSBundle:Cours:liste-des-classes.html.twig', [
      'asec'    => $as,
      'annee'   => $annee, 
      'annexe'  => $annexe,
      'regime'  => $regime,
      'emplois' => $tab,
      'classes' => $classes,
    ]);
  }


  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/liste-des-classes-enregistrement-cours-francais/{as}/{regime}", name="ens_liste_des_classes_save_cours_francais")
   */
  public function listeDesClassesSaveCoursFrancais(Request $request, int $as, string $regime)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee      = $repoAnnee->find($as);
    $classes    = $repoClasse->classesDeLAnnee($as, $annexeId, $regime);

    return $this->render('ENSBundle:Cours:liste-des-classes-cours-francais.html.twig', [
      'asec'    => $as,
      'annee' => $annee, 'annexe'   => $annexe,
      'regime'  => $regime,
      'classes' => $classes,
    ]);
  }


  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/liste-des-halaqas/{as}/{regime}", name="ens_liste_des_halaqas_save_cours")
   */
  public function listeDesHalaqasSaveCours(Request $request, int $as, string $regime)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoHalaqa       = $em->getRepository('ISIBundle:Halaqa');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $repoCours        = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee            = $repoAnnee->find($as);
    $halaqas          = $repoHalaqa->findBy(['annee' => $as, 'regime' => $regime, 'annexe' => $annexeId]);
    $infoCours        = [];
    foreach ($halaqas as $halaqa) {
      $halaqaId = $halaqa->getId();
      $coursDeLaClasse = $repoCours->findOneBy(["annee" => $as, "halaqa" => $halaqaId]);
      if(!empty($coursDeLaClasse)){
        $infoCours[$halaqaId] = $coursDeLaClasse;
      }
    }

    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);
    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);
    $matiere = $repoMatiere->find(1);
    // dump($infoCours, $anneeContrats[0]);
    // die();

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $enseignants = $data["enseignants"];
      foreach ($enseignants as $key => $value) {
        $halaqa            = $repoHalaqa->find($key);
        $anneeContrat      = $repoAnneeContrat->find($value);
        if(!empty($anneeContrat)){
          $verificationCours = $repoCours->findBy(["annee" => $as, "halaqa" => $key, "matiere" => 1]);
          if(!empty($verificationCours) and $verificationCours[0]->getAnneeContrat()->getId() !== (int) $value){
            foreach ($verificationCours as $cours) {
              # code...
              $cours->setAnneeContrat($anneeContrat);
              $cours->setUpdatedAt(new \DateTime());
              $cours->setUpdatedBy($this->getUser());
            }
          }
          elseif(empty($verificationCours)){
            $cpt = 0;
            for ($j=2; $j < 7; $j++) { 
              for ($h=1; $h < 4; $h++) { 
                $cpt++;
                $matiere = $repoMatiere->find(1);
                $cours = new AnneeContratClasse();
                $cours->setNumero($cpt);
                $cours->setAnnee($annee);
                $cours->setHalaqa($halaqa);
                $cours->setJour($j);
                $cours->setHeure($h + 5);
                $cours->setMatiere($matiere);
                $cours->setAnneeContrat($anneeContrat);
                $cours->setCreatedAt(new \DateTime());
                $cours->setCreatedBy($this->getUser());
                $em->persist($cours);
                $tab[] = $cours;
              }
            }
          }
        }
      }
      // dump($tab);
      // die();
      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Opération exécutée avec succès.');
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      return $this->redirectToRoute('ens_liste_des_halaqas_save_cours', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $regime]);
    }

    return $this->render('ENSBundle:Cours:liste-des-halaqas.html.twig', [
      'asec'          => $as,
      'annee' => $annee, 'annexe'   => $annexe,
      'regime'        => $regime,
      'halaqas'       => $halaqas,
      'infoCours'     => $infoCours,
      'anneeContrats' => $anneeContrats,
    ]);
  }


  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/enregistrement-de-cours/{as}/{regime}/{classeId}", name="ens_enregistrer_cours_classe")
   */
  public function enregistrerCoursDuneClasse(Request $request, int $as, string $regime, int $classeId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoClasse       = $em->getRepository('ISIBundle:Classe');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoCours        = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee            = $repoAnnee->find($as);
    $classe           = $repoClasse->find($classeId);
    $niveauId         = $classe->getNiveau()->getId();
    $matieres         = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    $anneeContrats    = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);
    $coursDeLaClasse  = $repoCours->findBy(["annee"  => $as, "classe" => $classeId]);
    $infoCours        = [];
    foreach ($coursDeLaClasse as $key => $value) {
      $cle = $value->getMatiere()->getId();
      $infoCours[$cle] = $value;
    }

    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    // dump($anneeContrats);
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $enseignants = $data["enseignants"];
      $mode = null;
      $enseignements = $repoEnseignement->enseignementDuNiveau($as, $niveauId);
      foreach ($enseignants as $key => $value) {
        $anneeContrat = $repoAnneeContrat->find($value);
        $verificationCours = $repoCours->findBy(["annee" => $as, "classe" => $classeId, "matiere" => $key]);
        if(empty($value))
        {
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas précisé l\'enseignant pour une matière.');
          return $this->redirectToRoute('ens_enregistrer_cours_classe', ['as' => $as, 'annexeId'=> $annexeId, 'regime' => $regime, 'classeId' => $classeId]);
        }
        elseif(empty($verificationCours)){
          $nbr = $this->nombre_heures_de_cours($key, $niveauId, $as, $enseignements);
          $ids[] = $nbr;
          $mode = "enregistrement";
          $cpt = 0;
          for ($i=0; $i < $nbr; $i++) { 
            # code...
            $cpt++;
            $matiere = $repoMatiere->find($key);
            $cours = new AnneeContratClasse();
            $cours->setNumero($cpt);
            $cours->setAnnee($annee);
            $cours->setClasse($classe);
            $cours->setMatiere($matiere);
            $cours->setAnneeContrat($anneeContrat);
            $cours->setCreatedAt(new \DateTime());
            $cours->setCreatedBy($this->getUser());
            $em->persist($cours);
            $tab[] = $cours;
          }
        }
      }
      try{
        $em->flush();
        if ($mode == "modification") {
          $request->getSession()->getFlashBag()->add('info', 'Les cours de la classe <strong>'.$classe->getLibelleFr().'</strong> ont été mis à jour avec succès.');
        } 
        elseif ($mode == "enregistrement") {
          $request->getSession()->getFlashBag()->add('info', 'Les cours de la classe <strong>'.$classe->getLibelleFr().'</strong> ont été enrégistrés avec succès.');
        }
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      return $this->redirectToRoute('ens_liste_des_classes_save_cours', ['as' => $as, 'annexeId'=> $annexeId, 'regime' => $regime, 'classeId' => $classeId]);
    }

    return $this->render('ENSBundle:Cours:enregistrer-cours-classe.html.twig', [
      'asec'            => $as,
      'annee'           => $annee, 
      'annexe'          => $annexe,
      'regime'          => $regime,
      'classe'          => $classe,
      'infoCours'       => $infoCours,
      'matieres'        => $matieres,
      'anneeContrats'   => $anneeContrats,
      'coursDeLaClasse' => $coursDeLaClasse,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/mise-a-jour-des-cours/{as}/{regime}/{classeId}", name="ens_editer_cours_classe")
   */
  public function editerCoursDuneClasse(Request $request, int $as, string $regime, int $classeId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoClasse       = $em->getRepository('ISIBundle:Classe');
    $repoCours        = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee            = $repoAnnee->find($as);
    $classe           = $repoClasse->find($classeId);
    $niveauId         = $classe->getNiveau()->getId();
    $matieres         = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    $anneeContrats    = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);
    $coursDeLaClasse  = $repoCours->findBy(["annee"  => $as, "classe" => $classeId]);
    $infoCours        = [];
    foreach ($coursDeLaClasse as $key => $value) {
      $cle = $value->getMatiere()->getId();
      $infoCours[$cle] = $value;
    }
    // dump($coursDeLaClasse);

    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    if($request->isMethod('post'))
    {
      $data        = $request->request->all();
      $coursIds    = $data["cours"];
      $jours       = $data["jours"];
      $heures      = $data["heures"];
      // dump($jours, $heures);
      // die();
      foreach ($coursIds as $key => $value) {
        $cours = $repoCours->find($key);
        $anneeContrat = $cours->getAnneeContrat();
        $matiereId = $cours->getMatiere()->getId();
        // $verificationCours = $repoCours->findOneBy(["annee" => $as, "classe" => $classeId, "matiere" => $matiereId]);
        if(empty($value))
        {
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas précisé l\'enseignant pour une matière.');
          return $this->redirectToRoute('ens_enregistrer_cours_classe', ['as' => $as, 'annexeId'=> $annexeId, 'regime' => $regime, 'classeId' => $classeId]);
        }
        // elseif(!empty($verificationCours)){
        elseif(!empty($cours) and $cours->getAnneeContrat()->getId() !== (int) $value){
          $cours->setAnneeContrat($anneeContrat);
          $cours->setUpdatedAt(new \DateTime());
          $cours->setUpdatedBy($this->getUser());
        }
        
        if(!(empty($jours[$key])) and (int) $jours[$key] != $cours->getJour()){
          $cours->setJour($jours[$key]);
        }

        if(!(empty($heures[$key])) and (int) $heures[$key] != $cours->getHeure()){
          $cours->setHeure($heures[$key]);
        }
      }
      // dump($tab);
      // die();
      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Opérations effectuées avec succès.');
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      return $this->redirectToRoute('ens_liste_des_classes_save_cours', ['as' => $as, 'annexeId'=> $annexeId, 'regime' => $regime, 'classeId' => $classeId]);
    }

    return $this->render('ENSBundle:Cours:editer-cours-classe.html.twig', [
      'asec'            => $as,
      'annee'           => $annee, 
      'annexe'          => $annexe,
      'regime'          => $regime,
      'classe'          => $classe,
      'infoCours'       => $infoCours,
      'matieres'        => $matieres,
      'anneeContrats'   => $anneeContrats,
      'coursDeLaClasse' => $coursDeLaClasse,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/editer-un-cours/{as}/{regime}/{coursId}", name="ens_editer_un_cours")
   */
  public function editerUnCours(Request $request, int $as, string $regime, int $coursId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoCours        = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee            = $repoAnnee->find($as);
    $cours            = $repoCours->find($coursId);
    $classe           = $cours->getClasse();
    $classeId         = $cours->getClasse()->getId();
    $anneeContrats    = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);


    if($request->isMethod('post'))
    {
      $data           = $request->request->all();
      $anneeContratId = $data["cours"];
      $jour           = $data["jour"];
      $heure          = $data["heure"];
      // dump($jours, $heures);
      // die();
      if(!empty($cours) and $cours->getAnneeContrat()->getId() !== (int) $anneeContratId){
        $anneeContrat = $repoAnneeContrat->find($anneeContratId);
        $cours->setAnneeContrat($anneeContrat);
        $cours->setUpdatedAt(new \DateTime());
        $cours->setUpdatedBy($this->getUser());
      }
      
      if(!(empty($jour)) and (int) $jour != $cours->getJour()){
        $cours->setJour((int)$jour);
      }
      elseif (empty($jour)) {
        $cours->setJour(null);
      }

      if(!(empty($heure)) and (int) $heure != $cours->getHeure()){
        $cours->setHeure((int)$heure);
      }
      elseif (empty($heure)) {
        $cours->setHeure(null);
      }
      // dump($tab);
      // die();
      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Opérations effectuées avec succès.');
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      return $this->redirectToRoute('ens_cours_de_la_classe', ['as' => $as, 'annexeId'=> $annexeId, 'regime' => $regime, 'classeId' => $classeId]);
    }

    return $this->render('ENSBundle:Cours:editer-un-cours.html.twig', [
      'asec'            => $as,
      'annee' => $annee, 'annexe'   => $annexe,
      'cours'           => $cours,
      'regime'          => $regime,
      'classe'          => $classe,
      'anneeContrats'   => $anneeContrats,
    ]);
  }

  public function nombre_heures_de_cours(int $matiereId, int $niveauId, int $anneeId, array $enseignements)
  {
    $nbr = 0;
    foreach ($enseignements as $value) {
      if ($value->getMatiere()->getId() == $matiereId and $value->getNiveau()->getId() == $niveauId and $anneeId == $value->getAnnee()->getId())
        $nbr = $value->getNombreHeureCours();
    }

    return $nbr;
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/liste-des-cours-de-la-classe/{as}/{regime}/{classeId}", name="ens_cours_de_la_classe")
   */
  public function voirLesCoursDeLaClasse(Request $request, int $as, string $regime, int $classeId)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoEns     = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoCours   = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoEns     = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee       = $repoAnnee->find($as);
    $classe      = $repoClasse->find($classeId);
    $niveauId    = $classe->getNiveau()->getId();
    $ens         = $repoEns->findBy(['annee' => $as, 'niveau' => $niveauId]);
    $cours       = $repoCours->findBy(["annee" => $as, "classe" => $classeId]);

    return $this->render('ENSBundle:Cours:cours-enregistres.html.twig', [
      'asec'   => $as,
      'annee' => $annee, 'annexe'   => $annexe,
      'regime' => $regime,
      'classe' => $classe,
      'cours'  => $cours,
      'ens'    => $ens
    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/tous-les-cours-enrgistres/{as}/{annexeId}/{regime}", name="tous_les_cours")
   */
  public function tous_les_cours(Request $request, int $as, int $annexeId, string $regime)
  {
    $em        = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $repoCours = $em->getRepository('ENSBundle:AnneeContratClasse');
    $annee     = $repoAnnee->find($as);
    $cours     = $repoCours->tousLesCoursDeLAnnee($as, $annexeId, $regime);
    
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    foreach ($cours as $value) {
      if($regime == "A"){
        if($value->getClasse() !== null and $value->getClasse()->getNiveau()->getGroupeFormation()->getReference() != $regime){
          unset($cours[array_search($value, $cours)]);
        }
      }
      else{
        if($value->getHalaqa() !== null or $value->getClasse() !== null and $value->getClasse()->getNiveau()->getGroupeFormation()->getReference() != $regime){
          unset($cours[array_search($value, $cours)]);
        }
      }
    }

    return $this->render('ENSBundle:Cours:tous-les-cours-enregistres.html.twig', [
        'asec'    => $as,
        'annee'   => $annee, 
        'annexe'  => $annexe,      
        'cours'   => $cours,     
        'regime'  => $regime,     
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/enregistrement-de-cours-de-francais/{as}/{regime}/{classeId}", name="ens_enregistrer_cours_francais_classe")
   */
  public function enregistrerCoursFrancaisDuneClasse(Request $request, int $as, string $regime, int $classeId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoMatiere      = $em->getRepository('ISIBundle:Matiere');
    $repoClasse       = $em->getRepository('ISIBundle:Classe');
    $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
    $repoCours        = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee            = $repoAnnee->find($as);
    $classe           = $repoClasse->find($classeId);
    $niveauId         = $classe->getNiveau()->getId();
    $matieres         = $repoMatiere->lesMatieresEnfantsDuNiveau($as, $niveauId);
    $anneeContrats    = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);
    $coursDeLaClasse  = $repoCours->findBy(["annee"  => $as, "classe" => $classeId]);
    $infoCours        = [];
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
      $enseignements = $repoEnseignement->enseignementDuNiveau($as, $niveauId, true);
      foreach ($enseignants as $key => $value) {
        $anneeContrat = $repoAnneeContrat->find($value);
        $verificationCours = $repoCours->findBy(["annee" => $as, "classe" => $classeId, "matiere" => $key]);
        if(empty($value))
        {
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas précisé l\'enseignant pour une matière.');
          return $this->redirectToRoute('ens_enregistrer_cours_francais_classe', ['as' => $as, 'annexeId'=> $annexeId, 'regime' => $regime, 'classeId' => $classeId]);
        }
        elseif(empty($verificationCours)){
          $nbr = $this->nombre_heures_de_cours($key, $niveauId, $as, $enseignements);
          $mode = "enregistrement";
          $cpt = 0;
          for ($i=0; $i < $nbr; $i++) { 
            $cpt++;
            $matiere = $repoMatiere->find($key);
            $cours = new AnneeContratClasse();
            $cours->setNumero($cpt);
            $cours->setAnnee($annee);
            $cours->setClasse($classe);
            $cours->setMatiere($matiere);
            $cours->setAnneeContrat($anneeContrat);
            $cours->setCreatedAt(new \DateTime());
            $cours->setCreatedBy($this->getUser());
            $em->persist($cours);
          }
          $tab[] = $nbr;

          $mode = "enregistrement";
        }
      }
      // dump($tab);
      // die();
      try{
        $em->flush();
        if ($mode == "modification") {
          $request->getSession()->getFlashBag()->add('info', 'Les cours de la classe <strong>'.$classe->getLibelleFr().'</strong> ont été mis à jour avec succès.');
        } 
        elseif ($mode == "enregistrement") {
          $request->getSession()->getFlashBag()->add('info', 'Les cours de la classe <strong>'.$classe->getLibelleFr().'</strong> ont été enrégistrés avec succès.');
        }
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      return $this->redirectToRoute('ens_liste_des_classes_save_cours_francais', ['as' => $as, 'annexeId'=> $annexeId, 'regime' => $regime, 'classeId' => $classeId]);
    }

    return $this->render('ENSBundle:Cours:enregistrer-cours-de-francais-classe.html.twig', [
      'asec'            => $as,
      'annee' => $annee, 'annexe'   => $annexe,
      'regime'          => $regime,
      'classe'          => $classe,
      'infoCours'       => $infoCours,
      'matieres'        => $matieres,
      'anneeContrats'   => $anneeContrats,
      'coursDeLaClasse' => $coursDeLaClasse,
    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/liste-des-cours-de-francais-de-la-classe/{as}/{regime}/{classeId}", name="ens_cours_francais_de_la_classe")
   */
  public function voirLesCoursFrancaisDeLaClasse(Request $request, int $as, string $regime, int $classeId)
  {
    $em         = $this->getDoctrine()->getManager();
    $repoEns     = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoClasse  = $em->getRepository('ISIBundle:Classe');
    $repoCours   = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoEns     = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee       = $repoAnnee->find($as);
    $classe      = $repoClasse->find($classeId);
    $niveauId    = $classe->getNiveau()->getId();
    $ens         = $repoEns->findBy(['annee' => $as, 'niveau' => $niveauId]);
    $cours = $repoCours->findBy(["annee" => $as, "classe" => $classeId]);

    return $this->render('ENSBundle:Cours:cours-enregistres.html.twig', [
      'asec'   => $as,
      'annee' => $annee, 'annexe'   => $annexe,
      'regime' => $regime,
      'classe' => $classe,
      'cours'  => $cours,
      'ens'    => $ens
    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/nombre-heures-de-cours-des-enseignants/{as}", name="ens_nombre_heures_cours_enseignants")
   */
  public function nombreHeuresCoursEnseignants(Request $request, int $as)
  {
    $em                     = $this->getDoctrine()->getManager();
    $repoEns                = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee              = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat       = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnneeContratClasse = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    $anneeContrats  = $repoAnneeContrat->findBy(["annee" => $as]);
    $enseignements  = $repoEns->findBy(['annee' => $as]);
    $cours = $repoAnneeContratClasse->findBy(["annee" => $as]);

    $heuresCours = [];
    foreach ($anneeContrats as $key => $value) {
      $anneeContratId       = $value->getId();
      $heureCoran           = 0;
      $heureAcademie        = 0;
      $heureCentreFormation = 0;
      foreach ($cours as $k => $v) {
        if(!empty($v->getClasse())){
          $niveau = $v->getClasse()->getNiveau();
          $regimeClasse = $niveau->getGroupeFormation()->getReference();
          if ($anneeContratId == $v->getAnneeContrat()->getId()) {
            foreach ($enseignements as $keyEns => $valueEns) {
              // Etape 1: On va calculer les heures de cours à l'académie
              if ($regimeClasse == 'A' and $valueEns->getNiveau()->getId() === $niveau->getId() and $v->getMatiere()->getId()  === $valueEns->getMatiere()->getId()) {
                $heureAcademie += 1;
              } 
              // Etape 2: Puis ceux du centre de formation
              elseif ($regimeClasse == 'F' and $valueEns->getNiveau()->getId() === $niveau->getId() and $v->getMatiere()->getId()  === $valueEns->getMatiere()->getId()) {
                $heureCentreFormation += 1;
              }
            }
          }
        }
        elseif(!empty($v->getHalaqa()) and $v->getAnneeContrat()->getId() == $anneeContratId){
          $heureCoran += 1;
        }
      }
      $heuresCours[$anneeContratId] = ["ens" => $value->getContrat()->getEnseignant()->getNomFr().' '.$value->getContrat()->getEnseignant()->getPnomFr(), "academie" => $heureAcademie, "coran" => $heureCoran, "centre" => $heureCentreFormation];
    }
    return $this->render('ENSBundle:Cours:nombre-heures-cours-des-enseignants.html.twig', [
      'asec'       => $as,
      'annee' => $annee, 'annexe'   => $annexe,
      'heuresCours' => $heuresCours,
      'anneeContrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/empoi-du-temps-d-un-enseignant/{as}/{anneeContratId}", name="emploi_du_temps_enseignant")
   */
  public function emploi_du_temps_enseignant(Request $request, $as, int $anneeContratId)
  {
    $em                     = $this->getDoctrine()->getManager();
    $repoEns                = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee              = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat       = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnneeContratClasse = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId   = $request->get('annexeId');
    $annexe     = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    $anneeContrat   = $repoAnneeContrat->find($anneeContratId);
    $enseignements  = $repoEns->findBy(['annee' => $as]);
    $cours = $repoAnneeContratClasse->findBy(["annee" => $as]);

    $anneeContratId = $anneeContrat->getId();
    $heureAcademie = 0;
    $heureCoran = 0;
    $coursAcademie = [];
    $coursCoran = [];
    $ensAcademie = [];
    $heureCentreFormation = 0;
    $coursCentreFormation = [];
    $ensCentreFormation = [];
    $dars = [];
    foreach ($cours as $k => $v) {
      if(empty($v->getClasse())){
        $dars[] = $v;
        $heureCoran += 1;
        $coursCoran[] = $v;
      }
      else{
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
    }
    // dump($coursAcademie, $ensAcademie);
    // die();
    $heuresCours = ["ens" => $anneeContrat->getContrat()->getEnseignant()->getNomFr().' '.$anneeContrat->getContrat()->getEnseignant()->getPnomFr(), "academie" => $heureAcademie, "centre" => $heureCentreFormation];
    return $this->render('ENSBundle:Cours:emploi-du-temps-d-un-enseignant.html.twig', [
      'asec'                 => $as,
      'annee'                => $annee, 
      'annexe'               => $annexe,
      'heuresCours'          => $heuresCours,
      'coursCentreFormation' => $coursCentreFormation,
      'ensCentreFormation'   => $ensCentreFormation,
      'coursAcademie'        => $coursAcademie,
      'ensAcademie'          => $ensAcademie,
      'anneeContrat'         => $anneeContrat,      

    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/emploi-du-temps-pdf-d-un-enseignant/{as}/{anneeContratId}", name="emploi_du_temps_enseignant_pdf")
   */
  public function emploi_du_temps_enseignant_pdf(Request $request, $as, int $anneeContratId)
  {
    $em                     = $this->getDoctrine()->getManager();
    $repoEns                = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee              = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat       = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnneeContratClasse = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId   = $request->get('annexeId');
    $annexe     = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    $anneeContrat   = $repoAnneeContrat->find($anneeContratId);
    $enseignements  = $repoEns->findBy(['annee' => $as]);
    $cours = $repoAnneeContratClasse->findBy(["annee" => $as]);

    $anneeContratId = $anneeContrat->getId();
    $heureAcademie = 0;
    $heureCoran = 0;
    $coursAcademie = [];
    $coursCoran = [];
    $ensAcademie = [];
    $heureCentreFormation = 0;
    $coursCentreFormation = [];
    $ensCentreFormation = [];
    $dars = [];
    foreach ($cours as $k => $v) {
      if(empty($v->getClasse())){
        $dars[] = $v;
        $heureCoran += 1;
        $coursCoran[] = $v;
      }
      else{
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
    }
    // dump($coursAcademie, $ensAcademie);
    // die();
    $heuresCours = ["ens" => $anneeContrat->getContrat()->getEnseignant()->getNomFr().' '.$anneeContrat->getContrat()->getEnseignant()->getPnomFr(), "academie" => $heureAcademie, "centre" => $heureCentreFormation];
    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "emploi-du-temps-".$annee->getLibelle();

    $html = $this->renderView('ENSBundle:Cours:emploi-du-temps-d-un-enseignant-pdf.html.twig', [
    // return $this->render('ENSBundle:Cours:emploi-du-temps-d-un-enseignant-pdf.html.twig', [
      // "title" => "Titre de mon document",
      'asec'                 => $as,
      'annee'                => $annee, 
      'annexe'               => $annexe,
      'heuresCours'          => $heuresCours,
      'coursCentreFormation' => $coursCentreFormation,
      'ensCentreFormation'   => $ensCentreFormation,
      'coursAcademie'        => $coursAcademie,
      'ensAcademie'          => $ensAcademie,
      'anneeContrat'         => $anneeContrat,  
      'server'               => $_SERVER["DOCUMENT_ROOT"],   
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

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT') or has_role('ROLE_SCOLARITE') ")
   * @Route("/emploi-du-temps-de-classe/{as}/{classeId}", name="emploi_du_temps_classe")
   */
  public function emploi_du_temps_classe(Request $request, $as, int $classeId)
  {
    $em                     = $this->getDoctrine()->getManager();
    $repoEns                = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee              = $em->getRepository('ISIBundle:Annee');
    $repoClasse             = $em->getRepository('ISIBundle:Classe');
    $repoAnneeContratClasse = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId   = $request->get('annexeId');
    $annexe     = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee         = $repoAnnee->find($as);
    $classe        = $repoClasse->find($classeId);
    $enseignements = $repoEns->findBy(['annee' => $as]);
    $cours         = $repoAnneeContratClasse->findBy(["annee" => $as]);

    $classeId             = $classe->getId();
    $heureAcademie        = 0;
    $heureCoran           = 0;
    $coursAcademie        = [];
    $coursCoran           = [];
    $ensAcademie          = [];
    $heureCentreFormation = 0;
    $coursCentreFormation = [];
    $ensCentreFormation   = [];
    $dars = [];
    foreach ($cours as $k => $v) {
      if(empty($v->getClasse())){
        $dars[] = $v;
        $heureCoran += 1;
        $coursCoran[] = $v;
      }
      else{
        $niveau = $v->getClasse()->getNiveau();
        $regimeClasse = $niveau->getGroupeFormation()->getReference();
        if ($classeId == $v->getClasse()->getId()) {
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
    }
    // dump($coursAcademie, $ensAcademie);
    // die();
    return $this->render('ENSBundle:Cours:emploi-du-temps-d-une-classe.html.twig', [
      'asec'                 => $as,
      'annee'                => $annee, 
      'annexe'               => $annexe,
      'coursCentreFormation' => $coursCentreFormation,
      'ensCentreFormation'   => $ensCentreFormation,
      'coursAcademie'        => $coursAcademie,
      'ensAcademie'          => $ensAcademie,
      'classe'               => $classe,      
    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT') or has_role('ROLE_SCOLARITE') ")
   * @Route("/emploi-du-temps-pdf-de-classe/{as}/{classeId}", name="emploi_du_temps_classe_pdf")
   */
  public function emploi_du_temps_classe_pdf(Request $request, $as, int $classeId)
  {
    $em                     = $this->getDoctrine()->getManager();
    $repoEns                = $em->getRepository('ISIBundle:Enseignement');
    $repoAnnee              = $em->getRepository('ISIBundle:Annee');
    $repoClasse             = $em->getRepository('ISIBundle:Classe');
    $repoAnneeContratClasse = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId   = $request->get('annexeId');
    $annexe     = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee         = $repoAnnee->find($as);
    $classe        = $repoClasse->find($classeId);
    $enseignements = $repoEns->findBy(['annee' => $as]);
    $cours         = $repoAnneeContratClasse->findBy(["annee" => $as]);

    $classeId             = $classe->getId();
    $heureAcademie        = 0;
    $heureCoran           = 0;
    $coursAcademie        = [];
    $coursCoran           = [];
    $ensAcademie          = [];
    $heureCentreFormation = 0;
    $coursCentreFormation = [];
    $ensCentreFormation   = [];
    $dars = [];
    foreach ($cours as $k => $v) {
      if(empty($v->getClasse())){
        $dars[] = $v;
        $heureCoran += 1;
        $coursCoran[] = $v;
      }
      else{
        $niveau = $v->getClasse()->getNiveau();
        $regimeClasse = $niveau->getGroupeFormation()->getReference();
        if ($classeId == $v->getClasse()->getId()) {
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
    }
    // dump($coursAcademie, $ensAcademie);
    // die();
    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "emploi-temps-de-la-classe-".$classe->getLibelleFr();
    $html = $this->renderView('ENSBundle:Cours:emploi-du-temps-d-une-classe-pdf.html.twig', [
      'asec'                 => $as,
      'annee'                => $annee, 
      'annexe'               => $annexe,
      'coursCentreFormation' => $coursCentreFormation,
      'ensCentreFormation'   => $ensCentreFormation,
      'coursAcademie'        => $coursAcademie,
      'ensAcademie'          => $ensAcademie,
      'classe'               => $classe,      
      'server'               => $_SERVER["DOCUMENT_ROOT"],   
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

  /**
   * @Security("has_role('ROLE_SCOLARITE') or has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT') ")
   * @Route("/accueil-rapport-des-appels-{as}-{regime}", name="rapport_appel_cours_home")
   */
  public function rapport_appel_cours_home(Request $request, int $as, $regime)
  {
    $em                    = $this->getDoctrine()->getManager();
    $repoAnnee             = $em->getRepository('ISIBundle:Annee');
    $repoTache             = $em->getRepository('ISIBundle:Tache');
    $repoClasse            = $em->getRepository('ISIBundle:Classe');
    $repoCours             = $em->getRepository('ENSBundle:AnneeContratClasse');
    $repoAnneeContrat      = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnnexe            = $em->getRepository('ISIBundle:Annexe');
    $annexeId              = $request->get('annexeId');
    $annexe                = $repoAnnexe->find($annexeId);
    $date                  = new \DateTime();
    $nouvel_enregistrement = false;
    // Contrainte : Il faut que le jour sélectionné corresponde au jour du cours    
    
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $taches = $repoTache->appelsDuJours($date, $regime);
    if($request->isMethod('post')){
      $data = $request->request->all();
      $date = new \DateTime($data['date']);
      $taches = $repoTache->appelsDuJours($date, $regime);
    }
    $jour = $this->dateToFrench(date("l", strtotime($date->format("d-m-Y"))));

    $numero_du_jour = date("w", strtotime($date->format("Y-m-d")));
    if($numero_du_jour == 6) 
      $jour_du_cours = 1;
    elseif($numero_du_jour == 0) 
      $jour_du_cours = 2;
    elseif($numero_du_jour == 1) 
      $jour_du_cours = 3;
    elseif($numero_du_jour == 2) 
      $jour_du_cours = 4;
    elseif($numero_du_jour == 3) 
      $jour_du_cours = 5;
    elseif($numero_du_jour == 4) 
      $jour_du_cours = 6;
    else
      $jour_du_cours = 0;

    $cours = $repoCours->cours_d_un_jour($as, $annexeId, $jour_du_cours);
    // dump($taches);

    $annee   = $repoAnnee->find($as);
    $enseignants = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);

    // dump($cours);

    return $this->render('ENSBundle:Cours:rapport-absence-cours-home.html.twig', [
      'asec'        => $as,
      'regime'      => $regime,
      'annee'       => $annee,
      'annexe'      => $annexe,
      'cours'       => $cours,
      'taches'      => $taches,
      'date'        => $date,
      'jour'        => $jour,
      'enseignants' => $enseignants,
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
}


 ?>
