<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

// use Symfony\Component\Form\Extention\Core\Type\TextType;

use ISI\ISIBundle\Entity\Niveau;
use ISI\ISIBundle\Entity\Classe;
use ISI\ISIBundle\Entity\Examen;
use ISI\ISIBundle\Entity\Matiere;
use ISI\ISIBundle\Entity\Halaqa;
use ISI\ISIBundle\Entity\Enseignement;
use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ISIBundle\Repository\ExamenRepository;
use ISI\ISIBundle\Repository\NiveauRepository;
use ISI\ISIBundle\Repository\MatiereRepository;
use ISI\ISIBundle\Repository\EnseignementRepository;
use ISI\ISIBundle\Repository\AnneescolaireRepository;

use ISI\ISIBundle\Form\ExamenType;
use ISI\ISIBundle\Form\NiveauType;
use ISI\ISIBundle\Form\ClasseType;
use ISI\ISIBundle\Form\HalaqaType;
use ISI\ISIBundle\Form\MatiereType;
use ISI\ISIBundle\Form\EnseignementType;
use ISI\ISIBundle\Form\AnneescolaireType;

use ServicesBundle\Services\UserConnexion;
use ServicesBundle\DependencyInjection;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class ParametresController extends Controller
{
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function indexAction(Request $request, $as)
  {
    // // On vérifie que l'utilisateur dispose bien du rôle ROLE_AUTEUR
    // if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
    //   // Sinon on déclenche une exception « Accès interdit »
    //   throw new AccessDeniedException('Accès limité aux auteurs.');
    // }
    if ($as == null or $as == 0) {
      throw $this->createNotFoundException("Sélectionnez une année scolaire valide.");
    }
    else {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');

      $annee = $repoAnnee->find($as);

      return $this->render('ISIBundle:Parametres:index.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
      ]);
    }

  }

  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function nouvelleAnneeAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    // Si $as vaut 0, cela veut dire que nous sommes à la création de la première année scolaire
    if($as == 0)
    {
      $annee = new Anneescolaire();
      $annee->setAchevee(true);
      $annee->setLibelleAnneeScolaire("En cours d'insertion...");
    }
    else{
      $nbr = 0;
      $annee = $repoAnnee->anneeEnCours();
      $frequentation = $repoFrequenter->frequenterValidee($as);
      // return new Response($frequentation);
      // foreach ($eleves as $eleve) {
      //   $frequente       = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);
      //   $frequentation[] = $frequente;

      //   if($frequente->getValidation() == NULL)
      //     $nbr++;
      // }
      if (!empty($frequentation)) {
        if($annee->getAchevee() == FALSE)
        {
          // return new Response(var_dump($frequentation));
          $request->getSession()->getFlashBag()->add("error", "L'année scolaire en cours(".$annee->getLibelleAnneeScolaire().") n'est pas encore terminée.");
          return $this->redirect($this->generateUrl('isi_parametres',
              array('as' => $annee->getAnneeScolaireId())
            ));
        }
      }
      else {
        // On peut donc créer une nouvelle année
        $annee->setAchevee(TRUE);
        $annee->setDateUpdate(new \Datetime());
        $em->flush();
        // return new Response("C'est cool");
      }
      // return new Response(var_dump($frequentation));
    }

  	$nouvelleAnnee = new Anneescolaire();

  	// $form = $this->get('form.factory')->create(new Anneescolaire, $nouvelleAnnee);
  	$form = $this->createForm(AnneescolaireType::class, $nouvelleAnnee);

  	if($form->handleRequest($request)->isValid())
  	{
  		//Ici on procède à l'enrigistrement effectif de l'année scolaire en base de données
      $em = $this->getDoctrine()->getManager();
      $nouvelleAnnee->setAchevee(FALSE);
      $em->persist($nouvelleAnnee);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La nouvelle année scolaire a été bien enrégistrée.');

      // On redirige l'utilisateur vers index paramèTraversable
      return $this->redirect($this->generateUrl('isi_parametres',
          array('as' => $nouvelleAnnee->getAnneeScolaireId())
        ));
  	}

  	return $this->render('ISIBundle:Parametres:addAnneeScolaire.html.twig', array(
        'asec'  => $as,
        'annee' => $annee,
  			'form'  => $form->createView()
  		));
  }

  // Pour voir les années précédentes d'activité
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function anneesPrecedentesAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository("ISIBundle:Anneescolaire");
    $annee = $repoAnnee->find($as);
    $annees = $repoAnnee->toutesLesAnnees();

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
      'annee'  => $annee,
      'annees' => $annees,
      'route'  => $route,
      'route_params'  => $route_params,
    ]);
  }

  //Edition de Classe
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function editClasseAction(Request $request, $as, $classeId, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    $classe = $repoClasse->find($classeId);
    $genre  = $classe->getGenre();
    $annee  = $repoAnnee->find($as);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
       return $this->redirect($this->generateUrl('isi_gestion_classes', ['as' => $as, 'regime' => $regime]));
    }


    $form = $this->createForm(ClasseType::class, $classe, [
      'regime' => $regime
    ]);
    if($form->handleRequest($request)->isValid())
    {
      $data = $form->getData();
      // return new Response(var_dump($data->getGenre(), $genre));
      // return new Response(var_dump($data->getGenre()));
      // A la modofication de la classe, si l'on veut changer le genre alors qu'il y a déjà des élèves inscrits, 
      // on bloque de code
      $fq = $repoFrequenter->findBy(['classe' => $classe->getClasseId()]);
      if (count($fq) != 0 && $genre != $data->getGenre()) {
        # code...
        $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas modifier le genre de la classe '.$classe->getLibelleClasseFr().'. Des élèves y sont déjà inscrits.');
        return $this->redirect($this->generateUrl('isi_gestion_classes', [
        'as' => $as,
        'regime' => $regime
      ]));
      }
      $classe->setDateUpdate(new \Datetime());
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'La classe '.$classe->getLibelleClasseFr().' a été mise à jour avec succès.');

      return $this->redirect($this->generateUrl('isi_gestion_classes', [
        'as' => $as,
        'regime' => $regime
      ]));
    }

    return $this->render('ISIBundle:Parametres:editClasse.html.twig', array(
      'form'  => $form->createView(),
      'annee' => $annee,
      'asec'  => $as
    ));
  }

  //Edition de Halaqa
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function editHalaqaAction(Request $request, $as, $halaqaId, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');

    $halaqa = $repoHalaqa->find($halaqaId);
    $annee  = $repoAnnee->find($as);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des halaqas car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
      return $this->redirect($this->generateUrl('isi_gestion_halaqas', ['as' => $as, 'regime' => $regime]));
    }

    $form = $this->createForm(HalaqaType::class, $halaqa);
    if($form->handleRequest($request)->isValid())
    {
      $halaqa->setDateUpdate(new \Datetime());
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'Halaqa mise à jour avec sussès.');

      return $this->redirect($this->generateUrl('isi_gestion_halaqas', [
        'as'     => $as,
        'regime' => $regime
        ]
      ));
    }

    return $this->render('ISIBundle:Parametres:editHalaqa.html.twig', [
      'form'  => $form->createView(),
      'annee' => $annee,
      'asec'  => $as
    ]);
  }

  //Action pour l'affichage des classes
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function lesClassesAction($as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');

    $annee   = $repoAnnee->find($as);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);
    $classes = $repoClasse->findBy(['anneeScolaire' => $as]);

    return $this->render('ISIBundle:Parametres:gestion-des-classes.html.twig', array(
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'niveaux' => $niveaux,
      'classes' => $classes,
    ));
    // return $this->render('ISIBundle:Default:index.html.twig');
  }

  //Action pour l'affichage des classes
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function lesHalaqasAction($as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');

    $annee  = $repoAnnee->find($as);
    $regimeMiniscule = strtolower($regime);

    $listHalaqas = $repoHalaqa->findBy(['anneeScolaire' => $as, 'regime' => $regime]);

    return $this->render('ISIBundle:Parametres:gestionDesHalaqas.html.twig', array(
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'halaqas' => $listHalaqas
    ));
  }

  //Action d'ajout d'une nouvelle classe
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function addClasseAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');

    $annee   = $repoAnnee->find($as);
    $niveaux = $repoNiveau->niveauDeClasse($regime);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour. 
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter des classes car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
      return $this->redirect($this->generateUrl('isi_gestion_classes', ['as' => $as, 'regime' => $regime]));
    }

    $classe = new Classe;
    $form = $this->createForm(ClasseType::class, $classe, ['regime' => $regime]);
    // $form = $this->get('form.factory')->create(ClasseType::class, $classe, ['regime' => $regime]);

    if($form->handleRequest($request)->isValid())
    {
      $em = $this->getDoctrine()->getManager();
      $classe->setAnneeScolaire($annee);
      $niveauId = $classe->getNiveau()->getId();
      $niveau   = $repoNiveau->find($niveauId);
      $classe->setLibelleClasseFr($niveau->getLibelleFr().' - '.$classe->getLibelleClasseFr());
      $classe->setLibelleClasseAr($niveau->getLibelleAr().' - '.$classe->getLibelleClasseAr());
      // return new Response(var_dump($classe));
      $em->persist($classe);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La classe '.$classe->getLibelleClasseFr().' a été bien sauvegardée.');

      return $this->redirect($this->generateUrl('isi_gestion_classes', array(
        'as'     => $as,
        'regime' => $regime
      )));
    }

    return $this->render('ISIBundle:Parametres:addClasse.html.twig', array(
      'form'   => $form->createView(),
      'asec'   => $as,
      'regime' => $regime,
      'annee'  => $annee,
    ));
  }

  //Action d'ajout d'une nouvelle halaqa
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function addHalaqaAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');

    $annee  = $repoAnnee->find($as);

    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter des halaqass car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
      return $this->redirect($this->generateUrl('isi_gestion_halaqas', ['as' => $as, 'regime' => $regime]));
    } 

    $halaqa = new Halaqa;
    $form = $this->createForm(HalaqaType::class, $halaqa);

    if($form->handleRequest($request)->isValid())
    {
      $halaqa->setAnneeScolaire($annee);
      $halaqa->setRegime($regime);
      $halaqa->setDateSave(new \Datetime());
      $halaqa->setDateUpdate(new \Datetime());
      $em->persist($halaqa);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La halaqa '.$halaqa->getLibelleHalaqa().' a été bien sauvegardée.');

      return $this->redirect($this->generateUrl('isi_gestion_halaqas', array(
        'as'     => $as,
        'regime' => $regime
        )));
    }

    return $this->render('ISIBundle:Parametres:addHalaqa.html.twig', array(
      'form'  => $form->createView(),
      'asec'  => $as,
      'annee' => $annee,
    ));
  }

  //Page d'accueil pour la liaison entre les classes et les matières pour une année scolaire donnée
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function niveauxMatieresAction($as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');

    $listNiveaux = $repoNiveau->findAll();
    $annee  = $repoAnnee->find($as);
    // $listMatieresNiveaux = $repoMatiere->listMatieresNiveaux($as, );

    return $this->render('ISIBundle:Parametres:niveaux-matieres-home.html.twig', array(
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'niveaux' => $listNiveaux
    ));
  }

  // Pour voir la liste des matières d'un niveau de formation donné
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function listeMatieresNiveauxAction(Request $request, $as, $regime, $niveauId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEns     = $em->getRepository('ISIBundle:Enseignement');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');
    $repoNiveau  = $em->getRepository('ISIBundle:Niveau');

    $matieres = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
    $niveau   = $repoNiveau->find($niveauId);
    $annee    = $repoAnnee->find($as);
    $ens      = $repoEns->findBy(['anneeScolaire' => $as, 'niveau' => $niveauId]);

    return $this->render('ISIBundle:Parametres:liste-des-matieres-du-niveau.html.twig', [
      'matieres' => $matieres,
      'asec'     => $as,
      'regime'   => $regime,
      'annee'    => $annee,
      'niveau'   => $niveau,
      'ens'      => $ens,
    ]);
  }

  // Pour tous ce qui concernent les examens
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function examenAction($as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoExamen = $em->getRepository('ISIBundle:Examen');

    $examens = $repoExamen->examensAnneeEnCours($as);
    $annee   = $repoAnnee->find($as);

    return $this->render('ISIBundle:Parametres:accueil-examen.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'examens' => $examens
    ]);
  }

  // Ajout examen
  /**
   * @Security("has_role('ROLE_SCOLARITE')")
   */
  public function addExamenAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');

    $examen = new Examen();
    $annee = $repoAnnee->find($as);
    $examen->setAnneeScolaire($annee);
    $examen->setDateSave(new \Datetime());
    $examen->setDateUpdate(new \Datetime());

    $form = $this->createForm(ExamenType::class, $examen);
    if($form->handleRequest($request)->isValid())
    {
      $em = $this->getDoctrine()->getManager();
      $em->persist($examen);
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'Examen enregistré.');

      return $this->redirect($this->generateUrl('isi_examen', ['as' => $as]));
    }

    return $this->render('ISIBundle:Parametres:add-examen.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'form'  => $form->createView()
    ]);
  }
}

?>
