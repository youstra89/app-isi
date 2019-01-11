<?php

namespace ISI\ENSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contrat
 *
 * @ORM\Table(name="contrat")
 * @ORM\Entity(repositoryClass="ISI\ENSBundle\Repository\ContratRepository")
 */
class Contrat
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
     * @var \DateTime
     *
     * @ORM\Column(name="debut", type="date")
     */
    private $debut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fin", type="date")
     */
    private $fin;

    /**
     * @var int
     *
     * @ORM\Column(name="duree", type="integer")
     */
    private $duree;

    /**
     * @var \Enseignant
     *
     * @ORM\ManyToOne(targetEntity="Enseignant")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="enseignant", referencedColumnName="id")
     * })
     */
    private $enseignant;

    /**
     * @var \Anneescolaire
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\Anneescolaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_debut", referencedColumnName="annee_scolaire_id")
     * })
     */
    private $anneeDebut;

    /**
     * @var \Anneescolaire
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\Anneescolaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_fin", referencedColumnName="annee_scolaire_id")
     * })
     */
    private $anneeFin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fini", type="boolean", nullable=false)
     */
    private $fini = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=255, nullable=true)
     */
    private $commentaire;

    /**
     * @var string
     *
     * @ORM\Column(name="motif_fin", type="string", length=255, nullable=false)
     */
    private $motifFin;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set debut
     *
     * @param \DateTime $debut
     *
     * @return Contrat
     */
    public function setDebut($debut)
    {
        $this->debut = $debut;

        return $this;
    }

    /**
     * Get debut
     *
     * @return \DateTime
     */
    public function getDebut()
    {
        return $this->debut;
    }

    /**
     * Set fin
     *
     * @param \DateTime $fin
     *
     * @return Contrat
     */
    public function setFin($fin)
    {
        $this->fin = $fin;

        return $this;
    }

    /**
     * Get fin
     *
     * @return \DateTime
     */
    public function getFin()
    {
        return $this->fin;
    }

    /**
     * Set duree
     *
     * @param integer $duree
     *
     * @return Contrat
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return int
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set enseignant
     *
     * @param \ISI\ENSBundle\Entity\Enseignant $enseignant
     *
     * @return Contrat
     */
    public function setEnseignant(\ISI\ENSBundle\Entity\Enseignant $enseignant)
    {
        $this->enseignant = $enseignant;

        return $this;
    }

    /**
     * Get enseignant
     *
     * @return \ISI\ENSBundle\Entity\Enseignant
     */
    public function getEnseignant()
    {
        return $this->enseignant;
    }

    /**
     * Set anneeDebut
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $anneeDebut
     *
     * @return Contrat
     */
    public function setAnneeDebut($anneeDebut)
    {
        $this->anneeDebut = $anneeDebut;

        return $this;
    }

    /**
     * Get anneeDebut
     *
     * @return \ISI\ISIBundle\Entity\Anneescolaire
     */
    public function getAnneeDebut()
    {
        return $this->anneeDebut;
    }

    /**
     * Set anneeFin
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $anneeFin
     *
     * @return Contrat
     */
    public function setAnneeFin($anneeFin)
    {
        $this->anneeFin = $anneeFin;

        return $this;
    }

    /**
     * Get anneeFin
     *
     * @return \ISI\ISIBundle\Entity\Anneescolaire
     */
    public function getAnneeFin()
    {
        return $this->anneeFin;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Contrat
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
     * @return Contrat
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
     * Set commentaire
     *
     * @param string $commentaire
     *
     * @return Contrat
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
     * Set fini
     *
     * @param boolean $fini
     *
     * @return Contrat
     */
    public function setFini($fini)
    {
        $this->fini = $fini;

        return $this;
    }

    /**
     * Get fini
     *
     * @return boolean
     */
    public function getFini()
    {
        return $this->fini;
    }

    /**
     * Set motifFin
     *
     * @param string $motifFin
     *
     * @return Contrat
     */
    public function setMotifFin($motifFin)
    {
        $this->motifFin = $motifFin;

        return $this;
    }

    /**
     * Get motifFin
     *
     * @return string
     */
    public function getMotifFin()
    {
        return $this->motifFin;
    }
}
