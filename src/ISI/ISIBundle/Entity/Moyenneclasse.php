<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Moyenneclasse
 *
 * @ORM\Table(name="moyenneclasse", uniqueConstraints={@ORM\UniqueConstraint(name="fichedenote", columns={"classe", "examen"})}, indexes={@ORM\Index(name="classe", columns={"classe"}), @ORM\Index(name="examen", columns={"examen"})})
 * @ORM\Entity
 */
class Moyenneclasse
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
     * @ORM\Column(name="admis", type="integer", nullable=true)
     */
    private $admis;

    /**
     * @var integer
     *
     * @ORM\Column(name="recales", type="integer", nullable=true)
     */
    private $recales;

    /**
     * @var \Classe
     *
     * @ORM\ManyToOne(targetEntity="Classe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="classe", referencedColumnName="classe_id")
     * })
     */
    private $classe;

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
     * Set classe
     *
     * @param \ISI\ISIBundle\Entity\Classe $classe
     *
     * @return Fichedenote
     */
    public function setClasse(\ISI\ISIBundle\Entity\Classe $classe = null)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return \ISI\ISIBundle\Entity\Classe
     */
    public function getClasse()
    {
        return $this->classe;
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
     * Set admis
     *
     * @param integer $admis
     *
     * @return Moyenneclasse
     */
    public function setAdmis($admis)
    {
        $this->admis = $admis;

        return $this;
    }

    /**
     * Get admis
     *
     * @return integer
     */
    public function getAdmis()
    {
        return $this->admis;
    }

    /**
     * Set recales
     *
     * @param integer $recales
     *
     * @return Moyenneclasse
     */
    public function setRecales($recales)
    {
        $this->recales = $recales;

        return $this;
    }

    /**
     * Get recales
     *
     * @return integer
     */
    public function getRecales()
    {
        return $this->recales;
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


}
