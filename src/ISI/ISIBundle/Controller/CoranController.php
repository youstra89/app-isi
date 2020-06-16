<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class CoranController extends Controller
{
    /**
     * @Security("has_role('ROLE_SCOLARITE')")
     */
    public function indexAction(Request $request, $as, $regime)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $repoEleve = $em->getRepository('ISIBundle:Eleve');

        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexeId = $request->get('annexeId');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        $eleves = $repoEleve->findBy(['regime' => $regime]);
        $elevesInternes = $repoEleve->elevesInternes($as);
        $annee = $repoAnnee->find($request->get('as'));

        return $this->render('ISIBundle:Coran:index.html.twig', [
            'asec'   => $as,
            'annee'  => $annee,
            'eleves' => $eleves,
            'regime' => $regime,
            'elevesI' => $elevesInternes
        ]);
    }
}

?>