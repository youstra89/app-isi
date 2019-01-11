<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Matiere
 *
 * @ORM\Table(name="matiere", indexes={@ORM\Index(name="languematiere_matiere_fk", columns={"reference_langue"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\MatiereRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Matiere
{
    /**
     * @var integer
     *
     * @ORM\Column(name="matiere_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $matiereId;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_matiere", type="string", length=255, nullable=false, unique=true)
     */
    private $libelleMatiere;

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
     * @var \Languematiere
     *
     * @ORM\ManyToOne(targetEntity="Languematiere")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reference_langue", referencedColumnName="reference_langue")
     * })
     */
    private $referenceLangue;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\Enseignement", mappedBy="matiere")
     */
    private $enseignements;



    /**
     * Get matiereId
     *
     * @return integer
     */
    public function getMatiereId()
    {
        return $this->matiereId;
    }

    /**
     * Set libelleMatiere
     *
     * @param string $libelleMatiere
     *
     * @return Matiere
     */
    public function setLibelleMatiere($libelleMatiere)
    {
        $this->libelleMatiere = $libelleMatiere;

        return $this;
    }

    /**
     * Get libelleMatiere
     *
     * @return string
     */
    public function getLibelleMatiere()
    {
        return $this->libelleMatiere;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Matiere
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
     * @return Matiere
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
     * Set referenceLangue
     *
     * @param \ISI\ISIBundle\Entity\Languematiere $referenceLangue
     *
     * @return Matiere
     */
    public function setReferenceLangue(\ISI\ISIBundle\Entity\Languematiere $referenceLangue = null)
    {
        $this->referenceLangue = $referenceLangue;

        return $this;
    }

    /**
     * Get referenceLangue
     *
     * @return \ISI\ISIBundle\Entity\Languematiere
     */
    public function getReferenceLangue()
    {
        return $this->referenceLangue;
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->enseignements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add enseignement
     *
     * @param \ISI\ISIBundle\Entity\Enseignement $enseignement
     *
     * @return Matiere
     */
    public function addEnseignement(\ISI\ISIBundle\Entity\Enseignement $enseignement)
    {
        $this->enseignements[] = $enseignement;

        return $this;
    }

    /**
     * Remove enseignement
     *
     * @param \ISI\ISIBundle\Entity\Enseignement $enseignement
     */
    public function removeEnseignement(\ISI\ISIBundle\Entity\Enseignement $enseignement)
    {
        $this->enseignements->removeElement($enseignement);
    }

    /**
     * Get enseignements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEnseignements()
    {
        return $this->enseignements;
    }
}
