<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DiversController extends Controller
{
  /**
   * @Route("/saisie-des-notes-{as}-{annexeId}", name="isi_home_note")
   */
  public function indexAction(Request $request, $as, int $annexeId)
  {
    $repoAnnee = $this->getDoctrine()->getManager()->getRepository('ISIBundle:Annee');
    $em = $this->getDoctrine()->getManager();
    $annee = $repoAnnee->find($as);
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $userType = $request->query->get('userType');

    return $this->render('ISIBundle:Divers:index.html.twig', array(
      'asec'     => $as,
      'annexe'  => $annexe,
      'annee'    => $annee,
      'userType' => $userType
    ));
  }

}


?>
