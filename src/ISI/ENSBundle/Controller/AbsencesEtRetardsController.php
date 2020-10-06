<?php
namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use ISI\ENSBundle\Entity\AnneeContratRetard;
use ISI\ENSBundle\Entity\AnneeContratAbsence;

use ISI\ENSBundle\Form\AnneeContratAbsenceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/** 
 * @Route("/direction-enseignant")
 */
class AbsencesEtRetardsController extends Controller
{
    /**
     * @Security("has_role('ROLE_CONTROLE_ENSEIGNANT')")
     * @Route("/page-d-accueil-des-retards-des-enseignants-{as}-{annexeId}", name="ens_retards_home")
     */
    public function indexRetards(Request $request, $as, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee   = $em->getRepository('ISIBundle:Annee');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        $annee = $repoAnnee->find($as);

        if($request->isMethod('post'))
        {
            $data = $request->request->all();
            $date = $data['date'];
            $periode = $data['periode'];
            if(empty($date) && empty($periode))
            {
                $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez rien saisi.');
                return $this->redirectToRoute('ens_retards_home', ['as' => $as, 'annexeId' => $annexeId]);
            }
            elseif(empty($date))
            {
                $request->getSession()->getFlashBag()->add('error', 'La date est vide.');
                return $this->redirectToRoute('ens_retards_home', ['as' => $as, 'annexeId' => $annexeId]);
            }
            elseif(empty($periode))
            {
                $request->getSession()->getFlashBag()->add('error', 'La période est vide.');
                return $this->redirectToRoute('ens_retards_home', ['as' => $as, 'annexeId' => $annexeId]);
            }
            else{
                // if(gettype($date) == )
                // $date = strtotime($date);
                $date = new \DateTime($date);
                $date = $date->format('Y-m-d');
                // return new Response($date);
                return $this->redirectToRoute('ens_saisi_retards', ['as' => $as, 'annexeId' => $annexeId, 'date' => $date, 'periode' => $periode]);
            }
        }

        // $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);

        return $this->render('ENSBundle:AbsencesEtRetards:index-retards.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'annexe'    => $annexe,
            // 'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CONTROLE_ENSEIGNANT')")
     * @Route("/page-de-saisie-des-retards-des-enseignants-{as}-{periode}-{annexeId}", name="ens_saisi_retards")
     */
    public function saisieDesRetards(Request $request, $as, $periode, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee        = $em->getRepository('ISIBundle:Annee');
        $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        $annee = $repoAnnee->find($as);
        $date = $request->query->get('date');
        $date = new \DateTime($date);
        // $date = $date->format('Y-m-d');
        // $periode = $request->query->get('periode');
        if($request->isMethod('post'))
        {
            $data = $request->request->all();
            $retards = $data['retard'];
            $cpt = 0;
            foreach($retards as $key => $retard)
            {
                $retard = intval($retard);
                if($retard != 0)
                {
                    $cpt++;
                    $contrat = $repoAnneeContrat->find($key);
                    $contrat = $contrat->getContrat();

                    $newRetard = new AnneeContratRetard();
                    $newRetard->setAnnee($annee);
                    $newRetard->setContrat($contrat);
                    $newRetard->setDate($date);
                    $newRetard->setDuree($retard);
                    $newRetard->setPeriode($periode);
                    $newRetard->setCreatedBy($this->getUser());
                    $newRetard->setCreatedAt(new \Datetime());

                    $em->persist($newRetard);
                    // return new Response(var_dump($date));
                }
            }
            if($cpt != 0)
            {
                $request->getSession()->getFlashBag()->add('info', 'Les retards du <strong>'.$date->format('d-m-Y').' '.$periode.'</strong> ont été enregistrés avec succés.');
                $em->flush();
                return $this->redirectToRoute('ens_retards_home', ['as' => $as, 'annexeId' => $annexeId]);
            }
            
        }

        $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);

        return $this->render('ENSBundle:AbsencesEtRetards:saisie-des-retards.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'annexe'   => $annexe,
            'date'     => $date,
            'periode'  => $periode,
            'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CONTROLE_ENSEIGNANT')")
     * @Route("/apercu-des-retards-des-enseignants-enregistres-home-{as}-{annexeId}", name="ens_voir_retards_home")
     */
    public function apercuDesRetardsHome(Request $request, $as, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');
        $repoMois   = $em->getRepository('ISIBundle:Mois');
        $repoRetard = $em->getRepository('ENSBundle:AnneeContratRetard');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        $annee = $repoAnnee->find($as);
        $mois  = $repoMois->findAll();
        $retards = $repoRetard->findBy(['annee' => $as]);

        $RAW_QUERY = 'SELECT DISTINCT(MONTH(a.date)) AS mois FROM annee_contrat_retard a;';
        
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $moisR = $statement->fetchAll();

        return $this->render('ENSBundle:AbsencesEtRetards:retards-enregistres-home.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'annexe'    => $annexe,
            'mois'     => $mois,
            'moisR'    => $moisR,
            // 'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CONTROLE_ENSEIGNANT')")
     * @Route("/apercu-des-retards-des-enseignants-enregistres-pour-un-mois-donnee-{as}-{moisId}-{annexeId}", name="ens_voir_retards_mois")
     */
    public function apercuDesRetardsMois(Request $request, $as, $moisId, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');
        $repoMois   = $em->getRepository('ISIBundle:Mois');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        $annee = $repoAnnee->find($as);
        $mois  = $repoMois->find($moisId);

        $requete_des_absences = "SELECT e.id, e.matricule AS matricule, CONCAT(e.nom_fr, ' ', e.pnom_fr) AS nomFr, CONCAT(e.pnom_ar, ' ', e.nom_ar) AS nomAr, a.date AS date, a.duree AS duree, a.periode AS periode FROM annee_contrat_retard a JOIN contrat c ON a.contrat_id = c.id JOIN enseignant e ON c.enseignant_id = e.id WHERE a.annee_id = :asec AND MONTH(a.date) = :moisId AND a.annee_id = :asec AND e.annexe_id = :annexeId;";
        
        $statement = $em->getConnection()->prepare($requete_des_absences);
        $statement->bindValue('moisId', $moisId);
        $statement->bindValue('annexeId', $annexeId);
        $statement->bindValue('asec', $as);
        $statement->execute();
        $retards = $statement->fetchAll();
        
        
        $requete_des_jours_absences = 'SELECT DISTINCT(a.date) FROM annee_contrat_retard a WHERE MONTH(a.date) = :moisId AND a.annee_id = :asec ORDER BY a.date ASC;';
        $statement = $em->getConnection()->prepare($requete_des_jours_absences);
        $statement->bindValue('moisId', $moisId);
        $statement->bindValue('asec', $as);
        $statement->execute();
        $jours = $statement->fetchAll();
        // return new Response(var_dump($retards));

        return $this->render('ENSBundle:AbsencesEtRetards:retards-mensuels-enregistres.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'annexe'    => $annexe,
            'mois'     => $mois,
            'jours'    => $jours,
            'retards'  => $retards,
            // 'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CONTROLE_ENSEIGNANT')")
     * @Route("/apercu-du-bilan-des-retards-des-enseignants-enregistres-pour-un-mois-donnee-{as}-{moisId}-{annexeId}", name="ens_voir_retards_mois_cumul")
     */
    public function apercuDesRetardsMoisCumul(Request $request, $as, $moisId, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');
        $repoMois   = $em->getRepository('ISIBundle:Mois');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        $annee = $repoAnnee->find($as);
        $mois  = $repoMois->find($moisId);

        $requete_des_absences = "SELECT a.id, e.matricule AS matricule, CONCAT(e.nom_fr, ' ', e.pnom_fr) AS nomFr, CONCAT(e.pnom_ar, ' ', e.nom_ar) AS nomAr, a.date AS date, SUM(a.duree) AS duree FROM annee_contrat_retard a JOIN contrat c ON a.contrat_id = c.id JOIN enseignant e ON c.enseignant_id = e.id WHERE a.annee_id = :asec AND MONTH(a.date) = :moisId AND a.annee_id = :asec AND e.annexe_id = :annexeId GROUP BY a.id;";
        
        $statement = $em->getConnection()->prepare($requete_des_absences);
        $statement->bindValue('moisId', $moisId);
        $statement->bindValue('annexeId', $annexeId);
        $statement->bindValue('asec', $as);
        $statement->execute();
        $retards = $statement->fetchAll();

        return $this->render('ENSBundle:AbsencesEtRetards:retards-mensuels-enregistres-cumul.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'mois'     => $mois,
            'annexe'    => $annexe,
            'retards'  => $retards,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CONTROLE_ENSEIGNANT')")
     * @Route("/page-d-accueil-des-absences-des-enseignants-{as}-{annexeId}", name="ens_absences_home")
     */
    public function indexAbsences(Request $request, $as, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee        = $em->getRepository('ISIBundle:Annee');
        $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        $annee         = $repoAnnee->find($as);
        $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);

        return $this->render('ENSBundle:AbsencesEtRetards:index-absences.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'annexe'    => $annexe,
            'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_CONTROLE_ENSEIGNANT')")
     * @Route("/page-d-accueil-d-absence-d-un-enseignant-{as}-{contratId}-{annexeId}", name="ens_enregistrer_absence")
     */
    public function enregistrerAbsence(Request $request, $as, $contratId, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee   = $em->getRepository('ISIBundle:Annee');
        $repoContrat = $em->getRepository('ENSBundle:Contrat');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        $annee  = $repoAnnee->find($as);
        $contrat = $repoContrat->find($contratId);
        $absence = new AnneeContratAbsence();
        $absence->setAnnee($annee);
        $absence->setContrat($contrat);
        $absence->setCreatedBy($this->getUser());
        $absence->setCreatedAt(new \Datetime());
        $form  = $this->createForm(AnneeContratAbsenceType::class, $absence);
        if($form->handleRequest($request)->isValid())
        {
            $depart = $absence->getDateDepart();
            $retour = $absence->getDateRetour();
            if($depart > $retour)
            {
                $request->getSession()->getFlashBag()->add('error', 'La date de départ doit précéder la date de retour.');
                return $this->redirect($this->generateUrl('ens_enregistrer_absence', ['as' => $as, 'annexeId' => $annexeId, 'contratId' => $contratId]));
            }
            $em->persist($absence);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', 'L\'absence de <strong>'.$contrat->getEnseignant()->getNomFr().' '.$contrat->getEnseignant()->getPnomFr().'</strong> a été enregistrée avec succès.');

            return $this->redirect($this->generateUrl('ens_absences_home', ['as'=> $as, 'annexeId' => $annexeId]));
        }

        return $this->render('ENSBundle:AbsencesEtRetards:enregistrer-absence.html.twig', [
            'asec'    => $as,
            'annee'   => $annee,
            'annexe'    => $annexe,
            'contrat' => $contrat,
            'form'    => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_CONTROLE_ENSEIGNANT')")
     * @Route("/apercu-des-absences-des-enseignants-enregistrees-home-{as}-{annexeId}", name="ens_voir_absences_home")
     */
    public function apercuDesAbsencesHome(Request $request, $as, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee   = $em->getRepository('ISIBundle:Annee');
        $repoAbsence = $em->getRepository('ENSBundle:AnneeContratAbsence');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        $annee = $repoAnnee->find($as);
        $absences = $repoAbsence->findBy(['annee' => $as]);

        $requete_des_absences = "SELECT c.id, CONCAT(e.nom_fr, ' ', e.pnom_fr) AS nomFr, CONCAT(e.pnom_ar, ' ', e.nom_ar) AS nomAr FROM enseignant e JOIN contrat c ON e.id = c.enseignant_id JOIN annee_contrat_absence a ON c.id = a.contrat_id WHERE a.annee_id = :asec AND e.annexe_id = :annexeId GROUP BY c.id;";
        
        $statement = $em->getConnection()->prepare($requete_des_absences);
        $statement->bindValue('asec', $as);
        $statement->bindValue('annexeId', $annexeId);
        $statement->execute();
        $absencesEns = $statement->fetchAll();

        return $this->render('ENSBundle:AbsencesEtRetards:absences-enregistrees-home.html.twig', [
            'asec'        => $as,
            'annee'       => $annee,
            'annexe'      => $annexe,
            'absences'    => $absences,
            'absencesEns' => $absencesEns,
        ]);
    }
}