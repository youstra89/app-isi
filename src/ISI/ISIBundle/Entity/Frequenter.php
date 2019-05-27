<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Frequenter
 *
 * @ORM\Table(name="frequenter", uniqueConstraints={@ORM\UniqueConstraint(name="frequenter", columns={"annee_id", "eleve_id", "classe_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\FrequenterRepository")
 * @UniqueEntity(
 *     fields={"eleve_id", "annee_id", "classe_id"},
 *     errorPath="classe",
 *     message="Cet élève est déjà inscrit."
 * )
 */
class Frequenter
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
     * @ORM\Column(name="admission", type="string", length=255, nullable=true)
     */
    private $admission = NULL;

    /**
     * @var boolean
     *
     * @ORM\Column(name="redouble", type="boolean", nullable=false)
     */
    private $redouble;

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
     * @var \Eleve
     *
     * @ORM\ManyToOne(targetEntity="ISI\ISIBundle\Entity\Eleve", inversedBy="frequenter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eleve_id", referencedColumnName="id")
     * })
     */
    private $eleve;

    /**
     * @var \Annee
     *
     * @ORM\ManyToOne(targetEntity="Annee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_id", referencedColumnName="id")
     * })
     */
    private $annee;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set admission
     *
     * @param string $admission
     *
     * @return Frequenter
     */
    public function setAdmission($admission)
    {
        $this->admission = $admission;

        return $this;
    }

    /**
     * Get admission
     *
     * @return string
     */
    public function getAdmission()
    {
        return $this->admission;
    }

        /**
     * Set redouble
     *
     * @param boolean $redouble
     *
     * @return Frequenter
     */
    public function setRedouble($redouble)
    {
        $this->redouble = $redouble;

        return $this;
    }

    /**
     * Get redouble
     *
     * @return boolean
     */
    public function getRedouble()
    {
        return $this->redouble;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Frequenter
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
     * @return Frequenter
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
     * Set eleve
     *
     * @param \ISI\ISIBundle\Entity\Eleve $eleve
     *
     * @return Frequenter
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
     * Set classe
     *
     * @param \ISI\ISIBundle\Entity\Classe $classe
     *
     * @return Frequenter
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Frequenter
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
     * @return Frequenter
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
     * Set annee
     *
     * @param \ISI\ISIBundle\Entity\Annee $annee
     *
     * @return Frequenter
     */
    public function setAnnee(\ISI\ISIBundle\Entity\Annee $annee = null)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return \ISI\ISIBundle\Entity\Annee
     */
    public function getAnnee()
    {
        return $this->annee;
    }
}
