<?php

namespace ISI\ENSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnneeContratRencontre
 *
 * @ORM\Table(name="annee_contrat_rencontre")
 * @ORM\Entity(repositoryClass="ISI\ENSBundle\Repository\AnneeContratRencontreRepository")
 */
class AnneeContratRencontre
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
     * @ORM\ManyToOne(targetEntity="Rencontre")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rencontre", referencedColumnName="id")
     * })
     */
    private $rencontre;

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
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return AnneeContratRencontre
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
     * @return AnneeContratRencontre
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
     * @return AnneeContratRencontre
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
     * @return AnneeContratRencontre
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
     * Set rencontre
     *
     * @param \ISI\ENSBundle\Entity\Rencontre $rencontre
     *
     * @return AnneeContratRencontre
     */
    public function setRencontre(\ISI\ENSBundle\Entity\Rencontre $rencontre = null)
    {
        $this->rencontre = $rencontre;

        return $this;
    }

    /**
     * Get rencontre
     *
     * @return \ISI\ENSBundle\Entity\Rencontre
     */
    public function getRencontre()
    {
        return $this->rencontre;
    }
}
