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
 * @ORM\Table(name="region")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\RegionRepository")
 */

class Region
{
     const LOCALISATION = [
       0 => 'Nord',
       1 => 'Sud',
       2 => 'Est',
       3 => 'Ouest',
       4 => 'Centre',
     ];

     /**
      * @ORM\Id()
      * @ORM\GeneratedValue()
      * @ORM\Column(type="integer")
      */
     private $id;

     /**
      * @ORM\Column(type="string", length=255)
      * @Assert\Length(min=2, max=255)
      */
     private $nom;

     /**
      * @ORM\Column(type="integer")
      * @Assert\Regex("/^[0-4]{1}$/")
      */
     private $localisation;

     /**
      * @ORM\Column(type="datetime")
      */
     private $created_at;

     /**
      * @ORM\Column(type="datetime", nullable=true)
      */
     private $updated_at;

     /**
      * @ORM\OneToMany(targetEntity="Ville", mappedBy="region")
      */
     private $villes;


     public function __construct()
     {
       $this->created_at = new \Datetime();
       $this->villes = new ArrayCollection();
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

     public function getLocalisation(): ?int
     {
         return $this->localisation;
     }

     public function setLocalisation(int $localisation): self
     {
         $this->localisation = $localisation;

         return $this;
     }

     public function getLocalisationType(): string
     {
       return self::LOCALISATION[$this->localisation];
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
      * @return Collection|Ville[]
      */
     public function getVilles(): Collection
     {
         return $this->villes;
     }

     public function addVille(Ville $ville): self
     {
         if (!$this->villes->contains($ville)) {
             $this->villes[] = $ville;
             $ville->setRegion($this);
         }

         return $this;
     }

     public function removeVille(Ville $ville): self
     {
         if ($this->villes->contains($ville)) {
             $this->villes->removeElement($ville);
             // set the owning side to null (unless already changed)
             if ($ville->getRegion() === $this) {
                 $ville->setRegion(null);
             }
         }

         return $this;
     }
 }
