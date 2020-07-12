<?php

namespace ISI\ISIBundle\Controller;

use ISI\ISIBundle\Entity\Annee;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/choix-annexe", name="annexes_homepage")
     */
    public function choixAnnexeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annee   = $repoAnnee->anneeEnCours();
        $annexes = $repoAnnexe->findAll();
        if(!empty($request->get('as'))){
            $annee = $repoAnnee->find($request->get('as'));
        }
        $as = $annee->getId();

        return $this->render('ISIBundle:Default:annexes.html.twig', [
            "asec"        => $as,
            "annee"       => $annee,
            "choixAnnexe" => "choix",
            "annexes"     => $annexes,
        ]);
    }

    /**
     * @Route("/accueil/{annexeId}", name="isi_homepage")
     */
    public function indexAction(Request $request, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $annee = $repoAnnee->anneeEnCours();
        $user = $this->getUser();
        if((int) $user->getChangemdp() === 0){
            $request->getSession()->getFlashBag()->add('info', 'Bienvenu. Il semble que vous soyez à votre première connexion à l\'application. Nous vous invitons à modifier votre mot de passe initial.');
            return $this->redirect($this->generateUrl('isi_account', ['as' => $annee->getId(), 'annexeId' => $annexeId]));
        }
        if(null !== $request->get('as')){
            $annee = $repoAnnee->find($request->get('as'));
        }

        // Cette condition ne sera vraie que lorsque nous seront à la toute première exécution du programme
        if($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN') || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN_ANNEXE'))
        {
            if(empty($annee))
            {
                $request->getSession()->getFlashBag()->add('info', 'SUPER ADMIN, vous devez d\'abord créer une année scolaire.');
                return $this->redirect($this->generateUrl('isi_nouvelle_annee', ['as' => 0]));
            }

            return $this->redirect($this->generateUrl('admin_home', ['as' => $annee->getId(), 'annexeId' => $annexeId]));

            // return $this->render('ISIBundle:Admin:index.html.twig', [
            //     'asec'  => $annee->getId(),
            //     'annee' => $annee
            // ]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN_SCOLARITE'))
        {
            if(empty($annee))
            {
                $request->getSession()->getFlashBag()->add('info', 'Avant de pouvoir utiliser l\'application, vous devez d\'abord créer une année scolaire.');
                return $this->redirect($this->generateUrl('isi_nouvelle_annee', ['as' => 0]));
            }

            return $this->redirectToRoute('isi_home_scolarite', ['as' => $annee->getId(), 'annexeId' => $annexeId]);
        }
            // return $this->redirectToRoute('isi_homepage');
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_SCOLARITE') || $this->get('security.authorization_checker')->isGranted('ROLE_PREINSCRIPTION'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant votre directeur!!!');

            return $this->redirectToRoute('isi_home_scolarite', ['as' => $annee->getId(), 'annexeId' => $annexeId]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_NOTE'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant votre directeur!!!');

            return $this->redirectToRoute('isi_home_note', ['as' => $annee->getId(), 'annexeId' => $annexeId]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_INTERNAT'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant le directeur des affaires écolières/scolaires!!!');

            return $this->redirectToRoute('internat_home', ['as' => $annee->getId(), 'annexeId' => $annexeId]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_DIRECTION_ENSEIGNANT'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant le directeur des affaires écolières/scolaires!!!');

            return $this->redirectToRoute('ens_home', ['as' => $annee->getId(), 'annexeId' => $annexeId]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_ETUDE'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant le directeur des affaires écolières/scolaires!!!');

            return $this->redirectToRoute('etude_home', ['as' => $annee->getId(), 'annexeId' => $annexeId]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_ORGANISATION'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant le directeur des affaires écolières/scolaires!!!');

            return $this->redirectToRoute('org_homepage', ['as' => $annee->getId(), 'annexeId' => $annexeId]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant le directeur des affaires écolières/scolaires!!!');

            return $this->redirectToRoute('enseignant_home', ['as' => $annee->getId(), 'annexeId' => $annexeId]);
        }
        else
            return new Response("Authentification parfaite. Mais, vous n'avez pas de rôle pour le moment.");

    }
}
