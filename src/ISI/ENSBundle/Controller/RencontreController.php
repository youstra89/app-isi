<?php
namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ENSBundle\Entity\Rencontre;
use ISI\ENSBundle\Entity\AnneeContratRencontre;

use ISI\ENSBundle\Form\RencontreType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class RencontreController extends Controller
{
  /**
   * @Security("has_role('ROLE_ADJOINT_DIRECTION_ENSEIGNANT')")
   * @Route("/index-des-rencontres-{as}", name="ens_rencontre_home")
   */
  public function index(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Annee');
    $repoRencontre = $em->getRepository('ENSBundle:Rencontre');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee      = $repoAnnee->find($as);
    $rencontres = $repoRencontre->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Rencontre:index.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      'annexe' => $annexe,
      'rencontres' => $rencontres,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ADJOINT_DIRECTION_ENSEIGNANT')")
   * @Route("/enregistrement-de-rencontres-{as}", name="ens_add_rencontre")
   */
  public function addRencontre(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Annee');
    // $repoRencontre = $em->getRepository('ENSBundle:Rencontre');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee     = $repoAnnee->find($as);
    $rencontre = new Rencontre();
    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
       return $this->redirect($this->generateUrl('ens_rencontre_home', ['as' => $as, 'annexeId' => $annexeId]));
    }

    $form = $this->createForm(RencontreType::class, $rencontre);
    if($form->handleRequest($request)->isValid())
  	{
      $data = $request->request->all();
      $date = $data['date'];
      if($data['rapport'] !== null){
        $rencontre->setRapport($data['rapport']);
      }
      $date = new \Datetime($date);
      $rencontre->setAnnee($annee);
      $rencontre->setDate($date);
      $rencontre->setCreatedBy($this->getUser());
      $rencontre->setCreatedAt(new \Datetime());
      $em->persist($rencontre);
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'La rencontre à été bien enregistrée');
      return $this->redirectToRoute('ens_rencontre_home', ['as' => $as, 'annexeId' => $annexeId]);
    }

    return $this->render('ENSBundle:Rencontre:add-rencontre.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'annexe' => $annexe,
      'form'  => $form->createView(),
    ]);
  }

  /**
   * @Security("has_role('ROLE_ADJOINT_DIRECTION_ENSEIGNANT')")
   * @Route("/mise-a-jour-des-informations-d-une-rencontre-{as}-{rencontreId}", name="ens_edit_rencontre")
   */
  public function editRencontre(Request $request, $as, $rencontreId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Annee');
    $repoRencontre = $em->getRepository('ENSBundle:Rencontre');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee     = $repoAnnee->find($as);
    $rencontre = $repoRencontre->find($rencontreId);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire <strong>'.$annee->getLibelle().'</strong> est achevée.');
      return $this->redirect($this->generateUrl('ens_rencontre_home', ['as' => $as, 'annexeId' => $annexeId]));
    }

    $form = $this->createForm(RencontreType::class, $rencontre);
    if($form->handleRequest($request)->isValid())
  	{
      $data = $request->request->all();
      $date = $data['date'];
      $date = new \Datetime($date);
      if($data['rapport'] !== null and $rencontre->getRapport() !== $data['rapport']){
        $rencontre->setRapport($data['rapport']);
      }
      $rencontre->setUpdatedBy($this->getUser());
      $rencontre->setUpdatedAt(new \Datetime());
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'Les informations de la rencontre ont été mise à jour avec sussès.');
      return $this->redirectToRoute('ens_rencontre_home', ['as' => $as, 'annexeId' => $annexeId]);
    }

    return $this->render('ENSBundle:Rencontre:edit-rencontre.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'annexe' => $annexe,
      'rencontre' => $rencontre,
      'form'  => $form->createView(),
    ]);
  }

  /**
   * @Security("has_role('ROLE_ADJOINT_DIRECTION_ENSEIGNANT')")
   * @Route("/lire-le-rapport-d-une-rencontre-{as}-{rencontreId}", name="lire_rapport_rencontre")
   */
  public function lire_rapport_rencontre(Request $request, $as, $rencontreId)
  {
    $em            = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Annee');
    $repoRencontre = $em->getRepository('ENSBundle:Rencontre');
    $annexeId      = $request->get('annexeId');
    $repoAnnexe    = $em->getRepository('ISIBundle:Annexe');
    $annexe        = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee     = $repoAnnee->find($as);
    $rencontre = $repoRencontre->find($rencontreId);

    return $this->render('ENSBundle:Rencontre:lire-rapport-rencontre.html.twig', [
      'asec'      => $as,
      'annee'     => $annee,
      'annexe'    => $annexe,
      'rencontre' => $rencontre,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ADJOINT_DIRECTION_ENSEIGNANT')")
   * @Route("/ajouter-des-participants-a-la-rencontre-{as}-{rencontreId}", name="ens_participants_rencontre")
   */
  public function participantsRencontre(Request $request, $as, $rencontreId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Annee');
    $repoContrat   = $em->getRepository('ENSBundle:Contrat');
    $repoRencontre = $em->getRepository('ENSBundle:Rencontre');
    $repoAnneeContrat   = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnneeContratRencontre = $em->getRepository('ENSBundle:AnneeContratRencontre');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $rencontre    = $repoRencontre->find($rencontreId);
    $anneeContrats = $repoAnneeContrat->findBy(['annee' => $as]);
    $enseignantsRencontre = $repoAnneeContratRencontre->findBy(['annee' => $as, 'rencontre' => $rencontreId]);

    foreach ($anneeContrats as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $anneeContrats);

    $participantsId = [];
    foreach($enseignantsRencontre as $ens)
    {
      $participantsId[] = $ens->getContrat()->getId();
    }

    if($request->isMethod('post'))
    {
      $data = $request->request->all();
      if(isset($data['contrats']))
      {
        $contratsId = $data['contrats'];
        $nbr = 0;
        foreach($contratsId as $id)
        {
          $nbr++;
          $contrat = $repoContrat->find($id);
          $EnsRencontre = new AnneeContratRencontre();
          $EnsRencontre->setAnnee($annee);
          $EnsRencontre->setContrat($contrat);
          $EnsRencontre->setRencontre($rencontre);
          $EnsRencontre->setCreatedBy($this->getUser());
          $EnsRencontre->setCreatedAt(new \Datetime());
          $em->persist($EnsRencontre);
        }
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', $nbr.' participants ont été ajouté(s) à la liste de présence de la : <strong>'.$rencontre->getType().'</strong> du <strong>'.$rencontre->getDate()->format('d-m-Y').'</strong>.');
        return $this->redirectToRoute('ens_rencontre_home', ['as' => $as, 'annexeId' => $annexeId]);
        return new Response(var_dump($contratsId));
      }
      else{
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez sélectionné aucun enseignant.');
      }

    }

    return $this->render('ENSBundle:Rencontre:enseignants-participants-rencontre.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'annexe' => $annexe,
      'rencontre' => $rencontre,
      'contrats'  => $anneeContrats,
      'participantsId' => $participantsId
    ]);
  }

  /**
   * @Security("has_role('ROLE_ADJOINT_DIRECTION_ENSEIGNANT')")
   * @Route("/liste-des-participants-a-la-rencontre-{as}-{rencontreId}", name="ens_liste_des_participants")
   */
  public function listeDesParticipantsRencontre(Request $request, $as, $rencontreId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Annee');
    $repoRencontre = $em->getRepository('ENSBundle:Rencontre');
    $repoAnneeContratRencontre = $em->getRepository('ENSBundle:AnneeContratRencontre');
    $annexeId = $request->get('annexeId');
    $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
    $annexe = $repoAnnexe->find($annexeId);
    if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
      $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
      return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
    }

    $annee    = $repoAnnee->find($as);
    $rencontre    = $repoRencontre->find($rencontreId);
    $enseignantsRencontre = $repoAnneeContratRencontre->findBy(['annee' => $as, 'rencontre' => $rencontreId]);

    foreach ($enseignantsRencontre as $key => $ac) {
      $nom[$key]  = $ac->getContrat()->getEnseignant()->getNomFr();
      $pnom[$key] = $ac->getContrat()->getEnseignant()->getPnomFr();
    }
    array_multisort($nom, SORT_ASC, $pnom, SORT_ASC, $enseignantsRencontre);

    return $this->render('ENSBundle:Rencontre:liste-des-enseignants-participants-rencontre.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'annexe' => $annexe,
      'rencontre' => $rencontre,
      'enseignantsRencontre'  => $enseignantsRencontre,
    ]);
  }

}


 ?>
