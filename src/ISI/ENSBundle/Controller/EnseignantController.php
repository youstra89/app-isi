<?php

namespace ISI\ENSBundle\Controller;

use ISI\ISIBundle\Entity\Tache;
use ISI\ISIBundle\Entity\Presence;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/enseignant")
 */
class EnseignantController extends Controller
{
    /**
     * @Route("/{as}", name="enseignant_home")
     *
     * @param Request $request
     * @param integer $as
     * @return void
     */
    public function index(Request $request, int $as)
    {
        $em             = $this->getDoctrine()->getManager();
        $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
        $repoAnnee      = $em->getRepository('ISIBundle:Annee');
        $repoCours      = $em->getRepository('ENSBundle:AnneeContratClasse');
        $repoClasse     = $em->getRepository('ISIBundle:Classe');
        $annee          = $repoAnnee->find($as);
        $enseignant     = $repoEnseignant->findOneByUser($this->getUser()->getId());
        $enseignantId   = $enseignant->getId();
        $classes        = $repoClasse->lesClassesDeLEnseignant($as, $enseignantId);
        $cours          = $repoCours->lesCoursDeLEnseignant($as, $enseignantId);
        
        $repoAnnexe     = $em->getRepository('ISIBundle:Annexe');
        $annexeId       = $request->get('annexeId');
        $annexe         = $repoAnnexe->find($annexeId);
        // if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        //     $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        //     return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        // }

        $user = $this->getUser();
        if((int) $user->getChangemdp() === 0){
            $request->getSession()->getFlashBag()->add('info', 'Bienvenu. Il semble que vous soyez à votre première connexion à l\'application. Nous vous invitons à modifier votre mot de passe initial.');
            return $this->redirect($this->generateUrl('isi_account', ['as' => $annee->getId(), 'annexeId' => $annexeId]));
        }

    
        return $this->render('ENSBundle:Enseignant:index.html.twig', [
            'asec'    => $as,
            'annee'   => $annee, 
            'annexe'  => $annexe,      
            'cours'   => $cours,      
            'classes' => $classes,      
        ]);
    }

    /**
     * @Route("appel-cours/{as}/{coursId}", name="faire_appel_cours")
     */
    public function faire_appel_cours(Request $request, int $as, int $coursId)
    {
        $em             = $this->getDoctrine()->getManager();
        $repoEleve      = $em->getRepository('ISIBundle:Eleve');
        $repoAnnee      = $em->getRepository('ISIBundle:Annee');
        $repoPresence   = $em->getRepository('ISIBundle:Presence');
        $repoCours      = $em->getRepository('ENSBundle:AnneeContratClasse');
        $annee          = $repoAnnee->find($as);
        $cours          = $repoCours->find($coursId);
        $classeId       = $cours->getClasse()->getId();
        $repoAnnexe     = $em->getRepository('ISIBundle:Annexe');
        $annexeId       = $request->get('annexeId');
        $annexe         = $repoAnnexe->find($annexeId);
        $enseignantAnnexeId = $cours->getAnneeContrat()->getContrat()->getEnseignant()->getAnnexe()->getId();
        $eleves         = $repoEleve->lesElevesDeLaClasse($as, $enseignantAnnexeId, $classeId);
        $appels         = [];
        $appels_enregistres = $repoPresence->appels_des_eleves_de_la_classe($classeId, (new \DateTime())->format("Y-m-d"));
        foreach ($appels_enregistres as $value) {
            $appels[$value->getEleve()->getId()] = $value;
        }
        $lycee          = false;
        $heure_du_cours = $cours->getHeure();
        $origine        = $request->get('origine');
        $continious     = $this->permettre_appel($heure_du_cours, $lycee, $origine);
        if($continious == false){
            $this->addFlash('error', "Désolé!!! Vous ne pouvez faire l'appel car l'heure du cours sélectionné est soit passée, soit à venir.");
            if('all-courses' == $origine){
                return $this->redirectToRoute('tous_les_cours', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $cours->getClasse()->getNiveau()->getGroupeFormation()->getReference()]);
            }
            elseif('scolarite' == $origine){
                return $this->redirectToRoute('rapport_absence_cours_home', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $cours->getClasse()->getNiveau()->getGroupeFormation()->getReference()]);
            }
            return $this->redirectToRoute('enseignant_home', ['as' => $as, 'annexeId' => $annexeId]);
        }

        if($request->isMethod("post")){
            $data = $request->request->all();
            $date = new \DateTime($data["date"]);
            $numero_du_jour = date("w", strtotime($data["date"]));
            $jour_du_cours = $cours->getJour();
            $nouvel_enregistrement = false;
            // Contrainte : Il faut que le jour sélectionné corresponde au jour du cours
            if(
                $jour_du_cours == 1 and $numero_du_jour == 6 or
                $jour_du_cours == 2 and $numero_du_jour == 0 or
                $jour_du_cours == 3 and $numero_du_jour == 1 or
                $jour_du_cours == 4 and $numero_du_jour == 2 or
                $jour_du_cours == 5 and $numero_du_jour == 3 or
                $jour_du_cours == 6 and $numero_du_jour == 4
            )
            {
                if(isset($data["elevesId"])){
                    $elevesId = $data["elevesId"];
                    foreach ($eleves as $value) {
                        $eleveId  = $value["id"];
                        $eleve    = $repoEleve->find($eleveId);
                        $presence = $repoPresence->findOneBy(["eleve" => $eleveId, "date" => $date]);
                        if(empty($presence)){
                            $nouvel_enregistrement = true;
                            $presence = new Presence();
                            $presence->setEleve($eleve);
                            $presence->setDate($date);
                            $presence->setCreatedAt(new \DateTime());
                            $presence->setCreatedBy($this->getUser());
                            $em->persist($presence);
                        }
                        if(isset($elevesId[$eleveId])){
                            $this->pointer_absence($presence, $cours->getHeure(), true);                            
                        }
                        else{
                            $this->pointer_absence($presence, $cours->getHeure(), false);
                        }
                    }
                }
                else{
                    foreach ($eleves as $value) {
                        $eleveId  = $value["id"];
                        $eleve    = $repoEleve->find($eleveId);
                        $presence = $repoPresence->findOneBy(["eleve" => $eleveId, "date" => $date]);
                        if(empty($presence)){
                            $nouvel_enregistrement = true;
                            $presence = new Presence();
                            $presence->setEleve($eleve);
                            $presence->setDate($date);
                            $presence->setCreatedAt(new \DateTime());
                            $presence->setCreatedBy($this->getUser());
                            $em->persist($presence);
                        }
                        $this->pointer_absence($presence, $cours->getHeure(), false);                            
                    }
                }
                $label = !empty($cours->getClasse()) ? $cours->getClasse()->getLibelleFr() : $cours->getHalaqa()->getLibelle();
                $tache = new Tache();
                $tache->setTitre("Appel");
                $tache->setCours($cours);
                $tache->setDescription("Appel fait par ".$cours->getAnneeContrat()->getContrat()->getEnseignant()->getNom()." (".$this->getUser()->getUsername().") en ".$label." à la ".$cours->getHeure()." heure au cours de ".$cours->getMatiere()->getLibelle().".");
                $tache->setCreatedAt(new \DateTime());
                $tache->setCreatedBy($this->getUser());
                $em->persist($tache);
                try{
                    $em->flush();
                    $this->addFlash('info', 'Appel du cours de <strong>'.$cours->getMatiere()->getLibelle().'</strong> en <strong>'.$label.'</strong> le <strong>'.$cours->jourdecours().'</strong> à la <strong>'.$cours->getHeure().' heure</strong> enregistré avec succès.');
                    if('all-courses' == $origine){
                        return $this->redirectToRoute('tous_les_cours', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $cours->getClasse()->getNiveau()->getGroupeFormation()->getReference()]);
                    }
                    elseif('scolarite' == $origine){
                        return $this->redirectToRoute('rapport_absence_cours_home', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $cours->getClasse()->getNiveau()->getGroupeFormation()->getReference()]);
                    }
                    return $this->redirectToRoute('enseignant_home', ['as' => $as, 'annexeId' => $annexeId]);
                } 
                catch(\Doctrine\ORM\ORMException $e){
                    $this->addFlash('error', $e->getMessage());
                    $this->get('logger')->error($e->getMessage());
                } 
                catch(\Exception $e){
                    $this->addFlash('error', $e->getMessage());
                }
                if('all-courses' == $origine){
                    return $this->redirectToRoute('tous_les_cours', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $cours->getClasse()->getNiveau()->getGroupeFormation()->getReference()]);
                }
                elseif('scolarite' == $origine){
                    return $this->redirectToRoute('rapport_absence_cours_home', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $cours->getClasse()->getNiveau()->getGroupeFormation()->getReference()]);
                }
                return $this->redirectToRoute('faire_appel_cours', ['as' => $as, 'annexeId' => $annexeId, 'coursId' => $coursId]);
            }
            else{
                $jour = $this->dateToFrench(date("l", strtotime($data["date"])));
                $this->addFlash('error', "Désolé!!! Le jour selectionné (<strong>".$jour."</strong>) ne corespond pas au jour du cours (<strong>".$cours->jourdecours()."</strong>).");
                if('all-courses' == $origine){
                    return $this->redirectToRoute('tous_les_cours', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $cours->getClasse()->getNiveau()->getGroupeFormation()->getReference()]);
                }
                elseif('scolarite' == $origine){
                    return $this->redirectToRoute('rapport_absence_cours_home', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $cours->getClasse()->getNiveau()->getGroupeFormation()->getReference()]);
                }
                return $this->redirectToRoute('enseignant_home', ['as' => $as, 'annexeId' => $annexeId]);
            }
        }
        return $this->render('ENSBundle:Enseignant:faire-appel.html.twig', [
            'asec'    => $as,
            'annee'   => $annee, 
            'annexe'  => $annexe,      
            'cours'   => $cours,        
            'appels'  => $appels,
            'eleves'  => $eleves,     
        ]);
    }

    public function permettre_appel($heure_du_cours, $lycee, $origine)
    {
        $heure_actuelle = time();
        $continious     = false;
        if($origine == "scolarite"){
            if(strtotime((new \DateTime())->format("Y-m-d 19:00")) >= $heure_actuelle){
                if (
                    ($heure_du_cours == 1 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 08:10")) and $lycee == false) or
                    ($heure_du_cours == 2 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 08:50")) and $lycee == false) or
                    ($heure_du_cours == 3 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 09:30")) and $lycee == false) or
                    ($heure_du_cours == 4 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 10:10")) and $lycee == false) or
                    ($heure_du_cours == 5 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 10:50")) and $lycee == false) or
                    ($heure_du_cours == 6 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 11:55")) and $lycee == false) or
                    ($heure_du_cours == 7 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 12:35")) and $lycee == false) or
                    ($heure_du_cours == 8 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 13:15")) and $lycee == false) or
                    ($heure_du_cours == 1 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 08:20")) and $lycee ==  true) or
                    ($heure_du_cours == 2 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 09:10")) and $lycee ==  true) or
                    ($heure_du_cours == 3 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 10:00")) and $lycee ==  true) or
                    ($heure_du_cours == 4 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 10:50")) and $lycee ==  true) or
                    ($heure_du_cours == 5 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 12:15")) and $lycee ==  true) or
                    ($heure_du_cours == 6 and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 13:15")) and $lycee ==  true)
                ) {
                    # code...
                }
                else{
                    $continious = true;
                }
            }
            
            return $continious;
        }

        if($lycee == false)
        {
            switch ($heure_du_cours) {
                case 1:
                    if(strtotime((new \DateTime())->format("Y-m-d 07:30")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 08:10"))){
                        $continious = true;
                    }
                    break;
                case 2:
                    if(strtotime((new \DateTime())->format("Y-m-d 08:10")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 08:50"))){
                        $continious = true;
                    }
                    break;
                case 3:
                    if(strtotime((new \DateTime())->format("Y-m-d 08:50")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 09:30"))){
                        $continious = true;
                    }
                    break;
                case 4:
                    if(strtotime((new \DateTime())->format("Y-m-d 09:30")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 10:10"))){
                        $continious = true;
                    }
                    break;
                case 5:
                    if(strtotime((new \DateTime())->format("Y-m-d 10:10")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 10:50"))){
                        $continious = true;
                    }
                    break;
                case 6:
                    if(strtotime((new \DateTime())->format("Y-m-d 11:15")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 11:55"))){
                        $continious = true;
                    }
                    break;
                case 7:
                    if(strtotime((new \DateTime())->format("Y-m-d 11:55")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 12:35"))){
                        $continious = true;
                    }
                    break;
                case 8:
                    if(strtotime((new \DateTime())->format("Y-m-d 12:35")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 13:20"))){
                        $continious = true;
                    }
                    break;
                
                default:
                    break;
            }
        }
        else{
            switch ($heure_du_cours) {
                case 1:
                    if(strtotime((new \DateTime())->format("Y-m-d 07:30")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 08:20"))){
                        $continious = true;
                    }
                    break;
                case 2:
                    if(strtotime((new \DateTime())->format("Y-m-d 08:20")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 09:10"))){
                        $continious = true;
                    }
                    break;
                case 3:
                    if(strtotime((new \DateTime())->format("Y-m-d 09:10")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 10:00"))){
                        $continious = true;
                    }
                    break;
                case 4:
                    if(strtotime((new \DateTime())->format("Y-m-d 10:00")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 10:50"))){
                        $continious = true;
                    }
                    break;
                case 5:
                    if(strtotime((new \DateTime())->format("Y-m-d 11:15")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 12:15"))){
                        $continious = true;
                    }
                    break;
                case 6:
                    if(strtotime((new \DateTime())->format("Y-m-d 12:15")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 13:15"))){
                        $continious = true;
                    }
                    break;
                
                default:
                    break;
            }
        }


        return $continious;
    }

    /**
     * @Route("appel-halaqa/{as}/{coursId}", name="faire_appel_halaqa")
     */
    public function faire_appel_halaqa(Request $request, int $as, int $coursId)
    {
        $em             = $this->getDoctrine()->getManager();
        $repoEleve      = $em->getRepository('ISIBundle:Eleve');
        $repoAnnee      = $em->getRepository('ISIBundle:Annee');
        $repoPresence   = $em->getRepository('ISIBundle:Presence');
        $repoMemoriser  = $em->getRepository('ISIBundle:Memoriser');
        $repoCours      = $em->getRepository('ENSBundle:AnneeContratClasse');
        $annee          = $repoAnnee->find($as);
        $cours          = $repoCours->find($coursId);
        $halaqaId       = $cours->getHalaqa()->getId();
        $repoAnnexe     = $em->getRepository('ISIBundle:Annexe');
        $annexeId       = $request->get('annexeId');
        $annexe         = $repoAnnexe->find($annexeId);
        $memoriser      = $repoMemoriser->findByHalaqa($halaqaId);
        foreach ($memoriser as $key => $memo) {
            $nom[$key]   = $memo->getEleve()->getNomFr();
            $pnom[$key]  = $memo->getEleve()->getPnomFr();
        }
        array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $memoriser);
        $appels         = [];
        $appels_enregistres = $repoPresence->appels_des_eleves_de_la_halaqa($halaqaId, (new \DateTime())->format("Y-m-d"));
        foreach ($appels_enregistres as $value) {
            $appels[$value->getEleve()->getId()] = $value;
        }
        $continious     = false;
        $heure_actuelle = time();
        if(strtotime((new \DateTime())->format("Y-m-d 11:15")) <= $heure_actuelle and $heure_actuelle <= strtotime((new \DateTime())->format("Y-m-d 13:15"))){
            $continious = true;
        }
        if($continious == false){
            $this->addFlash('error', "Désolé!!! Vous ne pouvez faire l'appel car l'heure du cours sélectionné est soit passée, soit à venir.");
            return $this->redirectToRoute('enseignant_home', ['as' => $as, 'annexeId' => $annexeId]);
        }

        if($request->isMethod("post")){
            $data = $request->request->all();
            $date = new \DateTime($data["date"]);
            $numero_du_jour = date("w", strtotime($data["date"]));
            $jour_du_cours = $cours->getJour();
            $nouvel_enregistrement = false;
            // Contrainte : Il faut que le jour sélectionné corresponde au jour du cours
            if(
                $jour_du_cours == 1 and $numero_du_jour == 6 or
                $jour_du_cours == 2 and $numero_du_jour == 0 or
                $jour_du_cours == 3 and $numero_du_jour == 1 or
                $jour_du_cours == 4 and $numero_du_jour == 2 or
                $jour_du_cours == 5 and $numero_du_jour == 3 or
                $jour_du_cours == 6 and $numero_du_jour == 4
            )
            {
                if(isset($data["elevesId"])){
                    $elevesId = $data["elevesId"];      
                    foreach ($memoriser as $value) {
                        $eleveId = $value->getEleve()->getId();
                        $eleve   = $repoEleve->find($eleveId);
                        $presence = $repoPresence->findOneBy(["eleve" => $eleveId, "date" => $date]);
                        if(empty($presence)){
                            $nouvel_enregistrement = true;
                            $presence = new Presence();
                            $presence->setEleve($eleve);
                            $presence->setDate($date);
                            $presence->setCreatedAt(new \DateTime());
                            $presence->setCreatedBy($this->getUser());
                            $em->persist($presence);
                        }
                        if(isset($elevesId[$eleveId])){
                            $this->pointer_absence($presence, $cours->getHeure(), true, true);                            
                        }
                        else{
                            $this->pointer_absence($presence, $cours->getHeure(), false, true);
                        }
                    }
                }
                else{
                    foreach ($memoriser as $value) {
                        $eleveId = $value->getEleve()->getId();
                        $eleve   = $repoEleve->find($eleveId);
                        $presence = $repoPresence->findOneBy(["eleve" => $eleveId, "date" => $date]);
                        if(empty($presence)){
                            $nouvel_enregistrement = true;
                            $presence = new Presence();
                            $presence->setEleve($eleve);
                            $presence->setDate($date);
                            $presence->setCreatedAt(new \DateTime());
                            $presence->setCreatedBy($this->getUser());
                            $em->persist($presence);
                        }
                        $this->pointer_absence($presence, $cours->getHeure(), false, true);                         
                    }
                }

                $label = !empty($cours->getClasse()) ? $cours->getClasse()->getLibelleFr() : $cours->getHalaqa()->getLibelle();
                $tache = new Tache();
                $tache->setTitre("Appel");
                $tache->setCours($cours);
                $tache->setDescription("Appel fait par ".$cours->getAnneeContrat()->getContrat()->getEnseignant()->getNom()." (".$this->getUser()->getUsername().") en ".$label." à la ".$cours->getHeure()." heure au cours de ".$cours->getMatiere()->getLibelle().".");
                $tache->setCreatedAt(new \DateTime());
                $tache->setCreatedBy($this->getUser());
                $em->persist($tache);
                try{
                    $em->flush();
                    $this->addFlash('info', 'Appel du cours de <strong>'.$cours->getMatiere()->getLibelle().'</strong> en <strong>'.$cours->getHalaqa()->getLibelle().'</strong> le <strong>'.$cours->jourdecours().'</strong> à la <strong>'.$cours->getHeure().' heure</strong> enregistré avec succès.');
                    return $this->redirectToRoute('enseignant_home', ['as' => $as, 'annexeId' => $annexeId]);
                } 
                catch(\Doctrine\ORM\ORMException $e){
                    $this->addFlash('error', $e->getMessage());
                    $this->get('logger')->error($e->getMessage());
                } 
                catch(\Exception $e){
                    $this->addFlash('error', $e->getMessage());
                }
                return $this->redirectToRoute('faire_appel_halaqa', ['as' => $as, 'annexeId' => $annexeId, 'coursId' => $coursId]);
            }
            else{
                $jour = $this->dateToFrench(date("l", strtotime($data["date"])));
                $this->addFlash('error', "Désolé!!! Le jour selectionné (<strong>".$jour."</strong>) ne corespond pas au jour du cours (<strong>".$cours->jourdecours()."</strong>).");
                return $this->redirectToRoute('enseignant_home', ['as' => $as, 'annexeId' => $annexeId]);
            }
        }
        return $this->render('ENSBundle:Enseignant:faire-appel-halaqa.html.twig', [
            'asec'      => $as,
            'annee'     => $annee, 
            'annexe'    => $annexe,      
            'cours'     => $cours,        
            'appels'    => $appels,
            'memoriser' => $memoriser,     
        ]);
    }

    public function pointer_absence(Presence $presence, int $heure_du_cours, bool $cocher = true, bool $coran = false)
    {
        switch ($heure_du_cours) {
            case 1:
                $presence->setHeure1($cocher);
                break;
            case 2:
                $presence->setHeure2($cocher);
                break;
            case 3:
                $presence->setHeure3($cocher);
                break;
            case 4:
                $presence->setHeure4($cocher);
                break;
            case 5:
                $presence->setHeure5($cocher);
                break;
            case 6:
                $presence->setHeure6($cocher);
                if($coran == true){
                    $presence->setHeure7($cocher);
                    $presence->setHeure8($cocher);
                }
                break;
            case 7:
                $presence->setHeure7($cocher);
                if($coran == true){
                    $presence->setHeure6($cocher);
                    $presence->setHeure8($cocher);
                }
                break;
            default:
                $presence->setHeure8($cocher);
                if($coran == true){
                    $presence->setHeure6($cocher);
                    $presence->setHeure7($cocher);
                }
                break;
        }
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
