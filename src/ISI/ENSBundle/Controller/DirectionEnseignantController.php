<?php
namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use ISI\ENSBundle\Entity\Enseignant;
use ISI\ENSBundle\Form\EnseignantType;
use ISI\ENSBundle\Form\AnneeContratType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/direction-enseignant")
 */
class DirectionEnseignantController extends Controller
{
  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/direction-enseignant-{as}", name="ens_home")
   */
  public function index(Request $request, $as)
  {
    $em             = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $annee          = $repoAnnee->find($as);
    $annexeId       = $request->get('annexeId');
    $repoAnnexe     = $em->getRepository('ISIBundle:Annexe');
    $annexe         = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $enseignants = $repoEnseignant->findBy(["enseignant" => true, "annexe" => $annexeId]);


    return $this->render('ENSBundle:Default:index.html.twig', [
      'asec'        => $as,
      'annee'       => $annee, 
      'annexe'      => $annexe,
      'enseignants' => $enseignants,
    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   */
  public function getNewMatricule($as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $repoAnnee = $em->getRepository('ISIBundle:Annee');

    // Récupération du l'année pour le matricule le fameux /17
    $annee = $repoAnnee->anneeMatricule($as);
    $annee = $annee->getSingleScalarResult();
    $matricule = $repoEnseignant->dernierMatricule();

  	$matriculeNumeric = [];
  	foreach($matricule as $mat)
  	{
  		$mat1 = str_replace ( 'F', '', $mat);
  		$matriculeNumeric[] = intval($mat1['mat']);
  	}

  	$matriculeMax = max($matriculeNumeric);
    $matricule = $matriculeMax + 1;
	  //$matricule = preg_replace('/[^0-9]/', '',$matricule[0]['plus_grand_matricule']);

    $matriculeNew = 'ISI-0'.$matricule.'M-'.$annee;

    return $matriculeNew;
  }

  /**
   * @Security("has_role('ROLE_SUPER_ADMIN')")
   * @Route("/ajouter-un-nouvel-enseignant-{as}", name="ens_add")
   */
  public function addEnseignant(Request $request, $as)
  {
    $em          = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $annexeId    = $request->get('annexeId');
    $repoAnnexe  = $em->getRepository('ISIBundle:Annexe');
    $annexe      = $repoAnnexe->find($annexeId);
    $annexes     = $repoAnnexe->findAll();
    $userManager = $this->get('fos_user.user_manager');
    $users       = $userManager->findUsers();

    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee = $repoAnnee->find($as);

    $enseignant = new Enseignant();
    $form = $this->createForm(EnseignantType::class, $enseignant);

    $matriculeNew = $this->getNewMatricule($as);

    if($form->handleRequest($request)->isValid())
  	{
      $data = $request->request->all();
      $date = $data["date"];
      $date = new \DateTime($date);

      if($data["user"] !== null){
        $user = $userManager->findUserBy(["id" => (int) $data["user"]]);
        $enseignant->setUser($user);
      }

      if($data["annexe"] !== null){
        $userAnnexe = $repoAnnexe->find((int) $data["annexe"]);
        $enseignant->setAnnexe($userAnnexe);
      }
      else{
        $enseignant->setAnnexe($annexe);
      }

      //Ici on procède à l'enrigistrement effectif de l'année scolaire en base de données
      $enseignant->setMatricule($matriculeNew);
      $enseignant->setDateNaissance($date);
      $enseignant->setRupture(false);
      $enseignant->setDateRupture(null);
      $enseignant->setCreatedBy($this->getUser());
      $enseignant->setCreatedAt(new \Datetime());
      // $file stores the uploaded PDF file
      /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
      $file = $enseignant->getPhoto();
      if(!empty($file)){
        $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
  
        // Move the file to the directory where brochures are stored
        $file->move(
          $this->getParameter('dossier_photos_enseignants'),
          $fileName
        );
  
        // Update the 'brochure' property to store the PDF file name
        // instead of its contents
        $enseignant->setPhoto($fileName);
      }
      $em->persist($enseignant);
      try{
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'L\'enseignant(e) <strong>'.$enseignant->getNom().'</strong> a été enrégistré(e) avec succès.');
      } 
      catch(\Exception $e){
        $this->addFlash('error', $e->getMessage());
      }

      // On redirige l'utilisateur vers index paramèTraversable
      return $this->redirect($this->generateUrl('ens_home',
         ['as' => $as, 'annexeId' => $annexeId]
        ));
    }

    return $this->render('ENSBundle:Default:add-enseignant.html.twig', [
      'asec'      => $as,
      'form'      => $form->createView(),
      'annee'     => $annee, 
      'annexe'    => $annexe,
      'annexes'   => $annexes,
      'users'     => $users,
      'matricule' => $matriculeNew
    ]);
  }

  /**
   * @Security("has_role('ROLE_SUPER_ADMIN')")
   * @Route("/editer-un-enseignant-{as}-{enseignantId}", name="ens_edit")
   */
  public function editEnseignant(Request $request, $as, $enseignantId)
  {
    $em             = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $repoAnnexe     = $em->getRepository('ISIBundle:Annexe');
    $annexeId       = $request->get('annexeId');
    $annexe         = $repoAnnexe->find($annexeId);
    $annexes        = $repoAnnexe->findAll();
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee  = $repoAnnee->find($as);
    $enseignant = $repoEnseignant->find($enseignantId);
    $userManager = $this->get('fos_user.user_manager');
    $users = $userManager->findUsers();
    $enseignants = $repoEnseignant->findAll();
    // dump($users);
    // foreach ($enseignants as $value) {
    //   if($value->getUser() !== null)
    //   unset($users[array_search($value->getUser(), $users)]);
    // }
    // dump($users);

    if(!empty($enseignant->getPhoto())){
      $enseignant->setPhoto(
        new File($this->getParameter('dossier_photos_enseignants').'/'.$enseignant->getPhoto())
      );
    }
    
    
    $form = $this->createForm(EnseignantType::class, $enseignant);
    if($form->handleRequest($request)->isValid())
    {
      $data = $request->request->all();
      $date = $data["date"];
      $date = new \DateTime($date);
      if($data["user"] !== null and $enseignant->getUser() !== (int) $data["user"]){
        $user = $userManager->findUserBy(["id" => (int) $data["user"]]);
        $enseignant->setUser($user);
      }

      if($data["annexe"] !== null and $enseignant->getAnnexe() !== (int) $data["annexe"]){
        $userAnnexe = $repoAnnexe->find((int) $data["annexe"]);
        $enseignant->setAnnexe($userAnnexe);
      }
      // $file stores the uploaded PDF file
      /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
      $file = $enseignant->getPhoto();
      if(!empty($file)){
        $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
  
        // Move the file to the directory where brochures are stored
        $file->move(
          $this->getParameter('dossier_photos_enseignants'),
          $fileName
        );
  
        // Update the 'brochure' property to store the PDF file name
        // instead of its contents
        $enseignant->setPhoto($fileName);
      }
      // dump($enseignant->getPhoto());
      // die();

      $enseignant->setDateNaissance($date);
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'Mise à jour des informations relatives à <strong>'.$enseignant->getNom().'</strong> terminée avec succès.');

      return $this->redirect($this->generateUrl('ens_home', array(
        'as'     => $as, 'annexeId' => $annexeId
      )));
    }

    return $this->render('ENSBundle:Default:edit-enseignant.html.twig', [
      'asec'       => $as,
      'annee'      => $annee, 
      'annexe'     => $annexe,
      'annexes'    => $annexes,
      'users'      => $users,
      'form'       => $form->createView(),
      'enseignant' => $enseignant,
    ]);
  }

  /**
   * @return string
   */
  private function generateUniqueFileName()
  {
      // md5() reduces the similarity of the file names generated by
      // uniqid(), which is based on timestamps
      return md5(uniqid());
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/information-sur-l-enseignant-{as}-{enseignantId}", name="ens_info")
   */
  public function infoEnseignant(Request $request, $as, $enseignantId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    // $repoContrat = $em->getRepository('ENSBundle:Contrat');
    // $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    // $repoConduite = $em->getRepository('ENSBundle:AnneeContratConduite');
    // $repoSanction = $em->getRepository('ENSBundle:AnneeContratSanction');
    // // $repo = $em->getRepository('ENSBundle:AnneeContrat');
    // $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');

    // $contrat   = $repoAnneeContrat->findOneBy(['asec' => $as, 'enseignant' => $enseignantId]);
    // $contratId = $contrat->getContrat()->getId();

    $annee      = $repoAnnee->find($as);
    $enseignant = $repoEnseignant->find($enseignantId);
    return $this->render('ENSBundle:Default:info-enseignant.html.twig', [
      'asec'       => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/direction-enseignants-de-l-annee-{as}", name="ens_enseignant_de_l_annee")
   */
  public function enseignantsDeLAnnee(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat   = $em->getRepository('ENSBundle:AnneeContrat');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);
    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);

    return $this->render('ENSBundle:Default:enseignants-de-l-annee.html.twig', [
      'asec'     => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'contrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/liste-des-enseignants-annee-{as}", name="ens_impression_liste_des_enseignants")
   */
  public function impressionListeDesEnseignantsDeLAnnee(Request $request, $as)
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

    $annee         = $repoAnnee->find($as);
    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as, $annexeId);

    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $filename = "liste-des-enseignants-".$annee->getLibelle();

    $html = $this->renderView('ENSBundle:Default:impression-liste-des-enseignants.html.twig', [
      // "title" => "Titre de mon document",
      "annee"         => $annee,
      "anneeContrats" => $anneeContrats,
      'annexe'        => $annexe,
      'server'        => $_SERVER["DOCUMENT_ROOT"],   
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
      'annee'    => $annee, 
      'annexe'   => $annexe,
      'contrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/liste-detaillee-des-enseignants-de-l-annee-{as}", name="ens_impression_liste_des_enseignants_plus_de_details")
   */
  public function impressionListeDesEnseignantsDeLAnneeAvecDetails(Request $request, $as)
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

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   * @Route("/mise-a-jour-un-enseignant-pour-l-annee-en-cours-{as}-{anneeContratId}", name="ens_edit_enseignant_annee")
   */
  public function modifierEnseignantAnnee(Request $request, $as, $anneeContratId)
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
    $anneeContrat    = $repoAnneeContrat->find($anneeContratId);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as' => $as, 'annexeId' => $annexeId]));
    }

    $form  = $this->createForm(AnneeContratType::class, $anneeContrat);

    if($form->handleRequest($request)->isValid())
    {
      $em = $this->getDoctrine()->getManager();
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'Les heures de cours de <strong>'.$anneeContrat->getContrat()->getEnseignant()->getNom().'</strong> ont été mise à jour avec succès pour l\'année <strong>'.$annee->getLibelle().'</strong>.');

      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as'=> $as, 'annexeId' => $annexeId]));
    }

    return $this->render('ENSBundle:Default:edit-annee-contrat.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'annexe'  => $annexe,
      'form'    => $form->createView(),
      'contrat' => $anneeContrat,
    ]);
  }
  

  /**
   * @Security("has_role('ROLE_AGENT_DIRECTION_ENSEIGNANT')")
   * @Route("/fonctions-enseignant-en-cours-{as}", name="ens_fonctions_en_cours")
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
    $contrats = $repoContrat->contratsEnCours();
    $anneeContratsEnregistres = $repoAnneeContrat->findBy(['annee' => $as]);
    $anneeContrats = [];
    foreach ($anneeContratsEnregistres as $value) {
      $anneeContrats[$value->getContrat()->getId()] = $value;//>
    }
    
    return $this->render('ENSBundle:Default:fonctions-en-cours.html.twig', [
      'asec'          => $as,
      'annee'         => $annee, 
      'annexe'        => $annexe,
      'contrats'      => $contrats,
      'anneeContrats' => $anneeContrats,
    ]);
  }

  /**
   * @Route("/get-enseignant-by-id/{agentId}", name="get_agent", options={"expose"=true})
   */
  public function eleveAction(int $agentId)
  {
    $em = $this->getDoctrine()->getManager();
    $agent = $em->getRepository('ENSBundle:Enseignant')->find($agentId);
    if($agent){
      $mail = $agent->getEmail();
    }
    else {
      $mail = null;
    }

    $response = new JsonResponse();
    return $response->setData([
      'mail'         => $mail,
    ]);
  }
}


 ?>
