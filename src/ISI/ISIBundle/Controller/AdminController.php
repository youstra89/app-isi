<?php

namespace ISI\ISIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use ISI\ISIBundle\Repository\AnneeRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function indexAction(Request $request, int $as)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $annee = $repoAnnee->find($as);
        return $this->render('ISIBundle:Admin:index.html.twig', [
            'asec' => $annee->getId(),
            'annee' => $annee
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function indexUsersAction(Request $request, $as)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $annee = $repoAnnee->find($as);

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
        'users' => $users
        ));
    }

    /**
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function addRolesUserAction(Request $request, $as, $userId)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAnnee = $em->getRepository('ISIBundle:Annee');
        $annee = $repoAnnee->find($as);

        $roles = ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN_SCOLARITE', 'ROLE_SCOLARITE', 'ROLE_INTERNAT', 'ROLE_ENSEIGNANT'];

        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserBy(['id' => $userId]);

        if($request->isMethod('post')){
            $em = $this->getDoctrine()->getManager();
            $data = $request->request->all();
            $roles = $data['role'];
            // return new Response(var_dump($data));
            if(!empty($roles))
            {
                $user->setRoles($roles);
                $userManager->updateUser($user);
                $request->getSession()->getFlashBag()->add('info', 'Les rôles de '.$user->getUsername().' ont été mises à jour avec succès.');
                return $this->redirect($this->generateUrl('isi_user', ['as' => $as]));
            }
            else{
                return new Response('Vous n\'avez rien ajouter comme rôle pour cet user');
            }
        }

        return $this->render('ISIBundle:Admin:add-roles-user.html.twig', array(
        'asec'  => $as,
        'user'  => $user,
        'annee' => $annee,
        'rolesDispo' => $roles,
        ));
    }
}

?>