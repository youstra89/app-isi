<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Groupeformation
 *
 * @ORM\Table(name="groupeformation", uniqueConstraints={@ORM\UniqueConstraint(name="libelle_ar", columns={"libelle_ar"}), @ORM\UniqueConstraint(name="libelle_fr", columns={"libelle_fr"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\GroupeformationRepository")
 */
class Groupeformation
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
     * @var string
     *
     * @ORM\Column(name="reference_grp", type="string", length=255, nullable=false)
     */
     private $referenceGrp;


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
     * @return Groupeformation
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
     * @return Groupeformation
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
     * Set referenceGrp
     *
     * @param string $referenceGrp
     *
     * @return Groupeformation
     */
    public function setReferenceGrp($referenceGrp)
    {
        $this->referenceGrp = $referenceGrp;

        return $this;
    }

    /**
     * Get referenceGrp
     *
     * @return string
     */
    public function getReferenceGrp()
    {
        return $this->referenceGrp;
    }
}
