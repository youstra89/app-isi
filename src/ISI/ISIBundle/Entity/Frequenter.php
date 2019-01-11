<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Frequenter
 *
 * @ORM\Table(name="frequenter", uniqueConstraints={@ORM\UniqueConstraint(name="frequenter", columns={"annee_scolaire_id", "eleve_id", "classe_id"})}, indexes={@ORM\Index(name="eleve_id", columns={"eleve_id"}), @ORM\Index(name="classe_id", columns={"classe_id"}), @ORM\Index(name="annee_scolaire_id", columns={"annee_scolaire_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\FrequenterRepository")
 * @UniqueEntity(
 *     fields={"eleve", "anneeScolaire", "classe"},
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
     * @ORM\Column(name="admission", type="string", length=255, nullable=false)
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
     * @var \Eleve
     *
     * @ORM\ManyToOne(targetEntity="ISI\ISIBundle\Entity\Eleve", inversedBy="frequenter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eleve_id", referencedColumnName="eleve_id")
     * })
     */
    private $eleve;

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
     * @var \Classe
     *
     * @ORM\ManyToOne(targetEntity="Classe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="classe_id", referencedColumnName="classe_id")
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
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Frequenter
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
     * @return Frequenter
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
     * Set anneeScolaire
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire
     *
     * @return Frequenter
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
}
