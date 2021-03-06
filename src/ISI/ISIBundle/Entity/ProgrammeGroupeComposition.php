<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ProgrammeGroupeComposition
 *
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\ProgrammeGroupeCompositionRepository")
 */
class ProgrammeGroupeComposition
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
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false)
     */
    private $isDeleted;

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
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="deleted_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $deletedBy;

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
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var \Examen
     *
     * @ORM\ManyToOne(targetEntity="ISI\ISIBundle\Entity\Examen")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="examen_id", referencedColumnName="id")
     * })
     */
    private $examen;

    /**
     * @var \GroupeComposition
     *
     * @ORM\ManyToOne(targetEntity="GroupeComposition", inversedBy="programmegroupecomposition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="groupe_composition_id", referencedColumnName="id")
     * })
     */
    private $groupeComposition;

    /**
     * @var \ProgrammeExamenNiveau
     *
     * @ORM\ManyToOne(targetEntity="ProgrammeExamenNiveau", inversedBy="programmegroupecomposition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="programme_id", referencedColumnName="id")
     * })
     */
    private $programmeexamenniveau;

    /**
     * @var \Salle
     *
     * @ORM\ManyToOne(targetEntity="Salle")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="salle_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $salle;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\Surveillance", mappedBy="programmegroupecomposition")
     */
    private $surveillances;


    public function __construct()
    {
        $this->isDeleted = false;
        // $this->heure_debut = (new \DateTime())->format("HH:MM");
        // $this->heure_fin = (new \DateTime())->format("HH:MM");

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
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ProgrammeGroupeComposition
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ProgrammeGroupeComposition
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
     * @return ProgrammeGroupeComposition
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return ProgrammeGroupeComposition
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }


    /**
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return ProgrammeGroupeComposition
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
     * @return ProgrammeGroupeComposition
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

    /**
     * Set deletedBy
     *
     * @param \UserBundle\Entity\User $deletedBy
     *
     * @return ProgrammeGroupeComposition
     */
    public function setDeletedBy(\UserBundle\Entity\User $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    /**
     * Get deletedBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set examen
     *
     * @param \ISI\ISIBundle\Entity\Examen $examen
     *
     * @return ProgrammeGroupeComposition
     */
    public function setExamen(\ISI\ISIBundle\Entity\Examen $examen = null)
    {
        $this->examen = $examen;

        return $this;
    }

    /**
     * Get examen
     *
     * @return \ISI\ISIBundle\Entity\Examen
     */
    public function getExamen()
    {
        return $this->examen;
    }

    /**
     * Set groupeComposition
     *
     * @param \ISI\ISIBundle\Entity\GroupeComposition $groupeComposition
     *
     * @return ProgrammeGroupeComposition
     */
    public function setGroupeComposition(\ISI\ISIBundle\Entity\GroupeComposition $groupeComposition = null)
    {
        $this->groupeComposition = $groupeComposition;

        return $this;
    }

    /**
     * Get groupeComposition
     *
     * @return \ISI\ISIBundle\Entity\GroupeComposition
     */
    public function getGroupeComposition()
    {
        return $this->groupeComposition;
    }

    /**
     * Set salle
     *
     * @param \ISI\ISIBundle\Entity\Salle $salle
     *
     * @return ProgrammeGroupeComposition
     */
    public function setSalle(\ISI\ISIBundle\Entity\Salle $salle = null)
    {
        $this->salle = $salle;

        return $this;
    }

    /**
     * Get salle
     *
     * @return \ISI\ISIBundle\Entity\Salle
     */
    public function getSalle()
    {
        return $this->salle;
    }

    /**
     * Add surveillances
     *
     * @param \ISI\ISIBundle\Entity\Surveillance $surveillances
     *
     * @return ProgrammeGroupeComposition
     */
    public function addSurveillance(\ISI\ISIBundle\Entity\Surveillance $surveillances)
    {
        $this->surveillances[] = $surveillances;

        return $this;
    }

    /**
     * Remove surveillances
     *
     * @param \ISI\ISIBundle\Entity\Surveillance $surveillances
     */
    public function removeSurveillance(\ISI\ISIBundle\Entity\Surveillance $surveillances)
    {
        $this->surveillances->removeElement($surveillances);
    }

    /**
     * Get surveillances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSurveillance()
    {
        return $this->surveillances;
    }

    /**
     * Get surveillances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSurveillances()
    {
        return $this->surveillances;
    }

    /**
     * Set programmeexamenniveau
     *
     * @param \ISI\ISIBundle\Entity\ProgrammeExamenNiveau $programmeexamenniveau
     *
     * @return ProgrammeGroupeComposition
     */
    public function setProgrammeexamenniveau(\ISI\ISIBundle\Entity\ProgrammeExamenNiveau $programmeexamenniveau = null)
    {
        $this->programmeexamenniveau = $programmeexamenniveau;

        return $this;
    }

    /**
     * Get programmeexamenniveau
     *
     * @return \ISI\ISIBundle\Entity\ProgrammeExamenNiveau
     */
    public function getProgrammeexamenniveau()
    {
        return $this->programmeexamenniveau;
    }
}
