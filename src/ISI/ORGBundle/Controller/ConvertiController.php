<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ORGBundle\Entity\Converti;
use ISI\ORGBundle\Form\ConvertiType;
use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ISIBundle\Repository\AnneeContratRepository;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ConvertiController extends Controller
{
  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Anneescolaire');
    $repoConverti    = $em->getRepository('ORGBundle:Converti');
    $annee       = $repoAnnee->find($as);
    $convertis       = $repoConverti->findAll();


    return $this->render('ORGBundle:Converti:index.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'convertis' => $convertis,
    ]);
  }


    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     */
    public function addAction(Request $request, $as): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee = $em->getRepository('ISIBundle:Anneescolaire');
      $annee     = $repoAnnee->find($as);
      $tourneeId = $request->get('tourneeId');
      $communeId = $request->get('communeId');
      $converti  = new Converti();
      $form = $this->createForm(ConvertiType::class, $converti);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $url = $this->redirectToRoute('home_convertis', ['as' => $as]);
        if(isset($tourneeId)){
          $repoCommune = $em->getRepository('ORGBundle:Commune');
          $repoTournee = $em->getRepository('ORGBundle:Tournee');
          $commune = $repoCommune->find($communeId);
          $tournee = $repoTournee->find($tourneeId);
          $converti->setCommune($commune);
          $converti->setTournee($tournee);
          $url = $this->redirectToRoute('destination.tournee.add.activite', ['as' => $as, 'id' => $tourneeId]);
        }
        $data = $request->request->All();
        $date = new \DateTime($data['date']);
        $converti->setDateConversion($date);
        $em->persist($converti);
        $em->flush();
        $this->addFlash('info', 'Le converti '.$converti->getNom().' '.$converti->getPnom().' a été enregistré avec succès.');
        return $url;
      }
      //>>
      return $this->render('ORGBundle:Converti:converti-add.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'form' => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Converti $converti
     */
    public function editAction(Request $request, Converti $converti, $as): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
      $annee       = $repoAnnee->find($as);
      $form = $this->createForm(ConvertiType::class, $converti);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $converti->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('info', 'Les informations sur l\'converti '.$converti->getNom().' '.$converti->getPnom().' ont été mises à jour avec succès.');
        return $this->redirectToRoute('home_convertis', ['as' => $as]);
      }
      //>>
      return $this->render('ORGBundle:Converti:converti-edit.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'converti' => $converti,
        'form' => $form->createView(),
      ]);
    }


    /**
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Converti $converti
     */
    public function informationsAction(Request $request, Converti $converti, $as): Response
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
      $annee       = $repoAnnee->find($as);
      return $this->render('ORGBundle:Converti:converti-info.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'converti'  => $converti,
      ]);
    }
}
