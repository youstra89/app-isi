<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Examen
 *
 * @ORM\Table(name="examen", indexes={@ORM\Index(name="anneescolaire_examen_fk", columns={"annee_scolaire_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\ExamenRepository")
 */
class Examen
{
    /**
     * @var integer
     *
     * @ORM\Column(name="examen_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $examenId;

    /**
     * @var integer
     *
     * @ORM\Column(name="session", type="integer", nullable=false)
     */
     private $session;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_fr", type="string", length=255, nullable=false)
     */
    private $libelleFr;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_ar", type="string", length=255, nullable=false)
     */
    private $libelleAr;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_proclamation_resultats", type="date", nullable=false)
     */
    private $dateProclamationResultats;

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
     * Get examenId
     *
     * @return integer
     */
    public function getExamenId()
    {
        return $this->examenId;
    }

    /**
     * Set session
     *
     * @return integer
     */
    public function setSession($session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get session
     *
     * @return integer
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set libelleFr
     *
     * @param string $libelleFr
     *
     * @return Examen
     */
    public function setLibelleFr($libelleFr)
    {
        $this->libelleFr = $libelleFr;

        return $this;
    }

    /**
     * Get libelleFr
     *
     * @return string
     */
    public function getLibelleFr()
    {
        return $this->libelleFr;
    }

    /**
     * Set libelleAr
     *
     * @param string $libelleAr
     *
     * @return Examen
     */
    public function setLibelleAr($libelleAr)
    {
        $this->libelleAr = $libelleAr;

        return $this;
    }

    /**
     * Get libelleAr
     *
     * @return string
     */
    public function getLibelleAr()
    {
        return $this->libelleAr;
    }

    /**
     * Set dateProclamationResultats
     *
     * @param \DateTime $dateProclamationResultats
     *
     * @return Examen
     */
    public function setDateProclamationResultats($dateProclamationResultats)
    {
        $this->dateProclamationResultats = $dateProclamationResultats;

        return $this;
    }

    /**
     * Get dateProclamationResultats
     *
     * @return \DateTime
     */
    public function getDateProclamationResultats()
    {
        return $this->dateProclamationResultats;
    }

    /**
     * Set anneeScolaire
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire
     *
     * @return Examen
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
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Examen
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
     * @return Examen
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
}
