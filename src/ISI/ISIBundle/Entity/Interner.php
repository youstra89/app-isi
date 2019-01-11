<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Interner
 *
 * @ORM\Table(name="interner", uniqueConstraints={@ORM\UniqueConstraint(name="interner", columns={"chambre_id", "eleve_id", "annee_scolaire_id"})}, indexes={@ORM\Index(name="eleve_id", columns={"eleve_id"}), @ORM\Index(name="annee_scolaire_id", columns={"annee_scolaire_id"}), @ORM\Index(name="chambre_id", columns={"chambre_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\InternerRepository")
 */
class Interner
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
     * @var \Eleve
     *
     * @ORM\ManyToOne(targetEntity="Eleve", inversedBy="interner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eleve_id", referencedColumnName="eleve_id")
     * })
     */
    private $eleve;

    /**
     * @var \Chambre
     *
     * @ORM\ManyToOne(targetEntity="Chambre")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="chambre_id", referencedColumnName="id")
     * })
     */
    private $chambre;

    /**
     * @var boolean
     *
     * @ORM\Column(name="renvoye", type="boolean", nullable=false)
     */
    private $renvoye = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_renvoi", type="datetime", nullable=true)
     */
    private $dateRenvoi;



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
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Interner
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
     * @return Interner
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
     * @return Interner
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
     * @return Interner
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
     * Set chambre
     *
     * @param \ISI\ISIBundle\Entity\Chambre $chambre
     *
     * @return Interner
     */
    public function setChambre(\ISI\ISIBundle\Entity\Chambre $chambre = null)
    {
        $this->chambre = $chambre;

        return $this;
    }

    /**
     * Get chambre
     *
     * @return \ISI\ISIBundle\Entity\Chambre
     */
    public function getChambre()
    {
        return $this->chambre;
    }

    /**
     * Set renvoye
     *
     * @param boolean $renvoye
     *
     * @return Eleve
     */
    public function setRenvoye($renvoye)
    {
        $this->renvoye = $renvoye;

        return $this;
    }

    /**
     * Get renvoye
     *
     * @return boolean
     */
    public function getRenvoye()
    {
        return $this->renvoye;
    }

    /**
     * Set dateRenvoi
     *
     * @param \DateTime $dateRenvoi
     *
     * @return Eleve
     */
    public function setDateRenvoi($dateRenvoi)
    {
        $this->dateRenvoi = $dateRenvoi;

        return $this;
    }

    /**
     * Get dateRenvoi
     *
     * @return \DateTime
     */
    public function getDateRenvoi()
    {
        return $this->dateRenvoi;
    }
}
