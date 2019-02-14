<?php

namespace ISI\ORGBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Converti
 *
 * @ORM\Table(name="converti")
 * @ORM\Entity(repositoryClass="ISI\ORGBundle\Repository\ConvertiRepository")
 */
class Converti
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
     * @ORM\ManyToOne(targetEntity="Tournee", inversedBy="convertis")
     * @ORM\JoinColumn(name="tournee", nullable=true)
     */
    private $tournee;

    /**
     * @ORM\ManyToOne(targetEntity="Commune", inversedBy="convertis")
     * @ORM\JoinColumn(name="commune", nullable=true)
     */
    private $commune;

    /**
     * @ORM\ManyToOne(targetEntity="Pays", inversedBy="convertis")
     * @ORM\JoinColumn(name="pays", nullable=true)
     */
    private $pays;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="pnom", type="string", length=255)
     */
    private $pnom;

    /**
     * @var string
     *
     * @ORM\Column(name="residence", type="string", length=255)
     */
    private $residence;

    /**
     * @var string
     *
     * @ORM\Column(name="age", type="string", length=255, nullable=true)
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="sexe", type="string", length=255)
     */
    private $sexe;

    /**
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=255)
     */
    private $profession;

    /**
     * @var string
     *
     * @ORM\Column(name="ancienne_confession", type="string", length=255)
     */
    private $ancienneConfession;

    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=255, nullable=true)
     */
    private $numero;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_conversion", type="datetime", nullable=true)
     */
    private $dateConversion;

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


    public function __construct()
    {
      $this->createdAt = new \Datetime();
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

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune($commune): self
    {
        $this->commune = $commune;

        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays($pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getTournee(): ?Tournee
    {
        return $this->tournee;
    }

    public function setTournee($tournee): self
    {
        $this->tournee = $tournee;

        return $this;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Converti
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set pnom
     *
     * @param string $pnom
     *
     * @return Converti
     */
    public function setPnom($pnom)
    {
        $this->pnom = $pnom;

        return $this;
    }

    /**
     * Get pnom
     *
     * @return string
     */
    public function getPnom()
    {
        return $this->pnom;
    }

    /**
     * Set residence
     *
     * @param string $residence
     *
     * @return Converti
     */
    public function setResidence($residence)
    {
        $this->residence = $residence;

        return $this;
    }

    /**
     * Get residence
     *
     * @return string
     */
    public function getResidence()
    {
        return $this->residence;
    }

    /**
     * Set age
     *
     * @param string $age
     *
     * @return Converti
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return string
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set sexe
     *
     * @param string $sexe
     *
     * @return Converti
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe
     *
     * @return string
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * Set profession
     *
     * @param string $profession
     *
     * @return Converti
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;

        return $this;
    }

    /**
     * Get profession
     *
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * Set ancienneConfession
     *
     * @param string $ancienneConfession
     *
     * @return Converti
     */
    public function setAncienneConfession($ancienneConfession)
    {
        $this->ancienneConfession = $ancienneConfession;

        return $this;
    }

    /**
     * Get ancienneConfession
     *
     * @return string
     */
    public function getAncienneConfession()
    {
        return $this->ancienneConfession;
    }

    /**
     * Set numero
     *
     * @param string $numero
     *
     * @return Converti
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set dateConversion
     *
     * @param \DateTime $dateConversion
     *
     * @return Converti
     */
    public function setDateConversion($dateConversion)
    {
        $this->dateConversion = $dateConversion;

        return $this;
    }

    /**
     * Get dateConversion
     *
     * @return \DateTime
     */
    public function getDateConversion()
    {
        return $this->dateConversion;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     *
     * @return Converti
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Converti
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
     * @return Converti
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
