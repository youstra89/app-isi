<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class UserController extends Controller
{ 
  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee = $em->getRepository('ISIBundle:Anneescolaire');
    $annee = $repoAnnee->find($as);

    // $request->query->get('as')
    return $this->render('ISIBundle:Moustamil:index.html.twig', array(
      'asec'  => $as,
      'annee' => $annee
    ));
  }
}

?>
