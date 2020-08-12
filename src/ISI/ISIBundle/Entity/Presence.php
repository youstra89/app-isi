<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Presence
 *
 * @ORM\Table(
 *      name="presence",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unicite_appel", columns={"eleve_id", "date"})
 *      }, 
 *      indexes={
 *          @ORM\Index(name="eleve_id", columns={"eleve_id"}),
 *          @ORM\Index(name="created_by", columns={"created_by"}), 
 *          @ORM\Index(name="updated_by", columns={"updated_by"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\PresenceRepository")
 */
class Presence
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var \Eleve
     *
     * @ORM\ManyToOne(targetEntity="Eleve")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eleve_id", referencedColumnName="id")
     * })
     */
    private $eleve;

    /**
     * @var integer
     *
     * @ORM\Column(name="heure1", type="boolean", nullable=true)
     */
    private $heure1;

    /**
     * @var integer
     *
     * @ORM\Column(name="heure2", type="boolean", nullable=true)
     */
    private $heure2;

    /**
     * @var integer
     *
     * @ORM\Column(name="heure3", type="boolean", nullable=true)
     */
    private $heure3;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="heure4", type="boolean", nullable=true)
     */
    private $heure4;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="heure5", type="boolean", nullable=true)
     */
    private $heure5;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="heure6", type="boolean", nullable=true)
     */
    private $heure6;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="heure7", type="boolean", nullable=true)
     */
    private $heure7;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="heure8", type="boolean", nullable=true)
     */
    private $heure8;
        
    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $createdBy;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="updated_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $updatedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Presence
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    public function heure(int $heure)
    {
        switch ($heure) {
            case 1:
                return $this->heure1;
                break;
            case 2:
                return $this->heure2;
                break;
            case 3:
                return $this->heure3;
                break;
            case 4:
                return $this->heure4;
                break;
            case 5:
                return $this->heure5;
                break;
            case 6:
                return $this->heure6;
                break;
            case 7:
                return $this->heure7;
                break;
            default:
                return $this->heure8;
                break;
        }
    }

    public function rapportAbsence()
    {
        $cours = 0;
        $coran = 0;
        if($this->heure1 == 1){ $cours++; }
        if($this->heure2 == 2){ $cours++; }
        if($this->heure3 == 3){ $cours++; }
        if($this->heure4 == 4){ $cours++; }
        if($this->heure5 == 5){ $cours++; }
        if($this->heure6 == 6){ $coran++; }
        if($this->heure7 == 7){ $coran++; }
        if($this->heure8 == 8){ $coran++; }

        return [$cours, $coran];
    }

    /**
     * Set heure1
     *
     * @param boolean $heure1
     *
     * @return Presence
     */
    public function setHeure1($heure1)
    {
        $this->heure1 = $heure1;

        return $this;
    }

    /**
     * Get heure1
     *
     * @return boolean
     */
    public function getHeure1()
    {
        return $this->heure1;
    }

    /**
     * Set heure2
     *
     * @param boolean $heure2
     *
     * @return Presence
     */
    public function setHeure2($heure2)
    {
        $this->heure2 = $heure2;

        return $this;
    }

    /**
     * Get heure2
     *
     * @return boolean
     */
    public function getHeure2()
    {
        return $this->heure2;
    }

    /**
     * Set heure3
     *
     * @param boolean $heure3
     *
     * @return Presence
     */
    public function setHeure3($heure3)
    {
        $this->heure3 = $heure3;

        return $this;
    }

    /**
     * Get heure3
     *
     * @return boolean
     */
    public function getHeure3()
    {
        return $this->heure3;
    }

    /**
     * Set heure4
     *
     * @param boolean $heure4
     *
     * @return Presence
     */
    public function setHeure4($heure4)
    {
        $this->heure4 = $heure4;

        return $this;
    }

    /**
     * Get heure4
     *
     * @return boolean
     */
    public function getHeure4()
    {
        return $this->heure4;
    }

    /**
     * Set heure5
     *
     * @param boolean $heure5
     *
     * @return Presence
     */
    public function setHeure5($heure5)
    {
        $this->heure5 = $heure5;

        return $this;
    }

    /**
     * Get heure5
     *
     * @return boolean
     */
    public function getHeure5()
    {
        return $this->heure5;
    }

    /**
     * Set heure6
     *
     * @param boolean $heure6
     *
     * @return Presence
     */
    public function setHeure6($heure6)
    {
        $this->heure6 = $heure6;

        return $this;
    }

    /**
     * Get heure6
     *
     * @return boolean
     */
    public function getHeure6()
    {
        return $this->heure6;
    }

    /**
     * Set heure7
     *
     * @param boolean $heure7
     *
     * @return Presence
     */
    public function setHeure7($heure7)
    {
        $this->heure7 = $heure7;

        return $this;
    }

    /**
     * Get heure7
     *
     * @return boolean
     */
    public function getHeure7()
    {
        return $this->heure7;
    }

    /**
     * Set heure8
     *
     * @param boolean $heure8
     *
     * @return Presence
     */
    public function setHeure8($heure8)
    {
        $this->heure8 = $heure8;

        return $this;
    }

    /**
     * Get heure8
     *
     * @return boolean
     */
    public function getHeure8()
    {
        return $this->heure8;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Presence
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Presence
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set eleve
     *
     * @param \ISI\ISIBundle\Entity\Eleve $eleve
     *
     * @return Presence
     */
    public function setEleve(\ISI\ISIBundle\Entity\Eleve $eleve = null)
    {
        $this->eleve = $eleve;

        return $this;
    }

    /**
     * Get eleve
     *
     * @return \ISI\ISIBundle\Entity\Eleve
     */
    public function getEleve()
    {
        return $this->eleve;
    }

    /**
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return Presence
     */
    public function setCreatedBy(\UserBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \UserBundle\Entity\User $updatedBy
     *
     * @return Presence
     */
    public function setUpdatedBy(\UserBundle\Entity\User $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
