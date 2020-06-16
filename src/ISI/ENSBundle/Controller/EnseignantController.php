<?php

namespace ISI\ENSBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EnseignantController extends Controller
{
    public function indexAction(Request $request, int $as)
    {
        $em             = $this->getDoctrine()->getManager();
        $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
        $repoAnnee      = $em->getRepository('ISIBundle:Annee');
        $repoCours      = $em->getRepository('ENSBundle:AnneeContratClasse');
        $repoClasse     = $em->getRepository('ISIBundle:Classe');
        $annee     = $repoAnnee->find($as);
        $enseignant = $repoEnseignant->findOneByUser($this->getUser()->getId());
        $enseignantId = $enseignant->getId();
        $classes = $repoClasse->lesClassesDeLEnseignant($as, $enseignantId);
        $cours = $repoCours->lesCoursDeLEnseignant($as, $enseignantId);
        // dump($cours);
        $user = $this->getUser();
        if((int) $user->getChangemdp() === 0){
            $request->getSession()->getFlashBag()->add('info', 'Bienvenu. Il semble que vous soyez à votre première connexion à l\'application. Nous vous invitons à modifier votre mot de passe initial.');
            return $this->redirect($this->generateUrl('isi_account', ['as' => $annee->getId()]));
        }

        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexeId = $request->get('annexeId');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
    
        return $this->render('ENSBundle:Enseignant:index.html.twig', [
            'asec'  => $as,
            'annee'   => $annee, 'annexe'   => $annexe,      
            'cours' => $cours,      
            'classes' => $classes,      
        ]);
    }
}
