<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Paiementinternat
 *
 * @ORM\Table(name="paiementinternat", uniqueConstraints={@ORM\UniqueConstraint(name="paiement_unique", columns={"interner_id", "moisdepaiement_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\PaiementinternatRepository")
 */
class Paiementinternat
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
     * @var \Interner
     *
     * @ORM\ManyToOne(targetEntity="Interner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="interner_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $interner;

    /**
     * @var \Mois
     *
     * @ORM\ManyToOne(targetEntity="Moisdepaiementinternat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="moisdepaiement_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $moisdepaiement;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer", length=255, nullable=false)
     */
    private $montant;

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
     * Set interner
     *
     * @param \ISI\ISIBundle\Entity\Interner $interner
     *
     * @return Paiementinternat
     */
    public function setInterner(\ISI\ISIBundle\Entity\Interner $interner = null)
    {
        $this->interner = $interner;

        return $this;
    }

    /**
     * Get interner
     *
     * @return \ISI\ISIBundle\Entity\Interner
     */
    public function getInterner()
    {
        return $this->interner;
    }

    /**
     * Set moisdepaiement
     *
     * @param \ISI\ISIBundle\Entity\Moisdepaiement $mois
     *
     * @return Paiementinternat
     */
    public function setMoisdepaiement(\ISI\ISIBundle\Entity\Moisdepaiementinternat $moisdepaiement = null)
    {
        $this->moisdepaiement = $moisdepaiement;

        return $this;
    }

    /**
     * Get moisdepaiement
     *
     * @return \ISI\ISIBundle\Entity\Moisdepaiement
     */
    public function getMoisdepaiement()
    {
        return $this->moisdepaiement;
    }

    /**
     * Set montant
     *
     * @param integer $montant
     *
     * @return Paiementinternat
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return integer
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Paiementinternat
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
     * @return Paiementinternat
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Paiementinternat
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
     * @return Paiementinternat
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
     * @return Paiementinternat
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
     * @return Paiementinternat
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
