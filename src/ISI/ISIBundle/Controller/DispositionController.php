<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Salle;
use ISI\ISIBundle\Entity\SalleClasse;
use ISI\ISIBundle\Form\SalleType;
use ISI\ISIBundle\Entity\Batiment;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DispositionController extends Controller
{
    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @Route("/index-{as}", name="disposition_home")
     */
    public function indexAction(Request $request, int $as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee = $em->getRepository('ISIBundle:Annee');
      $annee     = $repoAnnee->find($as);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      return $this->render('ISIBundle:Disposition:index.html.twig', [
        'asec'  => $as,
        'annexe'  => $annexe,
        'annee' => $annee,
      ]);
    }

    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @Route("/gestions-des-batiments-{as}", name="batiment_home")
     */
    public function gestionDesBatimentsAction(Request $request, int $as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee    = $em->getRepository('ISIBundle:Annee');
      $repoBatiment = $em->getRepository('ISIBundle:Batiment');
      $annee     = $repoAnnee->find($as);
      $batiments = $repoBatiment->findBy(['utilisation' => 1]);

      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      return $this->render('ISIBundle:Disposition:batiments.html.twig', [
        'asec'      => $as,
        'annee'     => $annee,
        'annexe'  => $annexe,
        'batiments' => $batiments,
      ]);
    }

    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @Route("/ajouter-un-batiment-{as}", name="batiment_add")
     */
    public function addBatimentAction(Request $request, int $as): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee = $em->getRepository('ISIBundle:Annee');
      $annee     = $repoAnnee->find($as);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      if($request->isMethod('post'))
      {
        $data = $request->request->all();
        $nom = $data['nom'];
        if(empty($nom)){
          $this->addFlash('error', 'Le nom d\'un bâtiment ne doit pas être vide.');
          return $this->redirectToRoute('batiment_add', ['as' => $as, 'annexeId' => $annexeId]);
        }
        $batiment = new Batiment();
        $batiment->setNom($nom);
        $batiment->setUtilisation(1);
        $batiment->setCreatedBy($this->getUser());
        $batiment->setCreatedAt(new \DateTime());
        $em->persist($batiment);
        $em->flush();
        $this->addFlash('info', 'Le batiment '.$batiment->getNom().' a été enregistré avec succès.');
        return $this->redirectToRoute('batiment_home', ['as' => $as, 'annexeId' => $annexeId]);
      }
      //>>
      return $this->render('ISIBundle:Disposition:batiment-add.html.twig', [
        'asec'  => $as,
      'annexe'  => $annexe,
      'annee' => $annee
      ]);
    }

    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @param Batiment $batiment
     * @Route("/editer-informations-batiment-{as}-{id}", name="batiment_edit")
     */
    public function editBatimentAction(Request $request, Batiment $batiment, int $as): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee   = $em->getRepository('ISIBundle:Annee');
      $annee       = $repoAnnee->find($as);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      if($request->isMethod('post'))
      {
        $data = $request->request->all();
        $nom = $data['nom'];
        if(empty($nom)){
          $this->addFlash('error', 'Le nom d\'un bâtiment ne doit pas être vide.');
          return $this->redirectToRoute('batiment_edit', ['as' => $as, 'annexeId' => $annexeId, 'id' => $batiment->getId()]);
        }
        $batiment->setUpdatedBy($this->getUser());
        $batiment->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('info', 'Les informations sur le batiment '.$batiment->getNom().' ont été mises à jour avec succès.');
        return $this->redirectToRoute('batiment_home', ['as' => $as, 'annexeId' => $annexeId]);
      }
      //>>
      return $this->render('ISIBundle:Disposition:batiment-edit.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
      'annexe'  => $annexe,
      'batiment' => $batiment,
      ]);
    }

    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @Route("/gestions-des-salles-{as}", name="salle_home")
     */
    public function gestionDesSallesAction(Request $request, int $as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee    = $em->getRepository('ISIBundle:Annee');
      $repoSalle = $em->getRepository('ISIBundle:Salle');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      $annee     = $repoAnnee->find($as);
      $salles = $repoSalle->findBy(["annexe" => $annexeId]);

      return $this->render('ISIBundle:Disposition:salles.html.twig', [
        'asec'   => $as,
        'annee'  => $annee,
      'annexe'  => $annexe,
      'salles' => $salles,
      ]);
    }

    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @Route("/ajouter-un-salle-{as}", name="salle_add")
     */
    public function addSalleAction(Request $request, int $as): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee = $em->getRepository('ISIBundle:Annee');
      $annee     = $repoAnnee->find($as);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      $salle = new Salle();
      $form = $this->createForm(SalleType::class, $salle);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $salle->setAnnexe($annexe);
        $salle->setCreatedBy($this->getUser());
        $salle->setCreatedAt(new \DateTime());
        $em->persist($salle);
        $em->flush();
        $this->addFlash('info', 'La salle '.$salle->getNom().' a été enregistré avec succès.');
        return $this->redirectToRoute('salle_home', ['as' => $as, 'annexeId' => $annexeId]);
      }
      return $this->render('ISIBundle:Disposition:salle-add.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
      'annexe'  => $annexe,
      'form' => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @param Salle $salle
     * @Route("/editer-informations-salle-{as}-{id}", name="salle_edit")
     */
    public function editSalleAction(Request $request, int $as, Salle $salle): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee = $em->getRepository('ISIBundle:Annee');
      $annee     = $repoAnnee->find($as);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      $form = $this->createForm(SalleType::class, $salle);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $salle->setUpdatedBy($this->getUser());
        $salle->setUpdatedAt(new \DateTime());
        $em->persist($salle);
        $em->flush();
        $this->addFlash('info', 'La salle '.$salle->getNom().' a été mise à jour avec succès.');
        return $this->redirectToRoute('salle_home', ['as' => $as, 'annexeId' => $annexeId]);
      }
      return $this->render('ISIBundle:Disposition:salle-edit.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'salle' => $salle,
      'annexe'  => $annexe,
      'form' => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @Route("/disposition-des-classes-par-salles-{as}", name="disposition")
     */
    public function dispositionAction(Request $request, int $as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee = $em->getRepository('ISIBundle:Annee');
      $repoSalle = $em->getRepository('ISIBundle:Salle');
      $repoSC    = $em->getRepository('ISIBundle:SalleClasse');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      $annee     = $repoAnnee->find($as);
      $salles = $repoSalle->findBy(["annexe" => $annexeId]);
      $sallesClasses = $repoSC->findBy(['annee' => $as]);
      $sallesAOQP = [];
      $sallesFOQP = [];
      foreach ($sallesClasses as $key => $value) {
        if ($value->getClasse()->getNiveau()->getGroupeFormation()->getId() == 1) {
          # code...
          $sallesAOQP[] = $value->getSalle();
        } 
        elseif ($value->getClasse()->getNiveau()->getGroupeFormation()->getId() == 2) {
          # code...
          $sallesFOQP[] = $value->getSalle();
        }
        
      }
      // dump($sallesClasses, $salles);

      return $this->render('ISIBundle:Disposition:disposition.html.twig', [
        'asec'   => $as,
        'annee'  => $annee,
      'annexe'  => $annexe,
      'salles' => $salles,
        'sallesAOQP' => $sallesAOQP,
        'sallesFOQP' => $sallesFOQP,
        'sallesClasses' => $sallesClasses,
      ]);
    }

    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @param Salle $salle
     * @Route("/disposition-de-classes-en-salle-{as}-{id}", name="disposition_add")
     */
    public function dispositionDeClasseEnSalleAction(Request $request, int $as, Salle $salle): Response
    {
      $em = $this->getDoctrine()->getManager();
      $enregistrement = false;
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoClasse = $em->getRepository('ISIBundle:Classe');
      $repoSC     = $em->getRepository('ISIBundle:SalleClasse');
      $repoGrpF   = $em->getRepository('ISIBundle:Groupeformation');
      $annee      = $repoAnnee->find($as);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      // On sélectionne toutes les classes de l'académie et du centre de formation
      $classesA   = $repoClasse->classesDeLAnnee($as, $annexeId, 'A');
      $classesF   = $repoClasse->classesDeLAnnee($as, $annexeId, 'F');

      //On sélectionne ensuite toutes les classes qui ont déjà été disposées dans des salles
      $sallesClasses = $repoSC->findBy(['annee' => $as]);

      // En pour classe disposition de classe dans une salle, on retire la classe de la liste des classes de l'académie ou du centre de formation
      foreach ($sallesClasses as $key => $value) {
        if(in_array($value->getClasse(), $classesA)){
          unset($classesA[array_search($value->getClasse(), $classesA)]);
        }

        if(in_array($value->getClasse(), $classesF)){
          unset($classesF[array_search($value->getClasse(), $classesF)]);
        }
      }

      if($request->isMethod('post'))
      {
        $data = $request->request->all();
        $classeA = $data['classeA'];
        $classeF = $data['classeF'];
        if(empty($classeA)){
          $this->addFlash('error', 'Vous n\'avez pas sélectionné de classe à l\'academie.');
        }
        else{
          $classe   = $repoClasse->find($classeA);
          $regime   = $repoGrpF->findOneBy(['reference' => 'A']);
          $disposition = new SalleClasse();
          $disposition->setClasse($classe);
          $disposition->setSalle($salle);
          $disposition->setAnnee($annee);
          $disposition->setRegime($regime);
          $disposition->setCreatedBy($this->getUser());
          $disposition->setCreatedAt(new \DateTime());
          $em->persist($disposition);
          $enregistrement = true;
        }

        if(empty($classeF)){
          $this->addFlash('error', 'Vous n\'avez pas sélectionné de classe au centre de formation.');
        }
        else{
          $classe   = $repoClasse->find($classeF);
          $regime   = $repoGrpF->findOneBy(['reference' => 'F']);
          $disposition = new SalleClasse();
          $disposition->setClasse($classe);
          $disposition->setSalle($salle);
          $disposition->setAnnee($annee);
          $disposition->setRegime($regime);
          $disposition->setCreatedBy($this->getUser());
          $disposition->setCreatedAt(new \DateTime());
          $em->persist($disposition);
          $enregistrement = true;
        }
        if($enregistrement == true){
          try{
            $em->flush();
            $this->addFlash('info', 'Disposition de classe enregistrée avec succès.');
            return $this->redirectToRoute('disposition', ['as' => $as, 'annexeId' => $annexeId]);
          } 
          catch(\Doctrine\ORM\ORMException $e){
            $this->addFlash('error', $e->getMessage());
            $this->get('logger')->error($e->getMessage());
          } 
          catch(\Exception $e){
            $this->addFlash('error', $e->getMessage());
          }
          return $this->redirectToRoute('disposition_add', ['as' => $as, 'annexeId' => $annexeId, 'id' => $salle->getId()]);
        }
      }

      return $this->render('ISIBundle:Disposition:disposition-en-classe.html.twig', [
        'asec'     => $as,
        'annee'    => $annee,
        'salle'    => $salle,
      'annexe'  => $annexe,
      'classesA' => $classesA,
        'classesF' => $classesF,
      ]);
    }

    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     * @param Salle $salle
     * @Route("/editer-disposition-de-classes-en-salle-{as}-{id}", name="disposition_edit")
     */
    public function editerDispositionDeClasseEnSalleAction(Request $request, int $as, Salle $salle): Response
    {
      $em = $this->getDoctrine()->getManager();
      $enregistrement = false;
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoClasse = $em->getRepository('ISIBundle:Classe');
      $repoSC     = $em->getRepository('ISIBundle:SalleClasse');
      $repoGrpF   = $em->getRepository('ISIBundle:Groupeformation');
      $annee      = $repoAnnee->find($as);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      // On sélectionne toutes les classes de l'académie et du centre de formation
      $classesA   = $repoClasse->classesDeLAnnee($as, $annexeId, 'A');
      $classesF   = $repoClasse->classesDeLAnnee($as, $annexeId, 'F');

      //On sélectionne ensuite toutes les classes qui ont déjà été disposées dans des salles
      $sallesClasses = $repoSC->findBy(['annee' => $as]);

      $salleClasse     = $repoSC->findBy(['annee' => $as, 'salle' => $salle->getId()]);
      $academie        = $repoSC->findOneBy(['annee' => $as, 'salle' => $salle->getId(), 'regime' => 1]);
      $centreformation = $repoSC->findOneBy(['annee' => $as, 'salle' => $salle->getId(), 'regime' => 2]);
      // dump($academie, $centreformation);

      // En pour classe disposition de classe dans une salle, on retire la classe de la liste des classes de l'académie ou du centre de formation
      foreach ($sallesClasses as $key => $value) {
        if(
            (!empty($academie) && in_array($value->getClasse(), $classesA) && $value->getClasse() != $academie->getClasse()) ||
            (empty($academie) && in_array($value->getClasse(), $classesA))
          )
        {
          unset($classesA[array_search($value->getClasse(), $classesA)]);
        }

        if(
          (!empty($centreformation) && in_array($value->getClasse(), $classesF) && $value->getClasse() != $centreformation->getClasse()) ||
          (empty($centreformation) && in_array($value->getClasse(), $classesF))
        )
        {
          unset($classesF[array_search($value->getClasse(), $classesF)]);
        }
      }

      if($request->isMethod('post'))
      {
        $data = $request->request->all();
        $classeA = $data['classeA'];
        $classeF = $data['classeF'];

        // Dans le cas d'une modification
        if(empty($classeA)){
          $this->addFlash('error', 'Vous n\'avez pas sélectionné de classe à l\'academie.');
        }
        else{
          $classe = $repoClasse->find($classeA);
          if(empty($academie))
          {
            // return new Response("Insertion");
            $regime = $repoGrpF->findOneBy(['reference' => 'A']);
            $disposition = new SalleClasse();
            $disposition->setClasse($classe);
            $disposition->setSalle($salle);
            $disposition->setAnnee($annee);
            $disposition->setRegime($regime);
            $disposition->setCreatedBy($this->getUser());
            $disposition->setCreatedAt(new \DateTime());
            $em->persist($disposition);
          }
          else{
            // return new Response("Modification");
            $academie->setClasse($classe);
            $academie->setUpdatedBy($this->getUser());
            $academie->setUpdatedAt(new \DateTime());
          }
          $enregistrement = true;
        }

        if(empty($classeF)){
          $this->addFlash('error', 'Vous n\'avez pas sélectionné de classe à l\'academie.');
        }
        else{
          $classe = $repoClasse->find($classeF);
          if(empty($centreformation))
          {
            // return new Response("Insertion");
            $regime = $repoGrpF->findOneBy(['reference' => 'F']);
            $disposition = new SalleClasse();
            $disposition->setClasse($classe);
            $disposition->setSalle($salle);
            $disposition->setAnnee($annee);
            $disposition->setRegime($regime);
            $disposition->setCreatedBy($this->getUser());
            $disposition->setCreatedAt(new \DateTime());
            $em->persist($disposition);
          }
          else{
            // return new Response("Modification");
            $centreformation->setClasse($classe);
            $centreformation->setUpdatedBy($this->getUser());
            $centreformation->setUpdatedAt(new \DateTime());
          }
          $enregistrement = true;
        }

        if($enregistrement == true){
          try{
            $em->flush();
            $this->addFlash('info', 'Disposition de classe mise à jour avec succès.');
            return $this->redirectToRoute('disposition', ['as' => $as, 'annexeId' => $annexeId]);
          } 
          catch(\Doctrine\ORM\ORMException $e){
            $this->addFlash('error', $e->getMessage());
            $this->get('logger')->error($e->getMessage());
          } 
          catch(\Exception $e){
            $this->addFlash('error', $e->getMessage());
          }
          return $this->redirectToRoute('disposition_add', ['as' => $as, 'annexeId' => $annexeId, 'id' => $salle->getId()]);
        }
      }
      //>>
      return $this->render('ISIBundle:Disposition:editer-disposition-en-classe.html.twig', [
        'asec'     => $as,
        'annee'    => $annee,
        'salle'    => $salle,
        'classesA' => $classesA,
        'annexe'  => $annexe,
        'classesF' => $classesF,
        'academie' => $academie,
        'centreformation' => $centreformation,
      ]);
    }
}
