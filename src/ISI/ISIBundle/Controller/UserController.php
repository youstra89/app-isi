<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserController extends Controller
{ 

  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Annee');
    $annee = $repoAnnee->find($as);
    return $this->render('ISIBundle:Moustamil:index.html.twig', array(
      'asec'  => $as,
      'annee' => $annee
    ));
  }
}

?>