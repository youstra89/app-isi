<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * FrequenterMatiere
 *
 * @ORM\Table(name="frequenter_matiere", uniqueConstraints={@ORM\UniqueConstraint(name="frequenter_matiere", columns={"frequenter", "matiere"})}, indexes={@ORM\Index(name="frequenter", columns={"frequenter"}), @ORM\Index(name="matiere", columns={"matiere"})})
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"frequenter", "matiere"}
 * )
 */
class FrequenterMatiere
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
     * @var boolean
     *
     * @ORM\Column(name="validation", type="boolean", nullable=false)
     */
    private $validation;

    /**
     * @var float
     *
     * @ORM\Column(name="moyenne", type="float", nullable=true)
     */
    private $moyenne;

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
     * @var \Frequenter
     *
     * @ORM\ManyToOne(targetEntity="Frequenter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="frequenter", referencedColumnName="id")
     * })
     */
    private $frequenter;

    /**
     * @var \Matiere
     *
     * @ORM\ManyToOne(targetEntity="Matiere")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="matiere", referencedColumnName="matiere_id")
     * })
     */
    private $matiere;


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
     * Set validation
     *
     * @param boolean $validation
     *
     * @return FrequenterMatiere
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Get validation
     *
     * @return boolean
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Set moyenne
     *
     * @param float $moyenne
     *
     * @return FrequenterMatiere
     */
    public function setMoyenne($moyenne)
    {
        $this->moyenne = $moyenne;

        return $this;
    }

    /**
     * Get moyenne
     *
     * @return \DateTime
     */
    public function getMoyenne()
    {
        return $this->moyenne;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return FrequenterMatiere
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
     * @return FrequenterMatiere
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
     * Set frequenter
     *
     * @param \ISI\ISIBundle\Entity\Frequenter $frequenter
     *
     * @return FrequenterMatiere
     */
    public function setFrequenter(\ISI\ISIBundle\Entity\Frequenter $frequenter = null)
    {
        $this->frequenter = $frequenter;

        return $this;
    }

    /**
     * Get frequenter
     *
     * @return \ISI\ISIBundle\Entity\Frequenter
     */
    public function getFrequenter()
    {
        return $this->frequenter;
    }

    /**
     * Set matiere
     *
     * @param \ISI\ISIBundle\Entity\Matiere $matiere
     *
     * @return FrequenterMatiere
     */
    public function setMatiere(\ISI\ISIBundle\Entity\Matiere $matiere = null)
    {
        $this->matiere = $matiere;

        return $this;
    }

    /**
     * Get matiere
     *
     * @return \ISI\ISIBundle\Entity\Matiere
     */
    public function getMatiere()
    {
        return $this->matiere;
    }
}
