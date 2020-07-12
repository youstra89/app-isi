<?php

namespace ISI\ORGBundle\Controller;

use ISI\ORGBundle\Entity\Region;
use ISI\ORGBundle\Form\RegionType;
use ISI\ORGBundle\Entity\Ville;
use ISI\ORGBundle\Form\VilleType;
use ISI\ORGBundle\Entity\Commune;
use ISI\ORGBundle\Form\CommuneType;
use ISI\ISIBundle\Entity\Annee;
use ISI\ISIBundle\Repository\AnneeContratRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/localite")
 */
class LocaliteController extends Controller
{
  /**
   * @Route("/index-{as}", name="localite", methods="GET")
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexAction(Request $request, int $as)
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
    return $this->render('ORGBundle:Localite:index.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'annexe' => $annexe,
    ]);
  }

  /**
   * @Route("/region", name="regions", methods="GET")
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function regionsAction(Request $request, $as)
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
    $repoRegion = $em->getRepository(Region::class);
    $regions = $repoRegion->findAll();

    return $this->render('ORGBundle:Localite:regions.html.twig', [
      'regions' => $regions,
      'asec'  => $as,
      'annexe' => $annexe,
      'annee' => $annee,
    ]);
  }

  /**
   * @Route("/region/add", name="region.add", methods="GET|POST")
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function regionAddAction(Request $request, $as)
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
    $region = new Region();
    $form = $this->createForm(RegionType::class, $region);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid())
    {
      $em->persist($region);
      $em->flush();
      $this->addFlash('info', 'La région a bien été enregistrée.');
      return $this->redirectToRoute('region.add', ['as' => $as, 'annexeId' => $annexeId]);
    }
    return $this->render('ORGBundle:Localite:region-add.html.twig', [
      'form' => $form->createView(),
      'asec'  => $as,
      'annexe' => $annexe,
      'annee' => $annee,
    ]);
  }

  /**
   * @Route("/region/edit/{id<\d+>}", name="region.edit", methods="GET|POST")
   * @Security("has_role('ROLE_ORGANISATION')")
   * @param Region $region
   */
  public function regionEditAction(Region $region, Request $request, $as)
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
    $form = $this->createForm(RegionType::class, $region);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid())
    {
      $region->setUpdatedBy($this->getUser());
      $region->setUpdatedAt(new \DateTime());
      $em->flush();
      $this->addFlash('info', 'Mise à jour de la region réussie.');
      return $this->redirectToRoute('regions', ['as' => $as, 'annexeId' => $annexeId]);
    }
    return $this->render('ORGBundle:Localite:region-edit.html.twig', [
      'form' => $form->createView(),
      'asec'  => $as,
      'annexe' => $annexe,
      'annee' => $annee,
    ]);
  }

    /**
     * @Route("/ville", name="villes", methods="GET")
     * @Security("has_role('ROLE_ORGANISATION')")
     */
    public function villesAction(Request $request, $as)
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
      $repoVille = $em->getRepository(Ville::class);
      $villes = $repoVille->findAll();

      return $this->render('ORGBundle:Localite:villes.html.twig', [
        'villes' => $villes,
        'asec'  => $as,
        'annexe' => $annexe,
        'annee' => $annee,
      ]);
    }

    /**
     * @Route("/ville/add", name="ville.add", methods="GET|POST")
     * @Security("has_role('ROLE_ORGANISATION')")
     */
    public function villeAddAction(Request $request, $as)
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
      $ville = new Ville();
      $form = $this->createForm(VilleType::class, $ville);
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid())
      {
        $em->persist($ville);
        $em->flush();
        $this->addFlash('info', 'La ville a bien été enregistrée.');
        return $this->redirectToRoute('ville.add', ['as' => $as, 'annexeId' => $annexeId]);
      }
      return $this->render('ORGBundle:Localite:ville-add.html.twig', [
        'form' => $form->createView(),
        'asec'  => $as,
        'annexe' => $annexe,
        'annee' => $annee,
      ]);
    }


    /**
     * @Route("/ville/edit/{id<\d+>}", name="ville.edit", methods="GET|POST")
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Ville $ville
     */
    public function villeEditAction(Ville $ville, Request $request, $as)
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
      $form = $this->createForm(VilleType::class, $ville);
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid())
      {
        $ville->setUpdatedBy($this->getUser());
        $ville->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('info', 'Mise à jour de la ville réussie.');
        return $this->redirectToRoute('villes', ['as' => $as, 'annexeId' => $annexeId]);
      }
      return $this->render('ORGBundle:Localite:ville-edit.html.twig', [
        'form' => $form->createView(),
        'asec'  => $as,
        'annexe' => $annexe,
        'annee' => $annee,
      ]);
    }



    /**
     * @Route("/commune", name="communes", methods="GET")
     * @Security("has_role('ROLE_ORGANISATION')")
     */
    public function communesAction(Request $request, $as)
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
      $repoCommune = $em->getRepository(Commune::class);
      $communes = $repoCommune->findAll();

      return $this->render('ORGBundle:Localite:communes.html.twig', [
        'communes' => $communes,
        'asec'  => $as,
        'annexe' => $annexe,
        'annee' => $annee,
      ]);
    }

    /**
     * @Route("/commune/add/{villeId<\d+>}", name="commune.add", methods="GET|POST")
     * @Security("has_role('ROLE_ORGANISATION')")
     */
    public function communeAddAction(Request $request, int $villeId, $as)
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
      $repoVille = $em->getRepository(Ville::class);
      $ville = $repoVille->find($villeId);
      $commune = new Commune();
      $commune->setNom($ville->getNom());
      $commune->setVille($ville);
      $form = $this->createForm(CommuneType::class, $commune);
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid())
      {
        $em->persist($commune);
        $em->flush();
        $this->addFlash('info', 'La commune a bien été enregistrée.');
        return $this->redirectToRoute('villes', ['as' => $as, 'annexeId' => $annexeId]);
      }
      return $this->render('Localite/commune-add.html.twig', [
        'form' => $form->createView(),
        'asec'  => $as,
        'annexe' => $annexe,
        'annee' => $annee,
      ]);
    }


    /**
     * @Route("/commune/edit/{id<\d+>}", name="commune.edit", methods="GET|POST")
     * @Security("has_role('ROLE_ORGANISATION')")
     * @param Commune $commune
     */
    public function communeEditAction(Commune $commune, Request $request, $as)
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
      $form = $this->createForm(CommuneType::class, $commune);
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid())
      {
        $commune->setUpdatedBy($this->getUser());
        $commune->setUpdatedAt(new \DateTime());
        $em->flush();
        $this->addFlash('info', 'Mise à jour de la commune réussie.');
        return $this->redirectToRoute('communes', ['as' => $as, 'annexeId' => $annexeId]);
      }
      return $this->render('ORGBundle:Localite:commune-edit.html.twig', [
        'form' => $form->createView(),
        'asec'  => $as,
        'annexe' => $annexe,
        'annee' => $annee,
      ]);
    }
}
