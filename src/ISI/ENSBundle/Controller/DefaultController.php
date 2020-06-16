<?php

namespace ISI\ENSBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $as = $request->get('as');
        $annexeId = $request->get('annexeId');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        return $this->render('ENSBundle:Default:index.html.twig');
    }
}
