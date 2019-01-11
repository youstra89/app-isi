<?php

namespace ISI\ENSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnneeContrat
 *
 * @ORM\Table(name="annee_contrat")
 * @ORM\Entity(repositoryClass="ISI\ENSBundle\Repository\AnneeContratRepository")
 */
class AnneeContrat
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
     * @var \Anneescolaire
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\Anneescolaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee", referencedColumnName="annee_scolaire_id")
     * })
     */
    private $annee;

    /**
     * @var \Contrat
     *
     * @ORM\ManyToOne(targetEntity="Contrat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contrat", referencedColumnName="id")
     * })
     */
    private $contrat;

    /**
     * @var int
     *
     * @ORM\Column(name="nombre_heures_cours", type="integer")
     */
    private $nombreHeuresCours;

    /**
     * @var int
     *
     * @ORM\Column(name="nombre_heures_coran", type="integer")
     */
    private $nombreHeuresCoran;

    /**
     * @var int
     *
     * @ORM\Column(name="nombre_heures_samedi", type="integer")
     */
    private $nombreHeuresSamedi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_save", type="datetime")
     */
    private $dateSave;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_update", type="datetime")
     */
    private $dateUpdate;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set annee
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $annee
     *
     * @return AnneeContrat
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return \ISI\ISIBundle\Entity\Anneescolaire
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set contrat
     *
     * @param \ISI\ENSBundle\Entity\Contrat $contrat
     *
     * @return AnneeContrat
     */
    public function setContrat($contrat)
    {
        $this->contrat = $contrat;

        return $this;
    }

    /**
     * Get contrat
     *
     * @return \ISI\ENSBundle\Entity\Contrat
     */
    public function getContrat()
    {
        return $this->contrat;
    }

    /**
     * Set nombreHeuresCours
     *
     * @param integer $nombreHeuresCours
     *
     * @return AnneeContrat
     */
    public function setNombreHeuresCours($nombreHeuresCours)
    {
        $this->nombreHeuresCours = $nombreHeuresCours;

        return $this;
    }

    /**
     * Get nombreHeuresCours
     *
     * @return int
     */
    public function getNombreHeuresCours()
    {
        return $this->nombreHeuresCours;
    }

    /**
     * Set nombreHeuresCoran
     *
     * @param integer $nombreHeuresCoran
     *
     * @return AnneeContrat
     */
    public function setNombreHeuresCoran($nombreHeuresCoran)
    {
        $this->nombreHeuresCoran = $nombreHeuresCoran;

        return $this;
    }

    /**
     * Get nombreHeuresCoran
     *
     * @return int
     */
    public function getNombreHeuresCoran()
    {
        return $this->nombreHeuresCoran;
    }

    /**
     * Set nombreHeuresSamedi
     *
     * @param integer $nombreHeuresSamedi
     *
     * @return AnneeContrat
     */
    public function setNombreHeuresSamedi($nombreHeuresSamedi)
    {
        $this->nombreHeuresSamedi = $nombreHeuresSamedi;

        return $this;
    }

    /**
     * Get nombreHeuresSamedi
     *
     * @return int
     */
    public function getNombreHeuresSamedi()
    {
        return $this->nombreHeuresSamedi;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return AnneeContrat
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
     * @return AnneeContrat
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

