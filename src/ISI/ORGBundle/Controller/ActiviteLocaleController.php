<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ORGBundle\Entity\Activite;
use ISI\ORGBundle\Form\ActiviteType;
use ISI\ISIBundle\Entity\Annee;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ISI\ISIBundle\Repository\AnneeContratRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ActiviteLocaleController extends Controller
{
  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexActivitesLocalesAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoActivite   = $em->getRepository('ORGBundle:Activite');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee       = $repoAnnee->find($as);
    $paginator = $this->get('knp_paginator');
    $query = $repoActivite->getActivites();
    $activites = $paginator->paginate(
        $query,
        $request->query->get('page', 1),
        $request->query->get('limit', 12)

    );

    return $this->render('ORGBundle:Activite:index-activites-locales.html.twig', [
      'asec'  => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'activites' => $activites,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function choixDeLaCommuneActivitesLocalesAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $annee       = $repoAnnee->find($as);
    $repoCommune = $em->getRepository('ORGBundle:Commune');
    $communes    = $repoCommune->findBy(['ville' => 105]);
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    return $this->render('ORGBundle:Activite:choix-commune-activites-locales.html.twig', [
      'asec'  => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'communes' => $communes,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function addActivitesLocalesAction(Request $request, int $as, int $communeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoCommune = $em->getRepository('ORGBundle:Commune');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee       = $repoAnnee->find($as);
    $commune     = $repoCommune->find($communeId);
    $activite = new Activite();
    $form = $this->createForm(ActiviteType::class, $activite);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid())
    {
      $data = $request->request->All();
      $date = new \DateTime($data['date']);
      $activite->setCreatedBy($this->getUser());
      $activite->setCreatedAt(new \DateTime());
      $activite->setDate($date);
      $activite->setCommune($commune);
      $em->persist($activite);
      $em->flush();
      $this->addFlash('info', 'Le <strong>'.$activite->getTypeType().'</strong> du <strong>'.$activite->getDate()->format('d-m-Y').'</strong> a été enregistré avec succès.');
      return $this->redirectToRoute('activites_locales', ['as' => $as]);

    }

    return $this->render('ORGBundle:Activite:activite-add.html.twig', [
      'asec'  => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'commune' => $commune,
      'form'  => $form->createView()
    ]);
  }

  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function editActivitesLocalesAction(Request $request, int $as, int $id)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoActivite = $em->getRepository('ORGBundle:Activite');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee       = $repoAnnee->find($as);
    $activite     = $repoActivite->find($id);
    $form = $this->createForm(ActiviteType::class, $activite);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid())
    {
      $data = $request->request->All();
      $date = new \DateTime($data['date']);
      $activite->setUpdatedBy($this->getUser());
      $activite->setUpdatedAt(new \DateTime());
      $em->persist($activite);
      $em->flush();
      $this->addFlash('info', 'Le <strong>'.$activite->getTypeType().'</strong> du <strong>'.$activite->getDate()->format('d-m-Y').'</strong> a été mise à jour avec succès.');
      return $this->redirectToRoute('activites_locales', ['as' => $as]);

    }

    return $this->render('ORGBundle:Activite:activite-edit.html.twig', [
      'asec'  => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'activite' => $activite,
      'form'  => $form->createView()
    ]);
  }

  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function infoActivitesLocalesAction(Request $request, int $as, int $id)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoActivite = $em->getRepository('ORGBundle:Activite');
    $annee       = $repoAnnee->find($as);
    $activite     = $repoActivite->find($id);
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    return $this->render('ORGBundle:Activite:activite-info.html.twig', [
      'asec'     => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'activite' => $activite,
    ]);
  }

}
