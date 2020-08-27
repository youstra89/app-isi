<?php
namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use ISI\ENSBundle\Entity\AnneeContrat;
use ISI\ENSBundle\Form\AnneeContratType;

use ISI\ENSBundle\Entity\Contrat;
use ISI\ENSBundle\Form\ContratType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class AgentsController extends Controller
{

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/direction-technique/{as}", name="agents")
   */
  public function agents(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $annee       = $repoAnnee->find($as);
    $enseignants = $repoEnseignant->findAll();
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }


    return $this->render('ENSBundle:Agents:agents.html.twig', [
      'asec'        => $as,
      'annee'       => $annee, 
      'annexe'      => $annexe,
      'enseignants' => $enseignants,
    ]);
  }


  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/initialisation-de-la-prise-de-fonction-d-un-enseignant-{as}", name="ens_initialisation_prise_de_fonction")
   */
  public function priseDeFonctionHome(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoContrat    = $em->getRepository('ENSBundle:Contrat');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee  = $repoAnnee->find($as);
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('ens_initialisation_prise_de_fonction', ['as' => $as, 'annexeId' => $annexeId]));
    }

    if($request->isMethod('post'))
    {
      $data      = $request->request->all();
      $matricule = $data['matricule'];
      if(empty($matricule))
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas saisie de matricule.');
        return $this->redirectToRoute('ens_initialisation_prise_de_fonction', ['as' => $as, 'annexeId' => $annexeId]);
      }
      $enseignant = $repoEnseignant->findOneBy(['matricule' => $matricule]);
      if(empty($enseignant))
      {
        $request->getSession()->getFlashBag()->add('error', 'Le matricule saisi ne correspond à aucun enseignant');
        return $this->redirectToRoute('ens_initialisation_prise_de_fonction', ['as' => $as, 'annexeId' => $annexeId]);
      }
      $enseignantId = $enseignant->getId();
      $contrat = $repoContrat->dernierContrat($enseignantId);
      // dump($enseignantId, $contrat);
      // die();
      if(empty($contrat)){
        return $this->redirect($this->generateUrl('ens_prise_de_fonction', ['as' => $as, 'annexeId' => $annexeId, 'enseignantId' => $enseignantId]));
      }
      else{
        $nom = $enseignant->getNom();
        $matricule = $enseignant->getMatricule();
        $date = $contrat->getDebut()->format("d-m-Y");
        $request->getSession()->getFlashBag()->add('error', '<strong>'.$nom.'</strong> avec le matricule <strong>'.$matricule.'</strong> est déjà en fonction depuis le <strong>'.$date.'</strong>.');
        return $this->redirectToRoute('ens_initialisation_prise_de_fonction', ['as' => $as, 'annexeId' => $annexeId]);
      }

    }

    return $this->render('ENSBundle:Agents:initialisation-de-prise-de-fonction-enseignant.html.twig', [
      'asec'   => $as,
      'annee'  => $annee, 
      'annexe' => $annexe,
      // 'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/prise-de-fonction-de-l-enseignant-{as}-{enseignantId}", name="ens_prise_de_fonction")
   */
  public function priseDeFonction(Request $request, $as, $enseignantId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoContrat    = $em->getRepository('ENSBundle:Contrat');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $enseignant = $repoEnseignant->find($enseignantId);

    $annee   = $repoAnnee->find($as);
    $contrat = $repoContrat->dernierContrat($enseignantId);
    if(!is_null($contrat) and $contrat->getFini() == FALSE)
    {
      $request->getSession()->getFlashBag()->add('error', '<strong>'.$enseignant->getNom().'</strong> est déjà en fonction.');
      return $this->redirect($this->generateUrl('ens_initialisation_prise_de_fonction', ['as'=> $as, 'annexeId' => $annexeId]));
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

      $request->getSession()->getFlashBag()->add('info', 'La prise de fonction de l\'enseignant(e) <strong>'.$enseignant->getNom().'</strong> a été enregistrée avec succès.');

      return $this->redirect($this->generateUrl('ens_initialisation_prise_de_fonction', ['as'=> $as, 'annexeId' => $annexeId]));
    }

    return $this->render('ENSBundle:Agents:prise-de-fonction.html.twig', [
      'asec'       => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'form'       => $form->createView(),
      'enseignant' => $enseignant,
    ]);
  }


  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/initialisation-de-l-arret-de-fonction-d-un-enseignant-{as}", name="ens_initialisation_l_arret_de_fonction")
   */
  public function arretDeFonctionHome(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoContrat    = $em->getRepository('ENSBundle:Contrat');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee  = $repoAnnee->find($as);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('ens_initialisation_l_arret_de_fonction', ['as' => $as, 'annexeId' => $annexeId]));
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
        return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as, 'annexeId' => $annexeId]);
      }
      $enseignant = $repoEnseignant->findOneBy(['matricule' => $matricule]);
      if(empty($enseignant))
      {
        $request->getSession()->getFlashBag()->add('error', 'Le matricule saisi ne correspond à aucun enseignant');
        return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as, 'annexeId' => $annexeId]);
      }
      else
      {
        $contrat = $repoContrat->dernierContrat($enseignant->getId());
        if(is_null($contrat))
        {
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez jamais eu de relation de travail avec l\'enseignant(e) <strong>'.$enseignant->getNom().' ('.$matricule.')</strong>.');
          return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as, 'annexeId' => $annexeId]);
        }
        elseif($contrat->getFini() == TRUE)
        {
          $request->getSession()->getFlashBag()->add('error', 'Il n y a pas de contrat en cours pour l\'enseignant(e) <strong>'.$enseignant->getNom().' ('.$matricule.')</strong>.');
          return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as, 'annexeId' => $annexeId]);
        }
        else
        {
          return $this->redirect($this->generateUrl('ens_arret_de_fonction', ['as' => $as, 'annexeId' => $annexeId, 'contratId' => $contrat->getId()]));
        }
      }

    }

    return $this->render('ENSBundle:Agents:initialisation-de-l-arret-de-fonction-enseignant.html.twig', [
      'asec'       => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      // 'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/arret-de-fonction-de-l-enseignant-{as}-{contratId}", name="ens_arret_de_fonction")
   */
  public function arretDeFonction(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoContrat    = $em->getRepository('ENSBundle:Contrat');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

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

      $request->getSession()->getFlashBag()->add('info', 'L\'arrêt de fonction de l\'enseignant <strong>'.$contrat->getEnseignant()->getNom().'</strong> a s\'est effectué avec succès.');

      return $this->redirect($this->generateUrl('ens_initialisation_l_arret_de_fonction', ['as'=> $as, 'annexeId' => $annexeId]));
    }

    return $this->render('ENSBundle:Agents:arret-de-fonction.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'contrat' => $contrat,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/ajouter-cet-enseignant-pour-l-annee-en-cours-{as}-{contratId}", name="ens_ajouter_a_l_annee")
   */
  public function ajouterEnseignantAnnee(Request $request, $as, $contratId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoContrat      = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee        = $repoAnnee->find($as);
    $contrat      = $repoContrat->find($contratId);
    
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('ens_fonctions_en_cours', ['as' => $as, 'annexeId' => $annexeId]));
    }
    
    $anneeContrat = $repoAnneeContrat->findOneBy(['annee' => $as, 'contrat' => $contratId]);
    if(is_null($anneeContrat))
    {
      $anneeContrat = new AnneeContrat();
    }
    
    $form  = $this->createForm(AnneeContratType::class, $anneeContrat);
    
    if($form->handleRequest($request)->isValid())
    {
      $ancienAC = $repoAnneeContrat->findOneBy(['annee' => $as, 'contrat' => $contratId]);
      if(is_null($ancienAC)){
        $anneeContrat->setAnnee($annee);
        $anneeContrat->setContrat($contrat);
        $anneeContrat->setNombreHeuresBureau(0);
        $anneeContrat->setCreatedBy($this->getUser());
        $anneeContrat->setCreatedAt(new \Datetime());
        $request->getSession()->getFlashBag()->add('info', 'Les heures de cours de <strong>'.$contrat->getEnseignant()->getNom().'</strong> ont été bien enregistrées pour l\'année <strong>'.$annee->getLibelle().'</strong>.');
        $em->persist($anneeContrat);
      }
      else{
        $anneeContrat->setUpdatedBy($this->getUser());
        $anneeContrat->setUpdatedAt(new \Datetime());
        $request->getSession()->getFlashBag()->add('info', 'Les heures de cours de <strong>'.$contrat->getEnseignant()->getNom().'</strong> ont été mises à jour pour l\'année <strong>'.$annee->getLibelle().'</strong>.');
      }
      $em->flush();
      return $this->redirect($this->generateUrl('ens_fonctions_en_cours', ['as'=> $as, 'annexeId' => $annexeId]));
    }

    return $this->render('ENSBundle:Agents:nouveau-annee-contrat.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'annexe'  => $annexe,
      'form'    => $form->createView(),
      'contrat' => $contrat,
    ]);
  }

  /**
   * @Security("has_role('ROLE_SUPER_ADMIN')")
   * @Route("/definir-heures-de-travail-{as}-{contratId}", name="definir_le_temps_de_travail")
   */
  public function definirLeTempsDeTravail(Request $request, $as, $contratId)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoContrat      = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee        = $repoAnnee->find($as);
    $contrat      = $repoContrat->find($contratId);
    $anneeContrat = $repoAnneeContrat->findOneBy(['annee' => $as, 'contrat' => $contratId]);

    if($annee->getAchevee() == true){
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('agents_fonctions_en_cours', ['as' => $as, 'annexeId' => $annexeId]));
    }

    if($contrat->getEnseignant()->getAdministrateur() == false){
      $request->getSession()->getFlashBag()->add('error', '<strong>'.$contrat->getEnseignant()->getNom().'</strong> n\'est pas administrateur. Par conséquent, il ne peut avoir d\'heures de travail au bureau.');
      return $this->redirect($this->generateUrl('agents_fonctions_en_cours', ['as' => $as, 'annexeId' => $annexeId]));
    }

    
    if($request->isMethod("post")){
      $data = $request->request->all();
      $heures = (int) $data["heures"];

      if(!is_null($anneeContrat)){
        $request->getSession()->getFlashBag()->add('info', 'Les heures de travail au bureau de <strong>'.$contrat->getEnseignant()->getNom().'</strong> ont été bien mises à jour pour l\'année <strong>'.$annee->getLibelle().'</strong>.');
        $anneeContrat->setNombreHeuresBureau($heures);
      }
      else{
        $anneeContrat = new AnneeContrat();
        $anneeContrat->setAnnee($annee);
        $anneeContrat->setContrat($contrat);
        $anneeContrat->setNombreHeuresCours(0);
        $anneeContrat->setNombreHeuresCoran(0);
        $anneeContrat->setNombreHeuresSamedi(0);
        $anneeContrat->setNombreHeuresBureau($heures);
        $anneeContrat->setCreatedBy($this->getUser());
        $anneeContrat->setCreatedAt(new \Datetime());   
        $em->persist($anneeContrat);
        $request->getSession()->getFlashBag()->add('info', 'Les heures de travail au bureau de <strong>'.$contrat->getEnseignant()->getNom().'</strong> ont été bien enregistrées pour l\'année <strong>'.$annee->getLibelle().'</strong>.');
      }
      $em->flush();
      return $this->redirectToRoute('agents_fonctions_en_cours', ['as' => $as, 'annexeId' => $annexeId]);
    }

    return $this->render('ENSBundle:Agents:definir-heures-de-travail.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'annexe'  => $annexe,
      'contrat' => $contrat,
      'anneeContrat' => $anneeContrat,
    ]);
  }

  /**
   * @Security("has_role('ROLE_SUPER_ADMIN')")
   * @Route("/nombre-heures-de-travail-des-agents/{as}", name="heures_de_travail")
   */
  public function heuresDeTravail(Request $request, int $as)
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
    $anneeContrats  = $repoAnneeContrat->findBy(["annee" => $as]);
    $enseignements  = $repoEns->findBy(['annee' => $as]);
    $cours = $repoAnneeContratClasse->findBy(["annee" => $as]);

    $heuresTravail = [];
    foreach ($anneeContrats as $key => $value) {
      $anneeContratId = $value->getId();
      $heuresCours    = 0;
      $heuresBureau   = 0;
      foreach ($cours as $k => $v) {
        if(!empty($v->getClasse())){
          $niveau = $v->getClasse()->getNiveau();
          $regimeClasse = $niveau->getGroupeFormation()->getReference();
          if ($anneeContratId == $v->getAnneeContrat()->getId()) {
            foreach ($enseignements as $keyEns => $valueEns) {
              // Etape 1: On va calculer les heures de cours à l'académie
              if ($valueEns->getNiveau() === $niveau and $v->getMatiere()  === $valueEns->getMatiere()) {
                $heuresCours += 1;
              } 
            }
          }
        }
        elseif(!empty($v->getHalaqa()) and $v->getAnneeContrat()->getId() == $anneeContratId){
          $heuresCours += 1;
        }
      }
      $heuresBureau = $value->getNombreHeuresBureau();
      $heuresTravail[$anneeContratId] = ["ens" => $value->getContrat()->getEnseignant()->getNom(), "cours" => $heuresCours, "bureau" => $heuresBureau];
    }
    return $this->render('ENSBundle:Agents:heures-de-travail.html.twig', [
      'asec'          => $as,
      'annee'         => $annee, 
      'annexe'        => $annexe,
      'heuresTravail'   => $heuresTravail,
      'anneeContrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/agents-en-cours-de-fonctions/{as}", name="agents_fonctions_en_cours")
   */
  public function fonctionEnCours(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $contrats = $repoContrat->agentsContratsEnCours();
    $anneeContratsEnregistres = $repoAnneeContrat->findBy(['annee' => $as]);
    $anneeContrats = [];
    foreach ($anneeContratsEnregistres as $value) {
      $anneeContrats[$value->getContrat()->getId()] = $value;//>
    }

    return $this->render('ENSBundle:Agents:fonctions-en-cours.html.twig', [
      'asec'          => $as,
      'annee'         => $annee, 
      'annexe'        => $annexe,
      'contrats'      => $contrats,
      'anneeContrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/liste-detaillee-des-enseignants-de-l-annee-{as}", name="liste_des_agents")
   */
  public function liste_des_agents(Request $request, $as)
  {
    $em               = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $annexeId         = $request->get('annexeId');
    $repoAnnexe       = $em->getRepository('ISIBundle:Annexe');
    $annexe           = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);
    $anneeContrats  = $repoAnneeContrat->findBy(["annee" => $as]);

    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "Landscape");
    $filename = "liste-des-enseignants-".$annee->getLibelle();

    $html = $this->renderView('ENSBundle:Default:impression-liste-des-enseignants-avec-details.html.twig', [
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

    return $this->render('ENSBundle:Default:enseignants-de-l-annee.html.twig', [
      'asec'     => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'contrats' => $anneeContrats,
    ]);
  }
}

 ?>
