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
    $annee       = $repoAnnee->find($as);
    $imams       = $repoImam->findAll();


    return $this->render('ORGBundle:Imam:index.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
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
      $annee       = $repoAnnee->find($as);
      $imam = new Imam();
      $form = $this->createForm(ImamType::class, $imam);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $em->persist($imam);
        $em->flush();
        $this->addFlash('info', 'L\'imam '.$imam->getNom().' '.$imam->getPnom().' a été enregistré avec succès.');
        return $this->redirectToRoute('home_imams', ['as' => $as]);
      }
      return $this->render('ORGBundle:Imam:imam-add.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
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
      $annee       = $repoAnnee->find($as);
      $form = $this->createForm(ImamType::class, $imam);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $mosquee->setUpdatedBy($this->getUser());
        $mosquee->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('info', 'Les informations sur l\'imam '.$imam->getNom().' '.$imam->getPnom().' ont été mises à jour avec succès.');
        return $this->redirectToRoute('mosquees');
      }
      //>>
      return $this->render('ORGBundle:Imam:imam-edit.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'imam' => $imam,
        'form' => $form->createView(),
      ]);
    }


    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Mosquee $mosquee
     */
    public function informationsAction(Request $request, Mosquee $mosquee): Response
    {
      $em = $this->getDoctrine()->getManager();
      return $this->render('ORGBundle:Mosquee:mosquee-info.html.twig', [
        'mosquee'  => $mosquee,
      ]);
    }
}
