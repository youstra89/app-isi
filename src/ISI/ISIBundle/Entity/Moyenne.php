<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Moyenne
 *
 * @ORM\Table(name="moyenne", uniqueConstraints={@ORM\UniqueConstraint(name="moyenne", columns={"eleve_id", "examen_id"})})
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
     * @ORM\ManyToOne(targetEntity="Eleve", inversedBy="moyenne")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eleve_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $eleve;

    /**
     * @var \Examen
     *
     * @ORM\ManyToOne(targetEntity="Examen")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="examen_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $examen;

    /**
     * @var float
     *
     * @ORM\Column(name="total_points", type="float", nullable=true)
     */
    private $totalPoints;

    /**
     * @var float
     *
     * @ORM\Column(name="moyenne", type="float", nullable=true)
     */
    private $moyenne;

    /**
     * @var integer
     *
     * @ORM\Column(name="rang", nullable=true)
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Moyenne
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
     * @return Moyenne
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

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Moyenne
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
     * @return Moyenne
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
}
