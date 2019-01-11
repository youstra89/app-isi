<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Moyenne
 *
 * @ORM\Table(name="moyenne", uniqueConstraints={@ORM\UniqueConstraint(name="moyenne", columns={"eleve", "examen"})}, indexes={@ORM\Index(name="eleve", columns={"eleve"}), @ORM\Index(name="examen", columns={"examen"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\MoyenneRepository")
 */
class Moyenne
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
     * @ORM\ManyToOne(targetEntity="Eleve")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eleve", referencedColumnName="eleve_id")
     * })
     */
    private $eleve;

    /**
     * @var \Examen
     *
     * @ORM\ManyToOne(targetEntity="Examen")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="examen", referencedColumnName="examen_id")
     * })
     */
    private $examen;

    /**
     * @var float
     *
     * @ORM\Column(name="total_points", type="float", nullable=false)
     */
    private $totalPoints;

    /**
     * @var float
     *
     * @ORM\Column(name="moyenne", type="float", nullable=false)
     */
    private $moyenne;

    /**
     * @var integer
     *
     * @ORM\Column(name="rang", type="float", nullable=true)
     */
    private $rang;

    /**
     * @var float
     *
     * @ORM\Column(name="moyenne_annuelle", type="float", nullable=true)
     */
    private $moyenneAnnuelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="classement_annuel", type="integer", nullable=true)
     */
    private $classementAnnuel;

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
     * @return Moyenne
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
     * Set examen
     *
     * @param \ISI\ISIBundle\Entity\Examen $examen
     *
     * @return Fichedenote
     */
    public function setExamen(\ISI\ISIBundle\Entity\Examen $examen = null)
    {
        $this->examen = $examen;

        return $this;
    }

    /**
     * Get examen
     *
     * @return \ISI\ISIBundle\Entity\Examen
     */
    public function getExamen()
    {
        return $this->examen;
    }

    /**
     * Set moyenne
     *
     * @param integer $moyenne
     *
     * @return Moyenne
     */
    public function setMoyenne($moyenne)
    {
        $this->moyenne = $moyenne;

        return $this;
    }

    /**
     * Get moyenne
     *
     * @return integer
     */
    public function getMoyenne()
    {
        return $this->moyenne;
    }

    /**
     * Set rang
     *
     * @param integer $rang
     *
     * @return Moyenne
     */
    public function setRang($rang)
    {
        $this->rang = $rang;

        return $this;
    }

    /**
     * Get rang
     *
     * @return integer
     */
    public function getRang()
    {
        return $this->rang;
    }

    /**
     * Set moyenneAnnuelle
     *
     * @param float $moyenneAnnuelle
     *
     * @return Moyenne
     */
    public function setMoyenneAnnuelle($moyenneAnnuelle)
    {
        $this->moyenneAnnuelle = $moyenneAnnuelle;

        return $this;
    }

    /**
     * Get moyenneAnnuelle
     *
     * @return float
     */
    public function getMoyenneAnnuelle()
    {
        return $this->moyenneAnnuelle;
    }
    /**
     * Set classementAnnuel
     *
     * @param integer $classementAnnuel
     *
     * @return Moyenne
     */
    public function setClassementAnnuel($classementAnnuel)
    {
        $this->classementAnnuel = $classementAnnuel;

        return $this;
    }

    /**
     * Get classementAnnuel
     *
     * @return integer
     */
    public function getClassementAnnuel()
    {
        return $this->classementAnnuel;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Moyenne
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
     * @return Moyenne
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
     * Set totalPoints
     *
     * @param float $totalPoints
     *
     * @return Moyenne
     */
    public function setTotalPoints($totalPoints)
    {
        $this->totalPoints = $totalPoints;

        return $this;
    }

    /**
     * Get totalPoints
     *
     * @return float
     */
    public function getTotalPoints()
    {
        return $this->totalPoints;
    }
}
