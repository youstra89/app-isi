<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Examen
 *
 * @ORM\Table(name="examen", indexes={@ORM\Index(name="anneescolaire_examen_fk", columns={"annee_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\ExamenRepository")
 */
class Examen
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_publication_programme", type="date", nullable=true)
     */
    private $datePublicationProgramme;

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
     * Set annee
     *
     * @param \ISI\ISIBundle\Entity\Annee $annee
     *
     * @return Examen
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

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Examen
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
     * @return Examen
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
     * @return Examen
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
     * @return Examen
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
     * @return Examen
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
     * @return Examen
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
     * Set datePublicationProgramme
     *
     * @param \DateTime $datePublicationProgramme
     *
     * @return Examen
     */
    public function setDatePublicationProgramme($datePublicationProgramme)
    {
        $this->datePublicationProgramme = $datePublicationProgramme;

        return $this;
    }

    /**
     * Get datePublicationProgramme
     *
     * @return \DateTime
     */
    public function getDatePublicationProgramme()
    {
        return $this->datePublicationProgramme;
    }
}
