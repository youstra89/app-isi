<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Annee;
use ISI\ISIBundle\Repository\AnneeRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DiversController extends Controller
{
  public function indexAction(Request $request, $as)
  {

    $repoAnnee = $this->getDoctrine()->getManager()->getRepository('ISIBundle:Annee');
    $em = $this->getDoctrine()->getManager();
    $annee = $repoAnnee->find($as);
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexeId = $request->get('annexeId');
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
