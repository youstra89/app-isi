<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use FOS\UserBundle\Model\User as BaseUser;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", nullable=true)
     */
    protected $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="pnom", type="string", nullable=true)
     */
    protected $pnom;


    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return User
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set pnom
     *
     * @param string $pnom
     *
     * @return User
     */
    public function setPnom($pnom)
    {
        $this->pnom = $pnom;

        return $this;
    }

    /**
     * Get pnom
     *
     * @return string
     */
    public function getPnom()
    {
        return $this->pnom;
    }
}
