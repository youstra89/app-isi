<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chambre
 *
 * @ORM\Table(name="chambre")
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\ChambreRepository")
 */
class Chambre
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
     * @var \Batiment
     *
     * @ORM\ManyToOne(targetEntity="Batiment", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="batiment", referencedColumnName="id")
     * })
     */
    private $batiment;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_chambre", type="string", length=255, nullable=true)
     */
    private $libelleChambre;

    /**
     * @var string
     *
     * @ORM\Column(name="genre", type="string", nullable=false)
     */
    private $genre;

    /**
     * @var integer
     *
     * @ORM\Column(name="nombre_de_places", type="integer", nullable=false)
     */
    private $nombreDePlaces;

    /**
     * @var integer
     *
     * @ORM\Column(name="places_disponibles", type="integer", nullable=false)
     */
    private $placesDisponibles;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_save", type="datetime", nullable=false)
     */
    private $dateSave;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_update", type="datetime", nullable=false)
     */
    private $dateUpdate;



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
     * Set batiment
     *
     * @param \ISI\ISIBundle\Entity\Batiment $niveau
     *
     * @return Chambre
     */
    public function setBatiment(\ISI\ISIBundle\Entity\Batiment $batiment = null)
    {
        $this->batiment = $batiment;

        return $this;
    }

    /**
     * Get batiment
     *
     * @return \ISI\ISIBundle\Entity\Batiment
     */
    public function getBatiment() 
    {
        return $this->batiment;
    }

    /**
     * Set libelleChambre
     *
     * @param string $libelleChambre
     *
     * @return Chambre
     */
    public function setLibelleChambre($libelleChambre)
    {
        $this->libelleChambre = $libelleChambre;

        return $this;
    }

    /**
     * Get libelleChambre
     *
     * @return string
     */
    public function getLibelleChambre()
    {
        return $this->libelleChambre;
    }

    /**
     * Set genre
     *
     * @param integer $genre
     *
     * @return Chambre
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get genre
     *
     * @return integer
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set nombreDePlaces
     *
     * @param float $nombreDePlaces
     *
     * @return Chambre
     */
    public function setNombreDePlaces($nombreDePlaces)
    {
        $this->nombreDePlaces = $nombreDePlaces;

        return $this;
    }

    /**
     * Get nombreDePlaces
     *
     * @return float
     */
    public function getNombreDePlaces()
    {
        return $this->nombreDePlaces;
    }

    /**
     * Set placesDisponibles
     *
     * @param float $placesDisponibles
     *
     * @return Chambre
     */
    public function setPlacesDisponibles($placesDisponibles)
    {
        $this->placesDisponibles = $placesDisponibles;

        return $this;
    }

    /**
     * Get placesDisponibles
     *
     * @return float
     */
    public function getPlacesDisponibles()
    {
        return $this->placesDisponibles;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Chambre
     */
    public function setDateSave($dateSave)
    {
        $this->dateSave = $dateSave;

        return $this;
    }

    /**
     * Get dateSave
     *
     * @return \DateTime
     */
    public function getDateSave()
    {
        return $this->dateSave;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     *
     * @return Chambre
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }
}
