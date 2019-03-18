<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ISIBundle\Repository\AnneescolaireRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AccountController extends Controller
{
  public function indexAction(Request $request, $as)
  {
    $repoAnnee = $this->getDoctrine()->getManager()->getRepository('ISIBundle:Anneescolaire');
    $annee = $repoAnnee->find($as);

    $userType = $request->query->get('userType');

    return $this->render('ISIBundle:Hissaaby:index.html.twig', array(
      'asec'  => $as,
      'annee' => $annee,
      'userType' => $userType
    ));
  }

  public function changerLangueAction(Request $request, $as)
  {

    $locale = $request->getLocale();
    $langue = ($locale == 'fr') ? 'ar' : 'fr' ;
    $request->setLocale($langue);
    $route        = $request->query->get('route');
    $route_params = $request->query->get('route_params');
    $route_params['_locale'] = $langue;
    // return new Response(var_dump($route, $route_params));
    // return new Response($this->generateUrl($route, $route_params));
    return $this->redirect($this->generateUrl($route, $route_params));
  }
}


?>
