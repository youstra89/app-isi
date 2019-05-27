<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Absence
 *
 * @ORM\Table(name="absence", uniqueConstraints={@ORM\UniqueConstraint(name="absence", columns={"mois_id", "eleve_id", "annee_id"})}, indexes={@ORM\Index(name="eleve_id", columns={"eleve_id"}), @ORM\Index(name="annee_id", columns={"annee_id"}), @ORM\Index(name="mois_id", columns={"mois_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\AbsenceRepository")
 */
class Absence
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
     * @var integer
     *
     * @ORM\Column(name="heure_cours", type="integer", length=255, nullable=true)
     */
    private $heureCours;

    /**
     * @var integer
     *
     * @ORM\Column(name="heure_coran", type="integer", length=255, nullable=true)
     */
    private $heureCoran;

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
     * @var \Annee
     *
     * @ORM\ManyToOne(targetEntity="Annee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_id", referencedColumnName="id")
     * })
     */
    private $annee;

    /**
     * @var \Mois
     *
     * @ORM\ManyToOne(targetEntity="Mois")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mois_id", referencedColumnName="id")
     * })
     */
    private $mois;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set heureCours
     *
     * @param integer $heureCours
     *
     * @return Absence
     */
    public function setHeureCours($heureCours)
    {
        $this->heureCours = $heureCours;

        return $this;
    }

    /**
     * Get heureCours
     *
     * @return integer
     */
    public function getHeureCours()
    {
        return $this->heureCours;
    }

    /**
     * Set heureCoran
     *
     * @param integer $heureCoran
     *
     * @return Absence
     */
    public function setHeureCoran($heureCoran)
    {
        $this->heureCoran = $heureCoran;

        return $this;
    }

    /**
     * Get heureCoran
     *
     * @return integer
     */
    public function getHeureCoran()
    {
        return $this->heureCoran;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Absence
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
     * @return Absence
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
     * @return Absence
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
     * Set mois
     *
     * @param \ISI\ISIBundle\Entity\Mois $mois
     *
     * @return Absence
     */
    public function setMois(\ISI\ISIBundle\Entity\Mois $mois = null)
    {
        $this->mois = $mois;

        return $this;
    }

    /**
     * Get mois
     *
     * @return \ISI\ISIBundle\Entity\Mois
     */
    public function getMois()
    {
        return $this->mois;
    }


    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Absence
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
     * @return Absence
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
