<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Enseignement
 *
 * @ORM\Table(name="enseignement", uniqueConstraints={@ORM\UniqueConstraint(name="enseignement", columns={"niveau_id", "annee_scolaire_id", "matiere_id"})}, indexes={@ORM\Index(name="annee_scolaire_id", columns={"annee_scolaire_id"}), @ORM\Index(name="matiere_id", columns={"matiere_id"}), @ORM\Index(name="classe_id", columns={"niveau_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\EnseignementRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Enseignement
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
     * @var integer
     *
     * @ORM\Column(name="coefficient", type="integer", nullable=false)
     */
    private $coefficient = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="statu_matiere", type="boolean", nullable=false)
     */
    private $statuMatiere;

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
     * @var \Matiere
     *
     * @ORM\ManyToOne(targetEntity="Matiere", inversedBy="enseignements")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="matiere_id", referencedColumnName="matiere_id")
     * })
     */
    private $matiere;

    /**
     * @var \Niveau
     *
     * @ORM\ManyToOne(targetEntity="Niveau")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="niveau_id", referencedColumnName="id")
     * })
     */
    private $niveau;

    /**
     * @var \Livre
     *
     * @ORM\ManyToOne(targetEntity="Livre")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="livre_id", referencedColumnName="id")
     * })
     */
    private $livre;



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
     * Set coefficient
     *
     * @param integer $coefficient
     *
     * @return Enseignement
     */
    public function setCoefficient($coefficient)
    {
        $this->coefficient = $coefficient;

        return $this;
    }

    /**
     * Get coefficient
     *
     * @return integer
     */
    public function getCoefficient()
    {
        return $this->coefficient;
    }

    /**
     * Set statuMatiere
     *
     * @param boolean $statuMatiere
     *
     * @return Enseignement
     */
    public function setStatuMatiere($statuMatiere)
    {
        $this->statuMatiere = $statuMatiere;

        return $this;
    }

    /**
     * Get statuMatiere
     *
     * @return boolean
     */
    public function getStatuMatiere()
    {
        return $this->statuMatiere;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Enseignement
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
     * @return Enseignement
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
     * @return Enseignement
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
     * Set matiere
     *
     * @param \ISI\ISIBundle\Entity\Matiere $matiere
     *
     * @return Enseignement
     */
    public function setMatiere(\ISI\ISIBundle\Entity\Matiere $matiere = null)
    {
        $this->matiere = $matiere;

        return $this;
    }

    /**
     * Get matiere
     *
     * @return \ISI\ISIBundle\Entity\Matiere
     */
    public function getMatiere()
    {
        return $this->matiere;
    }

    /**
     * Set niveau
     *
     * @param \ISI\ISIBundle\Entity\Niveau $niveau
     *
     * @return Enseignement
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

    /**
     * Set livre
     *
     * @param \ISI\ISIBundle\Entity\Livre $livre
     *
     * @return Enseignement
     */
    public function setLivre(\ISI\ISIBundle\Entity\Livre $livre = null)
    {
        $this->livre = $livre;

        return $this;
    }

    /**
     * Get livre
     *
     * @return \ISI\ISIBundle\Entity\Livre
     */
    public function getLivre()
    {
        return $this->livre;
    }

    //Mes méthodes personnelles
    /**
     * @ORM\PrePersist
     */
    public function dateEnregistrement()
    {
      $this->setDateSave(new \Datetime());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function dateModification()
    {
      $this->setDateUpdate(new \Datetime());
    }
}
