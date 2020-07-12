<?php

namespace ISI\ORGBundle\Controller;

use ISI\ISIBundle\Entity\Annee;
use ISI\ORGBundle\Entity\Cours;

use ISI\ORGBundle\Entity\Mosquee;
use ISI\ORGBundle\Form\CoursType;
use ISI\ORGBundle\Repository\CoursRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use ISI\ISIBundle\Repository\AnneeContratRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CoursController extends Controller
{
  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $jours = [
      1 => 'Lundi',
      2 => 'Mardi',
      3 => 'Mercredi',
      4 => 'Jeudi',
      5 => 'Vendredi',
      6 => 'Samedi',
      7 => 'Dimanche',
    ];

    $heures = [
      0 => 'Après Fadjr',
      1 => 'Après Zhour',
      2 => 'Après Asr',
      3 => 'Entre Maghreb et Icha',
    ];
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoCours   = $em->getRepository('ORGBundle:Cours');
    $annee       = $repoAnnee->find($as);
    $requete_des_cours = "SELECT c.id, c.discipline, c.livre, c.heure, c.jour, c.annee_debut, cm.nom, m.nom, m.quartier FROM cours c JOIN mosquee m ON c.mosquee_id = m.id JOIN commune cm ON m.commune_id = cm.id";
    $statement = $em->getConnection()->prepare($requete_des_cours);
    $statement->execute();
    $cours = $statement->fetchAll();
    dump($cours);

    return $this->render('ORGBundle:Cours:index.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'annexe'    => $annexe,
      'cours' => $cours,
      'jours' => $jours,
      'heures' => $heures,
    ]);
  }


    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     */
    public function addAction(Request $request,$as): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee      = $em->getRepository('ISIBundle:Annee');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
          return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      $annee       = $repoAnnee->find($as);
      $cours = new Cours();
      $form = $this->createForm(CoursType::class, $cours);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $em->persist($cours);
        $em->flush();
        $this->addFlash('info', 'Le cours de <strong>'.$cours->getDiscipline().'</strong> à la mosquée <strong>'.$cours->getMosquee()->getNom().'</strong> a été enregistré avec succès.');
        return $this->redirectToRoute('cours_home', ['as' => $as, 'annexeId' => $annexeId]);
      }
      //>>
      return $this->render('ORGBundle:Cours:cours-add.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'annexe' => $annexe,
        'form' => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Cours $cours
     */
    public function editAction(Request $request, Cours $cours, $as): Response
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
      $annee       = $repoAnnee->find($as);
      $form = $this->createForm(CoursType::class, $cours);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $cours->setUpdatedBy($this->getUser());
        $cours->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('info', 'Les informations sur l\'cours '.$cours->getNom().' '.$cours->getPnom().' ont été mises à jour avec succès.');
        return $this->redirectToRoute('mosquees', ['as' => $as, 'annexeId' => $annexeId]);
      }
      //>>
      return $this->render('ORGBundle:Cours:cours-edit.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'annexe' => $annexe,
        'cours' => $cours,
        'form' => $form->createView(),
      ]);
    }


    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Cours $cours
     */
    public function informationsAction(Request $request, Cours $cours, int $as): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee = $em->getRepository('ISIBundle:Annee');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $annee = $repoAnnee->find($as);
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
          return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      return $this->render('ORGBundle:Cours:cours-info.html.twig', [
        'cours'  => $cours,
        'annee' => $annee,
        'annexe' => $annexe,
      ]);
    }
}
