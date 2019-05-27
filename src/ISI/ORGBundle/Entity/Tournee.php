<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Tournee
 *
 * @ORM\Table(name="tournee")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\TourneeRepository")
 */
class Tournee
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="debut", type="datetime")
     */
    private $debut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fin", type="datetime")
     */
    private $fin;

    /**
     * @var bool
     *
     * @ORM\Column(name="nationale", type="boolean")
     */
    private $nationale;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=255, nullable=true)
     */
    private $commentaire;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="TourneePays", mappedBy="tournee")
     */
    private $pays;

    /**
     * @ORM\OneToMany(targetEntity="TourneeCommune", mappedBy="tournee")
     */
    private $communes;

    /**
     * @ORM\OneToMany(targetEntity="Converti", mappedBy="tournee")
     */
    private $convertis;



    public function __construct()
    {
      $this->nationale = true;
      $this->createdAt = new \Datetime();
      $this->pays = new ArrayCollection();
      $this->communes = new ArrayCollection();
      $this->convertis = new ArrayCollection();
      // $this->mosquees = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set debut
     *
     * @param \DateTime $debut
     *
     * @return Tournee
     */
    public function setDebut($debut)
    {
        $this->debut = $debut;

        return $this;
    }

    /**
     * Get debut
     *
     * @return \DateTime
     */
    public function getDebut()
    {
        return $this->debut;
    }

    /**
     * Set fin
     *
     * @param \DateTime $fin
     *
     * @return Tournee
     */
    public function setFin($fin)
    {
        $this->fin = $fin;

        return $this;
    }

    /**
     * Get fin
     *
     * @return \DateTime
     */
    public function getFin()
    {
        return $this->fin;
    }

    /**
     * Set nationale
     *
     * @param boolean $nationale
     *
     * @return Tournee
     */
    public function setNationale($nationale)
    {
        $this->nationale = $nationale;

        return $this;
    }

    /**
     * Get nationale
     *
     * @return bool
     */
    public function getNationale()
    {
        return $this->nationale;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     *
     * @return Tournee
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Tournee
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
     * @param \DateTime $updated_at
     *
     * @return Tournee
     */
    public function setupdated_at($updated_at)
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
     * @return Collection|Pays[]
     */
    public function getPays(): Collection
    {
        return $this->pays;
    }

    /**
     * @return Collection|Commune[]
     */
    public function getCommunes(): Collection
    {
        return $this->communes;
    }

    /**
     * @return Collection|Convertis[]
     */
    public function getConvertis(): Collection
    {
        return $this->convertis;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Tournee
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
     * @param \DateTime $updatedAt;
     *
     * @return Tournee
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
     * Add pay
     *
     * @param \ISI\ORGBundle\Entity\TourneePays $pay
     *
     * @return Tournee
     */
    public function addPay(\ISI\ORGBundle\Entity\TourneePays $pay)
    {
        $this->pays[] = $pay;

        return $this;
    }

    /**
     * Remove pay
     *
     * @param \ISI\ORGBundle\Entity\TourneePays $pay
     */
    public function removePay(\ISI\ORGBundle\Entity\TourneePays $pay)
    {
        $this->pays->removeElement($pay);
    }

    /**
     * Add commune
     *
     * @param \ISI\ORGBundle\Entity\TourneeCommune $commune
     *
     * @return Tournee
     */
    public function addCommune(\ISI\ORGBundle\Entity\TourneeCommune $commune)
    {
        $this->communes[] = $commune;

        return $this;
    }

    /**
     * Remove commune
     *
     * @param \ISI\ORGBundle\Entity\TourneeCommune $commune
     */
    public function removeCommune(\ISI\ORGBundle\Entity\TourneeCommune $commune)
    {
        $this->communes->removeElement($commune);
    }

    /**
     * Add converti
     *
     * @param \ISI\ORGBundle\Entity\Converti $converti
     *
     * @return Tournee
     */
    public function addConverti(\ISI\ORGBundle\Entity\Converti $converti)
    {
        $this->convertis[] = $converti;

        return $this;
    }

    /**
     * Remove converti
     *
     * @param \ISI\ORGBundle\Entity\Converti $converti
     */
    public function removeConverti(\ISI\ORGBundle\Entity\Converti $converti)
    {
        $this->convertis->removeElement($converti);
    }
}
