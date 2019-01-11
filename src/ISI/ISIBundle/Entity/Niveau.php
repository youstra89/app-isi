<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Niveau
 *
 * @ORM\Table(name="niveau", uniqueConstraints={@ORM\UniqueConstraint(name="libelle_fr", columns={"libelle_fr"}), @ORM\UniqueConstraint(name="libelle_ar", columns={"libelle_ar"})}, indexes={@ORM\Index(name="groupe_formation_id", columns={"groupe_formation_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\NiveauRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Niveau
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
     * @var string
     *
     * @ORM\Column(name="libelle_fr", type="string", length=255, nullable=false)
     */
    private $libelleFr;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_ar", type="string", length=255, nullable=false)
     */
    private $libelleAr;

    /**
     * @var integer
     *
     * @ORM\Column(name="succession", type="integer", nullable=false)
     */
    private $succession;

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
     * @var \Groupeformation
     *
     * @ORM\ManyToOne(targetEntity="Groupeformation", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="groupe_formation_id", referencedColumnName="id")
     * })
     */
    private $groupeFormation;



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
     * Set libelleFr
     *
     * @param string $libelleFr
     *
     * @return Niveau
     */
    public function setLibelleFr($libelleFr)
    {
        $this->libelleFr = $libelleFr;

        return $this;
    }

    /**
     * Get libelleFr
     *
     * @return string
     */
    public function getLibelleFr()
    {
        return $this->libelleFr;
    }

    /**
     * Set libelleAr
     *
     * @param string $libelleAr
     *
     * @return Niveau
     */
    public function setLibelleAr($libelleAr)
    {
        $this->libelleAr = $libelleAr;

        return $this;
    }

    /**
     * Get libelleAr
     *
     * @return string
     */
    public function getLibelleAr()
    {
        return $this->libelleAr;
    }

    /**
     * Set succession
     *
     * @param integer $succession
     *
     * @return Niveau
     */
    public function setSuccession($succession)
    {
        $this->succession = $succession;

        return $this;
    }

    /**
     * Get succession
     *
     * @return integer
     */
    public function getSuccession()
    {
        return $this->succession;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Niveau
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
     * @return Niveau
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
     * Set groupeFormation
     *
     * @param \ISI\ISIBundle\Entity\Groupeformation $groupeFormation
     *
     * @return Niveau
     */
    public function setGroupeFormation(\ISI\ISIBundle\Entity\Groupeformation $groupeFormation = null)
    {
        $this->groupeFormation = $groupeFormation;

        return $this;
    }

    /**
     * Get groupeFormation
     *
     * @return \ISI\ISIBundle\Entity\Groupeformation
     */
    public function getGroupeFormation()
    {
        return $this->groupeFormation;
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
