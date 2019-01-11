<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Moisdepaiementinternat
 *
 * @ORM\Table(name="moisdepaiementinternat", uniqueConstraints={@ORM\UniqueConstraint(name="mois_unique", columns={"annee_scolaire", "mois"})}, indexes={@ORM\Index(name="annee_scolaire", columns={"annee_scolaire"}), @ORM\Index(name="mois", columns={"mois"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\MoisdepaiementinternatRepository")
 */
class Moisdepaiementinternat
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
     * @var \Anneescolaire
     *
     * @ORM\ManyToOne(targetEntity="Anneescolaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_scolaire", referencedColumnName="annee_scolaire_id")
     * })
     */
    private $anneeScolaire;

    /**
     * @var \Mois
     *
     * @ORM\ManyToOne(targetEntity="Mois")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mois", referencedColumnName="id")
     * })
     */
    private $mois;

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
     * Set anneeScolaire
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire
     *
     * @return Moisdepaiementinternat
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
     * Set mois
     *
     * @param \ISI\ISIBundle\Entity\Mois $mois
     *
     * @return Moisdepaiementinternat
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
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Moisdepaiementinternat
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
     * @return Moisdepaiementinternat
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
