<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Memoriser
 *
 * @ORM\Table(name="memoriser", uniqueConstraints={@ORM\UniqueConstraint(name="memorisation", columns={"eleve", "halaqa", "annee_scolaire"})}, indexes={@ORM\Index(name="eleve", columns={"eleve"}), @ORM\Index(name="halaqa", columns={"halaqa"}), @ORM\Index(name="anneeScolaire", columns={"annee_scolaire"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\MemoriserRepository")
 */
class Memoriser
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
     * @var \Eleve
     *
     * @ORM\ManyToOne(targetEntity="ISI\ISIBundle\Entity\Eleve", inversedBy="memoriser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eleve", referencedColumnName="eleve_id")
     * })
     */
    private $eleve;

    /**
     * @var \Halaqa
     *
     * @ORM\ManyToOne(targetEntity="Halaqa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="halaqa", referencedColumnName="id")
     * })
     */
    private $halaqa;

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
     * Set eleve
     *
     * @param \ISI\ISIBundle\Entity\Eleve $eleve
     *
     * @return Memoriser
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
     * Set halaqa
     *
     * @param \ISI\ISIBundle\Entity\Halaqa $halaqa
     *
     * @return Memoriser
     */
    public function setHalaqa(\ISI\ISIBundle\Entity\Halaqa $halaqa = null)
    {
        $this->halaqa = $halaqa;

        return $this;
    }

    /**
     * Get halaqa
     *
     * @return \ISI\ISIBundle\Entity\Halaqa
     */
    public function getHalaqa()
    {
        return $this->halaqa;
    }

    /**
     * Set anneeScolaire
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire
     *
     * @return Memoriser
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
     * @return Memoriser
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
     * @return Memoriser
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
