<?php

namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EnseignantController extends Controller
{
    public function indexAction(int $as)
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
    
        return $this->render('ENSBundle:Enseignant:index.html.twig', [
            'asec'  => $as,
            'annee' => $annee,      
            'cours' => $cours,      
            'classes' => $classes,      
        ]);
    }
}
