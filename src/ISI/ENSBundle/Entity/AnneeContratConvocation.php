<?php

namespace ISI\ENSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnneeContratConvocation
 *
 * @ORM\Table(name="annee_contrat_convocation")
 * @ORM\Entity(repositoryClass="ISI\ENSBundle\Repository\AnneeContratConvocationRepository")
 */
class AnneeContratConvocation
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
     * @var \Contrat
     *
     * @ORM\ManyToOne(targetEntity="Convocation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="convocation", referencedColumnName="id")
     * })
     */
    private $convocation;

    /**
     * @var string
     *
     * @ORM\Column(name="audition", type="string", length=255)
     */
    private $audition;

    /**
     * @var string
     *
     * @ORM\Column(name="verdict", type="string", length=255)
     */
    private $verdict;

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
     * Set audition
     *
     * @param string $audition
     *
     * @return AnneeContratConvocation
     */
    public function setAudition($audition)
    {
        $this->audition = $audition;

        return $this;
    }

    /**
     * Get audition
     *
     * @return string
     */
    public function getAudition()
    {
        return $this->audition;
    }

    /**
     * Set verdict
     *
     * @param string $verdict
     *
     * @return AnneeContratConvocation
     */
    public function setVerdict($verdict)
    {
        $this->verdict = $verdict;

        return $this;
    }

    /**
     * Get verdict
     *
     * @return string
     */
    public function getVerdict()
    {
        return $this->verdict;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return AnneeContratConvocation
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
     * @return AnneeContratConvocation
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
     * @return AnneeContratConvocation
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
     * @return AnneeContratConvocation
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

    /**
     * Set convocation
     *
     * @param \ISI\ENSBundle\Entity\Convocation $convocation
     *
     * @return AnneeContratConvocation
     */
    public function setConvocation(\ISI\ENSBundle\Entity\Convocation $convocation = null)
    {
        $this->convocation = $convocation;

        return $this;
    }

    /**
     * Get convocation
     *
     * @return \ISI\ENSBundle\Entity\Convocation
     */
    public function getConvocation()
    {
        return $this->convocation;
    }
}
