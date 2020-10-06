<?php

namespace ISI\ENSBundle\Controller;

use ISI\ISIBundle\Entity\Tache;
use ISI\ISIBundle\Entity\Presence;
use ISI\ISIBundle\Entity\ParticipationExamen;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/programme-examen-enseignant")
 */
class ProgrammeExamenEnseignantController extends Controller
{
    /**
     * @Route("/examens-{as}-{annexeId}", name="accueil_examen_enseignant")
     */
    public function accueil_examen_enseignant(Request $request, int $as, int $annexeId)
    {
        $em               = $this->getDoctrine()->getManager();
        $repoAnnee        = $em->getRepository('ISIBundle:Annee');
        $repoExamen       = $em->getRepository('ISIBundle:Examen');
        $annee            = $repoAnnee->find($as);
        $examens          = $repoExamen->lesExamensDeLAnnee($as);
        
        $repoAnnexe     = $em->getRepository('ISIBundle:Annexe');
        $annexe         = $repoAnnexe->find($annexeId);

    
        return $this->render('ENSBundle:Enseignant:examens.html.twig', [
            'asec'    => $as,
            'annee'   => $annee, 
            'annexe'  => $annexe,      
            'examens' => $examens,        
        ]);
    }

    /**
     * @Route("/programme-surveillance-{as}-{regime}-{examenId}-{annexeId}", name="programme_surveillance_enseignant")
     */
    public function programme_surveillance_enseignant(int $as, string $regime, int $examenId, int $annexeId)
    {
        $em                        = $this->getDoctrine()->getManager();
        $repoEnseignant            = $em->getRepository('ENSBundle:Enseignant');
        $repoAnnee                 = $em->getRepository('ISIBundle:Annee');
        $repoExamen                = $em->getRepository('ISIBundle:Examen');
        $repoSurveillance          = $em->getRepository('ISIBundle:Surveillance');
        $repoAnnexe                = $em->getRepository('ISIBundle:Annexe');
        $repoProgrammeExamenNiveau = $em->getRepository('ISIBundle:ProgrammeExamenNiveau');
        
        $annee            = $repoAnnee->find($as);
        $enseignant       = $repoEnseignant->findOneByUser($this->getUser()->getId());
        $enseignantId     = $enseignant->getId();
        $examen           = $repoExamen->find($examenId);
        $surveillances    = $repoSurveillance->lesCoursDeLEnseignant($examenId, $regime, $enseignantId);
        $annexe           = $repoAnnexe->find($annexeId);
        $programmes       = $repoProgrammeExamenNiveau->programme_examen_d_un_enseignant($enseignantId, $examenId, $enseignant->getAnnexe()->getId());
        $dates = [];
        foreach ($programmes as $value) {
            $jour = $this->dateToFrench(date("l", strtotime($value->getDate()->format("d-m-Y"))));
            $dates[$value->getDate()->format("d-m-Y")] = $jour;
        }
        $dates = array_unique($dates);
        // dump($dates, $programmes);
    
        return $this->render('ENSBundle:Enseignant:programme-surveillance.html.twig', [
            'asec'          => $as,
            'annee'         => $annee, 
            'annexe'        => $annexe,      
            'examen'        => $examen,        
            'regime'        => $regime,        
            'dates'         => $dates,        
            'programmes'    => $programmes,        
            'enseignant'    => $enseignant,        
            'surveillances' => $surveillances,        
        ]);
    }


    /**
     * @Route("/appel-lors-d-une-epreuve-{as}-{regime}-{examenId}-{surveillanceId}-{annexeId}", name="appel_lors_d_une_epreuve_a_l_examen")
     */
    public function appel_lors_d_une_epreuve_a_l_examen(Request $request, int $as, string $regime, int $surveillanceId, int $examenId, int $annexeId)
    {
        $em               = $this->getDoctrine()->getManager();
        $repoEnseignant   = $em->getRepository('ENSBundle:Enseignant');
        $repoAnnee        = $em->getRepository('ISIBundle:Annee');
        $repoEleve        = $em->getRepository('ISIBundle:Eleve');
        $repoExamen       = $em->getRepository('ISIBundle:Examen');
        $repoSurveillance = $em->getRepository('ISIBundle:Surveillance');
        $repoAnnexe       = $em->getRepository('ISIBundle:Annexe');
        $repoEleveGroupe  = $em->getRepository('ISIBundle:EleveGroupeComposition');
        $repoParticipationExamen  = $em->getRepository('ISIBundle:ParticipationExamen');

        $surveillance = $repoSurveillance->find($surveillanceId);
        $groupeId     = $surveillance->getProgrammeGroupeComposition()->getGroupeComposition()->getId();
        $label        = $surveillance->getProgrammeGroupeComposition()->getGroupeComposition()->getClasse()->getLibelleFr()." ".$surveillance->getProgrammeGroupeComposition()->getGroupeComposition()->getLibelle();
        $matiere      = $surveillance->getProgrammeGroupeComposition()->getProgrammeExamenNiveau()->getMatiere();
        $dateEpreuve  = $surveillance->getProgrammeGroupeComposition()->getProgrammeExamenNiveau()->getDate()->format("Y-m-d");
        $heureDebut   = $surveillance->getProgrammeGroupeComposition()->getProgrammeExamenNiveau()->getHeureDebut()->format("H:i");
        $heureFin     = $surveillance->getProgrammeGroupeComposition()->getProgrammeExamenNiveau()->getHeureFin()->format("H:i");
        $matiereId    = $matiere->getId();
        $elevesGroupe = $repoEleveGroupe->findByGroupeComposition($groupeId);
        $annee        = $repoAnnee->find($as);
        $enseignant   = $repoEnseignant->findOneByUser($this->getUser()->getId());
        $examen       = $repoExamen->find($examenId);
        $annexe       = $repoAnnexe->find($annexeId);
        $appels_enregistres = $repoParticipationExamen->appels_des_eleves_du_groupe($groupeId, $examenId, $matiereId);
        $appels       = [];
        $heure_actuelle = time();
        $heure_actuelle = (new \DateTime())->format("H:i");
        $date_actuelle = new \DateTime();

        if($date_actuelle->format("Y-m-d") != $dateEpreuve){
            $this->addFlash("error", "Impossible de continuer. Car le jour de la composition n'est pas encore arrivé ou est passé.");
            return $this->redirectToRoute('programme_surveillance_enseignant', ["as" => $as, "annexeId" => $annexeId, "regime" => $regime, "examenId" => $examenId]);
        }
        else{
            $heureDebut     = strtotime((new \DateTime($heureDebut))->format("Y-m-d H:i"));
            $heure_actuelle = strtotime((new \DateTime($heure_actuelle))->format("Y-m-d H:i"));
            $heureFin       = strtotime((new \DateTime($heureFin))->format("Y-m-d H:i"));
            if($heureDebut <= $heure_actuelle && $heure_actuelle <= $heureFin){
                $continious = true;
            }
            else{
                $this->addFlash("error", "Impossible de continuer. Il n'est pas encore l'heure de faire l'appel.");
                return $this->redirectToRoute('programme_surveillance_enseignant', ["as" => $as, "annexeId" => $annexeId, "regime" => $regime, "examenId" => $examenId]);
            }
        }


        foreach ($appels_enregistres as $value) {
            $appels[$value->getEleve()->getId()] = $value;
        }

        if($request->isMethod("post")){
            $data                  = $request->request->all();
            $elevesId = $data["elevesId"];
            foreach ($elevesGroupe as $value) {
                $eleve    = $value->getEleve();
                $eleveId  = $eleve->getId();
                $participation = $repoParticipationExamen->findOneBy(["eleve" => $eleveId, "examen" => $examenId, "matiere"  => $matiereId]);
                if(empty($participation)){
                    $participation = new ParticipationExamen();
                    $participation->setEleve($eleve);
                    $participation->setExamen($examen);
                    $participation->setMatiere($matiere);
                    $participation->setCreatedAt(new \DateTime());
                    $participation->setCreatedBy($this->getUser());
                    $em->persist($participation);
                }
                else{
                    $participation->setUpdatedAt(new \DateTime());
                    $participation->setUpdatedBy($this->getUser());
                }
                isset($elevesId[$eleveId]) ? $participation->setParticipation(false) :  $participation->setParticipation(true);
                // $tab[] = $participation;
            }
            // dump($tab);
            // die();

            try{
                $em->flush();
                $this->addFlash('info', 'Appel de l\'épreuve de <strong>'.$matiere->getLibelle().'</strong> en <strong>'.$label.'</strong> a été enregistré avec succès.');
            } 
            catch(\Doctrine\ORM\ORMException $e){
                $this->addFlash('error', $e->getMessage());
                $this->get('logger')->error($e->getMessage());
            } 
            catch(\Exception $e){
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('programme_surveillance_enseignant', ["as" => $as, "annexeId" => $annexeId, "regime" => $regime, "examenId" => $examenId]);

        }

        // dump($appels_enregistres);
        return $this->render('ENSBundle:Enseignant:appel-examen.html.twig', [
            'asec'         => $as,
            'annee'        => $annee, 
            'annexe'       => $annexe,      
            'regime'       => $regime,             
            'examen'       => $examen,             
            'enseignant'   => $enseignant,        
            'elevesGroupe' => $elevesGroupe,        
            'surveillance' => $surveillance,        
            'appels'       => $appels,        
        ]);
    }


    public function dateToFrench($english) 
    {
        switch ($english) {
            case 'Monday':
                $jour = 'Lundi';
                break;
            
            case 'Tuesday':
                $jour = 'Mardi';
                break;
            case 'Wednesday':
                $jour = 'Mercredi';
                break;
            case 'Thursday':
                $jour = 'Jeudi';
                break;
            case 'Friday':
                $jour = 'Vendredi';
                break;
            case 'Saturday':
                $jour = 'Samedi';
                break;
            default:
                $jour = 'Dimanche';
                break;
        }
        return $jour;
    }

}
