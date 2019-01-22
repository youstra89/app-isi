<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ORGBundle\Entity\Tournee;
use ISI\ORGBundle\Entity\Activite;
use ISI\ORGBundle\Form\ActiviteType;
use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ORGBundle\Entity\TourneeCommune;
use ISI\ORGBundle\Entity\ActiviteTournee;
use ISI\ORGBundle\Entity\ActiviteCommune;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ISI\ISIBundle\Repository\AnneeContratRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ActiviteExterneController extends Controller
{
  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexTourneesAction(Request $request, int $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoTournee = $em->getRepository('ORGBundle:Tournee');
    $annee       = $repoAnnee->find($as);
    $paginator = $this->get('knp_paginator');
    $query = $repoTournee->getTournees();
    $tournees = $paginator->paginate(
        $query,
        $request->query->get('page', 1),
        $request->query->get('limit', 12)
    );


    return $this->render('ORGBundle:Tournee:index.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'tournees' => $tournees,
    ]);
  }

  public function disponibilite(\DateTime $debut, \DateTime $fin)
  {
    $em = $this->getDoctrine()->getManager();
    $repoTournee = $em->getRepository('ORGBundle:Tournee');
    $tournees = $repoTournee->findAll();
    $disponibilite = true;
    foreach ($tournees as $key => $value) {
      if($value->getDebut() < $debut && $value->getFin() > $debut){
        return $disponibilite = false;
      }
      elseif($value->getDebut() < $fin && $value->getFin() > $fin){
        return $disponibilite = false;
      }
      elseif($value->getDebut() > $debut && $value->getDebut() < $fin){
        return $disponibilite = false;
      }
    }
    return $disponibilite;
  }

  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function addTourneesNationalesAction(Request $request, int $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoCommune = $em->getRepository('ORGBundle:Commune');
    $annee       = $repoAnnee->find($as);
    $communes    = $repoCommune->findAll();
    if ($request->isMethod('post')) {
      if($this->isCsrfTokenValid('add', $request->get('_token'))){
        $em = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        $commentaire = $data['commentaire'];
        $destinations = $data['destination'];
        $debut = new \DateTime($data['debut']);
        $fin = new \DateTime($data['fin']);
        $dispo = $this->disponibilite($debut, $fin);
        if($debut > $fin){
          $this->addFlash('error', 'La date de fin ne doit pas être antérieure à la date de départ.');
          return $this->redirectToRoute('tournee.nationale.add', ['as' => $as]);
        }

        // Cette condition va nous permettre de vérifier la disponibilité de la durée choisie
        if($dispo == false){
          $this->addFlash('error', 'La durée saisie est inclue dans la durée de l\'une des tournée déjà enregistrée.');
          return $this->redirectToRoute('tournee.nationale.add', ['as' => $as]);
        }
        $tournee = new Tournee();
        $tournee->setCommentaire($commentaire);
        $tournee->setDebut($debut);
        $tournee->setFin($fin);
        $tournee->setCreatedAt(new \DateTime());
        $em->persist($tournee);
        foreach ($destinations as $key => $value) {
          $commune = $repoCommune->find($value);
          $tourneeCommune = new TourneeCommune();
          $tourneeCommune->setTournee($tournee);
          $tourneeCommune->setCommune($commune);
          $em->persist($tourneeCommune);
          // return new Response(var_dump($tourneeCommune));
        }
        $em->flush();
        $this->addFlash('info', 'La tournée du <strong>'.$debut->format('d-m-Y').'</strong> au <strong>'.$fin->format('d-m-Y').'</strong> a été enregistrée avec succès.');
        return $this->redirectToRoute('destination.tournee.add.activite', ['as' => $as, 'id' => $tournee->getId()]);
      }
    }


    return $this->render('ORGBundle:Tournee:tournee-nationale-add.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'communes' => $communes,
    ]);
  }


  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function editTourneesNationalesAction(Request $request, int $as, int $id)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoCommune = $em->getRepository('ORGBundle:Commune');
    $repoTournee = $em->getRepository('ORGBundle:Tournee');
    $annee       = $repoAnnee->find($as);
    $communes    = $repoCommune->findAll();
    $tournee     = $repoTournee->find($id);
    foreach ($communes as $key => $value) {
      foreach ($tournee->getCommunes() as $keyC => $valueC) {
        if($value->getId() == $valueC->getCommune()->getId())
          unset($communes[$key]);
      }
    }
    if ($request->isMethod('post')) {
      if($this->isCsrfTokenValid('edit', $request->get('_token'))){
        $em = $this->getDoctrine()->getManager();
        $data = $request->request->all();
        $commentaire = $data['commentaire'];
        // return new Response(var_dump($data));
        $debut = new \DateTime($data['debut']);
        $fin = new \DateTime($data['fin']);
        if($debut > $fin){
          $this->addFlash('error', 'La date de fin ne doit pas être antérieure à la date de départ.');
          return $this->redirectToRoute('activite.nationale.add', ['as' => $as]);
        }
        $tournee->setCommentaire($commentaire);
        $tournee->setDebut($debut);
        $tournee->setFin($fin);
        $tournee->setUpdatedAt(new \DateTime());
        if(isset($data['destination'])){
          $destinations = $data['destination'];
          foreach ($destinations as $key => $value) {
            $commune = $repoCommune->find($value);
            $tourneeCommune = new TourneeCommune();
            $tourneeCommune->setTournee($tournee);
            $tourneeCommune->setCommune($commune);
            $em->persist($tourneeCommune);
            // return new Response(var_dump($tourneeCommune));
          }
        }
        $em->flush();
        $this->addFlash('info', 'La tournée du <strong>'.$debut->format('d-m-Y').'</strong> au <strong>'.$fin->format('d-m-Y').'</strong> a été mise à jour avec succès.');
        return $this->redirectToRoute('activites_externes', ['as' => $as]);
      }
    }


    return $this->render('ORGBundle:Tournee:tournee-nationale-edit.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'tournee'  => $tournee,
      'communes' => $communes,
    ]);
  }


  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function destinationPourAddActivitesTourneesNationaleAction(Request $request, int $as, int $id)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoTournee = $em->getRepository('ORGBundle:Tournee');
    $annee       = $repoAnnee->find($as);
    $tournee     = $repoTournee->find($id);

    return $this->render('ORGBundle:Tournee:tournee-nationale-communes.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'tournee' => $tournee,
    ]);
  }


  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function destinationPourRemoveActivitesTourneesNationaleAction(Request $request, int $as, int $id)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoTournee = $em->getRepository('ORGBundle:Tournee');
    $annee       = $repoAnnee->find($as);
    $tournee     = $repoTournee->find($id);

    return $this->render('ORGBundle:Tournee:tournee-nationale-suppression-communes.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'tournee' => $tournee,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function deleteAction(Request $request, int $id, int $as, int $tourneeId)
  {
    $em = $this->getDoctrine()->getManager();
    $commune = $em->getRepository('ORGBundle:TourneeCommune')->find($id);

    if($this->isCsrfTokenValid('delete'.$commune->getId(), $request->get('_token')))
    {
      // return new Response('Suppression');
      $em->remove($commune);
      $em->flush();
      $this->addFlash('info', 'La commune a été supprimée avec succès');
    }
    return $this->redirectToRoute('destination.tournee.remove.activite', ['as' => $as, 'id' => $tourneeId]);

  }


  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function addActivitesTourneesNationaleAction(Request $request, int $as, int $id, int $communeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $repoTournee = $em->getRepository('ORGBundle:Tournee');
    $repoCommune = $em->getRepository('ORGBundle:Commune');
    $annee       = $repoAnnee->find($as);
    $tournee     = $repoTournee->find($id);
    $commune     = $repoCommune->find($communeId);
    $activite = new Activite();
    $form = $this->createForm(ActiviteType::class, $activite);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid())
    {
      $data = $request->request->All();
      $date = new \DateTime($data['date']);
      // On vérifie si la date sélectionnée est inclue dans la durée de la tournée
      if ($date < $tournee->getDebut() || $date > $tournee->getFin()) {
        return new Response('Date incorrecte');
        $this->addFlash('error', 'La date saisie n\'est pas inclue dans la durée de la tournée.');
        return $this->redirectToRoute('activite.tournee.add', ['as' => $as, 'id' => $id, 'communeId' => $communeId]);
      }
      $activite->setCreatedAt(new \DateTime());
      $activite->setDate($date);
      $em->persist($activite);
      // On va aussi enregistrer les tuples des tables intermediares
      $activiteTournee = new ActiviteTournee();
      $activiteTournee->setActivite($activite);
      $activiteTournee->setTournee($tournee);
      $activiteCommune = new ActiviteCommune();
      $activiteCommune->setActivite($activite);
      $activiteCommune->setCommune($commune);
      $em->persist($activiteTournee);
      $em->persist($activiteCommune);
      $em->flush();
      $this->addFlash('info', 'Le <strong>'.$activite->getTypeType().'</strong> du <strong>'.$activite->getDate()->format('d-m-Y').'</strong> pour la tournée du <strong>'.$tournee->getDebut()->format('d-m-Y').'</strong> au <strong>'.$tournee->getFin()->format('d-m-Y').'</strong> à <strong>'.$commune->getNom().'</strong> a été enregistré avec succès.');
      return $this->redirectToRoute('destination.tournee.add.activite', ['as' => $as, 'id' => $id]);

    }

    return $this->render('ORGBundle:Tournee:tournee-nationale-activite-add.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'tournee' => $tournee,
      'commune' => $commune,
      'form'    => $form->createView()
    ]);
  }


  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function addActivitesInternationalesAction(Request $request, int $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $annee       = $repoAnnee->find($as);


    return $this->render('ORGBundle:Tournee:index-activites-externes.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
    ]);
  }



  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function infoTourneeNationaleAction(Request $request, int $as, int $id)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Anneescolaire');
    $repoTournee   = $em->getRepository('ORGBundle:Tournee');
    $repoActiviteC = $em->getRepository('ORGBundle:ActiviteCommune');
    $annee         = $repoAnnee->find($as);
    $tournee       = $repoTournee->find($id);
    $requete_des_activites = "
    SELECT
      c.id AS communeId,
      c.nom AS nomCommune,
      a.id AS activiteId,
      a.type AS activiteType,
      a.date AS activiteDate,
      a.theme AS activiteTheme,
      a.lieu AS activiteLieu,
      a.heure AS activiteHeure
    FROM
      activite a
    JOIN activitecommune ac ON
      ac.activite = a.id
    JOIN commune c ON
      c.id = ac.commune
    JOIN activitetournee AT ON
      a.id = AT.activite
    JOIN tournee t ON AT
      .tournee = t.id
    JOIN tourneecommune tc ON
      tc.tournee = t.id
    WHERE
      tc.commune = c.id AND t.id = :id
    ;";
    $statement = $em->getConnection()->prepare($requete_des_activites);
    $statement->bindValue('id', $id);
    $statement->execute();
    $activites = $statement->fetchAll();

    dump($activites);

    return $this->render('ORGBundle:Tournee:tournee-nationale-info.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'tournee' => $tournee,
      'activites' => $activites,
    ]);
  }
}
