<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Region
 * @UniqueEntity("nom")
 * @ORM\Table(name="commune")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\CommuneRepository")
 */
class Commune
{
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
     * @ORM\ManyToOne(targetEntity="Ville", inversedBy="communes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ville;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Regex("/^[0-9]{8}$/")
     */
    private $nombre_habitants;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Regex("/^[0-9]{8}$/")
     */
    private $taux_musulmans;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    // /**
    //  * @ORM\OneToMany(targetEntity="App\Entity\Mosquee", mappedBy="commune")
    //  */
    // private $mosquees;


    public function __construct()
    {
      $this->created_at = new \Datetime();
      // $this->mosquees = new ArrayCollection();
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

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille($ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getNombreHabitants(): ?int
    {
        return $this->nombre_habitants;
    }

    public function setNombreHabitants(int $nombre_habitants): self
    {
        $this->nombre_habitants = $nombre_habitants;

        return $this;
    }

    public function getTauxMusulmans(): ?int
    {
        return $this->taux_musulmans;
    }

    public function setTauxMusulmans(int $taux_musulmans): self
    {
        $this->taux_musulmans = $taux_musulmans;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }


    /**
     * @return Collection|Mosquee[]
     */
    public function getMosquees(): Collection
    {
        return $this->mosquees;
    }

    public function addMosquee(Mosquee $mosquee): self
    {
        if (!$this->mosquees->contains($mosquee)) {
            $this->mosquees[] = $mosquee;
            $mosquee->setCommune($this);
        }

        return $this;
    }

    public function removeMosquee(Mosquee $mosquee): self
    {
        if ($this->mosquees->contains($mosquee)) {
            $this->mosquees->removeElement($mosquee);
            // set the owning side to null (unless already changed)
            if ($mosquee->getCommune() === $this) {
                $mosquee->setCommune(null);
            }
        }

        return $this;
    }
}
