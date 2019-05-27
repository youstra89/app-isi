<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ISI\ISIBundle\Entity\AnneeScolaire;
use ISI\ISIBundle\Entity\Eleve;

use ISI\ISIBundle\Repository\AnneeRepository;
use ISI\ISIBundle\Repository\EleveRepository;

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

        $eleves = $repoEleve->findBy(['regime' => $regime]);
        $elevesInternes = $repoEleve->elevesInternes($as);
        $annee  = $repoAnnee->anneeEnCours();

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