<?php
namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Annee;
use ISI\ENSBundle\Entity\Contrat;
use ISI\ENSBundle\Entity\Enseignant;
use ISI\ENSBundle\Entity\Convocation;
use ISI\ENSBundle\Entity\AnneeContrat;
use ISI\ENSBundle\Entity\AnneeContratConduite;
use ISI\ENSBundle\Entity\AnneeContratSanction;
use ISI\ENSBundle\Entity\AnneeContratConvocation;

use ISI\ENSBundle\Form\ContratType;
use ISI\ENSBundle\Form\EnseignantType;
use ISI\ENSBundle\Form\ConvocationType;
use ISI\ENSBundle\Form\AnneeContratType;
use ISI\ENSBundle\Form\AnneeContratConduiteType;
use ISI\ENSBundle\Form\AnneeContratSanctionType;

use ISI\ENSBundle\Repository\ContratRepository;
use ISI\ENSBundle\Repository\EnseignantRepository;
use ISI\ENSBundle\Repository\ConvocationRepository;
use ISI\ENSBundle\Repository\AnneeContratRepository;
use ISI\ISIBundle\Repository\AnneeRepository;
use ISI\ENSBundle\Repository\AnneeContratConduiteRepository;
use ISI\ENSBundle\Repository\AnneeContratSanctionRepository;
use ISI\ENSBundle\Repository\AnneeContratConvocationRepository;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DisciplineController extends Controller
{
  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function indexConduiteAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');

    $annee    = $repoAnnee->find($as);
    $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);
    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    return $this->render('ENSBundle:Discipline:index-conduite.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'anneeContrats' => $anneeContrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function enregistrerConduiteAction(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $annee   = $repoAnnee->find($as);
    $contrat = $repoContrat->find($contratId);

    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
       return $this->redirect($this->generateUrl('ens_conduite_home', ['as' => $as]));
    }

    $conduite = new AnneeContratConduite();
    $form = $this->createForm(AnneeContratConduiteType::class, $conduite);

    if($form->handleRequest($request)->isValid())
  	{
      $data = $request->request->all();
      $date = $data['date'];
      $date = new \Datetime($date);

      //Ici on procède à l'enrigistrement effectif de l'année scolaire en base de données
      $conduite->setAnnee($annee);
      $conduite->setContrat($contrat);
      $conduite->setDate($date);
      $conduite->setCreatedBy($this->getUser());
      $conduite->setCreatedAt(new \Datetime());
      $em->persist($conduite);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'La conduite de l\'enseignant(e) '.$contrat->getEnseignant()->getNomFr().' '.$contrat->getEnseignant()->getPnomFr().' a été enrégistré(e) avec succès.');

      // On redirige l'utilisateur vers index paramèTraversable
      return $this->redirect($this->generateUrl('ens_conduite_home',
         ['as' => $as]
        ));
    }

    return $this->render('ENSBundle:Discipline:enregistrer-conduite.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'contrat' => $contrat,
      'form'  => $form->createView(),
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function voirConduiteAction(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoConduite = $em->getRepository('ENSBundle:AnneeContratConduite');

    $annee     = $repoAnnee->find($as);
    $contrat   = $repoContrat->find($contratId);
    $conduites = $repoConduite->findBy(['contrat' => $contratId]);

    return $this->render('ENSBundle:Discipline:voir-les-conduite-d-un-enseignant.html.twig', [
      'asec'      => $as,
      'annee'     => $annee,
      'contrat'   => $contrat,
      'conduites' => $conduites,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function indexConvocationAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoEnseignant = $em->getRepository('ENSBundle:Enseignant');
    $repoConvocation = $em->getRepository('ENSBundle:Convocation');
    $repoAnneeConvocation = $em->getRepository('ENSBundle:AnneeContratConvocation');

    $annee  = $repoAnnee->find($as);

    $requete_des_convocations = "SELECT COUNT(c.id) AS nbr, c.id , c.instance, c.date, c.motif FROM convocation c JOIN annee_contrat_convocation a ON c.id = a.convocation WHERE a.annee = :asec GROUP BY c.id;";
    $statement = $em->getConnection()->prepare($requete_des_convocations);
    $statement->bindValue('asec', $as);
    $statement->execute();
    $convocations = $statement->fetchAll();
    // $convocations = $repoConvocation->findBy([['annee' => $as]]);
    $convoques    = $repoAnneeConvocation->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Discipline:index-convocation.html.twig', [
      'asec'         => $as,
      'annee'        => $annee,
      'convoques'    => $convoques,
      'convocations' => $convocations,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function convoquerHomeAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee        = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');

    $annee    = $repoAnnee->find($as);
    $contrats = $repoAnneeContrat->findBy(['annee' => $as]);

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $session = $this->get('session');
      if(isset($data['contrats']))
      {
        $contratsId = $data['contrats'];
        $session->set('ids', $contratsId);
        return $this->redirectToRoute('ens_info_convocation', ['as' => $as]);
        return new Response(var_dump($contratsId));
      }
      else{
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez sélectionné aucun enseignant.');
      }

    }

    return $this->render('ENSBundle:Discipline:convoquer-home.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'contrats' => $contrats,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function remplirConvocationAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');

    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
       return $this->redirect($this->generateUrl('ens_convocation_home', ['as' => $as]));
    }

    $annee = $repoAnnee->find($as);
    $ids = $this->get('session')->get('ids');
    $convocation = new Convocation();
    $form = $this->createForm(ConvocationType::class, $convocation);
    // return new Response(var_dump($ids));
    $contrats = [];
    foreach($ids as $id)
    {
      $contrats[] = $repoContrat->find($id);
    }
    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      $date = $data['date'];
      $instance = $data['isi_ensbundle_convocation']['instance'];
      $motif = $data['isi_ensbundle_convocation']['motif'];
      $date = new \Datetime($date);
      // return new Response(var_dump($instance));
      $convocation->setDate($date);
      $convocation->setInstance($instance);
      $convocation->setMotif($motif);
      $convocation->setCreatedBy($this->getUser());
      $convocation->setCreatedAt(new \Datetime());
      $em->persist($convocation);

      foreach($ids as $id)
      {
        $contrat = $repoContrat->find($id);
        $convoque = new AnneeContratConvocation();
        $convoque->setAnnee($annee);
        $convoque->setContrat($contrat);
        $convoque->setConvocation($convocation);
        $convoque->setCreatedBy($this->getUser());
        $convoque->setCreatedAt(new \Datetime());
        $em->persist($convoque);
      }
      $request->getSession()->getFlashBag()->add('info', 'Convocation enregistrée avec succès.');
      $em->flush();
      return $this->redirectToRoute('ens_convocation_home', ['as' => $as]);
    }

    // $contrats = $repoAnneeContrat->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Discipline:info-convocation.html.twig', [
      'asec'     => $as,
      'annee'    => $annee,
      'form'     => $form->createView(),
      'contrats' => $contrats,
    ]);
  }


  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function auditionEtVerdictAction(Request $request, $as, $ensConvocationId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee      = $em->getRepository('ISIBundle:Annee');
    $repoContrat    = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeConvocation = $em->getRepository('ENSBundle:AnneeContratConvocation');

    # $ensConvocationId ici est le paramètre qui renvoi l'id de l'entité AnneeContratConvocation,
    #qui correspond à l'enregistrement d'une convocation d'un enseignant pour une année donnée
    $annee  = $repoAnnee->find($as);
    $convocation = $repoAnneeConvocation->find($ensConvocationId);

    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
       return $this->redirect($this->generateUrl('ens_convocation_home', ['as' => $as]));
    }

    if($request->isMethod('post'))
    {
      $data      = $request->request->all();
      $audition = $data['audition'];
      $verdict  = $data['verdict'];

      $convocation->setAudition($audition);
      $convocation->setVerdict($verdict);
      $convocation->setUpdatedBy($this->getUser());
      $convocation->setUpdatedAt(new \Datetime());
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'Le verdict pour l\'esnseignant '.$convocation->getContrat()->getEnseignant()->getNomFr().' '.$convocation->getContrat()->getEnseignant()->getPnomFr().' a bien été enregistré.');
      return $this->redirectToRoute('ens_convocation_home', ['as' => $as]);

    }

    return $this->render('ENSBundle:Discipline:audition-et-verdict-d-un-enseignant-convoque.html.twig', [
      'asec'        => $as,
      'annee'       => $annee,
      'convocation' => $convocation,
    ]);
  }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function sanctionHomeAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');

    $annee    = $repoAnnee->find($as);
    $requete_des_sanctions = "SELECT c.id, e.id AS ensid, e.matricule, CONCAT(e.nom_fr, ' ', e.pnom_fr) AS nomFr, CONCAT(e.pnom_ar, ' ', e.nom_ar) AS nomAr, e.sexe, COUNT(acs.id) AS nbrSanction FROM enseignant e JOIN contrat c ON e.id = c.enseignant JOIN annee_contrat ac ON ac.contrat = c.id LEFT OUTER JOIN annee_contrat_sanction acs ON acs.contrat = c.id WHERE ac.annee = :asec GROUP BY c.id;";
    $statement = $em->getConnection()->prepare($requete_des_sanctions);
    $statement->bindValue('asec', $as);
    $statement->execute();
    $sanctions = $statement->fetchAll();
    // $convocations = $repoConvocation->findBy([['annee' => $as]]);
    // $convoques    = $repoAnneeConvocation->findBy(['annee' => $as]);
    // $contrats = $repoAnneeContrat->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Discipline:index-sanction.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'sanctions' => $sanctions,
      ]);
    }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function voirSanctionAction(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeContrat = $em->getRepository('ENSBundle:AnneeContrat');
    $repoSanction = $em->getRepository('ENSBundle:AnneeContratSanction');

    $annee    = $repoAnnee->find($as);
    $contrat  = $repoContrat->find($contratId);
    $sanctions = $repoSanction->findBy(['annee' => $as, 'contrat' => $contratId]);
    // $convocations = $repoConvocation->findBy([['annee' => $as]]);
    // $convoques    = $repoAnneeConvocation->findBy(['annee' => $as]);
    // $contrats = $repoAnneeContrat->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Discipline:voir-sanctions-d-un-enseignant.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'contrat'   => $contrat,
      'sanctions' => $sanctions,
      ]);
    }

    /**
     * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
     */
    public function addSanctionAction(Request $request, $as, $contratId)
    {
      $em = $this->getDoctrine()->getManager();
      $repoAnnee    = $em->getRepository('ISIBundle:Annee');
      $repoContrat  = $em->getRepository('ENSBundle:Contrat');
      $repoSanction = $em->getRepository('ENSBundle:AnneeContratSanction');

      $annee    = $repoAnnee->find($as);
      $contrat  = $repoContrat->find($contratId);

      if($annee->getAchevee() == TRUE)
      {
        $request->getSession()->getFlashBag()->add('error', 'Impossible de sanctionner car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
        return $this->redirect($this->generateUrl('ens_sanction_home', ['as' => $as]));
      }
      $sanction = new AnneeContratSanction();
      $form = $this->createForm(AnneeContratSanctionType::class, $sanction);

      if($form->handleRequest($request)->isValid())
      {
        $data = $request->request->all();
        $date = $data['date'];
        $date = new \Datetime($date);
        // $date = $date->format('Y-m-d');

        $sanction->setContrat($contrat);
        $sanction->setAnnee($annee);
        $sanction->setDate($date);
        $sanction->setCreatedBy($this->getUser());
        $sanction->setCreatedAt(new \Datetime());
        $em->persist($sanction);
        $em->flush();

        $request->getSession()->getFlashBag()->add('info', 'La canstion pour l\'enseignant '.$contrat->getEnseignant()->getNomFr().' '.$contrat->getEnseignant()->getPnomFr().' a enregistré avec succès.');

        return $this->redirect($this->generateUrl('ens_sanction_home', ['as'=> $as]));
      }
      return $this->render('ENSBundle:Discipline:enregistrer-une-sanction.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'contrat' => $contrat,
      'form'  => $form->createView(),
      ]);
    }

  /**
   * @Security("has_role('ROLE_DIRECTION_ENSEIGNANT')")
   */
  public function ajouterEnseignantAnneeAction(Request $request, $as, $contratId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee   = $em->getRepository('ISIBundle:Annee');
    $repoContrat = $em->getRepository('ENSBundle:Contrat');
    $repoAnneeContrat   = $em->getRepository('ENSBundle:AnneeContrat');

    $annee    = $repoAnnee->find($as);
    $contrat    = $repoContrat->find($contratId);
    $anneeContrat = $repoAnneeContrat->findOneBy(['annee' => $as, 'contrat' => $contratId]);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible d\'ajouter un enseignant car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
      return $this->redirect($this->generateUrl('ens_enseignant_de_l_annee', ['as' => $as]));
    }

    if(!is_null($anneeContrat))
    {
      $request->getSession()->getFlashBag()->add('error', $anneeContrat->getContrat()->getEnseignant()->getNomFr().' '.$anneeContrat->getContrat()->getEnseignant()->getPnomFr().' est déjà utilisé(e) pour l\'année '.$annee->getLibelleAnneeScolaire().'.');
      return $this->redirectToRoute('ens_fonctions_en_cours', ['as' => $as]);
    }

    $newAnneeContrat = new AnneeContrat();
    $newAnneeContrat->setAnnee($annee);
    $newAnneeContrat->setContrat($contrat);
    $newAnneeContrat->setCreatedBy($this->getUser());
    $newAnneeContrat->setCreatedAt(new \Datetime());
    $form  = $this->createForm(AnneeContratType::class, $newAnneeContrat);

    if($form->handleRequest($request)->isValid())
    {
      $em = $this->getDoctrine()->getManager();
      $em->persist($newAnneeContrat);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', 'Les heures de cours de '.$contrat->getEnseignant()->getNomFr().' '.$contrat->getEnseignant()->getPnomFr().' a été bien enregistrée pour l\'année '.$annee->getLibelleAnneeScolaire().'.');

      return $this->redirect($this->generateUrl('ens_fonctions_en_cours', ['as'=> $as]));
    }

    return $this->render('ENSBundle:Enseignant:nouveau-annee-contrat.html.twig', [
      'asec'    => $as,
      'annee'   => $annee,
      'form'    => $form->createView(),
      'contrat' => $contrat,
    ]);
  }
}


 ?>
