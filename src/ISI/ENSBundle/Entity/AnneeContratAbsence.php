<?php

namespace ISI\ENSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnneeContratAbsence
 *
 * @ORM\Table(name="annee_contrat_absence")
 * @ORM\Entity(repositoryClass="ISI\ENSBundle\Repository\AnneeContratAbsenceRepository")
 */
class AnneeContratAbsence
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
     * @var \Anneescolaire
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\Anneescolaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee", referencedColumnName="annee_scolaire_id")
     * })
     */
    private $annee;

    /**
     * @var \Contrat
     *
     * @ORM\ManyToOne(targetEntity="Contrat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contrat", referencedColumnName="id")
     * })
     */
    private $contrat;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_depart", type="date")
     */
    private $dateDepart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_retour", type="date")
     */
    private $dateRetour;

    /**
     * @var string
     *
     * @ORM\Column(name="motif", type="string", length=255)
     */
    private $motif;

    /**
     * @var bool
     *
     * @ORM\Column(name="autorisation", type="boolean")
     */
    private $autorisation = FALSE;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_demande", type="date")
     */
    private $dateDemande;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_save", type="datetime")
     */
    private $dateSave; 

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_update", type="datetime")
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
     * Set dateDepart
     *
     * @param \DateTime $dateDepart
     *
     * @return AnneeContratAbsence
     */
    public function setDateDepart($dateDepart)
    {
        $this->dateDepart = $dateDepart;

        return $this;
    }

    /**
     * Get dateDepart
     *
     * @return \DateTime
     */
    public function getDateDepart()
    {
        return $this->dateDepart;
    }

    /**
     * Set dateRetour
     *
     * @param \DateTime $dateRetour
     *
     * @return AnneeContratAbsence
     */
    public function setDateRetour($dateRetour)
    {
        $this->dateRetour = $dateRetour;

        return $this;
    }

    /**
     * Get dateRetour
     *
     * @return \DateTime
     */
    public function getDateRetour()
    {
        return $this->dateRetour;
    }

    /**
     * Set motif
     *
     * @param string $motif
     *
     * @return AnneeContratAbsence
     */
    public function setMotif($motif)
    {
        $this->motif = $motif;

        return $this;
    }

    /**
     * Get motif
     *
     * @return string
     */
    public function getMotif()
    {
        return $this->motif;
    }

    /**
     * Set autorisation
     *
     * @param boolean $autorisation
     *
     * @return AnneeContratAbsence
     */
    public function setAutorisation($autorisation)
    {
        $this->autorisation = $autorisation;

        return $this;
    }

    /**
     * Get autorisation
     *
     * @return bool
     */
    public function getAutorisation()
    {
        return $this->autorisation;
    }

    /**
     * Set dateDemande
     *
     * @param \DateTime $dateDemande
     *
     * @return AnneeContratAbsence
     */
    public function setDateDemande($dateDemande)
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    /**
     * Get dateDemande
     *
     * @return \DateTime
     */
    public function getDateDemande()
    {
        return $this->dateDemande;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return AnneeContratAbsence
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
     * @return AnneeContratAbsence
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
     * Set annee
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $annee
     *
     * @return AnneeContratAbsence
     */
    public function setAnnee(\ISI\ISIBundle\Entity\Anneescolaire $annee = null)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return \ISI\ISIBundle\Entity\Anneescolaire
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set contrat
     *
     * @param \ISI\ENSBundle\Entity\Contrat $contrat
     *
     * @return AnneeContratAbsence
     */
    public function setContrat(\ISI\ENSBundle\Entity\Contrat $contrat = null)
    {
        $this->contrat = $contrat;

        return $this;
    }

    /**
     * Get contrat
     *
     * @return \ISI\ENSBundle\Entity\Contrat
     */
    public function getContrat()
    {
        return $this->contrat;
    }
}
