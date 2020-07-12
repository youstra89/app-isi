<?php
namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ENSBundle\Entity\Convocation;
use ISI\ENSBundle\Entity\AnneeContrat;
use ISI\ENSBundle\Entity\AnneeContratConduite;
use ISI\ENSBundle\Entity\AnneeContratSanction;
use ISI\ENSBundle\Entity\AnneeContratConvocation;
use ISI\ENSBundle\Form\ConvocationType;
use ISI\ENSBundle\Form\AnneeContratType;
use ISI\ENSBundle\Form\AnneeContratConduiteType;
use ISI\ENSBundle\Form\AnneeContratSanctionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;



class DisciplineController extends Controller
{
  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/conduite-home-{as}", name="ens_conduite_home")
   */
  public function indexConduite(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);
    $nom = [];
    $pnom = [];
    foreach ($anneeContrats as $key => $ac) {
      if($ac->getContrat()->getEnseignant()->getAnnexe()->getId() == $annexeId)
      {
        $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
        $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
      }
      else{
        unset($anneeContrats[array_search($ac, $anneeContrats)]);
      }
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    return $this->render('ENSBundle:Discipline:index-conduite.html.twig', [
      'asec'   => $as,
      'annee'  => $annee,
      'annexe' => $annexe,
      'anneeContrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/enregistrer-une-conduite-pour-un-enseignant-{as}-{contratId}", name="ens_enregistrement_conduite")
   */
  public function enregistrerConduite(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee   = $repoAnnee->find($as);
    $contrat = $repoContrat->find($contratId);

    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
       return $this->redirect($this->generateUrl('ens_conduite_home', ['as' => $as, 'annexeId' => $annexeId]));
    }

    $conduite = new AnneeContratConduite();
    $form = $this->createForm(AnneeContratConduiteType::class, $conduite);

    if($form->handleRequest($request)->isValid())
  	{
      $data = $request->request->all();
      $date = $data['date'];
      $date = new \Datetime($date);

      //Ici on procède à l'enrigistrement effectif de l'année scolaire en base de données
      $conduite->setAnnee($annee);
      $conduite->setContrat($contrat);
      $conduite->setDate($date);
      $conduite->setCreatedBy($this->getUser());
      $conduite->setCreatedAt(new \Datetime());
      $em->persist($conduite);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La conduite de l\'enseignant(e) <strong>'.$contrat->getEnseignant()->getNom().'</strong> a été enrégistré(e) avec succès.');

      // On redirige l'utilisateur vers index paramèTraversable
      return $this->redirect($this->generateUrl('ens_conduite_home',
         ['as' => $as, 'annexeId' => $annexeId]
        ));
    }

    return $this->render('ENSBundle:Discipline:enregistrer-conduite.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'annexe' => $annexe,
      'contrat' => $contrat,
      'form'  => $form->createView(),
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/voir-les-conduites-d-un-enseignant-{as}-{contratId}", name="ens_voir_conduite")
   */
  public function voirConduite(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoConduite = $em->getRepository('ENSBundle:AnneeContratConduite');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee     = $repoAnnee->find($as);
    $contrat   = $repoContrat->find($contratId);
    $conduites = $repoConduite->findBy(['contrat' => $contratId]);

    return $this->render('ENSBundle:Discipline:voir-les-conduite-d-un-enseignant.html.twig', [
      'asec'      => $as,
      'annee'     => $annee,
      'annexe' => $annexe,
      'contrat'   => $contrat,
      'conduites' => $conduites,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/convocation-home-{as}", name="ens_convocation_home")
   */
  public function indexConvocation(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoAnneeConvocation = $em->getRepository('ENSBundle:AnneeContratConvocation');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee  = $repoAnnee->find($as);

    $requete_des_convocations = "SELECT COUNT(c.id) AS nbr, c.id , c.instance, c.date, c.motif FROM convocation c JOIN annee_contrat_convocation a ON c.id = a.convocation_id WHERE a.annee_id = :asec GROUP BY c.id;";
    $statement = $em->getConnection()->prepare($requete_des_convocations);
    $statement->bindValue('asec', $as);
    $statement->execute();
    $convocations = $statement->fetchAll();
    // $convocations = $repoConvocation->findBy([['annee' => $as]]);
    $convoques    = $repoAnneeConvocation->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Discipline:index-convocation.html.twig', [
      'asec'         => $as,
      'annee'        => $annee,
      'annexe' => $annexe,
      'convoques'    => $convoques,
      'convocations' => $convocations,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/convoquer-des-enseignants-{as}", name="ens_convoquer_enseignant")
   */
  public function convoquerHome(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $contrats = $repoAnneeContrat->findBy(['annee' => $as]);

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $session = $this->get('session');
      if(isset($data['contrats']))
      {
        $contratsId = $data['contrats'];
        $session->set('ids', $contratsId);
        return $this->redirectToRoute('ens_info_convocation', ['as' => $as, 'annexeId' => $annexeId]);
        return new Response(var_dump($contratsId));
      }
      else{
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez sélectionné aucun enseignant.');
      }

    }

    return $this->render('ENSBundle:Discipline:convoquer-home.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'annexe' => $annexe,
      'contrats' => $contrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/rempli-convocation-des-enseignants-{as}", name="ens_info_convocation")
   */
  public function remplirConvocation(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
       return $this->redirect($this->generateUrl('ens_convocation_home', ['as' => $as, 'annexeId' => $annexeId]));
    }

    $ids = $this->get('session')->get('ids');
    $convocation = new Convocation();
    $form = $this->createForm(ConvocationType::class, $convocation);
    // return new Response(var_dump($ids));
    $contrats = [];
    foreach($ids as $id)
    {
      $contrats[] = $repoContrat->find($id);
    }
    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $date = $data['date'];
      $instance = $data['isi_ensbundle_convocation']['instance'];
      $motif = $data['isi_ensbundle_convocation']['motif'];
      $date = new \Datetime($date);
      // return new Response(var_dump($instance));
      $convocation->setDate($date);
      $convocation->setInstance($instance);
      $convocation->setMotif($motif);
      $convocation->setCreatedBy($this->getUser());
      $convocation->setCreatedAt(new \Datetime());
      $em->persist($convocation);

      foreach($ids as $id)
      {
        $contrat = $repoContrat->find($id);
        $convoque = new AnneeContratConvocation();
        $convoque->setAnnee($annee);
        $convoque->setContrat($contrat);
        $convoque->setConvocation($convocation);
        $convoque->setCreatedBy($this->getUser());
        $convoque->setCreatedAt(new \Datetime());
        $em->persist($convoque);
      }
      $request->getSession()->getFlashBag()->add('info', 'Convocation enregistrée avec succès.');
      $em->flush();
      return $this->redirectToRoute('ens_convocation_home', ['as' => $as, 'annexeId' => $annexeId]);
    }

    // $contrats = $repoAnneeContrat->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Discipline:info-convocation.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'annexe' => $annexe,
      'form'     => $form->createView(),
      'contrats' => $contrats,
    ]);
  }


  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/enregistrement-de-l-audition-et-du-verdict-pour-la-convocation-de-l-enseignant-{as}-{ensConvocationId}", name="ens_audition_et_verdict")
   */
  public function auditionEtVerdict(Request $request, $as, $ensConvocationId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoAnneeConvocation = $em->getRepository('ENSBundle:AnneeContratConvocation');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    # $ensConvocationId ici est le paramètre qui renvoi l'id de l'entité AnneeContratConvocation,
    #qui correspond à l'enregistrement d'une convocation d'un enseignant pour une année donnée
    $annee  = $repoAnnee->find($as);
    $convocation = $repoAnneeConvocation->find($ensConvocationId);

    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
       return $this->redirect($this->generateUrl('ens_convocation_home', ['as' => $as, 'annexeId' => $annexeId]));
    }

    if($request->isMethod('post'))
    {
      $data      = $request->request->all();
      $audition = $data['audition'];
      $verdict  = $data['verdict'];

      $convocation->setAudition($audition);
      $convocation->setVerdict($verdict);
      $convocation->setUpdatedBy($this->getUser());
      $convocation->setUpdatedAt(new \Datetime());
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'Le verdict pour l\'esnseignant <strong>'.$convocation->getContrat()->getEnseignant()->getNom().'</strong> a bien été enregistré.');
      return $this->redirectToRoute('ens_convocation_home', ['as' => $as, 'annexeId' => $annexeId]);

    }

    return $this->render('ENSBundle:Discipline:audition-et-verdict-d-un-enseignant-convoque.html.twig', [
      'asec'        => $as,
      'annee'       => $annee,
      'annexe' => $annexe,
      'convocation' => $convocation,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/sanction-home-{as}", name="ens_sanction_home")
   */
  public function sanctionHome(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $requete_des_sanctions = "SELECT c.id, e.id AS ensid, e.matricule, CONCAT(e.nom_fr, ' ', e.pnom_fr) AS nomFr, CONCAT(e.pnom_ar, ' ', e.nom_ar) AS nomAr, e.sexe, COUNT(acs.id) AS nbrSanction FROM enseignant e JOIN contrat c ON e.id = c.enseignant_id JOIN annee_contrat ac ON ac.contrat_id = c.id LEFT OUTER JOIN annee_contrat_sanction acs ON acs.contrat_id = c.id WHERE ac.annee_id = :asec AND e.annexe_id = :annexeId GROUP BY c.id;";
    $statement = $em->getConnection()->prepare($requete_des_sanctions);
    $statement->bindValue('asec', $as);
    $statement->bindValue('annexeId', $annexeId);
    $statement->execute();
    $sanctions = $statement->fetchAll();
    // $convocations = $repoConvocation->findBy([['annee' => $as]]);
    // $convoques    = $repoAnneeConvocation->findBy(['annee' => $as]);
    // $contrats = $repoAnneeContrat->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Discipline:index-sanction.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'annexe' => $annexe,
      'sanctions' => $sanctions,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/voir-les-sanctions-d-un-enseignant-{as}-{contratId}", name="ens_voir_sanction")
   */
  public function voirSanction(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoSanction = $em->getRepository('ENSBundle:AnneeContratSanction');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $contrat  = $repoContrat->find($contratId);
    $sanctions = $repoSanction->findBy(['annee' => $as, 'contrat' => $contratId]);
    // $convocations = $repoConvocation->findBy([['annee' => $as]]);
    // $convoques    = $repoAnneeConvocation->findBy(['annee' => $as]);
    // $contrats = $repoAnneeContrat->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Discipline:voir-sanctions-d-un-enseignant.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'contrat'   => $contrat,
      'annexe' => $annexe,
      'sanctions' => $sanctions,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/enregistrement-d-une-sanction-{as}-{contratId}", name="ens_add_sanction")
   */
  public function addSanction(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee    = $em->getRepository('ISIBundle:Annee');
    $repoContrat  = $em->getRepository('ENSBundle:Contrat');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $contrat  = $repoContrat->find($contratId);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de sanctionner car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('ens_sanction_home', ['as' => $as, 'annexeId' => $annexeId]));
    }
    $sanction = new AnneeContratSanction();
    $form = $this->createForm(AnneeContratSanctionType::class, $sanction);

    if($form->handleRequest($request)->isValid())
    {
      $data = $request->request->all();
      $date = $data['date'];
      $date = new \Datetime($date);
      // $date = $date->format('Y-m-d');

      $sanction->setContrat($contrat);
      $sanction->setAnnee($annee);
      $sanction->setDate($date);
      $sanction->setCreatedBy($this->getUser());
      $sanction->setCreatedAt(new \Datetime());
      $em->persist($sanction);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La canstion pour l\'enseignant <strong>'.$contrat->getEnseignant()->getNom().'</strong> a enregistré avec succès.');

      return $this->redirect($this->generateUrl('ens_sanction_home', ['as'=> $as, 'annexeId' => $annexeId]));
    }
    return $this->render('ENSBundle:Discipline:enregistrer-une-sanction.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'annexe' => $annexe,
      'contrat' => $contrat,
      'form'  => $form->createView(),
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("", name="")
   */
  public function ajouterEnseignantAnnee(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeContrat   = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $contrat    = $repoContrat->find($contratId);
    $anneeContrat = $repoAnneeContrat->findOneBy(['annee' => $as, 'annexeId' => $annexeId, 'contrat' => $contratId]);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter un enseignant car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as' => $as, 'annexeId' => $annexeId]));
    }

    if(!is_null($anneeContrat))
    {
      $request->getSession()->getFlashBag()->add('error', '<strong>'.$anneeContrat->getContrat()->getEnseignant()->getNom().'</strong> est déjà utilisé(e) pour l\'année <strong>'.$annee->getLibelle().'</strong>.');
      return $this->redirectToRoute('ens_fonctions_en_cours', ['as' => $as, 'annexeId' => $annexeId]);
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

      $request->getSession()->getFlashBag()->add('info', 'Les heures de cours de <strong>'.$contrat->getEnseignant()->getNom().'</strong> a été bien enregistrée pour l\'année <strong>'.$annee->getLibelle().'</strong>.');

      return $this->redirect($this->generateUrl('ens_fonctions_en_cours', ['as'=> $as, 'annexeId' => $annexeId]));
    }

    return $this->render('ENSBundle:Enseignant:nouveau-annee-contrat.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'annexe' => $annexe,
      'form'    => $form->createView(),
      'contrat' => $contrat,
    ]);
  }
}


 ?>
