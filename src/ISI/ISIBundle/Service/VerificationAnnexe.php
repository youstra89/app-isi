<?php

namespace ISI\ISIBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;


class VerificationAnnexe
{
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function verif_annexe(Request $request, int $as, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            // return 0;
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        return $annexe;
    }
}