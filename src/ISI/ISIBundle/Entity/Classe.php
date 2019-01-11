<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Classe
 *
 * @ORM\Table(name="classe", indexes={@ORM\Index(name="annee_scolaire_classe_fk", columns={"annee_scolaire_id"}), @ORM\Index(name="niveau_id", columns={"niveau_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\ClasseRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Classe
{
    /**
     * @var integer
     *
     * @ORM\Column(name="classe_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $classeId;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_classe_fr", type="string", length=255, nullable=false)
     */
    private $libelleClasseFr;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_classe_ar", type="string", length=255, nullable=false)
     */
    private $libelleClasseAr;

    /**
     * @var string
     *
     * @ORM\Column(name="genre", type="string", length=255, nullable=false)
     */
    private $genre;

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
     * @var \Anneescolaire
     *
     * @ORM\ManyToOne(targetEntity="Anneescolaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_scolaire_id", referencedColumnName="annee_scolaire_id")
     * })
     */
    private $anneeScolaire;

    /**
     * @var \Niveau
     *
     * @ORM\ManyToOne(targetEntity="Niveau", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="niveau_id", referencedColumnName="id")
     * })
     */
    private $niveau;


    /**
     * Get classeId
     *
     * @return integer
     */
    public function getClasseId()
    {
        return $this->classeId;
    }

    /**
     * Set libelleClasseFr
     *
     * @param string $libelleClasseFr
     *
     * @return Classe
     */
    public function setLibelleClasseFr($libelleClasseFr)
    {
        $this->libelleClasseFr = $libelleClasseFr;

        return $this;
    }

    /**
     * Get libelleClasseFr
     *
     * @return string
     */
    public function getLibelleClasseFr()
    {
        return $this->libelleClasseFr;
    }

    /**
     * Set libelleClasseAr
     *
     * @param string $libelleClasseAr
     *
     * @return Classe
     */
    public function setLibelleClasseAr($libelleClasseAr)
    {
        $this->libelleClasseAr = $libelleClasseAr;

        return $this;
    }

    /**
     * Get libelleClasseAr
     *
     * @return string
     */
    public function getLibelleClasseAr()
    {
        return $this->libelleClasseAr;
    }

    /**
     * Set genre
     *
     * @param string $genre
     *
     * @return Classe
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get genre
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Classe
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
     * @return Classe
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

    /**
     * Set anneeScolaire
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire
     *
     * @return Classe
     */
    public function setAnneeScolaire(\ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire = null)
    {
        $this->anneeScolaire = $anneeScolaire;

        return $this;
    }

    /**
     * Get anneeScolaire
     *
     * @return \ISI\ISIBundle\Entity\Anneescolaire
     */
    public function getAnneeScolaire()
    {
        return $this->anneeScolaire;
    }

    /**
     * Set niveau
     *
     * @param \ISI\ISIBundle\Entity\Niveau $niveau
     *
     * @return Classe
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

    //Les méthodes personnelles commencent indexAction
    /**
     * @ORM\PrePersist
     */
    public function saveDate()
    {
      $this->setDateSave(new \Datetime());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
      $this->setDateUpdate(new \Datetime());
    }

    public function anneeScolaireClasse($as)
    {
      $this->setAnneescolaire($as);
    }

    // public function __toString(){
    //     return $this->niveau.' '.$this->libelleClasseFr;
    // }
}
