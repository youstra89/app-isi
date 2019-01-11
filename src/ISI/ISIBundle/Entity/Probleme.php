<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Probleme
 *
 * @ORM\Table(name="probleme")
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\ProblemeRepository")
 */
class Probleme
{
    /**
     * @var integer
     *
     * @ORM\Column(name="probleme_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $problemeId;

    /**
     * @var string
     *
     * @ORM\Column(name="appreciation", type="string", length=255, nullable=false)
     */
    private $appreciation;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

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
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\Commettre", mappedBy="probleme")
     */
    private $commettre;



    /**
     * Get problemeId
     *
     * @return integer
     */
    public function getProblemeId()
    {
        return $this->problemeId;
    }

    /**
     * Set appreciation
     *
     * @param string $appreciation
     *
     * @return Probleme
     */
    public function setAppreciation($appreciation)
    {
        $this->appreciation = $appreciation;

        return $this;
    }

    /**
     * Get appreciation
     *
     * @return string
     */
    public function getAppreciation()
    {
        return $this->appreciation;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Probleme
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Probleme
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
     * @return Probleme
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
     * Constructor
     */
    public function __construct()
    {
        $this->commettre = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add commettre
     *
     * @param \ISI\ISIBundle\Entity\Commettre $commettre
     *
     * @return Probleme
     */
    public function addCommettre(\ISI\ISIBundle\Entity\Commettre $commettre)
    {
        $this->commettre[] = $commettre;

        return $this;
    }

    /**
     * Remove commettre
     *
     * @param \ISI\ISIBundle\Entity\Commettre $commettre
     */
    public function removeCommettre(\ISI\ISIBundle\Entity\Commettre $commettre)
    {
        $this->commettre->removeElement($commettre);
    }

    /**
     * Get commettre
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommettre()
    {
        return $this->commettre;
    }
}
