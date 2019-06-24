<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vendredi
 * @ORM\Table(name="vendredi", uniqueConstraints={@ORM\UniqueConstraint(name="vendredi_idx", columns={"imam_id", "mosquee_id"})})
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\VendrediRepository")
 */
class Vendredi
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
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Imam")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="imam_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $imam;

    /**
     * @ORM\ManyToOne(targetEntity="Mosquee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mosquee_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $mosquee;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $createdBy;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="updated_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $updatedBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;


    public function __construct()
    {
      $this->createdAt = new \Datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMosquee(): ?Mosquee
    {
        return $this->mosquee;
    }

    public function setMosquee($mosquee): self
    {
        $this->mosquee = $mosquee;

        return $this;
    }

    public function getImam(): ?Imam
    {
        return $this->imam;
    }

    public function setImam($imam): self
    {
        $this->imam = $imam;

        return $this;
    }

    public function getcreated_at(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setcreated_at(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getupdated_at(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setupdated_at(\DateTimeInterface $updated_at): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Vendredi
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
     * @return Vendredi
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
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return Vendredi
     */
    public function setCreatedBy(\UserBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \UserBundle\Entity\User $updatedBy
     *
     * @return Vendredi
     */
    public function setUpdatedBy(\UserBundle\Entity\User $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
