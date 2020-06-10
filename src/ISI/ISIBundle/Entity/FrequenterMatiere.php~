<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * FrequenterMatiere
 *
 * @ORM\Table(name="frequenter_matiere", uniqueConstraints={@ORM\UniqueConstraint(name="frequenter", columns={"matiere_id", "frequenter_id"})})
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"frequenter_id", "matiere_id"}
 * )
 */
class FrequenterMatiere
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="validation", type="boolean", nullable=false)
     */
    private $validation;

    /**
     * @var float
     *
     * @ORM\Column(name="moyenne", type="float", nullable=true)
     */
    private $moyenne;

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
     * @var \Frequenter
     *
     * @ORM\ManyToOne(targetEntity="Frequenter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="frequenter_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $frequenter;

    /**
     * @var \Matiere
     *
     * @ORM\ManyToOne(targetEntity="Matiere")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="matiere_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $matiere;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set validation
     *
     * @param boolean $validation
     *
     * @return FrequenterMatiere
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Get validation
     *
     * @return boolean
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Set moyenne
     *
     * @param float $moyenne
     *
     * @return FrequenterMatiere
     */
    public function setMoyenne($moyenne)
    {
        $this->moyenne = $moyenne;

        return $this;
    }

    /**
     * Get moyenne
     *
     * @return \DateTime
     */
    public function getMoyenne()
    {
        return $this->moyenne;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return FrequenterMatiere
     */
    public function setcreated_at($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getcreated_at()
    {
        return $this->createdAt;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     *
     * @return FrequenterMatiere
     */
    public function setupdated_at($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getupdated_at()
    {
        return $this->updatedAt;
    }


    /**
     * Set frequenter
     *
     * @param \ISI\ISIBundle\Entity\Frequenter $frequenter
     *
     * @return FrequenterMatiere
     */
    public function setFrequenter(\ISI\ISIBundle\Entity\Frequenter $frequenter = null)
    {
        $this->frequenter = $frequenter;

        return $this;
    }

    /**
     * Get frequenter
     *
     * @return \ISI\ISIBundle\Entity\Frequenter
     */
    public function getFrequenter()
    {
        return $this->frequenter;
    }

    /**
     * Set matiere
     *
     * @param \ISI\ISIBundle\Entity\Matiere $matiere
     *
     * @return FrequenterMatiere
     */
    public function setMatiere(\ISI\ISIBundle\Entity\Matiere $matiere = null)
    {
        $this->matiere = $matiere;

        return $this;
    }

    /**
     * Get matiere
     *
     * @return \ISI\ISIBundle\Entity\Matiere
     */
    public function getMatiere()
    {
        return $this->matiere;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FrequenterMatiere
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
     * @return FrequenterMatiere
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
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return FrequenterMatiere
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
     * @return FrequenterMatiere
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
