<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Dompdf\Dompdf;
use Dompdf\Options;

use ISI\ISIBundle\Entity\Eleve;
use ISI\ISIBundle\Entity\Classe;
use ISI\ISIBundle\Entity\Niveau;
use ISI\ISIBundle\Entity\Probleme;
use ISI\ISIBundle\Entity\Commettre;
use ISI\ISIBundle\Entity\Memoriser;
use ISI\ISIBundle\Entity\Frequenter;
use ISI\ISIBundle\Entity\Eleverenvoye;
use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ISIBundle\Entity\Elevereintegre;
use ISI\ISIBundle\Entity\Eleveautreregime;

use ISI\ISIBundle\Repository\EleveRepository;
use ISI\ISIBundle\Repository\ClasseRepository;
use ISI\ISIBundle\Repository\HalaqaRepository;
use ISI\ISIBundle\Repository\ProblemeRepository;
//use ISI\ISIBundle\Repository\EleveautreregimeRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use ISI\ISIBundle\Form\EleveType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Doctrine\ORM\EntityRepository;

class EleveController extends Controller
{
  public function indexAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repo = get_class($repoEleve);

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);
    $eleves = $repoEleve->elevesDUnRegime($regime);
    // $eleves = $this->tableauElevesJson($regime);

    $infoEleve = [];
    foreach ($eleves as $eleve) {
      # code...
      $elev = [];
      $elev['eleveId'] = $eleve->getEleveId();
      $elev['matricule'] = $eleve->getMatricule();
      $elev['nomFr'] = $eleve->getNomFr();
      $elev['pnomFr'] = $eleve->getPnomFr();
      $elev['nomAr'] = $eleve->getNomAr();
      $elev['pnomAr'] = $eleve->getPnomAr();
      $elev['sexe'] = $eleve->getSexeFr();
      $elev['dateNaissance'] = $eleve->getDateNaissance();
      $elev['renvoye'] = $eleve->getRenvoye();

      // $infoEleve[] = [
      //   $eleve->getEleveId(),
      //   $eleve->getMatricule(),
      //   $eleve->getNomFr().' '.$eleve->getPnomFr(),
      //   $eleve->getNomAr().' '.$eleve->getPnomAr(),
      //   $eleve->getSexeFr(),
      //   $eleve->getDateNaissance(),
      //   $eleve->getRenvoye()
      // ];

      $infoEleve[] = $elev;
    }
    // return new Response(var_dump($eleves));
    if($regime == 'A')
    {
      $elevesInternes = $repoEleve->elevesInternes($as);
    }
    else {
      $elevesInternes = [];
    }

    return $this->render('ISIBundle:Eleve:index.html.twig', [
    	'asec'    => $as,
      // 'eleves'  => $eleves,
      'eleves'  => $infoEleve,
      'annee'   => $annee,
      'regime'  => $regime,
      'elevesI' => $elevesInternes
    ]);
    // return new Response($repo);
  }

  // public function tableauElevesJson($regime)
  public function tableauElevesJsonAction($regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $eleves = $repoEleve->elevesDUnRegime($regime);
    $infoEleve = [];
    foreach ($eleves as $eleve) {
      # code...
      $elev = [];
      $elev['eleveId'] = $eleve->getEleveId();
      $elev['matricule'] = $eleve->getMatricule();
      $elev['nomFr'] = $eleve->getNomFr();
      $elev['pnomFr'] = $eleve->getPnomFr();
      $elev['nomAr'] = $eleve->getNomAr();
      $elev['pnomAr'] = $eleve->getPnomAr();
      $elev['sexe'] = $eleve->getSexeFr();
      $elev['dateNaissance'] = $eleve->getDateNaissance();
      $elev['renvoye'] = $eleve->getRenvoye();

      $infoEleve[] = $elev;
    }
    $data = $infoEleve;
    return new JsonResponse($data);
  }

  public function selectionDesElevesDuRegimeAction(Request $request, $regime)
  {
    $em = $this->getDoctrine()->getManager();

    // // Ajax dans le controller
    // // if($request->isXMLHttpRequest())
    // // {
    //   $cnx = $this->get('database_connection');
    //   $query = "SELECT * FROM eleve e WHERE e.matricule LIKE '*A___'";
    //   $rows = $cnx->fetchAll($query);
    //   return new JsonResponse(array('data' => json_encode($rows)));
    // // }
    //
    // return new Response("Erreur: Ce n'est pas une requête Ajax", 400);
    // Ajax dans le controller


    $elevesList = []; // initialisation du tableau qui contiendra le nom des classes

    $eleves = $em->getRepository('ISIBundle:Eleve')->elevesDUnRegime($regime);

      foreach ($eleves as $eleve) {
        $elevesList [] = [
          'id'            => $eleve->getEleveId(),
          'matricule'     => $eleve->getMatricule(),
          'nomFr'         => $eleve->getNomFr().' '.$eleve->getPnomFr(),
          'nomAr'         => $eleve->getPnomAr().' '.$eleve->getNomAr(),
          'sexe'          => $eleve->getSexeFr(),
          'dateNaissance' => $eleve->getDateNaissance(),
          // 'action'        => NULL,
        ];
      }

      $response = new JsonResponse($elevesList);
      return $response;
  }

  //

  // Function de récupération du dernier matricule (externalisée)
  public function getNewMatricule($as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve = $em->getRepository('ISIBundle:Eleve');
    $repoAnnee = $em->getRepository('ISIBundle:Anneescolaire');

    // Récupération du l'année pour le matricule le fameux /17
    $annee = $repoAnnee->anneeMatricule($as);
    $annee = $annee->getSingleScalarResult();
    $matricule = $repoEleve->dernierMatricule();

  	$matriculeNumeric = [];
  	foreach($matricule as $mat)
  	{
  		$mat1 = str_replace ( 'F', '', $mat);
  		$matriculeNumeric[] = intval($mat1['mat']);
  	}

  	$matriculeMax = max($matriculeNumeric);
    $matricule = $matriculeMax + 1;
	   //$matricule = preg_replace('/[^0-9]/', '',$matricule[0]['plus_grand_matricule']);

    $matriculeNew = 'ISI-0'.$matricule.''.$regime.'-'.$annee;

    return $matriculeNew;
  }

  /*****
   * La function qui permet de vérifier que le regime de formation de
   * l'élève en est identique au celui de la classe
   * Elle retourne un booléen qui autorise l'inscription s'il y a identité entre
   * la référence du groupe de formation de la classe et le regime de l'élève
   */
  public function groupeEleve($regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoClasse = $em->getRepository('ISIBundle:Classe');

    // myFindGrpFormationClasse($classeId) retourne la référence du groupe de formation c'est-à-dire elle retourne soit A soit F
    $reference = $repoClasse->myFindGrpFormationClasse($classeId);

    // On compare maintenant $lettre et $reference (la référence du groupe de formation)
    // $lettre == $reference ? $accepteInscription = TRUE : $accepteInscription = FALSE;
    if($regime == $reference[0]['referenceGrp'])
    {
      $accepteInscription = TRUE;
    }else{
      $accepteInscription = FALSE;
    }

    return $accepteInscription;
  }

  /*****
   * La function qui permet de vérifier que l'élève que l'on veut inscrire dans la classe
   * est autorisé à être inscrit en fonction de son sexe et du genre de la classe.
   * Juste pour éviter d'avoir des garçons dans des classes de filles ou vis-versa
   */
  public function autorisationSexe($sexeEleve, $genreClasse)
  {
    if($sexeEleve == 1)
    {
      $sexe = 'H';
    }
    else{
      $sexe = 'F';
    }

    // Si la classe est mixte, on accepte l'inscription de tout élève, qu'il soit homme ou femme
    if($genreClasse == 'M')
    {
      $accepteInscription = TRUE;
    }
    // Si la classe n'est pas mixte et qu'elle est Homme
    else{
      if($genreClasse == 'H')
      {
        // Si on a une classe d'homme et l'élève est de sexe masculin
        if($genreClasse == $sexe)
        {
          // On autorise l'inscription
          $accepteInscription = TRUE;
        }
        else{
          // Sinon, on la refuse: une femme ne doit pas se retrouver dans une classe d'homme
          $accepteInscription = FALSE;
        }
      }
      // Si c'est une classe de femme et que l'élève en cours d'inscription est aussi une femme
      else{
        if($genreClasse == $sexe)
        {
          // On autorise l'inscription
          $accepteInscription = TRUE;
        }
        else{
          // Sinon on la rejette du revers de la main
          $accepteInscription = FALSE;
        }
      }
    }

    return $accepteInscription;
  }
  // Ici finissent mes méthodes personnelles dans le controller

  //Function de présincription
  public function preinscriptionAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve  = $em->getRepository('ISIBundle:Eleve');
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    $eleve = new Eleve();
    $eleve->setDateSave(new \Datetime());
    $eleve->setDateUpdate(new \Datetime());


    $form  = $this->createForm(EleveType::class, $eleve);

    $matriculeNew = $this->getNewMatricule($as, $regime);
    // return new Response(var_dump($matriculeNew));

    if($form->handleRequest($request)->isValid())
    {
      $data = $request->request->all();
      $date = $data["date"];
      $date = new \Datetime($date);
      // return new Response(var_dump($date));

      /*
       * Comme on peut avoir plusieurs utilisateurs qui enregistrent les nouveaux élèves, il serait
       * mieux de regénérer un neauvau matricule juste avant l'enregistrement effectif en BD.
       * Comme ça, on est sûr qu'il n'aura pas de conflit de matricule.
       */
      $matriculeNew = $this->getNewMatricule($as, $regime);
      $eleve->setMatricule($matriculeNew);
      $eleve->setDateNaissance($date);
      $eleve->setRegime($regime);

      $em = $this->getDoctrine()->getManager();
      $em->persist($eleve);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'L\'élève '.$eleve->getNomFr().' '.$eleve->getPnomFr().' a été bien enregistré avec le matricule '.$eleve->getMatricule().'.');

      return $this->redirect($this->generateUrl('isi_preinscription', array(
        'as'     => $as,
        'regime' => $regime
      )));
    }

    return $this->render('ISIBundle:Eleve:preinscription.html.twig', array(
      'asec'      => $as,
      'regime'    => $regime,
      'annee'     => $annee,
      'form'      => $form->createView(),
      'matricule' => $matriculeNew
    ));
    // return $this->render('ISIBundle:Default:index.html.twig', ['mat' => $annee, 'asec' => $as]);
    // return $this->render('ISIBundle:Default:index.html.twig', ['mat' => $mat[0]['plus_grand_matricule'], 'asec' => $as]);
  }

  //Edition d'un élève
  public function editEleveAction(Request $request, $as, $regime, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    // Sélection de l'élève dont l'id est passé en paramètre de la fonction
    $repoEleve = $em->getRepository('ISIBundle:Eleve');

    $eleve = $repoEleve->find($eleveId);

    $form = $this->createForm(EleveType::class, $eleve);

    if($form->handleRequest($request)->isValid())
    {
      $data = $request->request->all();
      $date = $data["date"];
      $date = new \DateTime($date);
      // $date = $date->format('Y-m-d');

      // return new Response(var_dump($date));
      $eleve->setDateNaissance($date);
      $eleve->setDateUpdate(new \Datetime());
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'Les informations de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' ont bien été mise à jour.');
      return $this->redirect($this->generateUrl('isi_espace_eleve', [
        'as'     => $as,
        'regime' => $regime
      ]));
    }

    return $this->render('ISIBundle:Eleve:edition-eleve.html.twig', [
      'asec'   => $as,
      'regime' => $regime,
      'annee'  => $annee,
      'eleve'  => $eleve,
      'form'   => $form->createView()
    ]);
  }

  //Affichage de la fiche de renseignement d'un élève
  public function infoEleveAction(Request $request, $as, $regime, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee       = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve       = $em->getRepository('ISIBundle:Eleve');
    $repoProbleme    = $em->getRepository('ISIBundle:Probleme');
    $repoFrequenter  = $em->getRepository('ISIBundle:Frequenter');
    $repoEleveRenvoye     = $em->getRepository('ISIBundle:Eleverenvoye');
    $repoEleveReintegre   = $em->getRepository('ISIBundle:Elevereintegre');
    $repoEleveAutreregime = $em->getRepository('ISIBundle:Eleveautreregime');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    // Sélection de l'élève dont l'id est passé en paramètre de la fonction
    $eleve = $repoEleve->find($eleveId);

    // La classe de l'élève durant l'annee en cours
    $fq = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);

    // On va ici sélectionner les problèmes qu'auraient eu l'élève
    $problemes = $repoProbleme->problemesDUnEleveLorsDUneAnnee($eleve->getEleveId(), $as);

    $renvoye     = $repoEleveRenvoye->findBy(['eleve' => $eleve->getEleveId()]);
    $reintegre   = $repoEleveReintegre->findBy(['eleve' => $eleve->getEleveId()]);
    $autreRegime = $repoEleveAutreregime->findBy(['eleve' => $eleve->getEleveId()]);

    // return new Response(var_dump($problemes));

    return $this->render('ISIBundle:Eleve:info-eleve.html.twig', [
      'asec'       => $as,
      'regime'     => $regime,
      'annee'      => $annee,
      'eleve'      => $eleve,
      'problemes'  => $problemes,
      'frequenter' => $fq,
      'renvoye'    => $renvoye,
      'reintegre'  => $reintegre,
      'autreregime' => $autreRegime,
    ]);
  }

  //Pour voir la liste des élèves inscrits pour une année donnée
  public function elevesInscritsAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);
    $repo = get_class($repoEleve);
    $eleves = $repoEleve->elevesInscrits($as, $regime);
    if($regime == 'A')
    {
      $elevesInternes = $repoEleve->elevesInternes($as);
    }
    else {
      $elevesInternes = [];
    }

    $frequentation = [];
    foreach ($eleves as $eleve) {
      $frequentation[] = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);
    }
    unset($eleves);
    $eleves = $frequentation;
    return $this->render('ISIBundle:Eleve:eleves-inscrits.html.twig', array(
    	'asec'     => $as,
      'eleves'   => $eleves,
      'annee'    => $annee,
      'regime'   => $regime,
      'elevesI'  => $elevesInternes,
      // 'frequentation' => $frequentation
    ));
  }

  // Cette function vérifie l'admission en classe supérieure
  public function admissionEnClasseSuperieure($eleveId, $regime, $niveauId, $as)
  {
    // $admission = FALSE;
    /**
     * - On sélectionne l'occurrence de frequenter pour l'élève $eleveId et pour la dernière année de frequentation de l'élève
     * - Si elle est vide alors, s'il s'agit d'un élève que nous sommes en train d'inscrire tout nouvellement
     * - Sinon, on sélectionne le niveau lié à l'attribut classe et on teste la valeur de l'attribut admission
     *    - Si admission == FALSE, alors $niveauId ne doit pas != du niveau de l'attribut classe de l'entité frequenter
     *    - Sinon (c'est-à-dire que admission == TRUE) $niveau ne doit pas != du niveau de frequenter + 1
     * **/
    $em = $this->getDoctrine()->getManager();
    $repoEleve      = $em->getRepository("ISIBundle:Eleve");
    $repoNiveau     = $em->getRepository("ISIBundle:Niveau");
    $repoFrequenter = $em->getRepository("ISIBundle:Frequenter");
    // $repo->getRepository("ISIBundle:");

    $anPrec = $as - 1;
    $eleve  = $repoEleve->find($eleveId);
    $niveau = $repoNiveau->find($niveauId);
    // $frequenter = $repoFrequenter->findOneBy(["eleve" => $eleveId, "anneeScolaire" => $anPrec]);
    $frequenter = $repoFrequenter->derniereFrequentation($eleveId);

    // On vérifie le contenu de $frequenter
    if(empty($frequenter))
    {
      // Dans ce cas, il s'agit d'un élève qui n'a jamais été inscrit auparavant
      $admission = 1;
    }
    else
    {
      // L'élève était inscrit l'année dernière
      $niveauPrecedantId = $frequenter[0]->getClasse()->getNiveau()->getId();
      $niveauPrecedant   = $repoNiveau->find($niveauPrecedantId);
      $admis = $frequenter[0]->getAdmission();

      // On va sélectionner l'attribut succession du $niveau et de $niveauPrecedant
      $succession1 = $niveau->getSuccession();
      $succession2 = $niveauPrecedant->getSuccession();

      // On teste la valeur de l'attribut admission de l'entité frequenter
      if($admis == FALSE)
      {
        // Dans ce cas il doit avoir égalité entre les deux succession
        $succession1 == $succession2 ? $admission = 2 : $admission = 3 ;
      }
      else
      {
        // Dans ce cas il doit avoir égalité entre les deux succession
        ($succession1 == $succession2 + 1) ? $admission = 4 : $admission = 5 ;
      }
    }

    // En résumé, on a ceci:
    // 1: l eleve est nouveau, on l inscrit directement
    // 2: l eleve redouble, on l inscrit
    // 3: l eleve redouble, alors il faut choisir le même niveau
    // 4: l eleve est admis, on l inscrit
    // 5: l eleve est admis, alors il faut l incrire dans le bon niveau

    return $admission;
  }

  //Pour l'inscription d'un élève dans une classe
  public function inscriptionAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve  = $em->getRepository('ISIBundle:Eleve');
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');
    $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    $annee  = $repoAnnee->find($as);
    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'inscrire un élève car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
      return $this->redirect($this->generateUrl('isi_espace_eleve', ['as' => $as, 'regime' => $regime]));
    }
    $grp = $regime == 'A' ? 1 : 2 ;
    $niveaux   = $repoNiveau->findBy(['groupeFormation' => $grp]);
    $halaqas   = $repoHalaqa->findBy(['anneeScolaire' => $as, 'regime' => $regime]);
    $tousLesMatricules = $repoEleve->getMatricules($regime);
    $matricules = [];
    foreach ($tousLesMatricules as $matricule) {
      $matricules[] = $matricule['matricule'];
    }

    $defaultData = array('message' => 'Type your message here');
    $form = $this->createFormBuilder($defaultData)
      ->add('matricule',     TextType::class, [], ['attr' => ['class' => 'matricule', 'maxlength' => 15]])
      ->add('nomFr',         TextType::class, [], ['attr' => ['class' => 'nomFr']])
      ->add('pnomFr',        TextType::class, [], ['attr' => ['class' => 'pnomFr']])
      ->add('dateNaissance', TextType::class, [], ['attr' => ['class' => 'dateNaissance']])
      ->add('save', SubmitType::class, ['label' => 'Inscrire l\'élève'])
      ->getForm()
    ;

    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $em = $this->getDoctrine()->getManager();
      $repoEleveAR    = $em->getRepository('ISIBundle:Eleveautreregime');
      $repoEleve      = $em->getRepository('ISIBundle:Eleve');
      $repoClasse     = $em->getRepository('ISIBundle:Classe');
      $repoNiveau     = $em->getRepository('ISIBundle:Niveau');
      $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
      $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
      // $classeId = (int)$request->get('form')['classe'];
      // $niveauId = (int)$request->get('form')['niveau'];
      $data = $request->request->all();


      if($regime != 'F')
        $halaqaId = $data['halaqa'];

        // $data = $form->getData();
        // return new Response(var_dump($data));

      // Sélection des indentifiants de classe, d'élève, d'année scolaire et de matière
      $eleve     = $repoEleve->findOneBy(['matricule' => $data['form']['matricule']]);
      $annee     = $repoAnnee->find($as);
      if ($regime != 'C') {
        # code...
        $classeId  = $data['classe'];
        $niveauId  = $data['niveau'];
        $classe    = $repoClasse->find($classeId);
        $niveau    = $repoNiveau->find($niveauId);
        $frequente = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);
      }

      // Il faut aussi vérifier que l'on se trouve dans le bon regime pour l'inscription
      // de l'élève dont on a entré le matricule
      if ($eleve->getRegime() != $regime) {
        $request->getSession()->getFlashBag()->add('error', 'Placez-vous dans le bon regime de formation pour procéder à l\'inscription.');
        return $this->redirect($this->generateUrl('isi_inscription', ['as' => $as, 'regime' => $regime]));
      }

      if ($eleve->getRenvoye() == TRUE) {
        # code...
        $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être inscrit. Il(elle) a été renvoyé(e)');
        return $this->redirect($this->generateUrl('isi_inscription', ['as' => $as, 'regime' => $regime]));
      }

      // Ici on vérifie que l'élève n'est pas encore inscrit dans une classe quelconque
      if(!empty($frequente))
      {
        $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' est déjà inscrit(e) en '.$frequente->getClasse()->getLibelleClasseFr());
        return $this->redirect($this->generateUrl('isi_inscription', ['as' => $as, 'regime' => $regime]));
      }


      /**
       * On va créer une function qui nous permet de savoir si l'élève passe en classe supérieur ou pas.
       * Mais ici, il faut souligner que cela ne concerne que les anciens élèves. Ceux qui étaient là
       * l'année dernière
       * ****/
      $eleveId  = $eleve->getEleveId();
      // Pour les élèves qui ont connus un changement de regime, il faut qu'on permette leur inscription
      // peut importe le résultat de l'année dernière
      $autreRegime = $repoEleveAR->findOneBy(['eleve' => $eleveId]);
      // return new Response(var_dump($autreRegime));
      if (!empty($autreRegime)) {
        # code...
        $redouble = 1;
        // return new Response("C'est bon!!!");
      } else {
        # code...
        if ($regime != 'C') {
          # code...
          $redouble = $this->admissionEnClasseSuperieure($eleveId, $regime, $niveauId, $as);
        }
      }
      if ($regime != 'C') {
        # code...
        // 1: l eleve est nouveau, on l inscrit directement
        // 2: l eleve redouble, on l inscrit
        // 3: l eleve redouble, alors il faut choisir le même niveau
        // 4: l eleve est admis, on l inscrit
        // 5: l eleve est admis, alors il faut l incrire dans le bon niveau
        if ($redouble == 3) {
          # code...
          $anPrec = $as - 1;
          $frequenter = $repoFrequenter->findOneBy(["eleve" => $eleveId, "anneeScolaire" => $anPrec]);
          $niveauPrecedantId = $frequenter->getClasse()->getNiveau()->getId();
          $niveau  = $repoNiveau->find($niveauPrecedantId);
          $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' redouble sa classe. Il(elle) doit être inscrit(e) en '.$niveau->getLibelleFr());
          return $this->redirect($this->generateUrl('isi_inscription', ['as' => $as, 'regime' => $regime]));
        }
        elseif ($redouble == 5) {
          # code...
          $anPrec = $as - 1;
          $frequenter = $repoFrequenter->findOneBy(["eleve" => $eleveId, "anneeScolaire" => $anPrec]);
          $niveauPrecedantId = $frequenter->getClasse()->getNiveau()->getId();
          $niveau  = $repoNiveau->find($niveauPrecedantId + 1);
          $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' est admis(e). Il(elle) doit être inscrit(e) en '.$niveau->getLibelleFr());
          return $this->redirect($this->generateUrl('isi_inscription', ['as' => $as, 'regime' => $regime]));
        }
        elseif($redouble == 4)
        {
          $redoublant = FALSE;
        }
        elseif($redouble == 2)
        {
          $redoublant = TRUE;
        }
        else
        {
          $redoublant = FALSE;
        }

        /* Cette fonction permet de vérifier que l'élève sélectionné est bien un élève du groupe de formation
          * dans lequel on veut l'inscrire
          */
        $autoriserInscriptionGrp = $this->groupeEleve($eleve->getRegime(), $classe->getClasseId());
      }
      else{
        $autoriserInscriptionGrp = TRUE;
      }


      if($autoriserInscriptionGrp == FALSE)
      {
        // Impossible de poursuivre l'inscription
        $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être inscrit(e) en '.$classe->getLibelleClasseFr().'. Il(elle) n\'est pas de ce regime de formation.');
        return $this->redirect($this->generateUrl('isi_inscription', [
          'as' => $as, 'regime' => $regime
        ]));
        // return new Response(substr($eleve->getMatricule(), -4, 1));
        // return new Response("Impossible d'inscrire cet élève.");
      }
      else{
        // L'inscription peut se poursuivre.

        // Si nous sommes dans l'inscription de l'espace coran, il n y a pas de classe. Pour pouvoir donc
        // continuer l'inscription, on va mettre de $autoriserInscriptionSexe à TRUE
        if ($regime == 'C') {
          $autoriserInscriptionSexe = TRUE;
        }
        else{
          /***
           * On va maintenant voir si l'élève est en train d'être inscrit dans une classe d'homme ou de femme
           ****/
          $autoriserInscriptionSexe = $this->autorisationSexe($eleve->getSexeFr(), $classe->getGenre());
        }

        if($autoriserInscriptionSexe == FALSE)
        {
          // Impossible de poursuivre l'inscription
          $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être inscrit(e) en '.$classe->getLibelleClasseFr().'. Vous ne pouvez pas inscrire les hommes chez les femmes.');
          return $this->redirect($this->generateUrl('isi_inscription', [
            'as' => $as, 'regime' => $regime
          ]));
          // return new Response("sexe different.");
        }
        else{
          $em = $this->getDoctrine()->getManager();
          $repoHalaqa   = $em->getRepository('ISIBundle:Halaqa');
          // Apres cela, si l'élève est de l'academie, il faudra le mettre dans une halaqa
            if($regime == 'A' or $regime == 'C'){
              // Si le regime est == halaqa, on récupère la halaqa
              // $halaqaId = $data['halaqa'];
              $halaqa = $repoHalaqa->find($halaqaId);

              // On s'assure que la halaqa choisie est bien du genre de l'élève.
              $autoriserInscriptionHalaqa = $this->autorisationSexe($eleve->getSexeFr(), $halaqa->getGenre());
              if($autoriserInscriptionHalaqa == FALSE)
              {
                $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être inscrit(e) en '.$halaqa->getLibelleHalaqa().' - '.$halaqa->getGenre().'. Vous ne pouvez pas inscrire les hommes chez les femmes.');
                return $this->redirect($this->generateUrl('isi_inscription', [
                  'as' => $as, 'regime' => $regime
                ]));
              }

              $memoriser = new Memoriser();
              $memoriser->setEleve($eleve);
              $memoriser->setAnneeScolaire($annee);
              $memoriser->setHalaqa($halaqa);
              $memoriser->setDateSave(new \Datetime());
              $memoriser->setDateUpdate(new \Datetime());
              $em->persist($memoriser);
            }

          if ($regime != 'C') {
            # code...
            $frequenter = new Frequenter();
            $frequenter->setEleve($eleve);
            $frequenter->setClasse($classe);
            $frequenter->setRedouble($redoublant);
            $frequenter->setAnneeScolaire($annee);
            $frequenter->setDateSave(new \Datetime());
            $frequenter->setDateUpdate(new \Datetime());
            // On persist et flush l'entité
            $em->persist($frequenter);

          }
          if ($regime == 'A') {
            $request->getSession()->getFlashBag()->add('info', $eleve->getNomFr().' '.$eleve->getPnomFr().' a bien été inscrit(e) en '.$classe->getLibelleClasseFr().' et dans la halaqa '.$halaqa->getLibelleHalaqa());
          }
          elseif ($regime == 'F') {
            $request->getSession()->getFlashBag()->add('info', $eleve->getNomFr().' '.$eleve->getPnomFr().' a bien été inscrit(e) en '.$classe->getLibelleClasseFr());
          }
          else {
            $request->getSession()->getFlashBag()->add('info', $eleve->getNomFr().' '.$eleve->getPnomFr().' a bien été inscrit(e) dans la halaqa '.$halaqa->getLibelleHalaqa());
          }
          $em->flush();

          // return $this->render('ISIBundle:Default:index.html.twig', ['data' => $data, 'asec' => $as, 'regime' => 'A']);
          return $this->redirect($this->generateUrl('isi_inscription', ['as' => $as, 'regime' => $regime]));
        } // Fin  -  Autorisation liée au sexe de l'élève et au genre de la classe

      } // Fin  -  Autorisation liée à au groupe de formation de l'élève et au regime de la classe

    } // Fin du bouton submit
    return $this->render('ISIBundle:Eleve:inscription.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'niveaux'  => $niveaux,
      'annee'   => $annee,
      'halaqas' => $halaqas,
      'form'    => $form->createView(),
      'matricules' => $matricules
    ]);
  }

  // Pour signaler un probleme relatif à un élève
  public function problemesHomeAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve       = $em->getRepository('ISIBundle:Eleve');
    $repoAnnee       = $em->getRepository('ISIBundle:Anneescolaire');
    $repoFrequenter  = $em->getRepository('ISIBundle:Frequenter');

    $annee = $repoAnnee->find($as);

    if($request->isMethod('post'))
    {
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      $matricule = $data['matricule'];
      $eleve = $repoEleve->findOneBy(['matricule' => $matricule]);
      $fq    = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);
      if(empty($eleve)) {
        $request->getSession()->getFlashBag()->add('error', 'Le matricule saisi n\'est pas correct');
        return $this->redirect($this->generateUrl('isi_problemes_home', ['as' => $as, 'regime' => $regime]));
      }
      else {
        if (empty($fq)) {
          $request->getSession()->getFlashBag()->add('error', 'Cet élève n\'est pas inscrit. \n Impossible d\'enregister sa conduite.');
          return $this->redirect($this->generateUrl('isi_problemes_home', ['as' => $as, 'regime' => $regime]));
        }
          return $this->render('ISIBundle:Eleve:saisie-de-probleme-d-un-eleve.html.twig', [
            'asec'   => $as,
            'regime' => $regime,
            'annee'  => $annee,
            'eleve'  => $eleve,
            'fq'     => $fq
          ]);
        }
    }

    return $this->render('ISIBundle:Eleve:probleme-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
    ]);
  }

  public function enregistrerConduiteAction(Request $request, $as, $regime, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoNote       = $em->getRepository('ISIBundle:Note');
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    $annee = $repoAnnee->find($as);
    $eleve = $repoEleve->find($eleveId);
    $fq    = $repoFrequenter->findOneBy(['anneeScolaire' => $as, 'eleve' => $eleveId]);

    if(empty($fq))
    {
      $request->getSession()->getFlashBag()->add('error', 'Cet élève n\'est pas inscrit cette année. Vous ne pouvez donc pas enregistrer une conduite à son nom.');
      if ($regime == 'C') {
        # code...
        return $this->redirect($this->generateUrl('isi_espace_coran_home', ['as' => $as, 'regime' => $regime]));
      }
      return $this->redirect($this->generateUrl('isi_espace_eleve', ['as' => $as, 'regime' => $regime]));
    }

    // Sélection des objets
    // $note  = $repoNote->findBy(['eleve' => $eleveId]);
    // if(!empty($note))
    // {
    //   $request->getSession()->getFlashBag()->add('error', 'Il est impossible de ')
    // }
    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      if(isset($data['appreciation']))
        $appreciation = $data['appreciation'];
      else {
        $appreciation = 'Rien à signaler';
      }
      $description = $data['description'];
      $date = new \DateTime($data['date']);
      if(empty($description))
      {
        $request->getSession()->getFlashBag()->add('error', 'La description de la conduite ne doit pas être vide.');
        return $this->redirect($this->generateUrl('isi_problemes_home', ['as' => $as, 'regime' => $regime]));
      }

      // Un renvoi peut être considéré comme effet immédiat ou non d'un problème
      $probleme = new Probleme();
      $probleme->setAppreciation($appreciation);
      $probleme->setDescription($description);
      $probleme->setDate($date);
      $probleme->setDateSave(new \Datetime());
      $probleme->setDateUpdate(new \Datetime());

      // Il nous faut un occurence de commettre pour enregistrer un problème
      $commettre = new Commettre();
      $commettre->setEleve($eleve);
      $commettre->setProbleme($probleme);
      $commettre->setAnneescolaire($annee);

      $em->persist($probleme);
      $em->persist($commettre);
      // return new Response(var_dump($request));
      $em->flush();

      $js = "<script type='text/javascript'> alert('Voulez-vous vraiment renvoyer ".$eleve->getNomFr()." ".$eleve->getPnomFr()."') </script>";
      $request->getSession()->getFlashBag()->add('info', 'La conduite de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' a été bien enregistrée avec la mention "'.$probleme->getAppreciation().'".');
      $em->flush();

      return $this->redirect($this->generateUrl('isi_problemes_home', ['as' => $as, 'regime' => $regime]));
    }

    $dernierePage = 'eleveInscrits';
    return $this->render('ISIBundle:Eleve:saisie-de-probleme-d-un-eleve.html.twig', [
      'asec'   => $as,
      'regime' => $regime,
      'annee'  => $annee,
      'eleve'  => $eleve,
      'fq'     => $fq,
      // 'dernierePage' => $dernierePage,
    ]);
  }

  // Pour renvoyer un élève
  public function renvoiHomeAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve       = $em->getRepository('ISIBundle:Eleve');
    $repoAnnee       = $em->getRepository('ISIBundle:Anneescolaire');
    $repoFrequenter  = $em->getRepository('ISIBundle:Frequenter');

    $annee = $repoAnnee->find($as);
    $tousLesMatricules = $repoEleve->getMatricules($regime);
    $matricules = [];
    foreach ($tousLesMatricules as $matricule) {
      $matricules[] = $matricule['matricule'];
    }

    if($request->isMethod('post'))
    {
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      $matricule = $data['matricule'];
      $eleve = $repoEleve->findOneBy(['matricule' => $matricule]);
      $fq    = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);
      if(empty($eleve)) {
        $request->getSession()->getFlashBag()->add('error', 'Le matricule saisi n\'est pas correct');
        return $this->redirect($this->generateUrl('isi_renvoi_home', ['as' => $as, 'regime' => $regime]));
      }
      else {
        return $this->render('ISIBundle:Eleve:infos-eleve-a-renvoyer.html.twig', [
          'asec'   => $as,
          'regime' => $regime,
          'annee'  => $annee,
          'eleve'  => $eleve,
          'fq'     => $fq
        ]);
      }

    }

    return $this->render('ISIBundle:Eleve:renvoi-home.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'matricules' => $matricules,
    ]);
  }

  // Exécution du renvoi
  public function renvoyerAction(Request $request, $as, $regime, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoEleverenvoye = $em->getRepository('ISIBundle:Eleverenvoye');

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $motifRenvoi = $data['motifRenvoi'];
      if(empty($motifRenvoi))
      {
        $request->getSession()->getFlashBag()->add('error', 'Il vaut impérativement saisir le motif pour lequel vous voulez renvoyer cet(te) élève.');
        return $this->redirect($this->generateUrl('isi_renvoi_home', ['as' => $as, 'regime' => $regime]));
      }

      $annee = $repoAnnee->find($as);
      /**
       * Quand l'année scolaire est finie, on doit plus faire des
       * mofications, des mises à jour
       **/
      if($annee->getAchevee() == TRUE)
      {
        $request->getSession()->getFlashBag()->add('error', 'Impossible de renvoyer un(e) élève car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
        return $this->redirect($this->generateUrl('isi_renvoi_home', ['as' => $as, 'regime' => $regime]));
      }
      $eleve = $repoEleve->find($eleveId);
      if($eleve->getRenvoye() == TRUE)
      {
        $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' est déjà renvoyé(e).');
        return $this->redirect($this->generateUrl('isi_renvoi_home', ['as' => $as, 'regime' => $regime]));
      }
      $fq    = $repoFrequenter->findOneBy(['eleve' => $eleveId, 'anneeScolaire' => $as]);

      // Mise à jour des informations concernant le renvoi
      $eleve->setRenvoye(TRUE);
      $eleve->setDateRenvoi(new \Datetime());
      $eleve->setDateUpdate(new \Datetime());

      if (!is_null($fq)) {
        # code...
        $fq->setAdmission('renvoye');
        $fq->setDateUpdate(new \Datetime());
      }


      $eleveRenvoye = new Eleverenvoye();
      $eleveRenvoye->setEleve($eleve);
      $eleveRenvoye->setAnneeScolaire($annee);
      $eleveRenvoye->setMotif($motifRenvoi);
      // $eleveRenvoye->setDateRenvoi($dateRenvoi);
      $eleveRenvoye->setDateSave(new \Datetime());
      $eleveRenvoye->setDateUpdate(new \Datetime());

      // return new Response(var_dump($dateRenvoi));

      $em->persist($eleveRenvoye);
      $em->flush();

      $js = "<script type='text/javascript'> alert('Voulez-vous vraiment renvoyer ".$eleve->getNomFr()." ".$eleve->getPnomFr()."') </script>";
      $request->getSession()->getFlashBag()->add('info', 'Le renvoi de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' s\'est bien effectué');
      // $em->flush();

      return $this->redirect($this->generateUrl('isi_renvoi_home', ['as' => $as, 'regime' => $regime]));
    }
  }

  public function reintegrerAction(Request $request, $as, $regime, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleverenvoye = $em->getRepository('ISIBundle:Eleverenvoye');
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    $eleve = $repoEleve->find($eleveId);
    $annee = $repoAnnee->find($as);

    $er = $repoEleverenvoye->findBy(['eleve' => $eleve->getEleveId()]);
    if(count($er) <= 2 )
    {
      $fq = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $er[0]->getAnneeScolaire()->getAnneeScolaireId()]);
      if($fq != NULL)
        $fq->setAdmission(NULL);
      $anneeRenvoi = $er[0]->getAnneeScolaire()->getLibelleAnneeScolaire();
    }
    elseif(count($er) > 2)
    {
      $fq = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $er[1]->getAnneeScolaire()->getAnneeScolaireId()]);
      $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être réintégré(e) car il(elle) a été renvoyé(e) plus de deux fois.');
      return $this->redirect($this->generateUrl('isi_eleves_renvoyes', ['as' => $as, 'regime' => $regime]));
    }

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $motifReintegration = $data['motifReintegration'];
      if(empty($motifReintegration))
      {
        $request->getSession()->getFlashBag()->add('error', 'Quel est le motif de la réintégration de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' ?');
        return $this->redirect($this->generateUrl('isi_eleves_renvoyes', ['as' => $as, 'regime' => $regime]));
      }

      $annee = $repoAnnee->find($as);
      /**
       * Quand l'année scolaire est finie, on doit plus faire des
       * mofications, des mises à jour
       **/
      if($annee->getAchevee() == TRUE)
      {
        $request->getSession()->getFlashBag()->add('error', 'Impossible de réintégrer un(e) élève car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
        return $this->redirect($this->generateUrl('isi_renvoi_home', ['as' => $as, 'regime' => $regime]));
      }

      $fq = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $er[0]->getAnneeScolaire()->getAnneeScolaireId()]);
      if($fq != NULL)
      {
        $fq->setAdmission(NULL);
        $fq->setDateUpdate(new \Datetime());
      }
      $eleve = $repoEleve->find($eleveId);

      // Mise à jour des informations concernant le renvoi
      $eleve->setRenvoye(FALSE);
      $eleve->setDateRenvoi(NULL);
      $eleve->setDateUpdate(new \Datetime());


      $eleveReintegre = new Elevereintegre();
      $eleveReintegre->setEleve($eleve);
      $eleveReintegre->setAnneeScolaire($annee);
      $eleveReintegre->setMotif($motifReintegration);
      // $eleveRenvoye->setDateRenvoi($dateRenvoi);
      $eleveReintegre->setDateSave(new \Datetime());
      $eleveReintegre->setDateUpdate(new \Datetime());

      // return new Response(var_dump($dateRenvoi));

      $em->persist($eleveReintegre);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La réintegration de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' s\'est bien effectué');
      // $em->flush();

      return $this->redirect($this->generateUrl('isi_eleves_renvoyes', ['as' => $as, 'regime' => $regime]));
    }

    return $this->render('ISIBundle:Eleve:infos-eleve-a-reintegrer.html.twig', [
      'asec'        => $as,
      'regime'      => $regime,
      'annee'       => $annee,
      'eleve'       => $eleve,
      'anneeRenvoi' => $anneeRenvoi,
    ]);
  }

  //Pour voir la liste des élèves renvoyés
  public function elevesRenvoyesAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleverenvoye = $em->getRepository('ISIBundle:Eleverenvoye');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);
    $repo = get_class($repoEleve);
    $eleves = $repoEleve->elevesRenvoyes($regime);

    $renvoyes  = [];
    foreach ($eleves as $eleve) {
      $renvoye = [];
      $renvoye['id']            = $eleve->getEleveId();
      $renvoye['matricule']     = $eleve->getMatricule();
      $renvoye['nomFr']         = $eleve->getNomFr();
      $renvoye['pnomFr']        = $eleve->getPnomFr();
      $renvoye['nomAr']         = $eleve->getNomAr();
      $renvoye['pnomAr']        = $eleve->getPnomAr();
      $renvoye['sexe']          = $eleve->getSexeFr();
      $renvoye['dateNaissance'] = $eleve->getDateNaissance();
      // $renvoye['motif'] = $eleve->getDateRenvoi();
      $renvoye['dateRenvoi']    = $eleve->getDateRenvoi();
      // Soit la variable $er, un élève renvoyé
      $er = $repoEleverenvoye->dernierRenvoi($eleve->getEleveId());
      if(!is_null($er))
      {
        $fq = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $er->getAnneeScolaire()->getAnneeScolaireId()]);
        $renvoye['anneeRenvoi'] = $er->getAnneeScolaire()->getLibelleAnneeScolaire();
        $renvoye['motifRenvoi'] = $er->getMotif();
        $renvoye['nbrRenvoi'] = 1;
      }

      if ($regime == 'C') {
        # code...
        $renvoye['classe'] = 'Coran';
      } else {
        # code...
        $renvoye['classe'] = ($fq == NULL ? 'Pas inscrit(e)' : $fq->getClasse()->getLibelleClasseAr());
      }

      $renvoyes[] = $renvoye;
      // $frequentation[] = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);
    }
    unset($eleves);
    $eleves = $renvoyes;
    return $this->render('ISIBundle:Eleve:eleves-renvoyes.html.twig', array(
    	'asec'          => $as,
      'eleves'        => $eleves,
      'annee'         => $annee,
      'regime'        => $regime,
      'renvoyes'      => $renvoyes,
      // 'frequentation' => $frequentation
    ));
  }

  //Pour voir la liste des élèves renvoyés
  public function infoEleveRenvoyeAction(Request $request, $as, $regime, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee       = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve       = $em->getRepository('ISIBundle:Eleve');
    $repoProbleme    = $em->getRepository('ISIBundle:Probleme');
    $repoFrequenter  = $em->getRepository('ISIBundle:Frequenter');
    $repoEleveRenvoye     = $em->getRepository('ISIBundle:Eleverenvoye');
    $repoEleveReintegre   = $em->getRepository('ISIBundle:Elevereintegre');
    $repoEleveAutreregime = $em->getRepository('ISIBundle:Eleveautreregime');

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    // Sélection de l'élève dont l'id est passé en paramètre de la fonction
    $eleve = $repoEleve->find($eleveId);

    // La classe de l'élève durant l'annee en cours
    $fq = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);

    // On va ici sélectionner les problèmes qu'auraient eu l'élève
    $problemes   = $repoProbleme->problemesDUnEleveLorsDUneAnnee($eleve->getEleveId(), $as);

    $renvoye     = $repoEleveRenvoye->dernierRenvoi($eleveId);
    $reintegre   = $repoEleveReintegre->findBy(['eleve' => $eleve->getEleveId()]);
    $autreRegime = $repoEleveAutreregime->findBy(['eleve' => $eleve->getEleveId()]);

    // return new Response(var_dump($problemes));

    return $this->render('ISIBundle:Eleve:info-eleve-renvoye.html.twig', [
      'asec'       => $as,
      'regime'     => $regime,
      'annee'      => $annee,
      'eleve'      => $eleve,
      'problemes'  => $problemes,
      'frequenter' => $fq,
      'renvoye'    => $renvoye,
      'reintegre'  => $reintegre,
      'autreregime' => $autreRegime,
    ]);
  }

  // Ce code doit être supprimé, Il sert plus à rien
  // Pour la réintégration d'un élève qu'on a renvoyé
  public function reintegrerEleveAction(Request $request, $as, $regime, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleverenvoye = $em->getRepository('ISIBundle:Eleverenvoye');
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve  = $em->getRepository('ISIBundle:Eleve');
    $repoClasse = $em->getRepository('ISIBundle:Classe');

    $annee  = $repoAnnee->find($as);
    $eleve  = $repoEleve->find($eleveId);
    $er = $repoEleverenvoye->findBy(['eleve' => $eleve->getEleveId()]);
    if(count($er) == 1)
    {
      $fq = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $er[0]->getAnneeScolaire()->getAnneeScolaireId()]);
      $anneeRenvoi = $er[0]->getAnneeScolaire()->getLibelleAnneeScolaire();
    }
    elseif(count($er) == 2)
    {
      $fq = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $er[1]->getAnneeScolaire()->getAnneeScolaireId()]);
      $request->getSession()->getFlashBag()->add('error', $eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être réintégré(e) car il(elle) a été renvoyé(e) deux fois.');
      return $this->redirect($this->generateUrl('isi_eleves_renvoyes', ['as' => $as, 'regime' => $regime]));
    }

    // return new Response(var_dump($eleve));
    return $this->render('ISIBundle:Eleve:infos-eleve-a-reintegrer.html.twig', [
      'asec'        => $as,
      'regime'      => $regime,
      'annee'       => $annee,
      'eleve'       => $eleve,
      'anneeRenvoi' => $anneeRenvoi,
    ]);
  }

  public function changerDeRegimeHomeAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    $annee  = $repoAnnee->find($as);

    if($request->isMethod('post'))
    {
      $em = $this->getDoctrine()->getManager();
      $data = $request->request->all();
      $matricule = $data['matricule'];
      $eleve = $repoEleve->findOneBy(['matricule' => $matricule]);
      $fq    = $repoFrequenter->findOneBy(['eleve' => $eleve->getEleveId(), 'anneeScolaire' => $as]);
      if(empty($eleve)) {
        $request->getSession()->getFlashBag()->add('error', 'Le matricule saisi n\'est pas correct');
        return $this->redirect($this->generateUrl('isi_changer_regime_home', ['as' => $as, 'regime' => $regime]));
      } else {
        return $this->redirect($this->generateUrl('isi_changer_regime_eleve', ['as' => $as, 'regime' => $regime, 'eleveId' => $eleve->getEleveId()]));
      }
    }

    return $this->render('ISIBundle:Eleve:changement-de-regime-home.html.twig', [
      'asec'   => $as,
      'regime' => $regime,
      'annee'  => $annee,
    ]);
  }


  public function changerDeRegimeDUnEleveAction(Request $request, $as, $regime, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');

    $annee = $repoAnnee->find($as);
    $eleve = $repoEleve->find($eleveId);

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $motifChangement = $data['motifChangement'];
      if(empty($motifChangement))
      {
        $request->getSession()->getFlashBag()->add('error', 'Il vaut impérativement saisir le motif pour lequel vous voulez renvoyer cet(te) élève.');
        return $this->redirect($this->generateUrl('isi_changer_regime_home', ['as' => $as, 'regime' => $regime]));
      }

      if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN_SCOLARITE')) {
        // Sinon on déclenche une exception « Accès interdit »
        // throw new AccessDeniedException('Accès limité aux auteurs.');
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisé à faire ce changement. Veuillez contacter le Directeur des affaires scolaires.');
        return $this->redirect($this->generateUrl('isi_changer_regime_home', ['as' => $as, 'regime' => $regime]));
      }

      if($annee->getAchevee() == TRUE)
      {
        $request->getSession()->getFlashBag()->add('error', 'Impossible d\'effectuer le changement car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
        return $this->redirect($this->generateUrl('isi_changer_regime_home', ['as' => $as, 'regime' => $regime]));
      }

      $eleve = $repoEleve->find($eleveId);
      $fq    = $repoFrequenter->findOneBy(['eleve' => $eleveId, 'anneeScolaire' => $as]);
      if (!empty($fq)) {
        $request->getSession()->getFlashBag()->add('error', 'Cet(te) élève est déjà inscrit(e), vous ne pouvez pas changer son regime de formation. Vueillez contacter l\'administrateur du système.');
        return $this->redirect($this->generateUrl('isi_changer_regime_home', ['as' => $as, 'regime' => $regime]));
      }

      if ($regime != $eleve->getRegime()) {
        $request->getSession()->getFlashBag()->add('error', 'Avant de procéder au changement, veuillez changer le regime de formation dans lequel vous vous trouvez.');
        return $this->redirect($this->generateUrl('isi_changer_regime_eleve', ['as' => $as, 'regime' => $regime, 'eleveId' => $eleveId]));
      }

      // Mise à jour des informations concernant le changement
      $nvoRegime = ($regime == 'A') ? 'F' : 'A';
      $eleve->setRegime($nvoRegime);
      $eleve->setDateUpdate(new \Datetime());

      $changement = new Eleveautreregime();
      $changement->setEleve($eleve);
      $changement->setAnneeScolaire($annee);
      $changement->setMotif($motifChangement);
      $changement->setDateSave(new \Datetime());
      $changement->setDateUpdate(new \Datetime());
      $em->persist($changement);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'Le changement de regime de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' s\'est bien effectué');
      // $em->flush();

      return $this->redirect($this->generateUrl('isi_changer_regime_home', ['as' => $as, 'regime' => $regime]));
    }

    return $this->render('ISIBundle:Eleve:infos-eleve-pour-changement-de-regime.html.twig', [
      'asec'   => $as,
      'regime' => $regime,
      'annee'  => $annee,
      'eleve'  => $eleve,
    ]);
  }

  // L'action permettra de modifier l'inscription d'un élève dans une classe
  public function editInscriptionAction(Request $request, $as, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoNiveau = $em->getRepository('ISIBundle:Niveau');

    $classes = $repoClasse->classesDeLAnnee($as, $regime);
    $niveaux = $repoNiveau->niveauxDuGroupe($regime);
    $annee   = $repoAnnee->find($as);

    return $this->render('ISIBundle:Eleve:afficher-classes-pour-modifier-inscription.html.twig', [
      'asec'    => $as,
      'regime'  => $regime,
      'annee'   => $annee,
      'classes' => $classes,
      'niveaux' => $niveaux
    ]);
  }

  // Modification effective de l'inscription d'un élève donnée
  public function modifierInscriptionDUnEleveAction(Request $request, $as, $regime, $classeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve  = $em->getRepository('ISIBundle:Eleve');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    // $repo = $em->getRepository('ISIBundle:')

    $annee = $repoAnnee->find($as);
    $classe = $repoClasse->find($classeId);
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);

    return $this->render('ISIBundle:Eleve:modifier-l-inscription-d-un-eleve.html.twig', [
      'asec'   => $as,
      'classe' => $classe,
      'annee'  => $annee,
      'regime' => $regime,
      'elevesClasse' => $eleves
    ]);
  }

  public function modifierClasseDeLEleveAction(Request $request, $as, $regime, $classeId, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoFrequenter  = $em->getRepository('ISIBundle:Frequenter');
    $repoMemoriser   = $em->getRepository('ISIBundle:Memoriser');
    $repoHalaqa      = $em->getRepository('ISIBundle:Halaqa');
    $repoAnnee       = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve       = $em->getRepository('ISIBundle:Eleve');
    $repoNote        = $em->getRepository('ISIBundle:Note');
    $repoNiveau      = $em->getRepository('ISIBundle:Niveau');
    $repoExamen      = $em->getRepository('ISIBundle:Examen');
    $repoClasse      = $em->getRepository('ISIBundle:Classe');
    // $repo = $em->getRepository('ISIBundle:')

    $annee           = $repoAnnee->find($as);
    $classeActuelle  = $repoClasse->find($classeId);
    $niveau          = $repoNiveau->find($classeActuelle->getNiveau());
    $classes         = $repoClasse->lesClassesDuNiveau($as, $niveau->getId());
    $eleve           = $repoEleve->find($eleveId);
    $examens         = $repoExamen->findBy(['anneeScolaire' => $as]);
    $halaqas = [];

    if ($regime == 'A') {
      $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');
      $genre = ($eleve->getSexeFr() == 1) ? 'H' : 'F' ;
      $halaqas  = $repoHalaqa->findBy(['anneeScolaire' => $as, 'genre' => $genre]);
    }

    // $eleveASupprimer = $repoEleve->find($eleveId);

    // S'il se trouve que l'élève à déjà participé à un examen, il doit pas être possible de le supprimer de la classe
    if(!empty($examens))
    {
      $notes = $repoNote->findBy(['examen' => $examens[0], 'eleve' => $eleveId]);
      if(!empty($notes))
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas changer/modifier cet(te) élève. Il(elle) est participe déjà à un examen.');
        return $this->redirect($this->generateUrl('isi_modifier_inscription_d_un_eleve', [
          'as'       => $as,
          'regime'   => $regime,
          'classeId' => $classeId,
        ]));
      }
    }

    $defaultData = array('message' => 'Type your message here');
    $form = $this->createFormBuilder($defaultData);
    $form->getForm();

    // Quand on soumet le formulaire
    if($request->isMethod('post')){
      $data = $request->request->all();
      $idNouvelleClasse = $data['classe'];
      if (!empty($idNouvelleClasse)) {
        # code...
        $nouvelleClasse = $repoClasse->find($idNouvelleClasse);
        $frequenter = $repoFrequenter->findOneBy(['eleve' => $eleveId, 'anneeScolaire' => $as]);

        $frequenter->setClasse($nouvelleClasse);
        $frequenter->setDateUpdate(new \Datetime());
        $request->getSession()->getFlashBag()->add('info', 'Le changement de la classe de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' s\'est terminé avec succès.');
      }
      else {
        # code...
        $request->getSession()->getFlashBag()->add('error', 'Le changement de la classe de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être effectué. Vous n\'avez pas sélectionné une classe.');
      }


      if ($regime == 'A') {
        $halaqaId = $data['halaqa'];
        if (!empty($halaqaId)) {
          # code...
          $halaqa = $repoHalaqa->find($halaqaId);
          $memoriser = $repoMemoriser->findOneBy(['eleve' => $eleveId, 'anneeScolaire' => $as]);
          $memoriser->setHalaqa($halaqa);
          $memoriser->setDateUpdate(new \Datetime());
          $request->getSession()->getFlashBag()->add('info', 'La halaqa de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' a été mise à jour avec succès.');
        } else {
          # code...
          $request->getSession()->getFlashBag()->add('error', 'La halaqa de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être changer car vous n\'avez sélectionné aucune halaqa.');
        }


      }
      $em->flush();

      return $this->redirect($this->generateUrl('isi_modifier_inscription_d_un_eleve', [
        'as'       => $as,
        'regime'   => $regime,
        'classeId' => $classeId,
      ]));
    }

    return $this->render('ISIBundle:Eleve:modification-de-la-classe-d-dun-eleve.html.twig', [
      'asec'           => $as,
      'classes'        => $classes,
      'regime'         => $regime,
      'annee'          => $annee,
      'niveau'         => $niveau,
      'halaqas'        => $halaqas,
      'eleve'          => $eleve,
      'classeActuelle' => $classeActuelle
    ]);
  }


  public function modifierNiveauDeLEleveAction(Request $request, $as, $regime, $classeId, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoFrequenter  = $em->getRepository('ISIBundle:Frequenter');
    $repoMemoriser   = $em->getRepository('ISIBundle:Memoriser');
    $repoHalaqa      = $em->getRepository('ISIBundle:Halaqa');
    $repoNote        = $em->getRepository('ISIBundle:Note');
    $repoAnnee       = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve       = $em->getRepository('ISIBundle:Eleve');
    $repoExamen      = $em->getRepository('ISIBundle:Examen');
    $repoClasse      = $em->getRepository('ISIBundle:Classe');
    // $repo = $em->getRepository('ISIBundle:')

    $annee           = $repoAnnee->find($as);
    $classeActuelle  = $repoClasse->find($classeId);
    $classes         = $repoClasse->classeGrpFormation($as, $regime);
    $eleve           = $repoEleve->find($eleveId);
    $examens         = $repoExamen->findBy(['anneeScolaire' => $as]);
    $halaqas = [];

    // $nbrFrequenter = $repoFrequenter->findBy(['eleve' => $eleveId]);
    // if (count($nbrFrequenter) > 1) {
    //   # code...
    //   $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas changer/modifier le niveau de cet(te) élève. C\'est un(e) élève inscrit(e) anciennement.');
    //   return $this->redirect($this->generateUrl('isi_modifier_inscription_d_un_eleve', [
    //     'as'       => $as,
    //     'regime'   => $regime,
    //     'classeId' => $classeId,
    //   ]));
    // }

    if ($regime == 'A') {
      $repoHalaqa = $em->getRepository('ISIBundle:Halaqa');
      $genre = ($eleve->getSexeFr() == 1) ? 'H' : 'F' ;
      $halaqas  = $repoHalaqa->findBy(['anneeScolaire' => $as, 'genre' => $genre]);
    }

    // $eleveASupprimer = $repoEleve->find($eleveId);

    // S'il se trouve que l'élève à déjà participé à un examen, il doit pas être possible de le supprimer de la classe
    if(!empty($examens))
    {
      $notes = $repoNote->findBy(['examen' => $examens[0], 'eleve' => $eleveId]);
      if(!empty($notes))
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas changer/modifier cet(te) élève. Il(elle) a déjà participe à un examen.');
        return $this->redirect($this->generateUrl('isi_modifier_inscription_d_un_eleve', [
          'as'       => $as,
          'regime'   => $regime,
          'classeId' => $classeId,
        ]));
      }
    }

    $defaultData = array('message' => 'Type your message here');
    $form = $this->createFormBuilder($defaultData);
    $form->getForm();

    // Quand on soumet le formulaire
    if($request->isMethod('post')){
      $data = $request->request->all();
      $idNouvelleClasse = $data['classe'];
      if (!empty($idNouvelleClasse)) {
        # code...
        $nouvelleClasse = $repoClasse->find($idNouvelleClasse);
        $frequenter = $repoFrequenter->findOneBy(['eleve' => $eleveId, 'anneeScolaire' => $as]);

        $frequenter->setClasse($nouvelleClasse);
        $frequenter->setDateUpdate(new \Datetime());
        $request->getSession()->getFlashBag()->add('info', 'Le changement du niveau de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' s\'est terminé avec succès.');
      }
      else {
        # code...
        $request->getSession()->getFlashBag()->add('error', 'Le changement du niveau de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être effectué. Vous n\'avez pas sélectionné une classe.');
      }


      if ($regime == 'A') {
        $halaqaId = $data['halaqa'];
        if (!empty($halaqaId)) {
          # code...
          $halaqa = $repoHalaqa->find($halaqaId);
          $memoriser = $repoMemoriser->findOneBy(['eleve' => $eleveId, 'anneeScolaire' => $as]);
          $memoriser->setHalaqa($halaqa);
          $memoriser->setDateUpdate(new \Datetime());
          $request->getSession()->getFlashBag()->add('info', 'La halaqa de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' a été mise à jour avec succès.');
        } else {
          # code...
          $request->getSession()->getFlashBag()->add('error', 'La halaqa de '.$eleve->getNomFr().' '.$eleve->getPnomFr().' ne peut être changer car vous n\'avez sélectionné aucune halaqa.');
        }
      }
      $em->flush();

      return $this->redirect($this->generateUrl('isi_modifier_inscription_d_un_eleve', [
        'as'       => $as,
        'regime'   => $regime,
        'classeId' => $classeId,
      ]));
    }

    return $this->render('ISIBundle:Eleve:modification-du-niveau-d-dun-eleve.html.twig', [
      'asec'           => $as,
      'classes'        => $classes,
      'regime'         => $regime,
      'annee'          => $annee,
      'halaqas'        => $halaqas,
      'eleve'          => $eleve,
      'classeActuelle' => $classeActuelle
    ]);
  }

  public function remplirClasseAction(Request $request, $anneeId, $niveauId)
  {
    $em = $this->getDoctrine()->getManager();

    /*
     * Récuperation des paramètres envoyés en GET
     */
    $classList = []; // initialisation du tableau qui contiendra le nom des classes

    /*
    * Ici fait ta requête qui renvois la liste des classes
    * à partir de l'id de l'année et du niveau
    * Dans une variables $classes par exemple
    */
    $classes = $em->getRepository('ISIBundle:Classe')->lesClassesDuNiveau($anneeId, $niveauId);

      foreach ($classes as $classe) {
        $classList [] = [
          'id'   => $classe->getClasseId(),
          'name' => $classe->getLibelleClasseFr() // Nom de la classe (adapte à ton cas)
        ];
      }

      /*
      *  Notre liste de classe étant prête, on fait fait un retour de celle-ci en l'encodant en objet JSON
      */

      $response = new JsonResponse($classList);
      return $response;
      // $response = new JsonResponse([$niveauId]);
      // return $response;
  }

  // La méthode-ci me permet de récupérer tous les matricules des élèves d'un regime de formation
  public function getMatriculeAction(Request $request, $regime)
  {
    $em = $this->getDoctrine()->getManager();
    $repoEleve = $em->getRepository('ISIBundle:Eleve');

    $tousLesMatricules = $repoEleve->getMatricules($regime);
    $matricules = [];
    foreach ($tousLesMatricules as $matricule) {
      $matricules[] = $matricule['matricule'];
    }

    $response   = new JsonResponse($matricules);
    return $response;
  }

  public function eleveAction($matricule)
  {
    $em = $this->getDoctrine()->getManager();
    $eleveMat = $em->getRepository('ISIBundle:Eleve')->findOneBy(['matricule' => $matricule]);
    if($eleveMat)
    {
      $nomFr = $eleveMat->getNomFr();
      $pnomFr = $eleveMat->getPnomFr();
      $dateNaissance = $eleveMat->getDateNaissance();
    }
    else {
      $nomFr = null;
      $pnomFr = null;
      $dateNaissance = null;
    }

    $response = new JsonResponse();
    return $response->setData([
      'nomFr' => $nomFr,
      'pnomFr' => $pnomFr,
      'dateNaissance' => $dateNaissance,
    ]);
  }

  // La fonction suivante a été mise en place juste pour une question de maintenance
  // Des inscriptions ont été faites avant qu'on ne ajoute certaines matierès.
  // Alors on va les ajoutées
  // /ajout-de-frequenter-pour-une-matiere-qui-n-a-pas-ete-ajoutee-avant-les-inscriptions/{as}/{regime}/{classeId}/{matiereId}
  public function ajoutMatieresOublieesAvantInscriptionAction(Request $request, $as, $regime, $classeId, $matiereId)
  {
    $em = $this->getDoctrine()->getManager();

    $repoAnnee = $em->getRepository('ISIBundle:Anneescolaire');
    $repoEleve = $em->getRepository('ISIBundle:Eleve');
    $repoClasse = $em->getRepository('ISIBundle:Classe');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');

    // On sélectionne les élèves de la classe
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $matiere = $repoMatiere->find($matiereId);
    $classe = $repoClasse->find($classeId);
    $annee = $repoAnnee->find($as);

    foreach ($eleves as $eleve) {
		// if(!in_array($eleve->getEleveId(), [5664, 5743, 5742, 5658, 5317,5409]))
		// {

		// }
    $frequenter = new Frequenter();
			$frequenter->setEleve($eleve);
			$frequenter->setClasse($classe);
			$frequenter->setMatiere($matiere);
			$frequenter->setAnneeScolaire($annee);
			$frequenter->setDateSave(new \Datetime());
			$frequenter->setDateUpdate(new \Datetime());

			$em->persist($frequenter);
			$em->flush();

      // return new Response(var_dump($frequenter));


    }
    return new Response("Les frequenter on été parfaitement générés et insérés.");
  }

  // Cette fonction est en quelque sorte la réciproque de celle qui la
  // précède à savoir ajoutMatieresOublieesAvantInscriptionAction
  // /ajout-de-notes-pour-une-matiere-qui-n-a-pas-ete-ajoutee-après-la-generation-des-fiches-de-notes/{as}/{regime}/{classeId}/{matiereId}/{examenId}
  public function ajoutNotesDesMatieresOublieesAvantInscriptionAction(Request $request, $as, $regime, $classeId, $matiereId, $examenId)
  {
    $em = $this->getDoctrine()->getManager();

    $repoEleve = $em->getRepository('ISIBundle:Eleve');
    $repoExamen = $em->getRepository('ISIBundle:Examen');
    $repoMatiere = $em->getRepository('ISIBundle:Matiere');

    // On sélectionne les élèves de la classe
    $eleves = $repoEleve->lesElevesDeLaClasse($as, $classeId);
    $matiere = $repoMatiere->find($matiereId);
    $examen = $repoExamen->find($examenId);

    foreach ($eleves as $eleve) {
      $note = new Note();
      $note->setDateSave(new \Datetime());
      $note->setDateUpdate(new \Datetime());
      $note->setExamen($examen);
      $note->setEleve($eleve);
      $note->setMatiere($matiere);

      $em->persist($note);
      $em->flush();
    }

    return new Response("Les notes on été parfaitement générées.");
  }


  /* // Toujours dans le cadre de la maintenance
   * Certains élèves ont été inscrits (création de l'entité frequenter pour eux donc) après qu'on ait
   * déjà générer les notes des élèves de la classe où ils ont été inscrits.
   * Il faudra donc générer leurs notes
   * C'est justement ce que cette méthode (function) va nous permettre de faire in chaa Allah
   */
   public function AjouterNotesDesElevesSansNotesAction($classeId, $examenId)
   {
      $em = $this->getDoctrine()->getManager();
      // $repoAnnee   = $em->getRepository('ISIBundle:');
      $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
      $repoClasse  = $em->getRepository('ISIBundle:Classe');
      $repoEleve   = $em->getRepository('ISIBundle:Eleve');
      $repoMatiere = $em->getRepository('ISIBundle:Matiere');
      $repoNote    = $em->getRepository('ISIBundle:Note');
      // $repo = $em->getRepository('ISIBundle:');

      // $niveaux = $repoNiveau->niveauxDuGroupe($regime);
      // $classes = $repoClasse->classeGrpFormation($as, $regime);

      return $this->render('ISIBundle:Eleve:elevesSansAucuneNote.html.php');

      $eleves = $repoNote->elevesSansNotes($classeId, $examenId);

      return new Response(var_dump($eleves));

   }

   public function elevesSansLesNotesAuCompletesAction()
   {
     return $this->render('ISIBundle:Eleve:elevesSansQQNote.html.php');
   }

}

?>
