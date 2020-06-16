<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use FOS\UserBundle\Model\User as BaseUser;

use Doctrine\Common\Collections\Collection;
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
     * @var \DateTime
     *
     * @ORM\Column(name="pwd_changed_at", type="datetime", nullable=true)
     */
    private $pwdChangedAt; 
    
    /**
     * @var integer
     * @ORM\Column(name="change_pw", type="integer", nullable=true)
     */
    private $changemdp;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\UserAnnexe", mappedBy="user")
     */
    private $annexes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->annexes = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * @return Collection|Annexe[]
     */
    public function getAnnexes(): Collection
    {
        return $this->annexes;
    }

    public function idsAnnexes()
    {
        $ids = [];
        foreach ($this->annexes as $value) {
            $ids[] = $value->getAnnexe()->getId();
        }

        return $ids;
    }

    public function findAnnexe(int $annexeId)
    {
        $userAnnexe = null;
        foreach ($this->annexes as $value) {
            if($value->getAnnexe()->getId() == $annexeId){
                $userAnnexe = $value;
            }
        }

        return $userAnnexe;
    }

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
     * Get changemdp
     *
     * @return integer
     */
    public function getChangemdp()
    {
        return $this->changemdp;
    }

    /**
     * Set changemdp
     *
     * @param integer $changemdp
     *
     * @return User
     */
    public function setChangemdp($changemdp)
    {
        $this->changemdp = $changemdp;

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

    /**
     * Set pwdChangedAt
     *
     * @param \DateTime $pwdChangedAt
     *
     * @return User
     */
    public function setPwdChangedAt($pwdChangedAt)
    {
        $this->pwdChangedAt = $pwdChangedAt;

        return $this;
    }

    /**
     * Get pwdChangedAt
     *
     * @return \DateTime
     */
    public function getPwdChangedAt()
    {
        return $this->pwdChangedAt;
    }
}
