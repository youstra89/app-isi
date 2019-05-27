<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SalleClasse
 *
 * @ORM\Table(name="salle_classe", uniqueConstraints={@ORM\UniqueConstraint(columns={"classe_id", "annee_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\SalleClasseRepository")
 */
class SalleClasse
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
     * @var \Salle
     *
     * @ORM\ManyToOne(targetEntity="ISI\ISIBundle\Entity\Salle")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="salle_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $salle;

    /**
     * @var \Classe
     *
     * @ORM\ManyToOne(targetEntity="Classe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="classe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $classe;

    /**
     * @var \Annee
     *
     * @ORM\ManyToOne(targetEntity="Annee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $annee;

    /**
     * @var \Annee
     *
     * @ORM\ManyToOne(targetEntity="Groupeformation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="groupe_formation_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $regime;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return SalleClasse
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
     * @return SalleClasse
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
     * Set salle
     *
     * @param \ISI\ISIBundle\Entity\Salle $salle
     *
     * @return SalleClasse
     */
    public function setSalle(\ISI\ISIBundle\Entity\Salle $salle)
    {
        $this->salle = $salle;

        return $this;
    }

    /**
     * Get salle
     *
     * @return \ISI\ISIBundle\Entity\Salle
     */
    public function getSalle()
    {
        return $this->salle;
    }

    /**
     * Set classe
     *
     * @param \ISI\ISIBundle\Entity\Classe $classe
     *
     * @return SalleClasse
     */
    public function setClasse(\ISI\ISIBundle\Entity\Classe $classe)
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
     * Set annee
     *
     * @param \ISI\ISIBundle\Entity\Annee $annee
     *
     * @return SalleClasse
     */
    public function setAnnee(\ISI\ISIBundle\Entity\Annee $annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return \ISI\ISIBundle\Entity\Annee
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set reference
     *
     * @param \ISI\ISIBundle\Entity\Groupeformation $reference
     *
     * @return SalleClasse
     */
    public function setReference(\ISI\ISIBundle\Entity\Groupeformation $reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return \ISI\ISIBundle\Entity\Groupeformation
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set regime
     *
     * @param \ISI\ISIBundle\Entity\Groupeformation $regime
     *
     * @return SalleClasse
     */
    public function setRegime(\ISI\ISIBundle\Entity\Groupeformation $regime)
    {
        $this->regime = $regime;

        return $this;
    }

    /**
     * Get regime
     *
     * @return \ISI\ISIBundle\Entity\Groupeformation
     */
    public function getRegime()
    {
        return $this->regime;
    }
}
