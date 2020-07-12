<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ORGBundle\Entity\Mosquee;
use ISI\ORGBundle\Form\MosqueeType;
use ISI\ISIBundle\Entity\Annee;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MosqueeController extends Controller
{
  public function verif_annexe(Request $request, int $as, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      // return 0;
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    return $annexe;
  }
  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexAction(Request $request, $as)
  {
    $em          = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoMosquee = $em->getRepository('ORGBundle:Mosquee');
    $annexeId    = $request->get('annexeId');
    $annee       = $repoAnnee->find($as);
    $mosquees    = $repoMosquee->findAll();
    $annexe      = $this->verif_annexe($request, $as, $annexeId);


    return $this->render('ORGBundle:Mosquee:index.html.twig', [
      'asec'  =>$as,
      'annee' => $annee,
      'annexe' => $annexe,
      'mosquees' => $mosquees,
    ]);
  }


    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     */
    public function addAction(Request $request, $as): Response
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
      $mosquee = new Mosquee();
      $form = $this->createForm(MosqueeType::class, $mosquee);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        // $t = json_decode($mosquee->getOptions(), true);
        // return new Response(var_dump([$mosquee->getOptions(), $t]));
        $em->persist($mosquee);
        $em->flush();
        $this->addFlash('info', 'La mosquée '.$mosquee->getNom().' à '.$mosquee->getCommune()->getNom().' a été enregistré avec succès.');
        return $this->redirectToRoute('home_mosquees', ['as' => $as, 'annexeId' => $annexeId]);
      }
      //>>
      return $this->render('ORGBundle:Mosquee:mosquee-add.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'annexe' => $annexe,
        'form'  => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Mosquee $mosquee
     */
    public function editAction(Request $request, Mosquee $mosquee, $as): Response
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
      $form = $this->createForm(MosqueeType::class, $mosquee);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $mosquee->setUpdatedBy($this->getUser());
        $mosquee->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('info', 'Les informations sur la mosquée '.$mosquee->getNom().' à '.$mosquee->getCommune()->getNom().' ont été mises à jour avec succès.');
        return $this->redirectToRoute('home_mosquees', ['as' => $as, 'annexeId' => $annexeId]);
      }
      //>>
      return $this->render('ORGBundle:Mosquee:mosquee-edit.html.twig', [
        'asec'  => $as,
        'annexe' => $annexe,
        'annee' => $annee,
        'form' => $form->createView(),
      ]);
    }


    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Mosquee $mosquee
     */
    public function informationsAction(Request $request, Mosquee $mosquee, $as): Response
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
      return $this->render('ORGBundle:Mosquee:mosquee-info.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'annexe' => $annexe,
        'mosquee'  => $mosquee,
      ]);
    }
}
