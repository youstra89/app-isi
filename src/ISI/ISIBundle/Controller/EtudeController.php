<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Enseignement;
use ISI\ISIBundle\Entity\Matiere;
use ISI\ISIBundle\Entity\Niveau;
use ISI\ISIBundle\Entity\Examen;
use ISI\ISIBundle\Entity\Livre;

use ISI\ISIBundle\Form\EnseignementType;
use ISI\ISIBundle\Form\MatiereType;
use ISI\ISIBundle\Form\NiveauType;
use ISI\ISIBundle\Form\ExamenType;
use ISI\ISIBundle\Form\LivreType;
use ISI\ISIBundle\Form\LivreEditionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class EtudeController extends Controller
{
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/etude-{as}-{annexeId}", name="etude_home")
     */
    public function index(Request $request, $as, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
          return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        $annee = $repoAnnee->find($as);

        return $this->render('ISIBundle:Etude:index.html.twig', [
          'asec' => $as,
          'annexe'  => $annexe,
          'annee' => $annee,
        ]);
    }

    // Page d'accueil des progressions de l'année
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/accueil-des-progressions-de-l-annee-{as}-{regime}-{annexeId}", name="etude_progression_home")
     */
    public function progressionAccueil(Request $request, $as, $regime, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $repoNiveau = $em->getRepository('ISIBundle:Niveau');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
          return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        $annee = $repoAnnee->find($as);
        $niveaux = $repoNiveau->niveauxDuGroupe($regime);

        return $this->render('ISIBundle:Etude:progression-home.html.twig', [
            'asec' => $as,
            'annee' => $annee,
            'annexe'  => $annexe,
            'regime' => $regime,
            'niveaux' => $niveaux,
        ]);
    }

    //Page d'accueil pour la liaison entre les classes et les matières pour une année scolaire donnée
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/les-matieres-des-differents-niveaux-{as}-{regime}-{annexeId}", name="etude_niveaux_matieres")
     */
    public function niveauxMatieres(Request $request, $as, $regime, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee   = $em->getRepository('ISIBundle:Annee');
        $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
          return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        $listNiveaux = $repoNiveau->findAll();
        $annee  = $repoAnnee->find($as);
        // $listMatieresNiveaux = $repoMatiere->listMatieresNiveaux($as );

        return $this->render('ISIBundle:Etude:niveaux-matieres-home.html.twig', [
            'asec'    => $as,
            'regime'  => $regime,
            'annee'   => $annee,
            'annexe'  => $annexe,
            'niveaux' => $listNiveaux
        ]);
    }

    //Pour lier effectivement les matières aux nineaux, ou disons au niveau sélectionné
    /**
     * @Security("has_role('ROLE_DIRECTION_ETUDE')")
     * @Route("/lier-les-matieres-aux-niveaux-de-formation-{as}-{regime}-{niveauId}-{annexeId}", name="etude_lier_niveaux_matieres")
     */
    public function lierNiveauxMatieres(Request $request, $as, $niveauId, $regime, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');
        $repoNiveau = $em->getRepository('ISIBundle:Niveau');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
          return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        // Pour le niveau sélectionné, s'il y a des élèves déjà inscrits dans une classe, on ne permettra pas l'ajout
        // d'une autre matière au niveau
        $annee = $repoAnnee->find($as);

        /**
         * Quand l'année scolaire est finie, on doit plus faire des
         * mofications, des mises à jour
         **/
        if($annee->getAchevee() == TRUE)
        {
          $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter d\'autres matières car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
          return $this->redirect($this->generateUrl('etude_niveaux_matieres', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $regime]));
        }

        $niveau = $repoNiveau->find($niveauId);

        $enseignement = new Enseignement;

        //Remplissage des champs niveau et annee
        $enseignement->setNiveau($niveau);
        $enseignement->setAnnee($annee);
        $enseignement->setCreatedBy($this->getUser());
        $enseignement->setCreatedAt(new \Datetime());

        $form = $this->createForm(EnseignementType::class, $enseignement, [
            'as'             => $as,
            'niveauId'       => $niveauId,
            'niveau'         => $niveau,
            'entity_manager' => $em
        ]);

        if($form->handleRequest($request)->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($enseignement); 
            try{
              $em->flush();
              $this->addFlash('info', 'La matière '.$enseignement->getMatiere()->getLibelle().' a été enregistrée pour le niveau '.$enseignement->getNiveau()->getLibelleAr());
              return $this->redirect($this->generateUrl('etude_lier_niveaux_matieres', [
                  'as'     => $as,
                  'regime' => $regime, 'annexeId' => $annexeId,
                  'niveauId' => $niveauId,
              ]));
            } 
            catch(\Doctrine\ORM\ORMException $e){
              $this->addFlash('error', $e->getMessage());
              $this->get('logger')->error($e->getMessage());
            } 
            catch(\Exception $e){
              $this->addFlash('error', $e->getMessage());
            }

        }

        return $this->render('ISIBundle:Etude:lier-les-matieres-aux-niveaux.html.twig', [
            'asec'     => $as,
            'niveau'   => $niveau,
            'regime'   => $regime,
            'annexe'  => $annexe,
            'annee'    => $annee,
            'form'     => $form->createView()
        ]);
    }


    /**
     * @Security("has_role('ROLE_DIRECTION_ETUDE')")
     * @Route("/editer-liaison-des-matieres-aux-niveaux-de-formation/{as}/{regime}/{enseignementId}-{annexeId}", name="editer_liaison_niveaux_matieres")
     */
    public function editerLiaisonNiveauxMatieres(Request $request, $as, $regime, $enseignementId, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');
        $repoNiveau = $em->getRepository('ISIBundle:Niveau');
        $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
          return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        $annee = $repoAnnee->find($as);

        /**
         * Quand l'année scolaire est finie, on doit plus faire des
         * mofications, des mises à jour
         **/
        if($annee->getAchevee() == TRUE)
        {
          $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter d\'autres matières car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
          return $this->redirect($this->generateUrl('etude_niveaux_matieres', ['as' => $as, 'annexeId' => $annexeId, 'regime' => $regime]));
        }
        
        $enseignement = $repoEnseignement->find($enseignementId);
        $niveauId = $enseignement->getNiveau()->getId();
        $niveau = $repoNiveau->find($niveauId);
        
        $form = $this->createForm(EnseignementType::class, $enseignement, [
            'as'             => $as,
            'niveauId'       => $niveauId,
            'niveau'         => $niveau,
            'entity_manager' => $em
        ]);

        if($form->handleRequest($request)->isValid())
        {
          $enseignement->setUpdatedBy($this->getUser());
          $enseignement->setUpdatedAt(new \Datetime());
          $em = $this->getDoctrine()->getManager();
          $em->persist($enseignement); 
          try{
            $em->flush();
            $this->addFlash('info', 'La matière '.$enseignement->getMatiere()->getLibelle().' au niveau '.$enseignement->getNiveau()->getLibelleAr().' a été mise à jour avec succès');
            return $this->redirect($this->generateUrl('etude_liste_niveaux_matieres', [
                'as'     => $as, 'annexeId' => $annexeId,
                'regime' => $regime,
                'niveauId' => $niveauId,
            ]));
          } 
          catch(\Doctrine\ORM\ORMException $e){
            $this->addFlash('error', $e->getMessage());
            $this->get('logger')->error($e->getMessage());
          } 
          catch(\Exception $e){
            $this->addFlash('error', $e->getMessage());
          }
        }

        return $this->render('ISIBundle:Etude:editer-liaison-des-matieres-aux-niveaux.html.twig', [
            'asec'         => $as,
            'niveau'       => $niveau,
            'regime'       => $regime,
            'annee'        => $annee,
            'annexe'       => $annexe,
            'enseignement' => $enseignement,
            'form'         => $form->createView()
        ]);
    }

    // Pour voir la liste des matières d'un niveau de formation donné
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/voir-liste-des-matieres-du-niveaux-{as}-{niveauId}-{regime}-{annexeId}", name="etude_liste_niveaux_matieres")
     */
    public function listeMatieresNiveaux(Request $request, $as, $regime, $niveauId, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee   = $em->getRepository('ISIBundle:Annee');
      $repoEns     = $em->getRepository('ISIBundle:Enseignement');
      $repoMatiere = $em->getRepository('ISIBundle:Matiere');
      $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $matieres = $repoMatiere->lesMatieresDuNiveau($as, $niveauId);
      $matieresEnfants = $repoMatiere->lesMatieresEnfantsDuNiveau($as, $niveauId);
      $niveau   = $repoNiveau->find($niveauId);
      $annee    = $repoAnnee->find($as);
      $ens      = $repoEns->findBy(['annee' => $as, 'niveau' => $niveauId]);

      return $this->render('ISIBundle:Etude:liste-des-matieres-du-niveau.html.twig', [
        'matieresEnfants' => $matieresEnfants,
        'matieres' => $matieres,
        'asec'     => $as,
        'regime'   => $regime,
        'annee'    => $annee,
        'niveau'   => $niveau,
            'annexe'  => $annexe,
            'ens'      => $ens,
      ]);
    }

    // Accueil des paramètres
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/parametres-{as}-{annexeId}", name="etude_parametres")
     */
    public function parametres(Request $request, $as, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
          return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        $annee = $repoAnnee->find($as);
        return $this->render('ISIBundle:Etude:parametres.html.twig', [
            'asec'     => $as,
            'annexe'  => $annexe,
            'annee'    => $annee,
        ]);
    }

    // Page de gestion des matières
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/gestion-des-matieres/{as}-{annexeId}", name="etude_gestion_matieres")
     */
    public function lesMatieres(Request $request, int $as, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee   = $em->getRepository('ISIBundle:Annee');
      $repoMatiere = $em->getRepository('ISIBundle:Matiere');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $annee  = $repoAnnee->find($as);
      $listeMatieres = $repoMatiere->findAll();

      return $this->render('ISIBundle:Etude:gestion-des-matieres.html.twig', array(
        'asec'     => $as,
        'annee'    => $annee,
            'annexe'  => $annexe,
            'matieres' => $listeMatieres
      ));
    }

    // Ajout de nouvelle matières
    /**
     * @Security("has_role('ROLE_DIRECTION_ETUDE')")
     * @Route("/ajout-de-matiere/{as}-{annexeId}", name="etude_nouvelle_matiere")
     */
    public function addMatiere(Request $request, $as, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $annee  = $repoAnnee->find($as);

      $matiere = new Matiere;
      $form = $this->createForm(MatiereType::class, $matiere);
      if($form->handleRequest($request)->isValid())
      {
        $em = $this->getDoctrine()->getManager();
        $matiere->setCreatedBy($this->getUser());
        $matiere->setCreatedAt(new \Datetime());
        $em->persist($matiere);
        $em->flush();

        $request->getSession()->getFlashBag()->add('info', 'La matière <strong>'.$matiere->getLibelle().'</strong> a été ajoutée');
        return $this->redirect($this->generateUrl('etude_gestion_matieres', array(
          'as' => $as, 'annexeId' => $annexeId
        )));
      }

      return $this->render('ISIBundle:Etude:add-matiere.html.twig', array(
        'form'  => $form->createView(),
        'asec'  => $as,
        'annexe'  => $annexe,
        'annee' => $annee,
      ));
    }

    //Edition de matière
    /**
     * @Security("has_role('ROLE_DIRECTION_ETUDE')")
     * @Route("/edition-de-matiere-{as}-{matiereId}-{annexeId}", name="etude_edit_matiere")
     */
    public function editMatiere(Request $request, $as, $matiereId, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $annee   = $em->getRepository('ISIBundle:Annee')->find($as);
      $matiere = $em->getRepository('ISIBundle:Matiere')->find($matiereId);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $form = $this->createForm(MatiereType::class, $matiere);
      if($form->handleRequest($request)->isValid())
      {
        $matiere->setUpdatedBy($this->getUser());
        $matiere->setUpdatedAt(new \Datetime());
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Mise à jour de la matière <strong>'.$matiere->getLibelle().'</strong> réussie.');

        return $this->redirect($this->generateUrl('etude_gestion_matieres', array('as' => $as, 'annexeId' => $annexeId)));
      }

      return $this->render('ISIBundle:Etude:edit-matiere.html.twig', array(
        'asec'  => $as,
        'annee' => $annee,
            'annexe'  => $annexe,
            'form'  => $form->createView()
      ));
    }

    //Gestion des niveaux de formation
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/gestion-des-niveaux-de-formation-{as}-{regime}-{annexeId}", name="etude_gestion_niveaux")
     */
    public function lesNiveaux(Request $request, int $as, $regime, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoNiveau = $em->getRepository('ISIBundle:Niveau');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $niveaux = $repoNiveau->niveauxDuGroupe($regime);
      $annee   = $repoAnnee->find($as);

      return $this->render('ISIBundle:Etude:gestion-des-niveaux.html.twig', array(
        'asec'    => $as,
        'annee'   => $annee,
        'niveaux' => $niveaux,
            'annexe'  => $annexe,
            'regime'  => $regime,
      ));
    }

    /**
     * @Security("has_role('ROLE_DIRECTION_ETUDE')")
     * @Route("/ajout-de-niveau-{as}-{regime}-{annexeId}", name="etude_nouveau_niveau")
     */
    public function addNiveau(Request $request, $as, $regime, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee           = $em->getRepository('ISIBundle:Annee');
      $repoNiveau          = $em->getRepository('ISIBundle:Niveau');
      $repoGroupeformation = $em->getRepository('ISIBundle:Groupeformation');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $grpFormation = $repoGroupeformation->findOneBy(['reference' => $regime]);
      $annee  = $repoAnnee->find($as);

      $niveau = new Niveau();

      $niveau->setGroupeFormation($grpFormation);

      $form = $this->createForm(NiveauType::class, $niveau, [
        'regime' => $regime
      ]);

      if($form->handleRequest($request)->isValid())
      {
        $em = $this->getDoctrine()->getManager();
        $dernierNiveau = $repoNiveau->dernierNiveauDuRegime($regime);
        $succession = (empty($dernierNiveau)) ? 1 : $dernierNiveau->getSuccession() + 1 ;
        $niveau->setSuccession($succession);
        // return new Response(var_dump($niveau));
        $niveau->setCreatedBy($this->getUser());
        $niveau->setCreatedAt(new \Datetime());
        $em->persist($niveau);
        $em->flush();

        $request->getSession()->getFlashBag()->add('info', 'Le niveau <strong>'.$niveau->getLibelleFr().'</strong> a été enregistré avec succès.');
        return $this->redirect($this->generateUrl('etude_gestion_niveaux', ['as' => $as, 'annexeId' => $annexeId, 'regime'=> $regime]));
      }
      return $this->render('ISIBundle:Etude:add-niveau.html.twig', [
        'asec'    => $as,
        'regime'  => $regime,
        'annee'   => $annee,
        'annexe'  => $annexe,
        'form'    => $form->createView()
      ]);
      // return $this->render('ISIBundle:Default:index.html.twig', [
      //   'grpFormation' => $grpFormation,
      //   'regime'       => $regime,
      //   'asec'         => $as])
      // ;
    }

    //Edition de niveau
    /**
     * @Security("has_role('ROLE_DIRECTION_ETUDE')")
     * @Route("/edition-de-niveau-{as}-{niveauId}-{regime}-{annexeId}", name="etude_edit_niveau")
     */
    public function editNiveau(Request $request, $as, $regime, $niveauId, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoNiveau = $em->getRepository('ISIBundle:Niveau');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $annee  = $repoAnnee->find($as);
      $niveau = $repoNiveau->find($niveauId);

      $form = $this->createForm(NiveauType::class, $niveau, [
        'regime' => $regime
      ]);
      if($form->handleRequest($request)->isValid())
      {
        $em->flush();
        
        $request->getSession()->getFlashBag()->add('info', 'Mise à jour du niveau <strong>'.$niveau->getLibelleAr().'</strong> réussie.');

        return $this->redirect($this->generateUrl('etude_gestion_niveaux', array(
          'as'     => $as, 'annexeId' => $annexeId,
          'regime' => $regime
        )));
      }

      return $this->render('ISIBundle:Etude:edit-niveau.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
            'annexe'  => $annexe,
            'form'  => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_DIRECTION_ETUDE')")
     * @Route("/les-livres-etudies-{as}-{annexeId}", name="etude_livres_etudies")
     */
    public function indexLivres(Request $request, $as, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoLivre = $em->getRepository('ISIBundle:Livre');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $annee  = $repoAnnee->find($as);
      $livres = $repoLivre->findAll();
      
      $liv = new Livre();
      // $liv->setnom('Nom');
      // $liv->setauteur('Auteur');
      // $liv->setdescription('Rien de spéciale');
      // $liv->setCreatedAt(new \Datetime());
      $form = $this->createForm(LivreEditionType::class, $liv);

      if($form->handleRequest($request)->isValid())
      {
        $data = $request->request->all();
        $livreId = $data['livre'];
        $livre = $repoLivre->find($livreId);
        /* Début - Upload du fichier */
        // $file stores the uploaded PDF file
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        if (!is_null($liv->getSupport())) {
          # code...
          $file = $liv->getSupport();
          if($file->guessExtension() != 'pdf')
          {
            $request->getSession()->getFlashBag()->add('error', 'Le livre sélectionné n\'est pas un fichier "PDF".');
            return $this->redirect($this->generateUrl('etude_livres_etudies', ['as' => $as, 'annexeId' => $annexeId]));
          }
  
          $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
  
          // moves the file to the directory where brochures are stored
          $file->move(
              $this->getParameter('support_directory'),
              $fileName
          );
  
          // updates the 'brochure' property to store the PDF file name
          // instead of its contents
          $livre->setNomFichier($file->getClientOriginalName());
          $livre->setSupport($fileName);
          $livre->setUpdatedBy($this->getUser());
          $livre->setUpdatedAt(new \Datetime());
          $em->flush();
        }
        else{
          $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez sélectionné aucun fichier.');
          return $this->redirect($this->generateUrl('etude_livres_etudies', ['as' => $as, 'annexeId' => $annexeId]));
        }

        $request->getSession()->getFlashBag()->add('info', 'Le support numérique <strong>'.$livre->getNom().'</strong> a été ajouté avec succès.');
        return $this->redirect($this->generateUrl('etude_livres_etudies', ['as' => $as, 'annexeId' => $annexeId]));
        /* Fin - Upload du fichier */
      }
      
        return $this->render('ISIBundle:Etude:livres-etudies.html.twig', [
            'asec'   => $as,
            'annee'  => $annee,
            'annexe'  => $annexe,
            'livres' => $livres,
            'form'   => $form->createView()
        ]);
    }  
  
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/ajout-d-un-nouveau-livre-a-etudier-{as}-{annexeId}", name="etude_nouveau_livre")
     */
    public function addLivre(Request $request, $as, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee = $em->getRepository('ISIBundle:Annee');
      $annee     = $repoAnnee->find($as);
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $livre = new Livre();

      $form = $this->createForm(LivreType::class, $livre);

      if($form->handleRequest($request)->isValid())
      {
        // $file stores the uploaded PDF file
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        if (!is_null($livre->getSupport())) {
          # code...
          $file = $livre->getSupport();
          if($file->guessExtension() != 'pdf')
          {
            $request->getSession()->getFlashBag()->add('error', 'Le livre sélectionné n\'est pas un fichier "PDF".');
            return $this->redirect($this->generateUrl('etude_nouveau_livre', ['as' => $as, 'annexeId' => $annexeId]));
          }
  
          $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
  
          // moves the file to the directory where brochures are stored
          $file->move(
              $this->getParameter('support_directory'),
              $fileName
          );
  
          // updates the 'brochure' property to store the PDF file name
          // instead of its contents
          $livre->setNomFichier($file->getClientOriginalName());
          $livre->setSupport($fileName);
        }

        $livre->setCreatedBy($this->getUser());
        $livre->setCreatedAt(new \Datetime());
        // return new Response(var_dump($niveau));
        $em->persist($livre);
        $em->flush();

        $request->getSession()->getFlashBag()->add('info', 'Le livre <strong>'.$livre->getNom().'</strong> a été enregistré avec succès.');
        return $this->redirect($this->generateUrl('etude_livres_etudies', ['as' => $as, 'annexeId' => $annexeId]));
      }
      return $this->render('ISIBundle:Etude:add-livre.html.twig', [
        'asec'    => $as,
        'annee'   => $annee,
            'annexe'  => $annexe,
            'form'    => $form->createView()
      ]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    //Edition de niveau
    /**
     * @Security("has_role('ROLE_DIRECTION_ETUDE')")
     * @Route("/edition-d-un-livre-{as}-{livreId}-{annexeId}", name="etude_editer_livre")
     */
    public function editLivre(Request $request, $as, $livreId, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoLivre = $em->getRepository('ISIBundle:Livre');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $annee  = $repoAnnee->find($as);
      $livre = $repoLivre->find($livreId);

      $form = $this->createForm(LivreType::class, $livre);
      if($form->handleRequest($request)->isValid())
      {
        $livre->setUpdatedBy($this->getUser());
        $livre->setUpdatedAt(new \Datetime());
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Mise à jour du livre <strong>'.$livre->getNom().'</strong> réussie.');

        return $this->redirect($this->generateUrl('etude_livres_etudies', array(
          'as'     => $as, 'annexeId' => $annexeId
        )));
      }

      return $this->render('ISIBundle:Etude:edit-livre.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
            'annexe'  => $annexe,
            'form'  => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/examen-{as}-{annexeId}", name="etude_examen")
     */
    public function examens(Request $request, int $as, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoExamen = $em->getRepository('ISIBundle:Examen');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $examens = $repoExamen->lesExamensDeLAnnee($as);
      $annee   = $repoAnnee->find($as);

      return $this->render('ISIBundle:Etude:accueil-examen.html.twig', [
        'asec'    => $as,
        'annee'   => $annee,
        'annexe'  => $annexe,
        'examens' => $examens
      ]);
    }

    /**
     * @Security("has_role('ROLE_NOTE' or 'ROLE_ETUDE')")
     * @Route("/gestion-des-notes-{as}-{regime}-{annexeId}", name="etude_notes")
     */
    public function notes(Request $request, int $as, $regime, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoExamen = $em->getRepository('ISIBundle:Examen');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      $examen = $repoExamen->dernierExamen($as);
      $annee  = $repoAnnee->find($as);

      return $this->render('ISIBundle:Etude:accueil-notes.html.twig', [
        'asec'   => $as,
        'annee'  => $annee,
        'regime' => $regime,
            'annexe'  => $annexe,
            'examen' => $examen
      ]);
    }

    // Ajout examen
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/parametres/add-new-examen-{as}-{annexeId}", name="etude_add_examen")
     */
    public function add_examen(Request $request, $as, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoExamen = $em->getRepository('ISIBundle:Examen');
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      // Sélection de l'année scolaire
      $annee  = $repoAnnee->find($as);

      $examen = new Examen();
      $examen->setAnnee($annee);
      $examen->setCreatedBy($this->getUser());
      $examen->setCreatedAt(new \Datetime());

      $form = $this->createForm(ExamenType::class, $examen);
      if($form->handleRequest($request)->isValid())
      {
        $data                     = $request->request->all();
        $dateProclamation         = $data["dateProclamation"];
        $datePublicationProgramme = $data["datePublicationProgramme"];
        $dateProclamation         = new \DateTime($dateProclamation);
        $datePublicationProgramme = new \DateTime($datePublicationProgramme);
        // On va sélectionner le dernier examen enregistré pour l'année en cours.
        /**
         * S'il n'y a pas d'examen enregistré, cela suppose que la session de l'examen qu'on va
         * enrigistré maintenant doit être la session 1.
         * S'il y a un examen enregistré, en vérifie la session est bien 1. Dans ce cas la session
         * de l'examen que nous voulons enregistré doit forcément être 2.
         */
        $dernierExamenDeLAnnee = $this->getDoctrine()->getManager()->getRepository('ISIBundle:Examen')->dernierExamen($as);

        // Si c'est vide, cela veut dire qu'aucun examen n'a encore été enrégistré pour cette année.
        // Alors l'examen qui va être flusher doit forcément être session 1
        if(empty($dernierExamenDeLAnnee))
        {
          if($examen->getSession() == 2)
          {
            $request->getSession()->getFlashBag()->add('error', 'La session choisie pour cette examen n\'est pas correcte.\n Vous devez choisir la session 1.');
            return $this->render('ISIBundle:Etude:add-examen.html.twig', [
              'asec'   => $as,
              'annee'  => $annee,
              'annexe' => $annexe,
              'form'   => $form->createView()
            ]);
          }
        }
        // Fin de if(empty($dernierExamenDeLAnnee))
        // Dans le else, on a un examen et sa session est 1: dernier examen en date de l'année
        else {

          // Ici, la session du dernier examen est 1 et celui de $examen est 2
          $datetime1 = $dernierExamenDeLAnnee->getCreatedAt();
          $datetime2 = $examen->getCreatedAt();

          // A ce stade du travail, on doit vérifier qu'il y a bien une période d'au moins quatre moins entre les deux examens.
          $interval = $datetime1->diff($datetime2);
          if($interval->format('%a') < 120)
          {
            $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas créer un nouvel examen maintenant. Veuillez patienter encore.');
            return $this->render('ISIBundle:Examen:add-examen.html.twig', [
              'asec' => $as,
              'form' => $form->createView()
            ]);
          }

          if($examen->getSession() == 1)
          {
            $request->getSession()->getFlashBag()->add('error', 'La session choisie pour cette examen n\'est pas correcte. Vous devez choisir la session 2.');
            return $this->render('ISIBundle:Etude:add-examen.html.twig', [
              'asec'   => $as,
              'annee'  => $annee,
              'annexe' => $annexe,
              'form'   => $form->createView()
            ]);
          }
        }

        $em = $this->getDoctrine()->getManager();
        $examen->setDateProclamationResultats($dateProclamation);
        $examen->setDatePublicationProgramme($datePublicationProgramme);
        $em->persist($examen);
        $em->flush();

        // Cette partie me permet de récupérer l'examen qui vient d'être enregistrer à l'instant
        $lastExamen = $repoExamen->findOneBy(['dateProclamationResultats' => $examen->getDateProclamationResultats()]);

        $request->getSession()->getFlashBag()->add('info', 'Examen enregistré avec succès.');

        return $this->redirect($this->generateUrl('etude_examen', ['as' => $as, 'annexeId' => $annexeId]));
      }
      return $this->render('ISIBundle:Etude:add-examen.html.twig', [
        'asec'   => $as,
        'annee'  => $annee,
        'annexe' => $annexe,
        'form'   => $form->createView()
      ]);
    }

    // Ajout examen
    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/parametres/edit-examen-{as}-{examenId}-{annexeId}", name="edit_examen")
     */
    public function edit_examen(Request $request, int $as, int $examenId, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoExamen = $em->getRepository('ISIBundle:Examen');
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }

      // Sélection de l'année scolaire
      $annee  = $repoAnnee->find($as);

      $examen = $repoExamen->find($examenId);

      $form = $this->createForm(ExamenType::class, $examen);
      if($form->handleRequest($request)->isValid())
      {
        $data = $request->request->all();
        $dateProclamation         = $data["dateProclamation"];
        $datePublicationProgramme = $data["datePublicationProgramme"];
        $dateProclamation         = new \DateTime($dateProclamation);
        $datePublicationProgramme = new \DateTime($datePublicationProgramme);

        $examen->setDateProclamationResultats($dateProclamation);
        $examen->setDatePublicationProgramme($datePublicationProgramme);
        $examen->setUpdatedAt(new \DateTime());
        $examen->setUpdatedBy($this->getUser());
        $em->flush();

        $request->getSession()->getFlashBag()->add('info', 'Examen mis à jour avec succès.');

        return $this->redirect($this->generateUrl('etude_examen', ['as' => $as, 'annexeId' => $annexeId]));
      }
      return $this->render('ISIBundle:Etude:edit-examen.html.twig', [
        'asec'   => $as,
        'annee'  => $annee,
        'examen' => $examen,
        'annexe' => $annexe,
        'form'   => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_ETUDE')")
     * @Route("/les-livres-de-la-matiere-{matiereId}-{annexeId}", name="livres_de_la_matiere", options={"expose"=true})
     */
    public function lesLivresDeLaMatiere(Request $request, $matiereId, $as, int $annexeId)
    {
      $em = $this->getDoctrine()->getManager();
      // $matiereId = $request->query->get('matiereId');
      $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
      $annexe = $repoAnnexe->find($annexeId);
      if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
        return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
      }
      /*
      * Récuperation des paramètres envoyés en GET
      */
      $livres = []; // initialisation du tableau qui contiendra le nom des classes

      $livres = $em->getRepository('ISIBundle:Livre')->findBy(['matiere' => $matiereId]);

        foreach ($livres as $livre) {
          $livresList [] = [
            'id'   => $livre->getId(),
            'name' => $livre->getNom() 
          ];
        }

        /*
        *  Notre liste de classe étant prête, on fait fait un retour de celle-ci en l'encodant en objet JSON
        */

        $response = new JsonResponse($livresList);
        return $response;
        // $response = new JsonResponse([$niveauId]);
        // return $response;
    }
}


?>