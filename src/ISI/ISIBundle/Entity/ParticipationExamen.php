<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParticipationExamen
 *
 * @ORM\Table(name="participation_examen", uniqueConstraints={@ORM\UniqueConstraint(name="participe", columns={"eleve_id", "matiere_id", "examen_id"})}, indexes={@ORM\Index(name="examen_id", columns={"examen_id"}), @ORM\Index(name="matiere_id", columns={"matiere_id"}), @ORM\Index(name="eleve_id", columns={"eleve_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\ParticipationExamenRepository")
 */
class ParticipationExamen
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @var \Examen
     *
     * @ORM\ManyToOne(targetEntity="Examen")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="examen_id", referencedColumnName="id")
     * })
     */
    private $examen;

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
     * @var \Matiere
     *
     * @ORM\ManyToOne(targetEntity="Matiere")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="matiere_id", referencedColumnName="id")
     * })
     */
    private $matiere;

    /**
     * @var boolean
     *
     * @ORM\Column(name="participation", type="boolean", nullable=false)
     */
    private $participation;


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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ParticipationExamen
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
     * @return ParticipationExamen
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
     * @return ParticipationExamen
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
     * @return ParticipationExamen
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
     * Set examen
     *
     * @param \ISI\ISIBundle\Entity\Examen $examen
     *
     * @return ParticipationExamen
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
     * Set eleve
     *
     * @param \ISI\ISIBundle\Entity\Eleve $eleve
     *
     * @return ParticipationExamen
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
     * Set matiere
     *
     * @param \ISI\ISIBundle\Entity\Matiere $matiere
     *
     * @return ParticipationExamen
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
     * Set participation
     *
     * @param boolean $participation
     *
     * @return ParticipationExamen
     */
    public function setParticipation($participation)
    {
        $this->participation = $participation;

        return $this;
    }

    /**
     * Get participation
     *
     * @return boolean
     */
    public function getParticipation()
    {
        return $this->participation;
    }
}
