<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ISIBundle\Repository\AnneescolaireRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DiversController extends Controller
{
  public function indexAction(Request $request, $as)
  {
    $repoAnnee = $this->getDoctrine()->getManager()->getRepository('ISIBundle:Anneescolaire');
    $annee = $repoAnnee->find($as);

    $userType = $request->query->get('userType');

    return $this->render('ISIBundle:Divers:index.html.twig', array(
      'asec'     => $as,
      'annee'    => $annee,
      'userType' => $userType
    ));
  }

}


?>
