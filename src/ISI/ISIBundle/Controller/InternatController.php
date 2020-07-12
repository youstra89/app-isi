<?php
namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Form\ChambreType;

use ISI\ISIBundle\Entity\Interner;
use ISI\ISIBundle\Entity\Chambre;
use ISI\ISIBundle\Entity\Probleme;
use ISI\ISIBundle\Entity\Commettre;
use ISI\ISIBundle\Entity\Paiementinternat;
use ISI\ISIBundle\Entity\Moisdepaiementinternat;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class InternatController extends Controller
{
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/internat-home-{as}", name="internat_home")
   */
  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee    = $em->getRepository('ISIBundle:Annee');
    $repoInterner = $em->getRepository('ISIBundle:Interner');
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee    = $repoAnnee->find($as);
    $internes = $repoInterner->litseDesInternes($as, $annexeId);
    $ids = [];
    if(!empty($internes))
    {
      foreach ($internes as $key => $interne) {
        $nom[$key]  = $interne->getEleve()->getNomFr();
        $pnom[$key] = $interne->getEleve()->getPnomFr();
        $ids[] = $interne->getEleve()->getId();
      }
      array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $internes);
    }
    $frequenter = $repoFrequenter->classesDeCertainEleves($as, $annexeId, $ids);
    $tabFrequenter = [];
    foreach ($frequenter as $key => $fq) {
      $tabFrequenter[$fq->getEleve()->getId()] = $fq;
    }

    // dump(count($ids));

    // return new Response(var_dump($frequenter[0]->getClasse()->getLibelleFr()));

    return $this->render('ISIBundle:Internat:index.html.twig', [
      'asec'       => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'internes'   => $internes,
      'frequenter' => $tabFrequenter
    ]);
  }

  // Accueil de la gestion des chambres
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/gestion-des-chambres-{as}", name="internat_gestion_chambres")
   */
  public function gestionDesChambresAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoChambre = $em->getRepository('ISIBundle:Chambre');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee    = $repoAnnee->find($as);
    $chambres = $repoChambre->findAll();
    return $this->render('ISIBundle:Internat:gestion-des-chambres-home.html.twig', [
      'asec'     =>$as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'chambres' => $chambres
    ]);
  }

  // Accueil de la gestion des chambres
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/ajouter-des-chambres-{as}", name="internat_add_chambre")
   */
  public function addChambresAction(Request $request, int $as)
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
    $annee = $repoAnnee->find($as);

    $chambre = new Chambre;
    $form = $this->get('form.factory')->create(ChambreType::class, $chambre);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $chambre->setPlacesDisponibles($chambre->getNombreDePlaces());
      $chambre->setCreatedBy($this->getUser());
      $chambre->setCreatedAt(new \Datetime());
      $em->persist($chambre);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La chambre a été bien enregistrée');
      return $this->redirectToRoute('internat_gestion_chambres', array('as' => $as, 'annexeId' => $annexeId));
    }

    // return new Response('Que se passe-t-il ?');
    return $this->render('ISIBundle:Internat:add-chambre.html.twig', array(
      'annee'   => $annee, 'annexe'   => $annexe,
      'asec'  => $as,
      'form'  => $form->createView(),
    ));
  }

  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/editer-des-chambres/{chambreId}/{as}", name="internat_edit_chambre")
   */
  public function editChambresAction(Request $request, int $as, int $chambreId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoChambre = $em->getRepository('ISIBundle:Chambre');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee = $repoAnnee->find($as);
    $chambre = $repoChambre->find($chambreId);


    $form = $this->get('form.factory')->create(ChambreType::class, $chambre);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $chambre->setPlacesDisponibles($chambre->getNombreDePlaces());
      $chambre->setUpdatedBy($this->getUser());
      $chambre->setUpdatedAt(new \Datetime());
      $em->persist($chambre);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La chambre <strong>'.$chambre->getBatiment()->getNom().' - '.$chambre->getLibelle().'</strong> a été bien modifiée');
      return $this->redirectToRoute('internat_gestion_chambres', array('as' => $as, 'annexeId' => $annexeId));
    }

    // return new Response('Que se passe-t-il ?');
    return $this->render('ISIBundle:Internat:edit-chambre.html.twig', array(
      'annee'   => $annee, 'annexe'   => $annexe,
      'asec'  => $as,
      'chambre'  => $chambre,
      'form'  => $form->createView(),
    ));
  }

  // Ajout d'un élève à l'internat
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/ajouter-un-interne-home-{as}", name="internat_add")
   */
  public function addInterneAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $repoEleve = $em->getRepository('ISIBundle:Eleve');  
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }  
    $annee = $repoAnnee->find($as);

    if($request->isMethod('post'))
    {
      $data      = $request->request->all();
      $matricule = $data['matricule'];
      if(empty($matricule))
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas saisie de matricule.');
        return $this->redirectToRoute('internat_add', array('as' => $as, 'annexeId' => $annexeId));
      }
      $eleve = $repoEleve->findOneBy(['matricule' => $matricule, 'annexe' => $annexeId]);
      if(empty($eleve))
      {
        $request->getSession()->getFlashBag()->add('error', 'Le matricule saisi ne correspond à aucun élève');
        return $this->redirectToRoute('internat_add', array('as' => $as, 'annexeId' => $annexeId));
      }
      // $lettreMatricule = substr($matricule, -4, 1);
      if($eleve->getRegime() != 'A')
      {
        $request->getSession()->getFlashBag()->add('error', 'Cette élève n\'est pas inscrit à l\'académie, il ne peut donc pas être inscrit à l\'internat');
        return $this->redirectToRoute('internat_add', array('as' => $as, 'annexeId' => $annexeId));
      }

      if($eleve->getRenvoye() == TRUE)
      {
        $request->getSession()->getFlashBag()->add('error', 'Cet élève ne peut pas être inscrit à l\'internat car il a été renvoyé');
        return $this->redirectToRoute('internat_add', array('as' => $as, 'annexeId' => $annexeId));
      }

      return $this->redirectToRoute('internat_add_eleve', array('as' => $as, 'annexeId' => $annexeId, 'eleveId' => $eleve->getId()));
    }

    return $this->render('ISIBundle:Internat:add-interne.html.twig', [
      'asec'  => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
    ]);
  }

  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/ajouter-un-eleve-a-l-internat-{as}-{eleveId}", name="internat_add_eleve")
   */
  public function addEleveInternatAction(Request $request, $as, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoChambre = $em->getRepository('ISIBundle:Chambre');
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoEleve   = $em->getRepository('ISIBundle:Eleve');
    $repoReinscription = $em->getRepository('ISIBundle:Reinscription');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    
    
    $annee    = $repoAnnee->find($as);
    $eleve    = $repoEleve->find($eleveId);
    $fq       = $repoFrequenter->findOneBy(['annee' => $as, 'eleve' => $eleveId]);
    $fqs       = $repoFrequenter->findBy(['eleve' => $eleveId]);
    $chambres = $repoChambre->chambresDisponibles($eleve->getSexe());
    $reinscription = $repoReinscription->findOneBy(['eleve' => $eleveId, 'annee' => $as - 1]);

    if(empty($reinscription) and count($fqs) != 0) {
      $request->getSession()->getFlashBag()->add('error', 'Cet élève ne s\'est pas réinscrit pour l\'année scolaire en cours');
      return $this->redirectToRoute('internat_add', array('as' => $as, 'annexeId' => $annexeId));
    }

    if($request->isMethod('post')) {
      $data = $request->request->all();
      $chambreId = $data['chambre'];
      $dateI = new \DateTime($data['dateI']);

      if(empty($chambreId))
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous avez oublié de sélectionner la chambre de l\'élève');
        return $this->redirectToRoute('internat_add_eleve', array('as' => $as, 'annexeId' => $annexeId, 'eleveId' => $eleveId));
      }

      $annee   = $repoAnnee->find($as);
      $eleve   = $repoEleve->find($eleveId);
      $chambre = $repoChambre->find($chambreId);
      /**
       * Quand l'année scolaire est finie, on doit plus faire des
       * mofications, des mises à jour
       **/
      if($annee->getAchevee() == TRUE)
      {
        $request->getSession()->getFlashBag()->add('error', 'Impossible d\'inscrire un élève à l\'internat car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
        return $this->redirect($this->generateUrl('internat_add', ['as' => $as, 'annexeId' => $annexeId]));
      }

      // On va faire un test ici pour savoir s'il y a toujours de la place libre dans
      $nbrPlcs = $chambre->getPlacesDisponibles() - 1;
      $chambre->setPlacesDisponibles($nbrPlcs);
      $interner = new Interner();
      $interner->setEleve($eleve);
      $interner->setAnnee($annee);
      $interner->setChambre($chambre);
      $interner->setRenvoye(FALSE);
      $interner->setCreatedBy($this->getUser());
      $interner->setCreatedAt(new \Datetime());

      // On va profiter pour enregistrer une conduite
      // Un renvoi peut être considéré comme effet immédiat ou non d'un problème
      $probleme = new Probleme();
      $appreciation = "Interner";
      $description  = "Inscrit(e) à l'internat";
      $probleme->setDate($dateI);
      $probleme->setAppreciation($appreciation);
      $probleme->setDescription($description);
      $probleme->setCreatedBy($this->getUser());
      $probleme->setCreatedAt(new \Datetime());

      // Il nous faut un occurence de commettre pour enregistrer un problème
      $commettre = new Commettre();
      $commettre->setEleve($eleve);
      $commettre->setProbleme($probleme);
      $commettre->setAnnee($annee);

      $em->persist($probleme);
      $em->persist($commettre);
      $em->persist($interner);
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', '<strong>'.$eleve->getNom().'</strong> a bien été interné dans la chambre "<strong>'.$chambre->getLibelle().'</strong>"');
      return $this->redirectToRoute('internat_add', array('as' => $as, 'annexeId' => $annexeId));
    }

    return $this->render('ISIBundle:Internat:infos-eleve-a-interner.html.twig', [
      'asec'     => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'eleve'    => $eleve,
      'fq'       => $fq,
      'chambres' => $chambres,
    ]);
  }

  // Retrait d'un élève à l'internat
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/retirer-un-interne-home-{as}", name="internat_delete")
   */
  public function deleteInterneAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee    = $em->getRepository('ISIBundle:Annee');
    $repoEleve    = $em->getRepository('ISIBundle:Eleve');
    $repoInterner = $em->getRepository('ISIBundle:Interner');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);

    if($request->isMethod('post'))
    {
      $data      = $request->request->all();
      $matricule = $data['matricule'];
      if(empty($matricule))
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas saisie de matricule.');
        return $this->redirectToRoute('internat_delete', array('as' => $as, 'annexeId' => $annexeId));
      }
      $eleve = $repoEleve->findOneBy(['matricule' => $matricule, 'annexe' => $annexeId]);
      if(empty($eleve))
      {
        $request->getSession()->getFlashBag()->add('error', 'Le matricule saisi ne correspond à aucun élève');
        return $this->redirectToRoute('internat_delete', array('as' => $as, 'annexeId' => $annexeId));
      }
      // $lettreMatricule = substr($matricule, -4, 1);
      if($eleve->getRegime() != 'A')
      {
        $request->getSession()->getFlashBag()->add('error', 'Cette élève n\'est pas inscrit à l\'académie');
        return $this->redirectToRoute('internat_delete', array('as' => $as));
      }

      $eleveInterne = $repoInterner->findBy(['annee' => $as, 'eleve' => $eleve->getId()]);
      if(empty($eleveInterne))
      {
        $request->getSession()->getFlashBag()->add('error', 'L\'élève n\'est pas inscrit à l\'internat');
        return $this->redirectToRoute('internat_delete', array('as' => $as));
      }
      return $this->redirectToRoute('internat_delete_eleve', array('as' => $as, 'annexeId' => $annexeId, 'eleveId' => $eleve->getId()));
    }

    return $this->render('ISIBundle:Internat:delete-interne.html.twig', [
      'asec'  => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
    ]);
  }

  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/retirer-un-eleve-de-l-internat-{as}-{eleveId}", name="internat_delete_eleve")
   */
  public function deleteEleveInternatAction(Request $request, $as, $eleveId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoFrequenter = $em->getRepository('ISIBundle:Frequenter');
    $repoInterner = $em->getRepository('ISIBundle:Interner');
    $repoChambre  = $em->getRepository('ISIBundle:Chambre');
    $repoAnnee    = $em->getRepository('ISIBundle:Annee');
    $repoEleve    = $em->getRepository('ISIBundle:Eleve');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $eleve    = $repoEleve->find($eleveId);
    $fq       = $repoFrequenter->findOneBy(['annee' => $as, 'eleve' => $eleveId]);

    if($request->isMethod('post'))
    {
      $annee = $repoAnnee->find($as);
      $eleve = $repoEleve->find($eleveId);
      $data  = $request->request->all();
      $description  = $data['motifRenvoi'];
      $date = new \DateTime($data['date']);
      if($annee->getAchevee() == TRUE)
      {
        $request->getSession()->getFlashBag()->add('error', 'Impossible d\'inscrire un élève à l\'internat car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
        return $this->redirect($this->generateUrl('internat_add', ['as' => $as, 'annexeId' => $annexeId]));
      }

      if(empty($description))
      {
        $request->getSession()->getFlashBag()->add('error', 'Le motif du renvoi ne doit pas être vide');
        return $this->redirectToRoute('internat_delete_eleve', array('as' => $as, 'annexeId' => $annexeId, 'eleveId' => $eleveId));
      }

      $interner = $repoInterner->findOneBy(['eleve' => $eleveId, 'annee' => $as]);
      $interner->setRenvoye(true);
      $interner->setDateRenvoi(new \Datetime());

      // On va profiter pour enregistrer une conduite
      // Un renvoi peut être considéré comme effet immédiat ou non d'un problème
      $probleme = new Probleme();
      $appreciation = "Retrait internat";
      $probleme->setAppreciation($appreciation);
      $probleme->setDescription($description);
      $probleme->setDate($date);
      $probleme->setCreatedBy($this->getUser());
      $probleme->setCreatedAt(new \Datetime());

      // Il nous faut un occurence de commettre pour enregistrer un problème
      $commettre = new Commettre();
      $commettre->setEleve($eleve);
      $commettre->setProbleme($probleme);
      $commettre->setAnnee($annee);

      $em->persist($probleme);
      $em->persist($commettre);
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', '<strong>'.$eleve->getNom().'</strong> a été renvoyé(e) de l\'internat');
      return $this->redirectToRoute('internat_home', array('as' => $as, 'annexeId' => $annexeId));
    }

    return $this->render('ISIBundle:Internat:infos-eleve-a-retirer-de-l-internat.html.twig', [
      'asec'     => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'eleve'    => $eleve,
      'fq'       => $fq,
    ]);
  }

  //
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/info-eleve-{as}-{id}", name="internat_info_eleve")
   */
  public function infoEleveAction(Request $request, $as, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee       = $em->getRepository('ISIBundle:Annee');
    $repoEleve       = $em->getRepository('ISIBundle:Eleve');
    $repoProbleme    = $em->getRepository('ISIBundle:Probleme');
    $repoFrequenter  = $em->getRepository('ISIBundle:Frequenter');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    // Sélection de l'année scolaire
    $annee  = $repoAnnee->find($as);

    // Sélection de l'élève dont l'id est passé en paramètre de la fonction
    $eleve = $repoEleve->find($id);

    // La classe de l'élève durant l'annee en cours
    $fq = $repoFrequenter->findOneBy(['eleve' => $id, 'annee' => $as]);

    // On va ici sélectionner les problèmes qu'auraient eu l'élève
    $problemes = $repoProbleme->problemesDUnEleveLorsDUneAnnee($id, $as);

    // return new Response(var_dump($problemes));

    return $this->render('ISIBundle:Internat:info-eleve.html.twig', [
      'asec'      => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'eleve'     => $eleve,
      'fq'        => $fq,
      'problemes' => $problemes
    ]);
  }

  // Voir la liste des élèves qui ont été retiré de l'internat
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/liste-des-eleves-retires-de-l-internat-{as}", name="internat_deleted")
   */
  public function deletedElevesAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee    = $em->getRepository('ISIBundle:Annee');
    $repoInterner = $em->getRepository('ISIBundle:Interner');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $internes = $repoInterner->elevesInternesRenvoyes($as, $annexeId);

    return $this->render('ISIBundle:Internat:liste-des-eleves-retires-de-l-interant.html.twig', [
      'asec'     => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'internes' => $internes,
    ]);
  }

  // Pour ajouter les mois qui seront pris en compte pour le paiement des frais de l'internat
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/ajouter-les-mois-de-paiement-de-l-internat-{as}", name="internat_add_mois_de_paiement")
   */
  public function addMoisDePaiementAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $repoMois  = $em->getRepository('ISIBundle:Mois');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    $mois  = $repoMois->findAll();

    $repoMoisDePaie = $em->getRepository('ISIBundle:Moisdepaiementinternat');
    $moisdepaie     = $repoMoisDePaie->findBy(['annee' => $as ]);

    return $this->render('ISIBundle:Internat:ajouter-les-mois-de-paiement.html.twig', [
      'asec'  => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'mois'  => $mois,
      'moisdepaie' => $moisdepaie
    ]);
  }

  // Les mois de paiement seront ajoutés l'un après l'autre
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/ajouter-un-mois-de-paiement-{as}-{moisId}", name="internat_add_mois_de_paie")
   */
  public function addLeMoisDePaieAction(Request $request, $as, $moisId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoMoisDePaie = $em->getRepository('ISIBundle:Moisdepaiementinternat');
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoMois       = $em->getRepository('ISIBundle:Mois');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    $mois  = $repoMois->find($moisId);

    $mdp = $repoMoisDePaie->findBy(["annee" => $as]);
    if(count($mdp) >= 10)
    {
      $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas ajouter plus de 10 mois de paiement');
      return $this->redirectToRoute('internat_add_mois_de_paiement', ['as' => $as, 'annexeId' => $annexeId]);
    }

    // Ici on va se permettre de faire en sorte que les mois soient successifs pour une année scolaire donnée
    // Ex: Novembre, Décembre, Janvier, Frévrier...
    $dernierMois = $repoMoisDePaie->dernierMoisDePaiementDeLAnnee($as);
    if(!empty($dernierMois))
    {
      if($moisId <= $dernierMois[0]->getMois()->getId() && $moisId != 1)
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas ajouer un mois antérieur au mois de <strong>'.$mois->getMois().'</strong>');
        return $this->redirectToRoute('internat_add_mois_de_paiement', ['as' => $as, 'annexeId' => $annexeId]);
      }
      elseif($moisId != ($dernierMois[0]->getMois()->getId() + 1) && $moisId != 1)
      {
        $request->getSession()->getFlashBag()->add('error', 'Les mois doivent être ajoutés successivement');
        return $this->redirectToRoute('internat_add_mois_de_paiement', ['as' => $as, 'annexeId' => $annexeId]);
      }
    }

    $moisdepaie = new Moisdepaiementinternat();
    $moisdepaie->setAnnee($annee);
    $moisdepaie->setMois($mois);
    $moisdepaie->setCreatedBy($this->getUser());
    $moisdepaie->setCreatedAt(new \Datetime());
    $em->persist($moisdepaie);
    $em->flush();

    $request->getSession()->getFlashBag()->add('info', 'Le mois de <strong>'.$mois->getMois().'</strong> a été ajouté avec succès');
    return $this->redirectToRoute('internat_add_mois_de_paiement', ['as' => $as, 'annexeId' => $annexeId]);
  }

  // Pour le règlement des droits/frais de l'internat
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/paiement-des-frais-internat-{as}", name="internat_pay")
   */
  public function payInterneRightAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoMoisDePaie = $em->getRepository('ISIBundle:Moisdepaiementinternat');
    $repoPaiement   = $em->getRepository('ISIBundle:Paiementinternat');
    $repoInterner   = $em->getRepository('ISIBundle:Interner');
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    $moisdepaie = $repoMoisDePaie->findBy(['annee' => $as ]);
    $paiements = $repoPaiement->lesPaiementsDUneAnnee($as);

    $internes = $repoInterner->findBy(['annee' => $as]);
    foreach ($internes as $key => $interne) {
      $nom[$key]  = $interne->getEleve()->getNomFr();
      $pnom[$key] = $interne->getEleve()->getPnomFr();
    }
    if (!empty($internes)) {
      # code...
      array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $internes);
    }

    return $this->render('ISIBundle:Internat:reglement-des-frais-de-l-internat.html.twig', [
      'asec'       => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'internes'   => $internes,
      'paiements'  => $paiements,
      'moisdepaie' => $moisdepaie,
    ]);
  }

  // Paiement des frais de l'internat pour un élève donné et pour un mois donné
  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/paiement-des-frais-internat-d-un-eleve-pour-un-mois-{as}-{eleveId}-{moisId}", name="internat_pay_mois")
   */
  public function paiementDesFraisPourUnMoisAction(Request $request, $as, $eleveId, $moisId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoMoisDePaie = $em->getRepository('ISIBundle:Moisdepaiementinternat');
    $repoPaiement   = $em->getRepository('ISIBundle:Paiementinternat');
    $repoInterner   = $em->getRepository('ISIBundle:Interner');
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    $eleve = $repoEleve->find($eleveId);
    $mois  = $repoMoisDePaie->find($moisId);
    $moisdepaie = $repoMoisDePaie->findBy(['annee' => $as ]);

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $montant = $data['montant'];
      if(empty($montant))
      {
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez saisi aucun montant');
        return $this->redirectToRoute('internat_pay_mois', ['as' => $as, 'annexeId' => $annexeId, 'eleveId' => $eleveId, 'moisId' => $moisId]);
      }
      elseif(!is_numeric($montant))
      {
        $request->getSession()->getFlashBag()->add('error', 'Le montant saisi n\'est pas un nombre juste');
        return $this->redirectToRoute('internat_pay_mois', ['as' => $as, 'annexeId' => $annexeId, 'eleveId' => $eleveId, 'moisId' => $moisId]);
      }
      else {
        $interner  = $repoInterner->findBy(['annee' => $as, 'eleve' => $eleveId]);
        $paiements = $repoPaiement->findBy(['interner' => $interner[0]->getId()]);
        // Si la $paiements est vide, cela signifie que c'est le premier paiement et donc, il doit
        // être effectué pour le premier mois de paiement de l'année
        if(empty($paiements))
        {
          // Si $paiement est vide, et si le mois de paiement sélectionné n'est pas le premier mois de paiement...
          if($moisdepaie[0]->getId() != $moisId)
          {
            $request->getSession()->getFlashBag()->add('error', 'Commencez par payez les frais du premier mois à l\'internat');
            return $this->redirectToRoute('internat_pay', ['as' => $as]);
          }
          else{
            $paiement = new Paiementinternat();
            $paiement->setInterner($interner[0]);
            $paiement->setMoisdepaiement($mois);
            $paiement->setMontant($montant);
            $paiement->setCreatedBy($this->getUser());
            $paiement->setCreatedAt(new \Datetime());
            $em->persist($paiement);
            $em->flush();
            $request->getSession()->getFlashBag()->add('info', 'Le paiement de '.$eleve->getNom().' pour le mois de '.$mois->getMois()->getMois().' s\'est bien effectué');
            return $this->redirectToRoute('internat_pay', ['as' => $as, 'annexeId' => $annexeId]);
          }
        }
        // Ici, des paiements ont été effectués pour l'élève. On va donc s'assurer que les paiements restent successifs
        else{
          // Ici, il y a eu au moins un paiement
          $idMoisDernierPaiement = end($paiements)->getMoisdepaiement()->getId();
          if($idMoisDernierPaiement > $moisId && $moidId != 1)
          {
            $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas payez ce mois sans avoir payé les mois précédents');
            return $this->redirectToRoute('internat_pay', ['as' => $as, 'annexeId' => $annexeId]);
          }
          elseif($moisId != ($idMoisDernierPaiement + 1) && $moisId != 1){
            $request->getSession()->getFlashBag()->add('error', 'Les paiements doivent se faire de façon successives');
            return $this->redirectToRoute('internat_pay', ['as' => $as, 'annexeId' => $annexeId]);
          }
        }
        $paiement = new Paiementinternat();
        $paiement->setInterner($interner[0]);
        $paiement->setMoisdepaiement($mois);
        $paiement->setMontant($montant);
        $paiement->setCreatedBy($this->getUser());
        $paiement->setCreatedAt(new \Datetime());
        $em->persist($paiement);
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Le paiement de <strong>'.$eleve->getNom().'</strong>  pour le mois de <strong>'.$mois->getMois()->getMois().'</strong> s\'est bien effectué');
        return $this->redirectToRoute('internat_pay', ['as' => $as]);
      }
    }
    return $this->render('ISIBundle:Internat:paiement-des-frais-pour-un-mois.html.twig', [
      'asec' => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'eleve' => $eleve,
      'mois'  => $mois,
    ]);
  }

  /**
   * @Security("has_role('ROLE_INTERNAT')")
   * @Route("/recu-de-paiement-{as}-{eleveId}-{moisId}-{paiementId}", name="internat_recu_de_paiement")
   */
  public function recuDePaiementAction(Request $request, $as, $eleveId, $moisId, $paiementId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoMoisDePaie = $em->getRepository('ISIBundle:Moisdepaiementinternat');
    $repoPaiement   = $em->getRepository('ISIBundle:Paiementinternat');
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEleve      = $em->getRepository('ISIBundle:Eleve');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    $eleve = $repoEleve->find($eleveId);
    $mois  = $repoMoisDePaie->find($moisId);
    $paiement = $repoPaiement->find($paiementId);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $filename = "recu-de-paiement-".$eleve->getNom()."-".$mois->getMois()->getMois();

    $html = $this->renderView('ISIBundle:Internat:recu-de-paiement.html.twig', [
      // "title" => "Titre de mon document",
      'as'    => $as,
      'mois'  => $mois,
      'annee'   => $annee, 'annexe'   => $annexe,
      'eleve' => $eleve,
      'paiement' => $paiement,
      'server'   => $_SERVER["DOCUMENT_ROOT"],   
      ]);

    return new Response(
        $snappy->getOutputFromHtml($html),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"'
        ]
    );
  }
}


 ?>
