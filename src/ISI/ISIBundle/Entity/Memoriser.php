<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Memoriser
 *
 * @ORM\Table(name="memoriser", uniqueConstraints={@ORM\UniqueConstraint(name="memorisation", columns={"eleve_id", "halaqa_id", "annee_id"})}, indexes={@ORM\Index(name="eleve_id", columns={"eleve_id"}), @ORM\Index(name="halaqa_id", columns={"halaqa_id"}), @ORM\Index(name="annee_id", columns={"annee_id"})})
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
     *   @ORM\JoinColumn(name="eleve_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $eleve;

    /**
     * @var \Halaqa
     *
     * @ORM\ManyToOne(targetEntity="Halaqa")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="halaqa_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $halaqa;

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
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Memoriser
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
     * @return Memoriser
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Memoriser
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
     * @return Memoriser
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
     * Set annee
     *
     * @param \ISI\ISIBundle\Entity\Annee $annee
     *
     * @return Memoriser
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
}
