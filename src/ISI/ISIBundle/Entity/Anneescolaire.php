<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Anneescolaire
 *
 * @ORM\Table(name="anneescolaire", indexes={@ORM\Index(name="libelleAnneeScolaire", columns={"libelle_annee_scolaire"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\AnneescolaireRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Anneescolaire
{
    /**
     * @var integer
     *
     * @ORM\Column(name="annee_scolaire_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $anneeScolaireId;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_annee_scolaire", type="string", length=255, nullable=false)
     */
    private $libelleAnneeScolaire;

    /**
     * @var boolean
     *
     * @ORM\Column(name="achevee", type="boolean", nullable=false)
     */
    private $achevee = FALSE;

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
     * Get anneeScolaireId
     *
     * @return integer
     */
    public function getAnneeScolaireId()
    {
        return $this->anneeScolaireId;
    }

    /**
     * Set libelleAnneeScolaire
     *
     * @param string $libelleAnneeScolaire
     *
     * @return Anneescolaire
     */
    public function setLibelleAnneeScolaire($libelleAnneeScolaire)
    {
        $this->libelleAnneeScolaire = $libelleAnneeScolaire;

        return $this;
    }

    /**
     * Get libelleAnneeScolaire
     *
     * @return string
     */
    public function getLibelleAnneeScolaire()
    {
        return $this->libelleAnneeScolaire;
    }

    /**
     * Set achevee
     *
     * @param string $achevee
     *
     * @return Anneescolaire
     */
    public function setAchevee($achevee)
    {
        $this->achevee = $achevee;

        return $this;
    }

    /**
     * Get achevee
     *
     * @return string
     */
    public function getAchevee()
    {
        return $this->achevee;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Anneescolaire
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
     * @return Anneescolaire
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

    //Méthode de mise à jour de la date de création
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
}
