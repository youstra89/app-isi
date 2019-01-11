<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Absence
 *
 * @ORM\Table(name="absence", uniqueConstraints={@ORM\UniqueConstraint(name="absence", columns={"mois_id", "eleve_id", "annee_scolaire_id"})}, indexes={@ORM\Index(name="eleve_id", columns={"eleve_id"}), @ORM\Index(name="annee_scolaire_id", columns={"annee_scolaire_id"}), @ORM\Index(name="mois_id", columns={"mois_id"})})
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
     * @ORM\Column(name="date_save", type="datetime", nullable=false)
     */
    private $dateSave;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_update", type="datetime", nullable=false)
     */
    private $dateUpdate;

    /**
     * @var \Anneescolaire
     *
     * @ORM\ManyToOne(targetEntity="Anneescolaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_scolaire_id", referencedColumnName="annee_scolaire_id")
     * })
     */
    private $anneeScolaire;

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
     *   @ORM\JoinColumn(name="eleve_id", referencedColumnName="eleve_id")
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
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Absence
     */
    public function setDateSave($dateSave)
    {
        $this->dateSave = $dateSave;

        return $this;
    }

    /**
     * Get dateSave
     *
     * @return \DateTime
     */
    public function getDateSave()
    {
        return $this->dateSave;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     *
     * @return Absence
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set anneeScolaire
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire
     *
     * @return Absence
     */
    public function setAnneeScolaire(\ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire = null)
    {
        $this->anneeScolaire = $anneeScolaire;

        return $this;
    }

    /**
     * Get anneeScolaire
     *
     * @return \ISI\ISIBundle\Entity\Anneescolaire
     */
    public function getAnneeScolaire()
    {
        return $this->anneeScolaire;
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

}
