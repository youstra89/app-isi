<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cours
 *
 * @ORM\Table(name="cours")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\CoursRepository")
 */
class Cours
{
    const JOURCOURS = [
      1 => 'Lundi',
      2 => 'Mardi',
      3 => 'Mercredi',
      4 => 'Jeudi',
      5 => 'Vendredi',
      6 => 'Samedi',
      7 => 'Dimanche',
    ];

    const HEURECOURS = [
      0 => 'Après Fadjr',
      1 => 'Après Zhour',
      2 => 'Après Asr',
      3 => 'Entre Maghreb et Icha',
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="discipline", type="string", length=255)
     */
    private $discipline;

    /**
     * @ORM\ManyToOne(targetEntity="Mosquee")
     * @ORM\JoinColumn(name="mosquee")
     */
    private $mosquee;

    /**
     * @var string
     *
     * @ORM\Column(name="livre", type="string", length=255)
     */
    private $livre;

    /**
     * @var string
     *
     * @ORM\Column(name="heure", type="string", length=255)
     */
    private $heure;

    /**
     * @var array
     *
     * @ORM\Column(name="jour", type="json_array")
     */
    private $jour;

    /**
     * @var string
     *
     * @ORM\Column(name="annee_debut", type="string", length=255)
     */
    private $anneeDebut;

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

    public function __construct()
    {
      $this->createdAt = new \DateTime();
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

    public function getMosquee(): ?Mosquee
    {
        return $this->mosquee;
    }

    public function setMosquee(?Mosquee $mosquee): self
    {
        $this->mosquee = $mosquee;

        return $this;
    }

    /**
     * Set discipline
     *
     * @param string $discipline
     *
     * @return Cours
     */
    public function setDiscipline($discipline)
    {
        $this->discipline = $discipline;

        return $this;
    }

    /**
     * Get discipline
     *
     * @return string
     */
    public function getDiscipline()
    {
        return $this->discipline;
    }

    /**
     * Set livre
     *
     * @param string $livre
     *
     * @return Cours
     */
    public function setLivre($livre)
    {
        $this->livre = $livre;

        return $this;
    }

    /**
     * Get livre
     *
     * @return string
     */
    public function getLivre()
    {
        return $this->livre;
    }

    /**
     * Set heure
     *
     * @param string $heure
     *
     * @return Cours
     */
    public function setHeure($heure)
    {
        $this->heure = $heure;

        return $this;
    }

    /**
     * Get heure
     *
     * @return string
     */
    public function getHeure()
    {
        return $this->heure;
    }

    /**
     * Set jour
     *
     * @param array $jour
     *
     * @return Cours
     */
    public function setJour(array $jour)
    {
        $this->jour = $jour;

        return $this;
    }

    public function getJour(): array
    {
      $jour = $this->jour;

      // Afin d'être sûr qu'un user a toujours au moins 1 rôle
      if (empty($jour)) {
          $jour = [];
      }

      return array_unique($jour);
    }

    public function getJourCoursType(): array
    {
      $jours = $this->jour;
      $nomJours = [];
      foreach($jours as $j)
      {
        $nomJours[] = self::JOURCOURS[$j];
      }
      return $nomJours;
    }

    public function getHeureCoursType(): string
    {
      return self::HEURECOURS[$this->heure];
    }

    /**
     * Set anneeDebut
     *
     * @param string $anneeDebut
     *
     * @return Cours
     */
    public function setAnneeDebut($anneeDebut)
    {
        $this->anneeDebut = $anneeDebut;

        return $this;
    }

    /**
     * Get anneeDebut
     *
     * @return string
     */
    public function getAnneeDebut()
    {
        return $this->anneeDebut;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Cours
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
     * @return Cours
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
}
