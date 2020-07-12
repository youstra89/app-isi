<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ORGBundle\Entity\Imam;
use ISI\ORGBundle\Form\ImamType;
use ISI\ISIBundle\Entity\Annee;
use ISI\ISIBundle\Repository\AnneeContratRepository;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ImamController extends Controller
{
  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoImam    = $em->getRepository('ORGBundle:Imam');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }
    $annee       = $repoAnnee->find($as);
    $imams       = $repoImam->findAll();


    return $this->render('ORGBundle:Imam:index.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'annexe' => $annexe,
      'imams' => $imams,
    ]);
  }


    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     */
    public function addAction(Request $request, $as): Response
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
      $imam = new Imam();
      $form = $this->createForm(ImamType::class, $imam);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $em->persist($imam);
        $em->flush();
        $this->addFlash('info', 'L\'imam '.$imam->getNom().' '.$imam->getPnom().' a été enregistré avec succès.');
        return $this->redirectToRoute('home_imams', ['as' => $as, 'annexeId' => $annexeId]);
      }
      return $this->render('ORGBundle:Imam:imam-add.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
      'annexe' => $annexe,
      'form' => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Imam $imam
     */
    public function editAction(Request $request, Imam $imam, $as): Response
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
      $form = $this->createForm(ImamType::class, $imam);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $imam->setUpdatedBy($this->getUser());
        $imam->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('info', 'Les informations sur l\'imam '.$imam->getNom().' '.$imam->getPnom().' ont été mises à jour avec succès.');
        return $this->redirectToRoute('mosquees', ['as' => $as, 'annexeId' => $annexeId]);
      }
      //>>
      return $this->render('ORGBundle:Imam:imam-edit.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
      'annexe' => $annexe,
      'imam' => $imam,
        'form' => $form->createView(),
      ]);
    }


    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Imam $imam
     */
    public function informationsAction(Request $request, Imam $imam, $as): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexeId = $request->get('annexeId');
      $repoAnnee = $em->getRepository('ISIBundle:Annee');

      $annee = $repoAnnee->find($as);
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
          return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      return $this->render('ORGBundle:Imam:imam-info.html.twig', [
        'imam'  => $imam,
        'annee' => $annee,
        'annexe' => $annexe,
      ]);
    }
}
