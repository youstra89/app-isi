<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ORGBundle\Entity\Activite;
use ISI\ORGBundle\Form\ActiviteType;
use ISI\ISIBundle\Repository\AnneeContratRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ActiviteLocaleController extends Controller
{
  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexActivitesLocalesAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoActivite   = $em->getRepository('ORGBundle:Activite');
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
      'annee' => $annee,
      'activites' => $activites,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function choixDeLaCommuneActivitesLocalesAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $annee       = $repoAnnee->find($as);
    $repoCommune = $em->getRepository('ORGBundle:Commune');
    $communes    = $repoCommune->findBy(['ville' => 105]);

    return $this->render('ORGBundle:Activite:choix-commune-activites-locales.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'communes' => $communes,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function addActivitesLocalesAction(Request $request, int $as, int $communeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoCommune = $em->getRepository('ORGBundle:Commune');
    $annee       = $repoAnnee->find($as);
    $commune     = $repoCommune->find($communeId);
    $activite = new Activite();
    $form = $this->createForm(ActiviteType::class, $activite);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid())
    {
      $data = $request->request->All();
      $date = new \DateTime($data['date']);
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
      'annee' => $annee,
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
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoActivite = $em->getRepository('ORGBundle:Activite');
    $annee       = $repoAnnee->find($as);
    $activite     = $repoActivite->find($id);
    $form = $this->createForm(ActiviteType::class, $activite);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid())
    {
      $data = $request->request->All();
      $date = new \DateTime($data['date']);
      $activite->setUpdatedAt(new \DateTime());
      $em->persist($activite);
      $em->flush();
      $this->addFlash('info', 'Le <strong>'.$activite->getTypeType().'</strong> du <strong>'.$activite->getDate()->format('d-m-Y').'</strong> a été mise à jour avec succès.');
      return $this->redirectToRoute('activites_locales', ['as' => $as]);

    }

    return $this->render('ORGBundle:Activite:activite-edit.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
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
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoActivite = $em->getRepository('ORGBundle:Activite');
    $annee       = $repoAnnee->find($as);
    $activite     = $repoActivite->find($id);

    return $this->render('ORGBundle:Activite:activite-info.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'activite' => $activite,
    ]);
  }

}