<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;

use ISI\ISIBundle\Repository\AnneeRepository;
use ISI\ISIBundle\Entity\Enseignement;
use ISI\ISIBundle\Entity\Annee;
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

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class EtudeController extends Controller
{
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function indexAction(Request $request, $as)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');

        $annee = $repoAnnee->find($as);

        return $this->render('ISIBundle:Etude:index.html.twig', [
            'asec' => $as,
            'annee' => $annee,
        ]);
    }

    // Page d'accueil des progressions de l'année
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function progressionAccueilAction(Request $request, $as, $regime)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $repoNiveau = $em->getRepository('ISIBundle:Niveau');

        $annee = $repoAnnee->find($as);
        $niveaux = $repoNiveau->niveauxDuGroupe($regime);

        return $this->render('ISIBundle:Etude:progression-home.html.twig', [
            'asec' => $as,
            'annee' => $annee,
            'regime' => $regime,
            'niveaux' => $niveaux,
        ]);
    }

    //Page d'accueil pour la liaison entre les classes et les matières pour une année scolaire donnée
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function niveauxMatieresAction(Request $request, $as, $regime)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee   = $em->getRepository('ISIBundle:Annee');
        $repoNiveau  = $em->getRepository('ISIBundle:Niveau');
        $repoMatiere = $em->getRepository('ISIBundle:Matiere');

        $listNiveaux = $repoNiveau->findAll();
        $annee  = $repoAnnee->find($as);
        // $listMatieresNiveaux = $repoMatiere->listMatieresNiveaux($as, );

        return $this->render('ISIBundle:Etude:niveaux-matieres-home.html.twig', [
            'asec'    => $as,
            'regime'  => $regime,
            'annee'   => $annee,
            'niveaux' => $listNiveaux
        ]);
    }

    //Pour lier effectivement les matières aux nineaux, ou disons au niveau sélectionné
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function lierNiveauxMatieresAction(Request $request, $as, $niveauId, $regime)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');
        $repoEleve  = $em->getRepository('ISIBundle:Eleve');
        $repoNiveau = $em->getRepository('ISIBundle:Niveau');

        // Pour le niveau sélectionné, s'il y a des élèves déjà inscrits dans une classe, on ne permettra pas l'ajout
        // d'une autre matière au niveau
        $annee = $repoAnnee->find($as);

        /**
         * Quand l'année scolaire est finie, on doit plus faire des
         * mofications, des mises à jour
         **/
        if($annee->getAchevee() == TRUE)
        {
          $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter d\'autres matières car l\'année scolaire '.$annee->getLibelle().' est achevée.');
          return $this->redirect($this->generateUrl('etude_niveaux_matieres', ['as' => $as, 'regime' => $regime]));
        }

        $niveau = $repoNiveau->find($niveauId);
        $eleves = $repoEleve->elevesDuNiveau($niveauId, $as);

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

        return $this->render('ISIBundle:Etude:lier-les-matieres-aux-niveaux.html.twig', [
            'asec'     => $as,
            'niveau'   => $niveau,
            'regime'   => $regime,
            'annee'    => $annee,
            'form'     => $form->createView()
        ]);
    }


    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function editerLiaisonNiveauxMatieresAction(Request $request, $as, $regime, $enseignementId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');
        $repoEleve  = $em->getRepository('ISIBundle:Eleve');
        $repoNiveau = $em->getRepository('ISIBundle:Niveau');
        $repoEnseignement = $em->getRepository('ISIBundle:Enseignement');
        $annee = $repoAnnee->find($as);

        /**
         * Quand l'année scolaire est finie, on doit plus faire des
         * mofications, des mises à jour
         **/
        if($annee->getAchevee() == TRUE)
        {
          $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter d\'autres matières car l\'année scolaire '.$annee->getLibelle().' est achevée.');
          return $this->redirect($this->generateUrl('etude_niveaux_matieres', ['as' => $as, 'regime' => $regime]));
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
                'as'     => $as,
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
            'enseignement' => $enseignement,
            'form'         => $form->createView()
        ]);
    }

    // Pour voir la liste des matières d'un niveau de formation donné
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function listeMatieresNiveauxAction(Request $request, $as, $regime, $niveauId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee   = $em->getRepository('ISIBundle:Annee');
      $repoEns     = $em->getRepository('ISIBundle:Enseignement');
      $repoMatiere = $em->getRepository('ISIBundle:Matiere');
      $repoNiveau  = $em->getRepository('ISIBundle:Niveau');

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
        'ens'      => $ens,
      ]);
    }

    // Accueil des paramètres
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function parametresAction(Request $request, $as)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');

        $annee = $repoAnnee->find($as);
        return $this->render('ISIBundle:Etude:parametres.html.twig', [
            'asec'     => $as,
            'annee'    => $annee,
        ]);
    }

    // Page de gestion des matières
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function lesMatieresAction($as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee   = $em->getRepository('ISIBundle:Annee');
      $repoMatiere = $em->getRepository('ISIBundle:Matiere');

      $annee  = $repoAnnee->find($as);
      $listeMatieres = $repoMatiere->findAll();

      return $this->render('ISIBundle:Etude:gestion-des-matieres.html.twig', array(
        'asec'     => $as,
        'annee'    => $annee,
        'matieres' => $listeMatieres
      ));
    }

    // Ajout de nouvelle matières
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function addMatiereAction(Request $request, $as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');

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

        $request->getSession()->getFlashBag()->add('info', 'La matière '.$matiere->getLibelle().' a été ajoutée');
        return $this->redirect($this->generateUrl('etude_gestion_matieres', array(
          'as' => $as
        )));
      }

      return $this->render('ISIBundle:Etude:add-matiere.html.twig', array(
        'form'  => $form->createView(),
        'asec'  => $as,
        'annee' => $annee,
      ));
    }

    //Edition de matière
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function editMatiereAction(Request $request, $as, $matiereId)
    {
      $em = $this->getDoctrine()->getManager();
      $annee   = $em->getRepository('ISIBundle:Annee')->find($as);
      $matiere = $em->getRepository('ISIBundle:Matiere')->find($matiereId);

      $form = $this->createForm(MatiereType::class, $matiere);
      if($form->handleRequest($request)->isValid())
      {
        $matiere->setUpdatedBy($this->getUser());
        $matiere->setUpdatedAt(new \Datetime());
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Mise à jour de la matière '.$matiere->getLibelle().' réussie.');

        return $this->redirect($this->generateUrl('etude_gestion_matieres', array('as' => $as)));
      }

      return $this->render('ISIBundle:Etude:edit-matiere.html.twig', array(
        'asec'  => $as,
        'annee' => $annee,
        'form'  => $form->createView()
      ));
    }

    //Gestion des niveaux de formation
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function lesNiveauxAction($as, $regime)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoNiveau = $em->getRepository('ISIBundle:Niveau');

      $niveaux = $repoNiveau->niveauxDuGroupe($regime);
      $annee   = $repoAnnee->find($as);

      return $this->render('ISIBundle:Etude:gestion-des-niveaux.html.twig', array(
        'asec'    => $as,
        'annee'   => $annee,
        'niveaux' => $niveaux,
        'regime'  => $regime,
      ));
    }

    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function addNiveauAction(Request $request, $as, $regime)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee           = $em->getRepository('ISIBundle:Annee');
      $repoNiveau          = $em->getRepository('ISIBundle:Niveau');
      $repoGroupeformation = $em->getRepository('ISIBundle:Groupeformation');

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

        $request->getSession()->getFlashBag()->add('info', 'Le niveau '.$niveau->getLibelleFr().' a été enregistré avec succès.');
        return $this->redirect($this->generateUrl('etude_gestion_niveaux', ['as' => $as, 'regime'=> $regime]));
      }
      return $this->render('ISIBundle:Etude:add-niveau.html.twig', [
        'asec'    => $as,
        'regime'  => $regime,
        'annee'   => $annee,
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
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function editNiveauAction(Request $request, $as, $regime, $niveauId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoNiveau = $em->getRepository('ISIBundle:Niveau');

      $annee  = $repoAnnee->find($as);
      $niveau = $repoNiveau->find($niveauId);

      $form = $this->createForm(NiveauType::class, $niveau, [
        'regime' => $regime
      ]);
      if($form->handleRequest($request)->isValid())
      {
        $em->flush();
        
        $request->getSession()->getFlashBag()->add('info', 'Mise à jour du niveau '.$niveau->getLibelleAr().' réussie.');

        return $this->redirect($this->generateUrl('etude_gestion_niveaux', array(
          'as'     => $as,
          'regime' => $regime
        )));
      }

      return $this->render('ISIBundle:Etude:edit-niveau.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'form'  => $form->createView()
      ]);
    }

    // index des livres étudiés
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function indexLivresAction(Request $request, $as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoLivre = $em->getRepository('ISIBundle:Livre');

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
            return $this->redirect($this->generateUrl('etude_livres_etudies', ['as' => $as]));
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
          return $this->redirect($this->generateUrl('etude_livres_etudies', ['as' => $as]));
        }

        $request->getSession()->getFlashBag()->add('info', 'Le support numérique '.$livre->getNom().' a été ajouté avec succès.');
        return $this->redirect($this->generateUrl('etude_livres_etudies', ['as' => $as]));
        /* Fin - Upload du fichier */
      }
      
        return $this->render('ISIBundle:Etude:livres-etudies.html.twig', [
            'asec'   => $as,
            'annee'  => $annee,
            'livres' => $livres,
            'form'   => $form->createView()
        ]);
    }  
  
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function addLivreAction(Request $request, $as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee = $em->getRepository('ISIBundle:Annee');
      $annee     = $repoAnnee->find($as);

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
            return $this->redirect($this->generateUrl('etude_nouveau_livre', ['as' => $as]));
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

        $request->getSession()->getFlashBag()->add('info', 'Le livre '.$livre->getNom().' a été enregistré avec succès.');
        return $this->redirect($this->generateUrl('etude_livres_etudies', ['as' => $as]));
      }
      return $this->render('ISIBundle:Etude:add-livre.html.twig', [
        'asec'    => $as,
        'annee'   => $annee,
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
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function editLivreAction(Request $request, $as, $livreId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoLivre = $em->getRepository('ISIBundle:Livre');

      $annee  = $repoAnnee->find($as);
      $livre = $repoLivre->find($livreId);

      $form = $this->createForm(LivreType::class, $livre);
      if($form->handleRequest($request)->isValid())
      {
        $livre->setUpdatedBy($this->getUser());
        $livre->setUpdatedAt(new \Datetime());
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', 'Mise à jour du livre '.$livre->getNom().' réussie.');

        return $this->redirect($this->generateUrl('etude_livres_etudies', array(
          'as'     => $as
        )));
      }

      return $this->render('ISIBundle:Etude:edit-livre.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'form'  => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function examenAction($as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoExamen = $em->getRepository('ISIBundle:Examen');

      $examens = $repoExamen->lesExamensDeLAnnee($as);
      $annee   = $repoAnnee->find($as);

      return $this->render('ISIBundle:Etude:accueil-examen.html.twig', [
        'asec'    => $as,
        'annee'   => $annee,
        'examens' => $examens
      ]);
    }

    /**
     * @Security("has_role('ROLE_NOTE' or 'ROLE_ETUDE')")
     */
    public function notesAction($as, $regime)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoExamen = $em->getRepository('ISIBundle:Examen');

      $examen = $repoExamen->dernierExamen($as);
      $annee  = $repoAnnee->find($as);

      return $this->render('ISIBundle:Etude:accueil-notes.html.twig', [
        'asec'   => $as,
        'annee'  => $annee,
        'regime' => $regime,
        'examen' => $examen
      ]);
    }

    // Ajout examen
    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function addExamenAction(Request $request, $as)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');
      $repoClasse = $em->getRepository('ISIBundle:Classe');
      $repoExamen = $em->getRepository('ISIBundle:Examen');
      $repoAnnee  = $em->getRepository('ISIBundle:Annee');

      // Sélection de l'année scolaire
      $annee  = $repoAnnee->find($as);

      $examen = new Examen();
      $annee = $repoAnnee->find($as);
      $examen->setAnnee($annee);
      $examen->setCreatedBy($this->getUser());
      $examen->setCreatedAt(new \Datetime());

      $form = $this->createForm(ExamenType::class, $examen);
      if($form->handleRequest($request)->isValid())
      {
        $data = $request->request->all();
        $date = $data["date"];
        $date = new \DateTime($date);
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
              'asec'  => $as,
              'annee' => $annee,
              'form'  => $form->createView()
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
          // $interval = $datetime1->diff($datetime2);
          // if($interval->format('%a') < 120)
          // {
          //   $request->getSession()->getFlashBag()->add('error', 'Vous ne pouvez pas créer un nouvel examen maintenant. Veuillez patienter encore.');
          //   return $this->render('ISIBundle:Examen:add-examen.html.twig', [
          //     'asec' => $as,
          //     'form' => $form->createView()
          //   ]);
          // }

          if($examen->getSession() == 1)
          {
            $request->getSession()->getFlashBag()->add('error', 'La session choisie pour cette examen n\'est pas correcte. Vous devez choisir la session 2.');
            return $this->render('ISIBundle:Etude:add-examen.html.twig', [
              'asec'  => $as,
              'annee' => $annee,
              'form'  => $form->createView()
            ]);
          }
        }

        $em = $this->getDoctrine()->getManager();
        $examen->setDateProclamationResultats($date);
        $em->persist($examen);
        $em->flush();

        // Cette partie me permet de récupérer l'examen qui vient d'être enregistrer à l'instant
        $lastExamen = $repoExamen->findOneBy(['dateProclamationResultats' => $examen->getDateProclamationResultats()]);

        $request->getSession()->getFlashBag()->add('info', 'Examen enregistré avec succès.');

        return $this->redirect($this->generateUrl('etude_examen', ['as' => $as]));
      }
      return $this->render('ISIBundle:Etude:add-examen.html.twig', [
        'asec'  => $as,
        'annee' => $annee,
        'form'  => $form->createView()
      ]);
    }

    /**
     * @Security("has_role('ROLE_ETUDE')")
     */
    public function lesLivresDeLaMatiereAction(Request $requestn, $matiereId)
    {
      $em = $this->getDoctrine()->getManager();
      // $matiereId = $request->query->get('matiereId');

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