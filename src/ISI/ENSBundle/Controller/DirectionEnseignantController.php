<?php
namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ENSBundle\Entity\Contrat;
use ISI\ENSBundle\Entity\Enseignant;
use ISI\ENSBundle\Entity\AnneeContrat;

use ISI\ENSBundle\Form\ContratType;
use ISI\ENSBundle\Form\EnseignantType;
use ISI\ENSBundle\Form\AnneeContratType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DirectionEnseignantController extends Controller
{
  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $annee       = $repoAnnee->find($as);
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $enseignants = $repoEnseignant->findBy(["enseignant" => true, "annexe" => $annexeId]);


    return $this->render('ENSBundle:Default:index.html.twig', [
      'asec'        => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'enseignants' => $enseignants,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function agentsAction(Request $request, $as)
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


    return $this->render('ENSBundle:Default:agents.html.twig', [
      'asec'        => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'enseignants' => $enseignants,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
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
   */
  public function addEnseignantAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
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

      //Ici on procède à l'enrigistrement effectif de l'année scolaire en base de données
      $enseignant->setAnnexe($annexe);
      $enseignant->setMatricule($matriculeNew);
      $enseignant->setDateNaissance($date);
      $enseignant->setRupture(false);
      $enseignant->setDateRupture(null);
      $enseignant->setCreatedBy($this->getUser());
      $enseignant->setCreatedAt(new \Datetime());
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
         ['as' => $as]
        ));
    }

    return $this->render('ENSBundle:Default:add-enseignant.html.twig', [
      'asec'    => $as,
      'form'  => $form->createView(),
      'annee'   => $annee, 'annexe'   => $annexe,
      'matricule' => $matriculeNew
    ]);
  }

  /**
   * @Security("has_role('ROLE_SUPER_ADMIN')")
   */
  public function editEnseignantAction(Request $request, $as, $enseignantId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee  = $em->getRepository('ISIBundle:Annee');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee  = $repoAnnee->find($as);
    $enseignant = $repoEnseignant->find($enseignantId);

    $form = $this->createForm(EnseignantType::class, $enseignant);
    if($form->handleRequest($request)->isValid())
    {
      $data = $request->request->all();
      $date = $data["date"];
      $date = new \DateTime($date);
      $enseignant->setDateNaissance($date);
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'Mise à jour des informations relatives à <strong>'.$enseignant->getNom().'</strong> terminée avec succès.');

      return $this->redirect($this->generateUrl('ens_home', array(
        'as'     => $as,
      )));
    }

    return $this->render('ENSBundle:Default:edit-enseignant.html.twig', [
      'asec'  => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'form'  => $form->createView(),
      'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function infoEnseignantAction(Request $request, $as, $enseignantId)
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
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function priseDeFonctionHomeAction(Request $request, $as)
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

    $annee  = $repoAnnee->find($as);
    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
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

    return $this->render('ENSBundle:Default:initialisation-de-prise-de-fonction-enseignant.html.twig', [
      'asec'       => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      // 'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function priseDeFonctionAction(Request $request, $as, $enseignantId)
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

      $request->getSession()->getFlashBag()->add('info', 'La prise de fonction de l\'enseignant(e) <strong>'.$enseignant->getNom().'</strong> a été enregistrée avec succès.');

      return $this->redirect($this->generateUrl('ens_initialisation_prise_de_fonction', ['as'=> $as]));
    }

    return $this->render('ENSBundle:Default:prise-de-fonction.html.twig', [
      'asec'       => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'form'       => $form->createView(),
      'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function fonctionEnCoursAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $contrats = $repoContrat->contratsEnCours();

    return $this->render('ENSBundle:Default:fonctions-en-cours.html.twig', [
      'asec'     => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'contrats' => $contrats,
    ]);
  }


  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function arretDeFonctionHomeAction(Request $request, $as)
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
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez jamais eu de relation de travail avec l\'enseignant(e) <strong>'.$enseignant->getNom().' ('.$matricule.')</strong>.');
          return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as]);
        }
        elseif($contrat->getFini() == TRUE)
        {
          $request->getSession()->getFlashBag()->add('error', 'Il n y a pas de contrat en cours pour l\'enseignant(e) <strong>'.$enseignant->getNom().' ('.$matricule.')</strong>.');
          return $this->redirectToRoute('ens_initialisation_l_arret_de_fonction', ['as' => $as]);
        }
        else
        {
          return $this->redirect($this->generateUrl('ens_arret_de_fonction', ['as' => $as, 'contratId' => $contrat->getId()]));
        }
      }

    }

    return $this->render('ENSBundle:Default:initialisation-de-l-arret-de-fonction-enseignant.html.twig', [
      'asec'       => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      // 'enseignant' => $enseignant,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function arretDeFonctionAction(Request $request, $as, $contratId)
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

      return $this->redirect($this->generateUrl('ens_initialisation_l_arret_de_fonction', ['as'=> $as]));
    }

    return $this->render('ENSBundle:Default:arret-de-fonction.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'contrat' => $contrat,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function enseignantsDeLAnneeAction(Request $request, $as)
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
    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as);

    return $this->render('ENSBundle:Default:enseignants-de-l-annee.html.twig', [
      'asec'     => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'contrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function impressionListeDesEnseignantsDeLAnneeAction(Request $request, $as)
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
    $anneeContrats = $repoAnneeContrat->fonctionDeLAnnee($as);

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
   */
  public function impressionListeDesEnseignantsDeLAnneeAvecDetailsAction(Request $request, $as)
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
   */
  public function ajouterEnseignantAnneeAction(Request $request, $as, $contratId)
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

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('ens_fonctions_en_cours', ['as' => $as]));
    }

    if(!is_null($anneeContrat))
    {
      $request->getSession()->getFlashBag()->add('error', '<strong>'.$anneeContrat->getContrat()->getEnseignant()->getNom().'</strong> est déjà utilisé(e) pour l\'année <strong>'.$annee->getLibelle().'</strong>.');
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

      $request->getSession()->getFlashBag()->add('info', 'Les heures de cours de <strong>'.$contrat->getEnseignant()->getNom().'</strong> ont été bien enregistrées pour l\'année <strong>'.$annee->getLibelle().'</strong>.');

      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as'=> $as]));
    }

    return $this->render('ENSBundle:Default:nouveau-annee-contrat.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'form'    => $form->createView(),
      'contrat' => $contrat,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function modifierEnseignantAnneeAction(Request $request, $as, $anneeContratId)
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
    $anneeContrat    = $repoAnneeContrat->find($anneeContratId);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter une prise de fonction car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as' => $as]));
    }

    $form  = $this->createForm(AnneeContratType::class, $anneeContrat);

    if($form->handleRequest($request)->isValid())
    {
      $em = $this->getDoctrine()->getManager();
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'Les heures de cours de <strong>'.$anneeContrat->getContrat()->getEnseignant()->getNom().'</strong> ont été mise à jour avec succès pour l\'année <strong>'.$annee->getLibelle().'</strong>.');

      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as'=> $as]));
    }

    return $this->render('ENSBundle:Default:edit-annee-contrat.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'form'    => $form->createView(),
      'contrat' => $anneeContrat,
    ]);
  }
}


 ?>
