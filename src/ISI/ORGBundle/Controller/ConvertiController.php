<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ORGBundle\Entity\Converti;
use ISI\ORGBundle\Form\ConvertiType;
use ISI\ISIBundle\Entity\Annee;
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
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
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
      $repoAnnee = $em->getRepository('ISIBundle:Annee');
      $annee     = $repoAnnee->find($as);
      $tourneeId = $request->get('tourneeId');
      $communeId = $request->get('communeId');
      $paysId    = $request->get('paysId');
      $converti  = new Converti();
      $form = $this->createForm(ConvertiType::class, $converti);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $url = $this->redirectToRoute('home_convertis', ['as' => $as]);
        $repoTournee = $em->getRepository('ORGBundle:Tournee');
        $tournee = $repoTournee->find($tourneeId);
        $converti->setTournee($tournee);
        if(isset($tourneeId) && isset($communeId)){
          $repoCommune = $em->getRepository('ORGBundle:Commune');
          $commune     = $repoCommune->find($communeId);
          $converti->setCommune($commune);
          $url = $this->redirectToRoute('destination.tournee.add.activite', ['as' => $as, 'id' => $tourneeId]);
        }
        elseif(isset($tourneeId) && isset($paysId)){
          $repoPays = $em->getRepository('ORGBundle:Pays');
          $pays     = $repoPays->find($paysId);
          $converti->setPays($pays);
          $url = $this->redirectToRoute('destination.tournee.internationale.add.activite', ['as' => $as, 'id' => $tourneeId]);
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
      $repoAnnee   = $em->getRepository('ISIBundle:Annee');
      $annee       = $repoAnnee->find($as);
      $form = $this->createForm(ConvertiType::class, $converti);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid())
      {
        $url = $this->redirectToRoute('home_convertis', ['as' => $as]);
        $tourneeId = $request->get('tourneeId');
        $nationale = $request->get('nationale');
        if(isset($tourneeId) and $nationale == 1)
          $url = $this->redirectToRoute('tournee.info', ['as' => $as, 'id' => $tourneeId]);
        elseif(isset($tourneeId) and $nationale == 0)
          $url = $this->redirectToRoute('tournee.internationale.info', ['as' => $as, 'id' => $tourneeId]);
        $converti->setUpdatedBy($this->getUser());
        $converti->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('info', 'Les informations sur l\'converti '.$converti->getNom().' '.$converti->getPnom().' ont été mises à jour avec succès.');
        return $url;
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
      $repoAnnee   = $em->getRepository('ISIBundle:Annee');
      $annee       = $repoAnnee->find($as);
      return $this->render('ORGBundle:Converti:converti-info.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'converti'  => $converti,
      ]);
    }
}
