<?php
namespace ISI\ENSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ENSBundle\Entity\Contrat;
use ISI\ENSBundle\Entity\Rencontre;
use ISI\ENSBundle\Entity\Enseignant;
use ISI\ENSBundle\Entity\AnneeContrat;
use ISI\ENSBundle\Entity\AnneeContratRencontre;

use ISI\ENSBundle\Form\ContratType;
use ISI\ENSBundle\Form\RencontreType;
use ISI\ENSBundle\Form\EnseignantType;
use ISI\ENSBundle\Form\AnneeContratType;
use ISI\ENSBundle\Form\AnneeContratRencontreType;

use ISI\ENSBundle\Repository\ContratRepository;
use ISI\ENSBundle\Repository\RencontreRepository;
use ISI\ENSBundle\Repository\EnseignantRepository;
use ISI\ISIBundle\Repository\AnneeContratRepository;
use ISI\ISIBundle\Repository\AnneescolaireRepository;
use ISI\ISIBundle\Repository\AnneescolaireRencontreRepository;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class RencontreController extends Controller
{
  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   */
  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Anneescolaire');
    $repoRencontre = $em->getRepository('ENSBundle:Rencontre');

    $annee      = $repoAnnee->find($as);
    $rencontres = $repoRencontre->findBy(['annee' => $as]);

    return $this->render('ENSBundle:Rencontre:index.html.twig', [
      'asec'       => $as,
      'annee'      => $annee,
      'rencontres' => $rencontres,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   * */
  public function addRencontreAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Anneescolaire');
    // $repoRencontre = $em->getRepository('ENSBundle:Rencontre');

    $annee     = $repoAnnee->find($as);
    $rencontre = new Rencontre();
    /**
     * Quand l'année scolaire est finie, on doit plus faire des
     * mofications, des mises à jour
     **/
    if($annee->getAchevee() == TRUE)
    {
       $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
       return $this->redirect($this->generateUrl('ens_rencontre_home', ['as' => $as]));
    }

    $form = $this->createForm(RencontreType::class, $rencontre);
    if($form->handleRequest($request)->isValid())
  	{
      $data = $request->request->all();
      $date = $data['date'];
      $date = new \Datetime($date);
      $rencontre->setAnnee($annee);
      $rencontre->setDate($date);
      $rencontre->setDateSave(new \Datetime());
      $rencontre->setDateUpdate(new \Datetime());
      $em->persist($rencontre);
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'La rencontre à été bien enregistrée');
      return $this->redirectToRoute('ens_rencontre_home', ['as' => $as]);
    }

    return $this->render('ENSBundle:Rencontre:add-rencontre.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'form'  => $form->createView(),
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   * */
  public function editRencontreAction(Request $request, $as, $rencontreId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Anneescolaire');
    $repoRencontre = $em->getRepository('ENSBundle:Rencontre');

    $annee     = $repoAnnee->find($as);
    $rencontre = $repoRencontre->find($rencontreId);

    if($annee->getAchevee() == TRUE)
    {
      $request->getSession()->getFlashBag()->add('error', 'Impossible de faire la mise à jour des classes car l\'année scolaire '.$annee->getLibelleAnneeScolaire().' est achevée.');
      return $this->redirect($this->generateUrl('ens_rencontre_home', ['as' => $as]));
    }

    $form = $this->createForm(RencontreType::class, $rencontre);
    if($form->handleRequest($request)->isValid())
  	{
      $data = $request->request->all();
      $date = $data['date'];
      $date = new \Datetime($date);
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', 'Les informations de la rencontre ont été mise à jour avec sussès.');
      return $this->redirectToRoute('ens_rencontre_home', ['as' => $as]);
    }

    return $this->render('ENSBundle:Rencontre:edit-rencontre.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'form'  => $form->createView(),
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   * */
  public function participantsRencontreAction(Request $request, $as, $rencontreId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Anneescolaire');
    $repoContrat   = $em->getRepository('ENSBundle:Contrat');
    $repoRencontre = $em->getRepository('ENSBundle:Rencontre');
    $repoAnneeContrat   = $em->getRepository('ENSBundle:AnneeContrat');
    $repoAnneeContratRencontre = $em->getRepository('ENSBundle:AnneeContratRencontre');

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
          $EnsRencontre->setDateSave(new \Datetime());
          $EnsRencontre->setDateUpdate(new \Datetime());
          $em->persist($EnsRencontre);
        }
        $em->flush();
        $request->getSession()->getFlashBag()->add('info', $nbr.' participants ont été ajouté(s) à la liste de présence de la : '.$rencontre->getType().' du '.$rencontre->getDate()->format('d-m-Y').'.');
        return $this->redirectToRoute('ens_rencontre_home', ['as' => $as]);
        return new Response(var_dump($contratsId));
      }
      else{
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez sélectionné aucun enseignant.');
      }

    }

    return $this->render('ENSBundle:Rencontre:enseignants-participants-rencontre.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'rencontre' => $rencontre,
      'contrats'  => $anneeContrats,
      'participantsId' => $participantsId
    ]);
  }

  /**
   * @Security("has_role('ROLE_ENSEIGNANT')")
   * */
  public function listeDesParticipantsRencontreAction(Request $request, $as, $rencontreId)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee     = $em->getRepository('ISIBundle:Anneescolaire');
    $repoRencontre = $em->getRepository('ENSBundle:Rencontre');
    $repoAnneeContratRencontre = $em->getRepository('ENSBundle:AnneeContratRencontre');

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
      'rencontre' => $rencontre,
      'enseignantsRencontre'  => $enseignantsRencontre,
    ]);
  }

}


 ?>
