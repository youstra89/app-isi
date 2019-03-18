<?php

namespace ServicesBundle\Services;

class UserConnexion
{
    /**
     * Cette fonction a pour rôle d'enregistrer les connexions d'un utilisateur donné.
     * Elle prend en paramètre l'utilisateur courant
     */
    public function userConnexion($user)
    {
        // $em = $this->getDoctrine()->getManager();
        return $user->getRoles();
    }
} 

?>