<?php

namespace ISI\ENSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnneeContratSanction
 *
 * @ORM\Table(name="annee_contrat_sanction")
 * @ORM\Entity(repositoryClass="ISI\ENSBundle\Repository\AnneeContratSanctionRepository")
 */
class AnneeContratSanction
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
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return AnneeContratSanction
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return AnneeContratSanction
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return AnneeContratSanction
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return AnneeContratSanction
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
     * @return AnneeContratSanction
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
     * @return AnneeContratSanction
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
     * @return AnneeContratSanction
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
