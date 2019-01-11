<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ISIBundle\Repository\AnneescolaireRepository;

use Doctrine\ORM\EntityRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Anneescolaire');
        $annee = $repoAnnee->anneeEnCours();

        // Cette condition ne sera vraie que lorsque nous seront à la toute première exécution du programme
        if($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN'))
        {
            if(empty($annee))
            {
                $request->getSession()->getFlashBag()->add('info', 'SUPER ADMIN, vous devez d\'abord créer une année scolaire.');
                return $this->redirect($this->generateUrl('isi_nouvelle_annee', ['as' => 0]));
            }

            return $this->redirect($this->generateUrl('admin_home', ['as' => $annee->getAnneeScolaireId()]));

            // return $this->render('ISIBundle:Admin:index.html.twig', [
            //     'asec'  => $annee->getAnneeScolaireId(),
            //     'annee' => $annee
            // ]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN_SCOLARITE') || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN_ANNEXE'))
        {
            if(empty($annee))
            {
                $request->getSession()->getFlashBag()->add('info', 'Avant de pouvoir utiliser l\'application, vous devez d\'abord créer une année scolaire.');
                return $this->redirect($this->generateUrl('isi_nouvelle_annee', ['as' => 0]));
            }

            return $this->redirectToRoute('isi_home_scolarite');
        }
            // return $this->redirectToRoute('isi_homepage');
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_SCOLARITE') || $this->get('security.authorization_checker')->isGranted('ROLE_PREINSCRIPTION'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant votre directeur!!!');

            return $this->redirectToRoute('isi_home_scolarite');
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_NOTE'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant votre directeur!!!');

            return $this->redirectToRoute('isi_home_note', ['as' => $annee->getAnneeScolaireId()]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_INTERNAT'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant le directeur des affaires écolières/scolaires!!!');

            return $this->redirectToRoute('internat_home', ['as' => $annee->getAnneeScolaireId()]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_ENSEIGNANT'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant le directeur des affaires écolières/scolaires!!!');

            return $this->redirectToRoute('ens_home', ['as' => $annee->getAnneeScolaireId()]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_ETUDE'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant le directeur des affaires écolières/scolaires!!!');

            return $this->redirectToRoute('etude_home', ['as' => $annee->getAnneeScolaireId()]);
        }
        elseif($this->get('security.authorization_checker')->isGranted('ROLE_ORGANISATION'))
        {
            if(empty($annee))
                return new Response('Vous ne pouvez pas utiliser l\'application avant le directeur des affaires écolières/scolaires!!!');

            return $this->redirectToRoute('org_homepage', ['as' => $annee->getAnneeScolaireId()]);
        }
        else
            return new Response("Authentification parfaite. Mais, vous n'avez pas de rôle pour le moment.");

    }

}
