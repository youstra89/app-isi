<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class MySecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            return $this->redirectToRoute('annexes_homepage');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        return $this->render('UserBundle:Security:login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    public function addUserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = [
            0 => [
                'username' => 'mdiallo',
                'password' => 'mdi@llo',
                'email'    => 'mdiallo@isi.islam',
                'roles'    => ['ROLE_ADMIN_SCOLARITE']
            ],
            1 => [
                'username' => 'diomande',
                'password' => 'kh@derDiom',
                'email'    => 'diomande@isi.islam',
                'roles'    => ['ROLE_SCOLARITE']
            ],
            2 => [
                'username' => 'agre',
                'password' => 'ousmane',
                'email'    => 'agre@isi.islam',
                'roles'    => ['ROLE_SCOLARITE']
            ],
            3 => [
                'username' => 'bambaH',
                'password' => 'b@mb@H',
                'email'    => 'bambaH@isi.islam',
                'roles'    => ['ROLE_SCOLARITE']
            ],
            4 => [
                'username' => 'sana',
                'password' => 's@n@',
                'email'    => 'sana@isi.islam',
                'roles'    => ['ROLE_INTERNAT']
            ],
            5 => [
                'username' => 'bambaY',
                'password' => 'b@mb@you$$',
                'email'    => 'bambaY@isi.islam',
                'roles'    => ['ROLE_DIRECTION_ENSEIGNANT']
            ],
            6 => [
                'username' => 'admin',
                'password' => 'l@note20$',
                'email'    => 'admin@isi.islam',
                'roles'    => ['ROLE_SUPER_ADMIN']
            ],
        ];

        foreach ($users as $userA) {
            $user = new User();
            $user->setUsername($userA['username']);
            $user->setUsernameCanonical($userA['username']);
            $user->setPassword($userA['password']);
            $user->setEmail($userA['email']);
            $user->setEmailCanonical($userA['email']);
            $user->setRoles($userA['roles']);
            $em->persist($user);
        }
        $em->flush();

        return new Response('Users ajoutés avec succès!');
    }

    public function editUserAction()
    {
        $userManager = $this->get('fos_user.user_manager');

        // Pour charger un utilisateur
        $user = $userManager->findUserBy(array('username' => 'mdiallo'));

        // Pour modifier un utilisateur
        $user->setRoles(['ROLE_ADMIN_SCOLARITE']);
        $userManager->updateUser($user);

        return new Response("C'est OK");
    }
}
