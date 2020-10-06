<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use ISI\ISIBundle\Entity\Programme;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ProgrammeController extends Controller
{
    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/programme-annuel-{as}-{annexeId}", name="programme_annuel")
     */
    public function index(Request $request, int $as, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoProgramme = $em->getRepository('ISIBundle:Programme');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $annee  = $repoAnnee->find($as);
      $programmes = $repoProgramme->programmeDeLAnnee($as);
      
      return $this->render('ISIBundle:Programme:index.html.twig', [
          'asec'       => $as,
          'annee'      => $annee,
          'annexe'     => $annexe,
          'programmes' => $programmes,
      ]);
    }  
  
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/ajout-d-un-nouveau-programme-{as}-{annexeId}", name="ajouter_programme")
     */
    public function ajouter_programme(Request $request, $as, int $annexeId)
    {
      $em         = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $annee      = $repoAnnee->find($as);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe     = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      
      if($request->isMethod("post"))
      {
        $data        = $request->request->all();
        $description = $data["description"];
        $debut       = new \DateTime($data["debut"]);
        $fin         = new \DateTime($data["fin"]);
        $commentaire = isset($data["commentaire"]) ? $data["commentaire"] : null;
        $periodeDeCours = isset($data["periodeDeCours"]) ? true : false;
        // dump($periodeDeCours);
        // die();
        $programme   = new Programme();
        $programme->setDescription($description);
        $programme->setDebut($debut);
        $programme->setIsPeriodeDeCours($periodeDeCours);
        $programme->setAnnee($annee);
        $programme->setFin($fin);
        $programme->setCommentaire($commentaire);
        $programme->setCreatedBy($this->getUser());
        $programme->setCreatedAt(new \Datetime());
        $em->persist($programme);
        try{
          $em->flush();
          $request->getSession()->getFlashBag()->add('info', 'Le programme <strong>'.$programme->getDescription().'</strong> a été enregistré avec succès.');
        } 
        catch(\Doctrine\ORM\ORMException $e){
          $this->addFlash('error', $e->getMessage());
          $this->get('logger')->error($e->getMessage());
        } 
        catch(\Exception $e){
          $this->addFlash('error', $e->getMessage());
        }
        return $this->redirect($this->generateUrl('programme_annuel', ['as' => $as, 'annexeId' => $annexeId]));
      }
      return $this->render('ISIBundle:Programme:ajout-programme.html.twig', [
        'asec'   => $as,
        'annee'  => $annee,
        'annexe' => $annexe,
      ]);
    }

    //Edition de niveau
    /**
     * @Security("has_role('ROLE_DIRECTION_ETUDE')")
     * @Route("/edition-d-un-programme-{as}-{programmeId}-{annexeId}", name="editer_programme")
     */
    public function editer_programme(Request $request, $as, $programmeId, int $annexeId)
    {
      $em            = $this->getDoctrine()->getManager();
      $repoAnnee     = $em->getRepository('ISIBundle:Annee');
      $repoProgramme = $em->getRepository('ISIBundle:Programme');
      $repoAnnexe    = $em->getRepository('ISIBundle:Annexe');
      $annexe        = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $annee  = $repoAnnee->find($as);
      $programme = $repoProgramme->find($programmeId);
      if($request->isMethod("post"))
      {
        $data        = $request->request->all();
        $description = $data["description"];
        $debut       = new \DateTime($data["debut"]);
        $fin         = new \DateTime($data["fin"]);
        $commentaire = isset($data["commentaire"]) ? $data["commentaire"] : null;
        $periodeDeCours = isset($data["periodeDeCours"]) ? true : false;
        $programme->setIsPeriodeDeCours($periodeDeCours);
        $programme->setDescription($description);
        $programme->setDebut($debut);
        $programme->setAnnee($annee);
        $programme->setFin($fin);
        $programme->setCommentaire($commentaire);
        $programme->setUpdatedBy($this->getUser());
        $programme->setUpdatedAt(new \Datetime());
        $em->persist($programme);
        try{
          $em->flush();
          $request->getSession()->getFlashBag()->add('info', 'Le programme <strong>'.$programme->getDescription().'</strong> a été mis à jour avec succès.');
        } 
        catch(\Doctrine\ORM\ORMException $e){
          $this->addFlash('error', $e->getMessage());
          $this->get('logger')->error($e->getMessage());
        } 
        catch(\Exception $e){
          $this->addFlash('error', $e->getMessage());
        }
        return $this->redirect($this->generateUrl('programme_annuel', ['as' => $as, 'annexeId' => $annexeId]));
      }


      return $this->render('ISIBundle:Programme:edit-programme.html.twig', [
        'asec'      => $as,
        'annee'     => $annee,
        'annexe'    => $annexe,
        'programme' => $programme,
      ]);
    }


    /**
     * @Route("/supprimer-programme/{as}/{programmeId-{annexeId}}", name="supprimer_programme", methods="GET|POST", requirements={"programmeId"="\d+"})
     */
    public function supprimer_programme(Request $request, int $programmeId, int $as, int $annexeId)
    {
      $em            = $this->getDoctrine()->getManager();
      $repoAnnexe    = $em->getRepository('ISIBundle:Annexe');
      $annexe        = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      $token = $request->get('_csrf_token');
      if($this->isCsrfTokenValid('supprimer_programme', $token))
      {
        $repoProgramme = $em->getRepository('ISIBundle:Programme');
        $programme = $repoProgramme->find($programmeId);  
        $programme->setIsDeleted(true);
        $programme->setDeletedAt(new \DateTime());
        // Si le $user est un enseignant, il va falloir supprimer également
        try{
          $em->flush();
          $this->addFlash('info', 'L\'utilisateur <strong>'.$programme->getDescription().'</strong> supprimée avec succès.');
        }  
        catch(\Exception $e){
          $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('programme_annuel', ['as' => $as, 'annexeId' => $annexeId]);
      }
    }


    /**
   * @Route("/impression-du-programme-annuel/{as}-{annexeId}", name="impression_programme_annuel")
   */
  public function impression_programme_annuel(Request $request, $as, int $annexeId)
  {
    $em            = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Annee');
    $repoAnnexe    = $em->getRepository('ISIBundle:Annexe');
    $repoProgramme = $em->getRepository('ISIBundle:Programme');
    $programmes    = $repoProgramme->programmeDeLAnnee($as);
    $annexe        = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);

    $snappy = $this->get("knp_snappy.pdf");
    $snappy->setOption("encoding", "UTF-8");
    $snappy->setOption("orientation", "portrait");
    $filename = "programme-annuel-".$annee->getLibelle();

    $html = $this->renderView('ISIBundle:Programme:programme-annuel-pdf.html.twig', [
      'asec'       => $as,
      'annee'      => $annee, 
      'annexe'     => $annexe,  
      'programmes' => $programmes,  
      'server'     => $_SERVER["DOCUMENT_ROOT"],   
    ]);
    $header = $this->renderView( '::header.html.twig' );
    // $footer = $this->renderView( '::footer.html.twig' );

    $options = [
        'header-html' => $header,
        // 'footer-html' => $footer,
    ];

    // Tcpdf
    // $this->returnPDFResponseFromHTML($html);

    return new Response(
        $snappy->getOutputFromHtml($html, $options),
        200,
        [
          'Content-Type'        => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"'
        ]
    );
  }

}


?>