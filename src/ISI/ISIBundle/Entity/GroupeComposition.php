<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * GroupeComposition
 * @ORM\Table(name="groupe_composition", uniqueConstraints={@ORM\UniqueConstraint(name="uniq_groupe_composition", columns={"classe_id", "examen_id", "libelle"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\GroupeCompositionRepository")
 */
class GroupeComposition
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
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, nullable=false)
     */
    private $libelle;

    /**
     * @var \Classe
     *
     * @ORM\ManyToOne(targetEntity="Classe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="classe_id", referencedColumnName="id")
     * })
     */
    private $classe;

    /**
     * @var \Examen
     *
     * @ORM\ManyToOne(targetEntity="Examen")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="examen_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $examen;

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
     * @var boolean
     *
     * @ORM\Column(name="redouble", type="boolean", nullable=false)
     */
    private $isDeleted;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\EleveGroupeComposition", mappedBy="groupeComposition")
     */
    private $elevegroupecomposition;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\ProgrammeGroupeComposition", mappedBy="groupeComposition")
     */
    private $programmegroupecomposition;


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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return GroupeComposition
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle($optionLibelle = null)
    {
        if($optionLibelle == 1)
            return $this->classe->getLibelleAr()." - ".$this->libelle;

        return $this->libelle;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return GroupeComposition
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
     * @return GroupeComposition
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
     * @return GroupeComposition
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
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return GroupeComposition
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
     * Set classe
     *
     * @param \ISI\ISIBundle\Entity\Classe $classe
     *
     * @return GroupeComposition
     */
    public function setClasse(\ISI\ISIBundle\Entity\Classe $classe = null)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return \ISI\ISIBundle\Entity\Classe
     */
    public function getClasse()
    {
        return $this->classe;
    }

    /**
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return GroupeComposition
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
     * @return GroupeComposition
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
     * @return GroupeComposition
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
     * @return GroupeComposition
     */
    public function setExamen(\ISI\ISIBundle\Entity\Examen $examen)
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
     * Add elevegroupecomposition
     *
     * @param \ISI\ISIBundle\Entity\EleveGroupeComposition $elevegroupecomposition
     *
     * @return GroupeComposition
     */
    public function addElevegroupecomposition(\ISI\ISIBundle\Entity\EleveGroupeComposition $elevegroupecomposition)
    {
        $this->elevegroupecomposition[] = $elevegroupecomposition;

        return $this;
    }

    /**
     * Remove elevegroupecomposition
     *
     * @param \ISI\ISIBundle\Entity\EleveGroupeComposition $elevegroupecomposition
     */
    public function removeElevegroupecomposition(\ISI\ISIBundle\Entity\EleveGroupeComposition $elevegroupecomposition)
    {
        $this->elevegroupecomposition->removeElement($elevegroupecomposition);
    }

    /**
     * Get elevegroupecomposition
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getElevegroupecomposition()
    {
        return $this->elevegroupecomposition;
    }

    /**
     * Add programmegroupecomposition
     *
     * @param \ISI\ISIBundle\Entity\ProgrammeGroupeComposition $programmegroupecomposition
     *
     * @return GroupeComposition
     */
    public function addProgrammegroupecomposition(\ISI\ISIBundle\Entity\ProgrammeGroupeComposition $programmegroupecomposition)
    {
        $this->programmegroupecomposition[] = $programmegroupecomposition;

        return $this;
    }

    /**
     * Remove programmegroupecomposition
     *
     * @param \ISI\ISIBundle\Entity\ProgrammeGroupeComposition $programmegroupecomposition
     */
    public function removeProgrammegroupecomposition(\ISI\ISIBundle\Entity\ProgrammeGroupeComposition $programmegroupecomposition)
    {
        $this->programmegroupecomposition->removeElement($programmegroupecomposition);
    }

    /**
     * Get programmegroupecomposition
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProgrammegroupecomposition()
    {
        return $this->programmegroupecomposition;
    }
}
