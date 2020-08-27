<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Annee
 *
 * @ORM\Table(name="annee", indexes={@ORM\Index(name="libelle", columns={"libelle"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\AnneeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Annee
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
     * @var \DateTime
     *
     * @ORM\Column(name="debut_premier_semestre", type="date", nullable=true)
     */
    private $debutPremierSemestre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fin_premier_semestre", type="date", nullable=true)
     */
    private $finPremierSemestre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="debut_second_semestre", type="date", nullable=true)
     */
    private $debutSecondSemestre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fin_second_semestre", type="date", nullable=true)
     */
    private $finSecondSemestre;

    /**
     * @var boolean
     *
     * @ORM\Column(name="achevee", type="boolean", nullable=false)
     */
    private $achevee = false;

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
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Annee
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set achevee
     *
     * @param string $achevee
     *
     * @return Annee
     */
    public function setAchevee($achevee)
    {
        $this->achevee = $achevee;

        return $this;
    }

    /**
     * Get achevee
     *
     * @return string
     */
    public function getAchevee()
    {
        return $this->achevee;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Annee
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     *
     * @return Annee
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

    //Méthode de mise à jour de la date de création
    /**
     * @ORM\PrePersist
     */
    public function saveDate()
    {
      $this->setCreatedAt(new \Datetime());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
      $this->setupdated_at(new \Datetime());
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Annee
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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Annee
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
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return Annee
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
     * @return Annee
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
     * Set debutPremierSemestre
     *
     * @param \DateTime $debutPremierSemestre
     *
     * @return Annee
     */
    public function setDebutPremierSemestre($debutPremierSemestre)
    {
        $this->debutPremierSemestre = $debutPremierSemestre;

        return $this;
    }

    /**
     * Get debutPremierSemestre
     *
     * @return \DateTime
     */
    public function getDebutPremierSemestre()
    {
        return $this->debutPremierSemestre;
    }

    /**
     * Set finPremierSemestre
     *
     * @param \DateTime $finPremierSemestre
     *
     * @return Annee
     */
    public function setFinPremierSemestre($finPremierSemestre)
    {
        $this->finPremierSemestre = $finPremierSemestre;

        return $this;
    }

    /**
     * Get finPremierSemestre
     *
     * @return \DateTime
     */
    public function getFinPremierSemestre()
    {
        return $this->finPremierSemestre;
    }

    /**
     * Set debutSecondSemestre
     *
     * @param \DateTime $debutSecondSemestre
     *
     * @return Annee
     */
    public function setDebutSecondSemestre($debutSecondSemestre)
    {
        $this->debutSecondSemestre = $debutSecondSemestre;

        return $this;
    }

    /**
     * Get debutSecondSemestre
     *
     * @return \DateTime
     */
    public function getDebutSecondSemestre()
    {
        return $this->debutSecondSemestre;
    }

    /**
     * Set finSecondSemestre
     *
     * @param \DateTime $finSecondSemestre
     *
     * @return Annee
     */
    public function setFinSecondSemestre($finSecondSemestre)
    {
        $this->finSecondSemestre = $finSecondSemestre;

        return $this;
    }

    /**
     * Get finSecondSemestre
     *
     * @return \DateTime
     */
    public function getFinSecondSemestre()
    {
        return $this->finSecondSemestre;
    }
}
