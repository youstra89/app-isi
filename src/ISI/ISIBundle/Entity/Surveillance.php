<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Surveillance
 *
 * @ORM\Table(name="surveillance")
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\SurveillanceRepository")
 */
class Surveillance
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
     * @var \ProgrammeGroupeComposition
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\ProgrammeGroupeComposition", inversedBy="surveillance")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="matiere_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $programmegroupecomposition;

    /**
     * @var \AnneeContrat
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ENSBundle\Entity\AnneeContrat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_contrat_id", referencedColumnName="id")
     * })
     */
    private $anneeContrat;

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
     * @var integer
     *
     * @ORM\Column(name="disabled", type="boolean", nullable=false)
     */
    private $disabled;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt; 

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;


    public function __construct()
    {
        $this->isDeleted = false;
    }


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
     * Set disabled
     *
     * @param boolean $disabled
     *
     * @return Surveillance
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Get disabled
     *
     * @return boolean
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Surveillance
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
     * @return Surveillance
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
     * Set programmegroupecomposition
     *
     * @param \ISI\ISIBundle\Entity\ProgrammeGroupeComposition $programmegroupecomposition
     *
     * @return Surveillance
     */
    public function setProgrammegroupecomposition(\ISI\ISIBundle\Entity\ProgrammeGroupeComposition $programmegroupecomposition)
    {
        $this->programmegroupecomposition = $programmegroupecomposition;

        return $this;
    }

    /**
     * Get programmegroupecomposition
     *
     * @return \ISI\ISIBundle\Entity\ProgrammeGroupeComposition
     */
    public function getProgrammegroupecomposition()
    {
        return $this->programmegroupecomposition;
    }

    /**
     * Set anneeContrat
     *
     * @param \ISI\ENSBundle\Entity\AnneeContrat $anneeContrat
     *
     * @return Surveillance
     */
    public function setAnneeContrat(\ISI\ENSBundle\Entity\AnneeContrat $anneeContrat = null)
    {
        $this->anneeContrat = $anneeContrat;

        return $this;
    }

    /**
     * Get anneeContrat
     *
     * @return \ISI\ENSBundle\Entity\AnneeContrat
     */
    public function getAnneeContrat()
    {
        return $this->anneeContrat;
    }

    /**
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return Surveillance
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
     * @return Surveillance
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
