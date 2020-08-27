<?php

namespace ISI\ISIBundle\Controller;

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
        if(count($this->getUser()->getAnnexes()) == 1){
            return $this->redirect($this->generateUrl('isi_homepage', ['as' => $annee->getId(), 'annexeId' => $this->getUser()->getAnnexes()[0]->getAnnexe()->getId()]));
        }

        return $this->render('ISIBundle:Default:annexes.html.twig', [
            "asec"        => $as,
            "annee"       => $annee,
            "choixAnnexe" => "choix",
            // "annexes"     => $annexes,
        ]);
    }

    /**
     * @Route("/statistiques-annexes", name="statistiques_annexes")
     */
    public function statistiques_annexes(Request $request)
    {
        $em             = $this->getDoctrine()->getManager();
        $repoAnnee      = $em->getRepository('ISIBundle:Annee');
        $repoEleve      = $em->getRepository('ISIBundle:Eleve');
        $repoClasse     = $em->getRepository('ISIBundle:Classe');
        $annee          = $repoAnnee->anneeEnCours();
        $annexes        = $this->getUser()->getAnnexes();
        if(!empty($request->get('as'))){
            $annee = $repoAnnee->find($request->get('as'));
        }
        $as = $annee->getId();
        if(count($this->getUser()->getAnnexes()) == 1){
            return $this->redirect($this->generateUrl('isi_homepage', ['as' => $annee->getId(), 'annexeId' => $this->getUser()->getAnnexes()[0]->getAnnexe()->getId()]));
        }

        $infosAnnexes = [];
        foreach ($annexes as $annexe) {
            $annexeId   = $annexe->getAnnexe()->getId();
            $eleves = $repoEleve->tousLesElevesInscrits($as, $annexeId);
            $classes = $repoClasse->lesClasseDeLAnnee($as, $annexeId);
            
            $garconsAcademie = 0;
            $fillesAcademie  = 0;
            $garconsCF       = 0;
            $fillesCF        = 0;
            foreach ($eleves as $eleve) {
                $sexe = $eleve->getSexe();
                $regime = $eleve->getRegime();
                if($sexe == 1 and $regime == "A")
                    $garconsAcademie++;
                elseif($sexe == 2 and $regime == "A")
                    $fillesAcademie++;
                elseif($sexe == 1 and $regime == "F")
                    $garconsCF++;
                elseif($sexe == 2 and $regime == "F")
                    $fillesCF++;
            }

            $classeGarconsAcademie = 0;
            $classeFillesAcademie  = 0;
            $classeMixtesAcademie  = 0;
            $classeGarconsCF       = 0;
            $classeFillesCF        = 0;
            $classeMixtesCF        = 0;
            foreach ($classes as $classe) {
                $genre = $classe->getGenre();
                $regime = $classe->getNiveau()->getGroupeFormation()->getReference();
                if($genre == "H" and $regime == "A")
                    $classeGarconsAcademie++;
                elseif($genre == "F" and $regime == "A")
                    $classeFillesAcademie++;
                elseif($genre == "M" and $regime == "A")
                    $classeMixtesAcademie++;
                elseif($genre == "H" and $regime == "F")
                    $classeGarconsCF++;
                elseif($genre == "F" and $regime == "F")
                    $classeFillesCF++;
                elseif($genre == "M" and $regime == "F")
                    $classeMixtesCF++;
            }

            $infosAnnexes[$annexeId] = [
                "garconsAcademie"       => $garconsAcademie, 
                "fillesAcademie"        => $fillesAcademie, 
                "garconsCF"             => $garconsCF, 
                "fillesCF"              => $fillesCF,
                "classeGarconsAcademie" => $classeGarconsAcademie, 
                "classeFillesAcademie"  => $classeFillesAcademie, 
                "classeMixtesAcademie"  => $classeMixtesAcademie, 
                "classeGarconsCF"       => $classeGarconsCF, 
                "classeFillesCF"        => $classeFillesCF,
                "classeMixtesCF"        => $classeMixtesCF
            ];
        }

        // dump($infosAnnexes);
        return $this->render('ISIBundle:Default:statistiques-annexes.html.twig', [
            "asec"         => $as,
            "annee"        => $annee,
            "annexes"      => $annexes,
            "infosAnnexes" => $infosAnnexes,
            "choixAnnexe"  => "choix",
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
        elseif(
            $this->get('security.authorization_checker')->isGranted('ROLE_AGENT_DIRECTION_ENSEIGNANT') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_CONTROLE_ENSEIGNANT') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_DISCIPLINE_ENSEIGNANT') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_ADJOINT_DIRECTION_ENSEIGNANT') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_DIRECTION_ENSEIGNANT') 
        )
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
