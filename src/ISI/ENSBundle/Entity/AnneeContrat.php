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
     * @var \Annee
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\Annee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_id", referencedColumnName="id")
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
     * @ORM\Column(name="created_at", type="datetime")
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set annee
     *
     * @param \ISI\ISIBundle\Entity\Annee $annee
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
     * @return \ISI\ISIBundle\Entity\Annee
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return AnneeContrat
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
     * @return AnneeContrat
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
     * @return AnneeContrat
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
     * @return AnneeContrat
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
}
