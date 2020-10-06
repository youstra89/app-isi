<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

// use Symfony\Component\Form\Extention\Core\Type\TextType;

use ISI\ISIBundle\Entity\Classe;
use ISI\ISIBundle\Entity\Halaqa;
use ISI\ISIBundle\Entity\Annee;
use ISI\ISIBundle\Form\ClasseType;
use ISI\ISIBundle\Form\HalaqaType;
use ISI\ISIBundle\Form\AnneeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ParametresController extends Controller
{
  /**
   * @Security("has_role('ROLE_SCOLARITE') or has_role('ROLE_SCOLARITE_ANNEXE')")
   * @Route("/parametres-scolarite/{as}-{annexeId}", name="isi_parametres")
   */
  public function index(Request $request, int $as, int $annexeId)
  {
    // // On vérifie que l'utilisateur dispose bien du rôle ROLE_AUTEUR
    // if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
    //   // Sinon on déclenche une exception « Accès interdit »
    //   throw new AccessDeniedException('Accès limité aux auteurs.');
    // }
    $em = $this->getDoctrine()->getManager();
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    // dump($as);
    $annee = $repoAnnee->find($as);

    return $this->render('ISIBundle:Parametres:index.html.twig', [
      'asec'  => $as,
      'annee' => $annee, 
      'annexe' => $annexe,
    ]);

  }

  /**
   * @Security("has_role('ROLE_SUPER_ADMIN')")
   * @Route("/nouvelle-annnee-scolaire-{as}-{annexeId}", name="isi_nouvelle_annee")
   */
  public function nouvelleAnneeAction(Request $request, $as, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    // Si $as vaut 0, cela veut dire que nous sommes à la création de la première année scolaire
    if($as == 0)
    {
      $annee = new Annee();
      $annee->setAchevee(true);
      $annee->setLibelleAnnee("En cours d'insertion...");
    }
    else{
      $nbr = 0;
      $annee = $repoAnnee->anneeEnCours();
      $frequentation = $repoFrequenter->frequenterValidee($as);
      // return new Response($frequentation);
      // foreach ($eleves as $eleve) {
      //   $frequente       = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'annee' => $as]);
      //   $frequentation[] = $frequente;

      //   if($frequente->getValidation() == NULL)
      //     $nbr++;
      // }
      // if (!empty($frequentation)) {
      //   if($annee->getAchevee() == FALSE)
      //   {
      //     // return new Response(var_dump($frequentation));
      //     $request->getSession()->getFlashBag()->add("error", "L'année scolaire en cours(<strong>".$annee->getLibelle()."</strong>) n'est pas encore terminée.");
      //     return $this->redirect($this->generateUrl('isi_parametres',
      //         array('as' => $annee->getId(), 'annexeId' => $annexeId)
      //       ));
      //   }
      // }
      // return new Response(var_dump($frequentation));
    }

  	$nouvelleAnnee = new Annee();

  	// $form = $this->get('form.factory')->create(new Annee, $nouvelleAnnee);
  	$form = $this->createForm(AnneeType::class, $nouvelleAnnee);

  	if($form->handleRequest($request)->isValid())
  	{
      //Ici on procède à l'enrigistrement effectif de l'année scolaire en base de données
      $data  = $request->request->all();
      $debut_semestre_1 = new \DateTime($data['debut_semestre_1']);
      $fin_semestre_1   = new \DateTime($data['fin_semestre_1']);
      $debut_semestre_2 = new \DateTime($data['debut_semestre_2']);
      $fin_semestre_2   = new \DateTime($data['fin_semestre_2']);

      $nouvelleAnnee->setDebutPremierSemestre($debut_semestre_1);
      $nouvelleAnnee->setFinPremierSemestre($fin_semestre_1);
      $nouvelleAnnee->setDebutSecondSemestre($debut_semestre_2);
      $nouvelleAnnee->setFinSecondSemestre($fin_semestre_2);
      $nouvelleAnnee->setCreatedAt(new \DateTime());
      $nouvelleAnnee->setCreatedAt($this->getUser());
      $nouvelleAnnee->setAchevee(false);
      $em->persist($nouvelleAnnee);

      $annee->setAchevee(true);
      $annee->setUpdatedBy($this->getUser());
      $annee->setUpdatedAt(new \Datetime());
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La nouvelle année scolaire <strong>'.$nouvelleAnnee->getLibelle().'</strong> a été bien enrégistrée.');

      // On redirige l'utilisateur vers index paramèTraversable
      return $this->redirect($this->generateUrl('isi_parametres',[
        'as'       => $nouvelleAnnee->getId(), 
        'annexeId' => $annexeId
      ]));
  	}

  	return $this->render('ISIBundle:Parametres:add-annee.html.twig', array(
        'asec'   => $as,
        'annee'  => $annee, 
        'annexe' => $annexe,
  			'form'   => $form->createView()
  	));
  }

  /**
   * @Security("has_role('ROLE_SUPER_ADMIN')")
   * @Route("/mise-a-jour-de-l-annnee-scolaire-{as}-{annexeId}", name="edit_annee")
   */
  public function edit_annee(Request $request, int $as, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee = $repoAnnee->find($as);

    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
       return $this->redirect($this->generateUrl('isi_annees_precedentes', ['as' => $as, 'annexeId' => $annexeId]));
    }

  	if($request->isMethod('post'))
  	{
      $data  = $request->request->all();
      $debut_semestre_1 = new \DateTime($data['debut_semestre_1']);
      $fin_semestre_1   = new \DateTime($data['fin_semestre_1']);
      $debut_semestre_2 = new \DateTime($data['debut_semestre_2']);
      $fin_semestre_2   = new \DateTime($data['fin_semestre_2']);

      $annee->setDebutPremierSemestre($debut_semestre_1);
      $annee->setFinPremierSemestre($fin_semestre_1);
      $annee->setDebutSecondSemestre($debut_semestre_2);
      $annee->setFinSecondSemestre($fin_semestre_2);
      $annee->setUpdatedAt(new \DateTime());
      $annee->setUpdatedAt($this->getUser());
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'L\'année scolaire <strong>'.$annee->getLibelle().'</strong> a été mise à jour avec succès.');

      // On redirige l'utilisateur vers index paramèTraversable
      return $this->redirect($this->generateUrl('isi_annees_precedentes',[
        'as'       => $annee->getId(), 
        'annexeId' => $annexeId
      ]));
  	}

  	return $this->render('ISIBundle:Parametres:edit-annee.html.twig', array(
        'asec'   => $as,
        'annee'  => $annee, 
        'annexe' => $annexe,
  	));
  }

  // Pour voir les années précédentes d'activité
  /**
   * @Security("has_role('ROLE_USER')")
   * @Route("/les-annnees-scolaires-precedentes-{as}-{annexeId}", name="isi_annees_precedentes")
   */
  public function anneesPrecedentesAction(Request $request, $as, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository("ISIBundle:Annee");
    $annee = $repoAnnee->find($as);
    $annees = $repoAnnee->toutesLesAnnees();
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    // $userManager = $this->get('fos_user.user_manager');
    $user = $this->getUser();
    // $user = $this->container->get('security.context')->getToken()->getUser();
    // $user = $userManager->findUserBy(['id' => $userId]);

    // $connexion = $this->container->get('services.userconnexion');

    // $cnx = $connexion->userConnexion($user);

    // $session = $this->get("session");
    // return new Response(var_dump($cnx));

    // Le code qu'on va écrire ici nous permettra de faire en sorte que l'utilisateur soit redirigé
    // vers la page où il se trouvait avant de choisir une année
    // $locale = $request->getLocale();
    // $langue = ($locale == 'fr') ? 'ar' : 'fr' ;
    // $request->setLocale($langue);
    $route        = $request->query->get('route');
    $route_params = $request->query->get('route_params');
    // $route_params['_locale'] = $langue;
    // return new Response(var_dump($route, $route_params));

    return $this->render('ISIBundle:Parametres:annees-precedentes.html.twig',[
      'asec'   => $as,
      'annee' => $annee, 'annexe'   => $annexe,
      'annees' => $annees,
      'route'  => $route,
      'route_params'  => $route_params,
    ]);
  }

  //Edition de Classe
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   * @Route("/edition-de-classe-{as}-{classeId}-{regime}-{annexeId}", name="isi_edit_classe")
   */
  public function editClasseAction(Request $request, $as, $classeId, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $classe = $repoClasse->find($classeId);
    $annee  = $repoAnnee->find($as);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
       return $this->redirect($this->generateUrl('isi_gestion_classes', ['as' => $as, 'regime' => $regime, 'annexeId' => $annexeId]));
    }


    $form = $this->createForm(ClasseType::class, $classe, [
      'regime' => $regime
    ]);
    if($form->handleRequest($request)->isValid())
    {
      $classe->setUpdatedBy($this->getUser());
      $classe->setUpdatedAt(new \Datetime());
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'La classe <strong>'.$classe->getLibelleFr().'</strong> a été mise à jour avec succès.');

      return $this->redirect($this->generateUrl('isi_gestion_classes', [
        'as' => $as,
        'regime' => $regime, 
        'annexeId' => $annexeId
      ]));
    }

    return $this->render('ISIBundle:Parametres:editClasse.html.twig', array(
      'form'  => $form->createView(),
      'annee' => $annee, 'annexe'   => $annexe,
      'asec'  => $as
    ));
  }

  //Edition de Halaqa
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   * @Route("/edition-de-halaqa-{as}-{halaqaId}-{regime}-{annexeId}", name="isi_edit_halaqa")
   */
  public function editHalaqaAction(Request $request, $as, $halaqaId, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $halaqa = $repoHalaqa->find($halaqaId);
    $annee  = $repoAnnee->find($as);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des halaqas car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('isi_gestion_halaqas', ['as' => $as, 'regime' => $regime]));
    }

    $form = $this->createForm(HalaqaType::class, $halaqa);
    if($form->handleRequest($request)->isValid())
    {
      $halaqa->setUpdatedBy($this->getUser());
      $halaqa->setUpdatedAt(new \Datetime());
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'Halaqa <strong>'.$halaqa->getLibelle().'</strong> mise à jour avec sussès.');

      return $this->redirect($this->generateUrl('isi_gestion_halaqas', [
          'as'     => $as,
          'regime' => $regime,
          'annexeId' => $annexeId,
        ]));
      }
      
      return $this->render('ISIBundle:Parametres:editHalaqa.html.twig', [
        'form'  => $form->createView(),
        'halaqa' => $halaqa,
        'regime' => $regime,
        'annee' => $annee, 'annexe'   => $annexe,
        'asec'  => $as
      ]);
  }

  //Action pour l'affichage des classes
  /**
   * @Security("has_role('ROLE_SCOLARITE') or has_role('ROLE_SCOLARITE_ANNEXE')")
   * @Route("/gestion-des-classes-{as}-{regime}-{annexeId}", name="isi_gestion_classes")
   */
  public function lesClassesAction(Request $request, int $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);

    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee   = $repoAnnee->find($as);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);
    $classes = $repoClasse->findBy(['annee' => $as, 'annexe' => $annexeId]);

    return $this->render('ISIBundle:Parametres:gestion-des-classes.html.twig', array(
      'asec'    => $as,
      'regime'  => $regime,
      'annee' => $annee, 
      'annexe'   => $annexe,
      'niveaux' => $niveaux,
      'classes' => $classes,
    ));
    // return $this->render('ISIBundle:Default:index.html.twig');
  }

  //Action pour l'affichage des classes
  /**
   * @Security("has_role('ROLE_SCOLARITE') or has_role('ROLE_SCOLARITE_ANNEXE')")
   * @Route("/gestion-des-halaqas-{as}-{regime}-{annexeId}", name="isi_gestion_halaqas")
   */
  public function lesHalaqasAction(Request $request, int $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee  = $repoAnnee->find($as);
    $regimeMiniscule = strtolower($regime);

    $listHalaqas = $repoHalaqa->findBy(['annee' => $as, 'regime' => $regime, 'annexe' => $annexeId]);

    return $this->render('ISIBundle:Parametres:gestionDesHalaqas.html.twig', array(
      'asec'    => $as,
      'regime'  => $regime,
      'annee' => $annee, 'annexe'   => $annexe,
      'halaqas' => $listHalaqas
    ));
  }

  //Action d'ajout d'une nouvelle classe
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   * @Route("/ajout-de-classe-{as}-{regime}-{annexeId}", name="isi_nouvelle_classe")
   */
  public function addClasseAction(Request $request, $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee   = $repoAnnee->find($as);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour. 
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter des classes car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('isi_gestion_classes', ['as' => $as, 'regime' => $regime, 'annexeId' => $annexeId]));
    } 

    $classe = new Classe;
    $form = $this->createForm(ClasseType::class, $classe, ['regime' => $regime]);
    // $form = $this->get('form.factory')->create(ClasseType::class, $classe, ['regime' => $regime]);

    if($form->handleRequest($request)->isValid())
    {
      $em = $this->getDoctrine()->getManager();
      $classe->setAnnee($annee);
      $niveauId = $classe->getNiveau()->getId();
      $niveau   = $repoNiveau->find($niveauId);
      $classe->setAnnexe($annexe);
      $classe->setLibelleFr($niveau->getLibelleFr().' - '.$classe->getLibelleFr());
      $classe->setLibelleAr($niveau->getLibelleAr().' - '.$classe->getLibelleAr());
      // return new Response(var_dump($classe));
      $em->persist($classe);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La classe <strong>'.$classe->getLibelleFr().'</strong> a été bien sauvegardée.');

      return $this->redirect($this->generateUrl('isi_gestion_classes', array(
        'as'     => $as,
        'regime' => $regime, 'annexeId' => $annexeId
      )));
    }

    return $this->render('ISIBundle:Parametres:addClasse.html.twig', array(
      'form'   => $form->createView(),
      'asec'   => $as,
      'regime' => $regime,
      'annee' => $annee, 'annexe'   => $annexe,
    ));
  }

  //Action d'ajout d'une nouvelle halaqa
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   * @Route("/ajout-de-halaqa-{as}-{regime}-{annexeId}", name="isi_nouvelle_halaqa")
   */
  public function addHalaqaAction(Request $request, $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee  = $repoAnnee->find($as);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter des halaqass car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('isi_gestion_halaqas', ['as' => $as, 'regime' => $regime, 'annexeId' => $annexeId]));
    } 

    $halaqa = new Halaqa;
    $form = $this->createForm(HalaqaType::class, $halaqa);

    if($form->handleRequest($request)->isValid())
    {
      $halaqa->setAnnexe($annexe);
      $halaqa->setAnnee($annee);
      $halaqa->setRegime($regime);
      $halaqa->setCreatedBy($this->getUser());
      $halaqa->setCreatedAt(new \Datetime());
      $em->persist($halaqa);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La halaqa <strong>'.$halaqa->getLibelle().'</strong> a été bien sauvegardée.');

      return $this->redirect($this->generateUrl('isi_gestion_halaqas', array(
        'as'     => $as,
        'regime' => $regime, 'annexeId' => $annexeId
        )));
    }

    return $this->render('ISIBundle:Parametres:addHalaqa.html.twig', array(
      'form'  => $form->createView(),
      'asec'  => $as,
      'annee' => $annee, 'annexe'   => $annexe,
    ));
  }

  //Page d'accueil pour la liaison entre les classes et les matières pour une année scolaire donnée
  /**
   * @Security("has_role('ROLE_SCOLARITE') or has_role('ROLE_SCOLARITE_ANNEXE')")
   * @Route("/niveaux-matieres-{as}-{regime}-{annexeId}", name="isi_niveaux_matieres")
   */
  public function niveauxMatieresAction(Request $request, int $as, $regime, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $listNiveaux = $repoNiveau->findAll();
    $annee  = $repoAnnee->find($as);
    // $listMatieresNiveaux = $repoMatiere->listMatieresNiveaux($as );

    return $this->render('ISIBundle:Parametres:niveaux-matieres-home.html.twig', array(
      'asec'    => $as,
      'regime'  => $regime,
      'annee' => $annee, 'annexe'   => $annexe,
      'niveaux' => $listNiveaux
    ));
  }

  // Pour voir la liste des matières d'un niveau de formation donné
  /**
   * @Security("has_role('ROLE_SCOLARITE') or has_role('ROLE_SCOLARITE_ANNEXE')")
   * @Route("/voir-liste-des-matieres-du-niveaux-{as}-{niveauId}-{regime}-{annexeId}", name="isi_liste_niveaux_matieres")
   */
  public function listeMatieresNiveauxAction(Request $request, $as, $regime, $niveauId, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEns     = $em->getRepository('ISIBundle:Enseignement');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $matieres = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    $niveau   = $repoNiveau->find($niveauId);
    $annee    = $repoAnnee->find($as);
    $ens      = $repoEns->findBy(['annee' => $as, 'niveau' => $niveauId]);

    return $this->render('ISIBundle:Parametres:liste-des-matieres-du-niveau.html.twig', [
      'matieres' => $matieres,
      'asec'     => $as,
      'regime'   => $regime,
      'annee' => $annee, 'annexe'   => $annexe,
      'niveau'   => $niveau,
      'ens'      => $ens,
    ]);
  }
}

?>
