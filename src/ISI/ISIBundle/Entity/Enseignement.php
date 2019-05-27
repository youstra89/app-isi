<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Enseignement
 *
 * @ORM\Table(name="enseignement", uniqueConstraints={@ORM\UniqueConstraint(name="enseignement", columns={"niveau_id", "annee_id", "matiere_id"})}, indexes={@ORM\Index(name="annee_id", columns={"annee_id"}), @ORM\Index(name="matiere_id", columns={"matiere_id"}), @ORM\Index(name="niveau_id", columns={"niveau_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\EnseignementRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Enseignement
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
     * @ORM\Column(name="coefficient", type="integer", nullable=false)
     */
    private $coefficient = 1;

    /**
     * @var boolean
     *
     * @ORM\Column(name="statu", type="boolean", nullable=false)
     */
    private $statu;

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
     * @var \Annee
     *
     * @ORM\ManyToOne(targetEntity="Annee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_id", referencedColumnName="id")
     * })
     */
    private $annee;

    /**
     * @var \Matiere
     *
     * @ORM\ManyToOne(targetEntity="Matiere", inversedBy="enseignements")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="matiere_id", referencedColumnName="id")
     * })
     */
    private $matiere;

    /**
     * @var \Niveau
     *
     * @ORM\ManyToOne(targetEntity="Niveau")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="niveau_id", referencedColumnName="id")
     * })
     */
    private $niveau;

    /**
     * @var \Livre
     *
     * @ORM\ManyToOne(targetEntity="Livre")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="livre_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $livre;



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
     * Set coefficient
     *
     * @param integer $coefficient
     *
     * @return Enseignement
     */
    public function setCoefficient($coefficient)
    {
        $this->coefficient = $coefficient;

        return $this;
    }

    /**
     * Get coefficient
     *
     * @return integer
     */
    public function getCoefficient()
    {
        return $this->coefficient;
    }

    /**
     * Set annee
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $annee
     *
     * @return Enseignement
     */
    public function setAnnee(\ISI\ISIBundle\Entity\Annee $annee = null)
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
     * Set matiere
     *
     * @param \ISI\ISIBundle\Entity\Matiere $matiere
     *
     * @return Enseignement
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

    /**
     * Set niveau
     *
     * @param \ISI\ISIBundle\Entity\Niveau $niveau
     *
     * @return Enseignement
     */
    public function setNiveau(\ISI\ISIBundle\Entity\Niveau $niveau = null)
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * Get niveau
     *
     * @return \ISI\ISIBundle\Entity\Niveau
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * Set livre
     *
     * @param \ISI\ISIBundle\Entity\Livre $livre
     *
     * @return Enseignement
     */
    public function setLivre(\ISI\ISIBundle\Entity\Livre $livre = null)
    {
        $this->livre = $livre;

        return $this;
    }

    /**
     * Get livre
     *
     * @return \ISI\ISIBundle\Entity\Livre
     */
    public function getLivre()
    {
        return $this->livre;
    }

    //Mes méthodes personnelles
    /**
     * @ORM\PrePersist
     */
    public function dateEnregistrement()
    {
      $this->setCreatedAt(new \Datetime());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function dateModification()
    {
      $this->setUpdatedAt(new \Datetime());
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Enseignement
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
     * @return Enseignement
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
     * Set statu
     *
     * @param boolean $statu
     *
     * @return Enseignement
     */
    public function setStatu($statu)
    {
        $this->statu = $statu;

        return $this;
    }

    /**
     * Get statu
     *
     * @return boolean
     */
    public function getStatu()
    {
        return $this->statu;
    }
}
