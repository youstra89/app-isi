<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ISIBundle\Repository\AnneeContratRepository;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
{
  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
    $annee       = $repoAnnee->find($as);


    return $this->render('ORGBundle:Default:index.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
    ]);
  }
}
