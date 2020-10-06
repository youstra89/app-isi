<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AccountController extends Controller
{
  /**
   * @Route("/account/{as}/profile-{annexeId}", name="isi_account")
   */
  public function indexAction(Request $request, $as, int $annexeId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $this->getDoctrine()->getManager()->getRepository('ISIBundle:Annee');
    $annee = $repoAnnee->find($as);

    $userType = $request->query->get('userType');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    return $this->render('ISIBundle:Hissaaby:index.html.twig', array(
      'asec'  => $as,
      'annee'   => $annee, 'annexe'   => $annexe,
      'userType' => $userType
    ));
  }

  /**
   * @Route("/changer-de-langue-{as}", name="isi_change_langue")
   */
  public function changerLangueAction(Request $request, $as)
  {

    $locale = $request->getLocale();
    $langue = ($locale == 'fr') ? 'ar' : 'fr' ;
    $request->setLocale($langue);
    $route                   = $request->query->get('route');
    $route_params            = $request->query->get('route_params');
    $route_params['_locale'] = $langue;
    return $this->redirect($this->generateUrl($route, $route_params));
  }
}


?>
