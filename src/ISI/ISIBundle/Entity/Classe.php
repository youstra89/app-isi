<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

// ALTER TABLE halaqa CHANGE annexe_id annexe_id INT NOT NULL;
// ALTER TABLE languematiere CHANGE reference reference VARCHAR(255) NOT NULL;
// ALTER TABLE classe CHANGE annexe_id annexe_id INT NOT NULL;
// CREATE UNIQUE INDEX libelle_fr ON classe (annee_id, annexe_id, libelle_fr);
// CREATE UNIQUE INDEX libelle_ar ON classe (annee_id, annexe_id, libelle_ar);
// ALTER TABLE eleve CHANGE annexe_id annexe_id INT NOT NULL;
// ALTER TABLE annee_contrat RENAME INDEX idx_4fb4eb2960349993 TO IDX_4FB4EB291823061F;
// ALTER TABLE enseignant CHANGE annexe_id annexe_id INT NOT NULL;
/**
 * Classe
 *
 * @ORM\Table(
 *      name="classe",
 *      indexes={
 *          @ORM\Index(name="annee_scolaire_classe_fk", columns={"annee_id"}), 
 *          @ORM\Index(name="niveau_id", columns={"niveau_id"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="libelle_fr", columns={"annee_id", "annexe_id", "libelle_fr"}),
 *          @ORM\UniqueConstraint(name="libelle_ar", columns={"annee_id", "annexe_id", "libelle_ar"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\ClasseRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Classe
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
     * @ORM\Column(name="genre", type="string", length=255, nullable=false)
     */
    private $genre;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $createdBy;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="updated_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $updatedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var \Annee
     *
     * @ORM\ManyToOne(targetEntity="Annee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_id", referencedColumnName="id")
     * })
     */
    private $annee;

    /**
     * @var \Niveau
     *
     * @ORM\ManyToOne(targetEntity="Niveau", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="niveau_id", referencedColumnName="id")
     * })
     */
    private $niveau;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ENSBundle\Entity\AnneeContratClasse", mappedBy="classe")
     */
    private $cours;

    /**
     * @var \Annexe
     *
     * @ORM\ManyToOne(targetEntity="Annexe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annexe_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $annexe;


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
     * @return Classe
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
     * @return Classe
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
     * Set genre
     *
     * @param string $genre
     *
     * @return Classe
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get genre
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Classe
     */
    public function setcreated_at($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getcreated_at()
    {
        return $this->createdAt;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     *
     * @return Classe
     */
    public function setupdated_at($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getupdated_at()
    {
        return $this->updatedAt;
    }


    /**
     * Set niveau
     *
     * @param \ISI\ISIBundle\Entity\Niveau $niveau
     *
     * @return Classe
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

    //Les méthodes personnelles commencent indexAction
    /**
     * @ORM\PrePersist
     */
    public function saveDate()
    {
      $this->setcreated_at(new \Datetime());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
      $this->setupdated_at(new \Datetime());
    }

    public function anneeScolaireClasse($as)
    {
      $this->setAnnee($as);
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Classe
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Classe
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set annee
     *
     * @param \ISI\ISIBundle\Entity\Annee $annee
     *
     * @return Classe
     */
    public function setAnnee(\ISI\ISIBundle\Entity\Annee $annee = null)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return \ISI\ISIBundle\Entity\Annee
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return Classe
     */
    public function setCreatedBy(\UserBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \UserBundle\Entity\User $updatedBy
     *
     * @return Classe
     */
    public function setUpdatedBy(\UserBundle\Entity\User $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \UserBundle\Entity\User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cours = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add cour
     *
     * @param \ISI\ENSBundle\Entity\AnneeContratClasse $cour
     *
     * @return Classe
     */
    public function addCour(\ISI\ENSBundle\Entity\AnneeContratClasse $cour)
    {
        $this->cours[] = $cour;

        return $this;
    }

    /**
     * Remove cour
     *
     * @param \ISI\ENSBundle\Entity\AnneeContratClasse $cour
     */
    public function removeCour(\ISI\ENSBundle\Entity\AnneeContratClasse $cour)
    {
        $this->cours->removeElement($cour);
    }

    /**
     * Get cours
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCours()
    {
        return $this->cours;
    }

    /**
     * Set annexe
     *
     * @param \ISI\ISIBundle\Entity\Annexe $annexe
     *
     * @return Classe
     */
    public function setAnnexe(\ISI\ISIBundle\Entity\Annexe $annexe = null)
    {
        $this->annexe = $annexe;

        return $this;
    }

    /**
     * Get annexe
     *
     * @return \ISI\ISIBundle\Entity\Annexe
     */
    public function getAnnexe()
    {
        return $this->annexe;
    }
}
