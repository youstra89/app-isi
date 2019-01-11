<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Paiementinternat
 *
 * @ORM\Table(name="paiementinternat", uniqueConstraints={@ORM\UniqueConstraint(name="paiement_unique", columns={"interner", "moisdepaiement"})}, indexes={@ORM\Index(name="interner", columns={"interner"}), @ORM\Index(name="mois", columns={"moisdepaiement"})})
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
     *   @ORM\JoinColumn(name="interner", referencedColumnName="id")
     * })
     */
    private $interner;

    /**
     * @var \Mois
     *
     * @ORM\ManyToOne(targetEntity="Moisdepaiementinternat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="moisdepaiement", referencedColumnName="id")
     * })
     */
    private $moisdepaiement;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer", length=255, nullable=true)
     */
    private $montant;

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
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Paiementinternat
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
     * @return Paiementinternat
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
