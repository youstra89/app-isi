<?php
namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ENSBundle\Entity\Enseignant;
use ISI\ENSBundle\Entity\RetardEnseignant;
use ISI\ENSBundle\Entity\Contrat;
use ISI\ENSBundle\Entity\AnneeContrat;
use ISI\ENSBundle\Entity\AnneeContratRetard;
use ISI\ENSBundle\Entity\AnneeContratAbsence;

use ISI\ISIBundle\Repository\AnneescolaireRepository;
use ISI\ENSBundle\Repository\EnseignantRepository;
use ISI\ENSBundle\Repository\ContratRepository;
use ISI\ENSBundle\Repository\AnneeContratRepository;
use ISI\ENSBundle\Repository\AnneeContratRetardRepository;
use ISI\ENSBundle\Repository\AnneeContratAbsenceRepository;

use ISI\ENSBundle\Form\AnneeContratAbsenceType;

class AbsencesEtRetardsController extends Controller
{
    /**
     * @Security("has_role('ROLE_ENSEIGNANT')")
     */
    public function indexRetardsAction(Request $request, $as)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
        $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
        $annee = $repoAnnee->find($as);

        if($request->isMethod('post'))
        {
            $data = $request->request->all();
            $date = $data['date'];
            $periode = $data['periode'];
            if(empty($date) && empty($periode))
            {
                $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez rien saisi.');
                return $this->redirectToRoute('ens_retards_home', ['as' => $as]);
            }
            elseif(empty($date))
            {
                $request->getSession()->getFlashBag()->add('error', 'La date est vide.');
                return $this->redirectToRoute('ens_retards_home', ['as' => $as]);
            }
            elseif(empty($periode))
            {
                $request->getSession()->getFlashBag()->add('error', 'La période est vide.');
                return $this->redirectToRoute('ens_retards_home', ['as' => $as]);
            }
            else{
                // if(gettype($date) == )
                // $date = strtotime($date);
                $date = new \DateTime($date);
                $date = $date->format('Y-m-d');
                // return new Response($date);
                return $this->redirectToRoute('ens_saisi_retards', ['as' => $as, 'date' => $date, 'periode' => $periode]);
            }
        }

        // $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);

        return $this->render('ENSBundle:AbsencesEtRetards:index-retards.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            // 'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ENSEIGNANT')")
     */
    public function saisieDesRetardsAction(Request $request, $as, $periode)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee        = $em->getRepository('ISIBundle:Anneescolaire');
        $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
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
                    $newRetard->setDateSave(new \Datetime());
                    $newRetard->setDateUpdate(new \Datetime());

                    $em->persist($newRetard);
                    // return new Response(var_dump($date));
                }
            }
            if($cpt != 0)
            {
                $request->getSession()->getFlashBag()->add('info', 'Les retards du '.$date->format('d-m-Y').' '.$periode.' ont été enregistrés avec succés.');
                $em->flush();
                return $this->redirectToRoute('ens_retards_home', ['as' => $as]);
            }
            
        }

        $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);

        return $this->render('ENSBundle:AbsencesEtRetards:saisie-des-retards.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'date'     => $date,
            'periode'  => $periode,
            'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ENSEIGNANT')")
     */
    public function apercuDesRetardsHomeAction(Request $request, $as)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
        $repoMois   = $em->getRepository('ISIBundle:Mois');
        $repoRetard = $em->getRepository('ENSBundle:AnneeContratRetard');
        // $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
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
            'mois'     => $mois,
            'moisR'    => $moisR,
            // 'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ENSEIGNANT')")
     */
    public function apercuDesRetardsMoisAction(Request $request, $as, $moisId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
        $repoMois   = $em->getRepository('ISIBundle:Mois');
        $repoRetard = $em->getRepository('ENSBundle:AnneeContratRetard');
        // $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
        $annee = $repoAnnee->find($as);
        $mois  = $repoMois->find($moisId);

        $requete_des_absences = "SELECT e.id, e.matricule AS matricule, CONCAT(e.nom_fr, ' ', e.pnom_fr) AS nomFr, CONCAT(e.pnom_ar, ' ', e.nom_ar) AS nomAr, a.date AS date, a.duree AS duree, a.periode AS periode FROM annee_contrat_retard a JOIN contrat c ON a.contrat = c.id JOIN enseignant e ON c.enseignant = e.id WHERE a.annee = :asec AND MONTH(a.date) = :moisId AND a.annee = :asec;";
        
        $statement = $em->getConnection()->prepare($requete_des_absences);
        $statement->bindValue('moisId', $moisId);
        $statement->bindValue('asec', $as);
        $statement->execute();
        $retards = $statement->fetchAll();
        
        
        $requete_des_jours_absences = 'SELECT DISTINCT(a.date) FROM annee_contrat_retard a WHERE MONTH(a.date) = :moisId AND a.annee = :asec ORDER BY a.date ASC;';
        $statement = $em->getConnection()->prepare($requete_des_jours_absences);
        $statement->bindValue('moisId', $moisId);
        $statement->bindValue('asec', $as);
        $statement->execute();
        $jours = $statement->fetchAll();
        // return new Response(var_dump($retards));

        return $this->render('ENSBundle:AbsencesEtRetards:retards-mensuels-enregistres.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'mois'     => $mois,
            'jours'    => $jours,
            'retards'  => $retards,
            // 'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ENSEIGNANT')")
     */
    public function apercuDesRetardsMoisCumulAction(Request $request, $as, $moisId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Anneescolaire');
        $repoMois   = $em->getRepository('ISIBundle:Mois');
        $repoRetard = $em->getRepository('ENSBundle:AnneeContratRetard');
        // $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
        $annee = $repoAnnee->find($as);
        $mois  = $repoMois->find($moisId);

        $requete_des_absences = "SELECT a.id, e.matricule AS matricule, CONCAT(e.nom_fr, ' ', e.pnom_fr) AS nomFr, CONCAT(e.pnom_ar, ' ', e.nom_ar) AS nomAr, a.date AS date, SUM(a.duree) AS duree FROM annee_contrat_retard a JOIN contrat c ON a.contrat = c.id JOIN enseignant e ON c.enseignant = e.id WHERE a.annee = :asec AND MONTH(a.date) = :moisId AND a.annee = :asec GROUP BY e.id;";
        
        $statement = $em->getConnection()->prepare($requete_des_absences);
        $statement->bindValue('moisId', $moisId);
        $statement->bindValue('asec', $as);
        $statement->execute();
        $retards = $statement->fetchAll();

        return $this->render('ENSBundle:AbsencesEtRetards:retards-mensuels-enregistres-cumul.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'mois'     => $mois,
            // 'jours'    => $jours,
            'retards'  => $retards,
            // 'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ENSEIGNANT')")
     */
    public function indexAbsencesAction(Request $request, $as)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee        = $em->getRepository('ISIBundle:Anneescolaire');
        $repoContrat      = $em->getRepository('ENSBundle:Contrat');
        $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');

        $annee         = $repoAnnee->find($as);
        $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);

        return $this->render('ENSBundle:AbsencesEtRetards:index-absences.html.twig', [
        'asec'     => $as,
        'annee'    => $annee,
        'contrats' => $anneeContrats,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ENSEIGNANT')")
     */
    public function enregistrerAbsenceAction(Request $request, $as, $contratId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
        $repoContrat = $em->getRepository('ENSBundle:Contrat');
        // $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');

        $annee  = $repoAnnee->find($as);
        $contrat = $repoContrat->find($contratId);
        $absence = new AnneeContratAbsence();
        $absence->setAnnee($annee);
        $absence->setContrat($contrat);
        $absence->setDateSave(new \Datetime());
        $absence->setDateUpdate(new \Datetime());
        $form  = $this->createForm(AnneeContratAbsenceType::class, $absence);
        if($form->handleRequest($request)->isValid())
        {
            $depart = $absence->getDateDepart();
            $retour = $absence->getDateRetour();
            if($depart > $retour)
            {
                $request->getSession()->getFlashBag()->add('error', 'La date de départ doit précéder la date de retour.');
                return $this->redirect($this->generateUrl('ens_enregistrer_absence', ['as' => $as, 'contratId' => $contratId]));
            }
            $em->persist($absence);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', 'L\'absence de '.$contrat->getEnseignant()->getNomFr().' '.$contrat->getEnseignant()->getPnomFr().' a été enregistrée avec succès.');

            return $this->redirect($this->generateUrl('ens_absences_home', ['as'=> $as]));
        }

        return $this->render('ENSBundle:AbsencesEtRetards:enregistrer-absence.html.twig', [
            'asec'    => $as,
            'annee'   => $annee,
            'contrat' => $contrat,
            'form'    => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ENSEIGNANT')")
     */
    public function apercuDesAbsencesHomeAction(Request $request, $as)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee   = $em->getRepository('ISIBundle:Anneescolaire');
        $repoMois    = $em->getRepository('ISIBundle:Mois');
        $repoAbsence = $em->getRepository('ENSBundle:AnneeContratAbsence');
        // $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
        $annee = $repoAnnee->find($as);
        $mois  = $repoMois->findAll();
        $absences = $repoAbsence->findBy(['annee' => $as]);

        $requete_des_absences = "SELECT c.id, CONCAT(e.nom_fr, ' ', e.pnom_fr) AS nomFr, CONCAT(e.pnom_ar, ' ', e.nom_ar) AS nomAr FROM enseignant e JOIN contrat c ON e.id = c.enseignant JOIN annee_contrat_absence a ON c.id = a.contrat WHERE a.annee = :asec GROUP BY e.id;";
        
        $statement = $em->getConnection()->prepare($requete_des_absences);
        $statement->bindValue('asec', $as);
        $statement->execute();
        $absencesEns = $statement->fetchAll();

        return $this->render('ENSBundle:AbsencesEtRetards:absences-enregistrees-home.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
            'absences' => $absences,
            'absencesEns' => $absencesEns,
        ]);
    }
}