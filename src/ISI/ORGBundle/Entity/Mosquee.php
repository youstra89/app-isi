<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\MosqueeRepository")
 */
class Mosquee
{

    const OPTIONS = [
      0 => 'Sous notre égide',
      1 => 'Cours Dr',
      2 => 'Programmation office du vendredi',
      3 => 'Programmation de cours',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=5, max=255)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity="ISI\ORGBundle\Entity\Commune", inversedBy="mosquees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $commune;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=5, max=255)
     */
    private $quartier;

    /**
     * @ORM\ManyToOne(targetEntity="ISI\ORGBundle\Entity\Imam")
     */
    private $imam;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $responsable;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numero_responsable;

    /**
     * @ORM\Column(type="boolean")
     */
    private $djoumoua = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex("/^[0-9]{4}$/")
     */
    private $annee_construction;

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

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="json_array", length=255)
     */
    private $options;


    public function __construct()
    {
      $this->createdAt = new \Datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune(Commune $commune): self
    {
        $this->commune = $commune;

        return $this;
    }

    public function getQuartier(): ?string
    {
        return $this->quartier;
    }

    public function setQuartier(string $quartier): self
    {
        $this->quartier = $quartier;

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

    public function getResponsable(): ?string
    {
        return $this->responsable;
    }

    public function setResponsable(string $responsable): self
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getNumeroResponsable(): ?string
    {
        return $this->numero_responsable;
    }

    public function setNumeroResponsable(string $numero_responsable): self
    {
        $this->numero_responsable = $numero_responsable;

        return $this;
    }

    public function getDjoumoua(): ?bool
    {
        return $this->djoumoua;
    }

    public function setDjoumoua(bool $djoumoua): self
    {
        $this->djoumoua = $djoumoua;

        return $this;
    }

    public function getAnneeConstruction(): ?string
    {
        return $this->annee_construction;
    }

    public function setAnneeConstruction(string $annee_construction): self
    {
        $this->annee_construction = $annee_construction;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOptions(): array
    {
      $options = $this->options;

      // Afin d'être sûr qu'un user a toujours au moins 1 rôle
      if (empty($options)) {
          $options = [];
      }

      return array_unique($options);
    }

    public function setOptions(array $options): void
    {

        $this->options = $options;
    }

    public function getOptionsType(): array
    {
      $options = $this->options;
      $_options = [];
      foreach($options as $j)
      {
        $_options[] = self::OPTIONS[$j];
      }
      return $_options;
    }


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Mosquee
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
     * @return Mosquee
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
     * @return Mosquee
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
     * @return Mosquee
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
