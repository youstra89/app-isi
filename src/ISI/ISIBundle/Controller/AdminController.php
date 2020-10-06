<?php

namespace ISI\ISIBundle\Controller;

use ISI\ISIBundle\Entity\UserAnnexe;
use ISI\ISIBundle\Repository\AnneeRepository;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUPER_ADMIN' or 'ROLE_ADMIN_ANNEXE')")
     * @Route("/admin-{as}-{annexeId}", name="admin_home")
     */
    public function index(Request $request, int $as, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $annee = $repoAnnee->find($as);
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }

        return $this->render('ISIBundle:Admin:index.html.twig', [
            'asec'   => $annee->getId(),
            'annee' => $annee,'annexe'   => $annexe,
            'annexe' => $annexe,
        ]);
    }


    /**
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Route("/admin/user/index-management/{as}-{annexeId}", name="isi_user")
     */
    public function indexUsers(Request $request, $as, int $annexeId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $annee = $repoAnnee->find($as);
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annexe = $repoAnnexe->find($annexeId);

        // Pour récupérer le service UserManager du bundle
        $userManager = $this->get('fos_user.user_manager');

        // Pour charger un utilisateur
        // $user = $userManager->findUserBy(array('username' => 'winzou'));

        // Pour modifier un utilisateur
        // $user->setEmail('cetemail@nexiste.pas');
        // $userManager->updateUser($user); // Pas besoin de faire un flush avec l'EntityManager, cette méthode le fait toute seule !

        // Pour supprimer un utilisateur
        // $userManager->deleteUser($user);

        // Pour récupérer la liste de tous les utilisateurs
        $users = $userManager->findUsers();

        // $request->query->get('as')
        return $this->render('ISIBundle:Admin:index-user.html.twig', array(
        'asec'  => $as,
        'annee' => $annee, 
        'annexe' => $annexe,
        'users' => $users
        ));
    }

    /**
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Route("/admin/user/{as}/{userId}/add-roles-user-{annexeId}", name="add_roles_user")
     */
    public function addRolesUserAction(Request $request, $as, $userId, int $annexeId)
    {
        $em         = $this->getDoctrine()->getManager();
        $repoAnnee  = $em->getRepository('ISIBundle:Annee');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $annee      = $repoAnnee->find($as);
        $annexes    = $repoAnnexe->findAll();
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $repoUserAnnexe = $em->getRepository('ISIBundle:UserAnnexe');
        $annexe = $repoAnnexe->find($annexeId);

        $roles = [
            'ROLE_SUPER_ADMIN', 
            'ROLE_ADMIN_SCOLARITE', 
            'ROLE_SCOLARITE', 
            'ROLE_PREINSCRIPTION', 
            'ROLE_INTERNAT', 
            'ROLE_DIRECTION_ENSEIGNANT', 
            'ROLE_ORGANISATION', 
            'ROLE_ENSEIGNANT', 
            'ROLE_ADMIN_ANNEXE', 
            'ROLE_DIRECTEUR_ANNEXE', 
            'ROLE_SCOLARITE_ANNEXE',
            'ROLE_ETUDE', 
            'ROLE_DIRECTION_ETUDE', 
            'ROLE_NOTE', 
            'ROLE_AGENT_DIRECTION_ENSEIGNANT', 
            'ROLE_CONTROLE_ENSEIGNANT', 
            'ROLE_DISCIPLINE_ENSEIGNANT', 
            'ROLE_ADJOINT_DIRECTION_ENSEIGNANT'
        ];

        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserBy(['id' => $userId]);

        if($request->isMethod('post')){
            $em       = $this->getDoctrine()->getManager();
            $data     = $request->request->all();
            $annexeId = $data['annexeId'];
            $roles    = isset($data['roles']) ? $data['roles'] : [];
            // return new Response(var_dump($data));
            if(!empty($roles)){
                $user->setRoles($roles);
                $request->getSession()->getFlashBag()->add('info', 'Les rôles de <strong>'.$user->getUsername().'</strong> ont été mises à jour avec succès.');
            }
            else{
                $user->setRoles($roles);
                $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez rien ajouter comme rôle pour l\'utilisateur <strong>'.$user->getUsername().'</strong>.');
            }

            if(!empty($data['annexes'])){
                $tab = [];
                foreach ($annexes as $value) {
                    $annexeId = $value->getId();
                    if(isset($data['annexes'][$annexeId]) and !in_array($annexeId, $user->idsAnnexes())){
                        $userAnnexe = new UserAnnexe();
                        $userAnnexe->setUser($user);
                        $userAnnexe->setAnnexe($value);
                        $userAnnexe->setDisabled(false);
                        $userAnnexe->setCreatedAt(new \DateTime());
                        $userAnnexe->setCreatedBy($this->getUser());
                        $em->persist($userAnnexe);
                        // dump(0);
                        // die();
                        $tab[] = $userAnnexe;
                    }
                    if(!isset($data['annexes'][$annexeId]) and in_array($annexeId, $user->idsAnnexes())){
                        $userAnnexe = $user->findAnnexe($annexeId);
                        $userAnnexe->setDisabled(true);
                        $userAnnexe->setUpdatedAt(new \DateTime());
                        $userAnnexe->setUpdatedBy($this->getUser());
                        // dump(1);
                        // die();
                        $tab[] = $userAnnexe;
                    }
                    if(isset($data['annexes'][$annexeId]) and in_array($annexeId, $user->idsAnnexes())){
                        $userAnnexe = $user->findAnnexe($annexeId);
                        $userAnnexe->setDisabled(false);
                        $userAnnexe->setUpdatedAt(new \DateTime());
                        $userAnnexe->setUpdatedBy($this->getUser());
                        // dump(2);
                        // die();
                        $tab[] = $userAnnexe;
                    }
                    // dump(3);
                    // die();
                }
            }
            else{
                $userAnnexes = $repoUserAnnexe->findByUser($user->getId());
                if(count($userAnnexes) != 0){
                    foreach ($userAnnexes as $value) {
                        $value->setDisabled(true);
                        $value->setUpdatedAt(new \DateTime());
                        $value->setUpdatedBy($this->getUser());
                    }
                }
                // dump(4);
                // die();
            }
            // dump($data['annexes'], $tab);
            // die();
            
            $userManager->updateUser($user);
            $em->flush();
            return $this->redirect($this->generateUrl('isi_user', ['as' => $as, 'annexeId' => $annexeId]));
        }

        return $this->render('ISIBundle:Admin:add-roles-user.html.twig', array(
            'asec'       => $as,
            'user'       => $user,
            'annee'      => $annee,
            'annexe'      => $annexe,
            'annexes'    => $annexes,
            'rolesDispo' => $roles,
        ));
    }
}

?>