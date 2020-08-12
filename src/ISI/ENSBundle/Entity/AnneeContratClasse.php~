<?php

namespace ISI\ENSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * AnneeContratClasse
 *
 * @ORM\Table(name="annee_contrat_classe")
 * @ORM\Entity(repositoryClass="ISI\ENSBundle\Repository\AnneeContratClasseRepository")
 * @UniqueEntity(
 *     fields={"annee_id", "annee_contrat_id", "classe_id", "matiere_id"},
 *     errorPath="anneeContrat",
 *     message="L'enseignant donne déjà ce cours dans la classe sélectionnée"
 * )
 */
class AnneeContratClasse
{
    const JOURS = [
        1 => 'Samedi',
        2 => 'Dimanche',
        3 => 'Lundi',
        4 => 'Mardi',
        5 => 'Mercredi',
        6 => 'Jeudi',
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \Annee
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\Annee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_id", referencedColumnName="id")
     * })
     */
    private $annee;

    /**
     * @var \Classe
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\Classe", inversedBy="cours")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="classe_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $classe;

    /**
     * @var \Halaqa
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\Halaqa", inversedBy="cours")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="halaqa_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $halaqa;

    /**
     * @var \Matiere
     *
     * @ORM\ManyToOne(targetEntity="\ISI\ISIBundle\Entity\Matiere")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="matiere_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $matiere;

    /**
     * @var \AnneeContrat
     *
     * @ORM\ManyToOne(targetEntity="AnneeContrat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_contrat_id", referencedColumnName="id")
     * })
     */
    private $anneeContrat;

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
     * @var integer
     *
     * @ORM\Column(name="numero", type="integer", nullable=false)
     */
    private $numero;

    /**
     * @var integer
     *
     * @ORM\Column(name="jour", type="integer", nullable=true)
     */
    private $jour;

    /**
     * @var integer
     *
     * @ORM\Column(name="heure", type="integer", nullable=true)
     */
    private $heure;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt; 

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;


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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return AnneeContratClasse
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
     * @return AnneeContratClasse
     */
    public function jourdecours()
    {
        return self::JOURS[$this->jour];
    }

    /**
     * @return AnneeContratClasse
     */
    public function trouvejour(int $jour)
    {
        return self::JOURS[$jour];
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return AnneeContratClasse
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
     * @return AnneeContratClasse
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
     * Set classe
     *
     * @param \ISI\ISIBundle\Entity\Classe $classe
     *
     * @return AnneeContratClasse
     */
    public function setClasse(\ISI\ISIBundle\Entity\Classe $classe = null)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return \ISI\ISIBundle\Entity\Classe
     */
    public function getClasse()
    {
        return $this->classe;
    }

    /**
     * Set matiere
     *
     * @param \ISI\ISIBundle\Entity\Matiere $matiere
     *
     * @return AnneeContratClasse
     */
    public function setMatiere(\ISI\ISIBundle\Entity\Matiere $matiere = null)
    {
        $this->matiere = $matiere;

        return $this;
    }

    /**
     * Get matiere
     *
     * @return \ISI\ISIBundle\Entity\Matiere
     */
    public function getMatiere()
    {
        return $this->matiere;
    }

    /**
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return AnneeContratClasse
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
     * @return AnneeContratClasse
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
     * Set contrat
     *
     * @param \ISI\ENSBundle\Entity\AnneeContrat $anneeContrat
     *
     * @return AnneeContratClasse
     */
    public function setAnneeContrat(\ISI\ENSBundle\Entity\AnneeContrat $anneeContrat = null)
    {
        $this->anneeContrat = $anneeContrat;

        return $this;
    }

    /**
     * Get anneeContrat
     *
     * @return \ISI\ENSBundle\Entity\AnneeContrat
     */
    public function getAnneeContrat()
    {
        return $this->anneeContrat;
    }

    /**
     * Set halaqa
     *
     * @param \ISI\ISIBundle\Entity\Halaqa $halaqa
     *
     * @return AnneeContratClasse
     */
    public function setHalaqa(\ISI\ISIBundle\Entity\Halaqa $halaqa = null)
    {
        $this->halaqa = $halaqa;

        return $this;
    }

    /**
     * Get halaqa
     *
     * @return \ISI\ISIBundle\Entity\Halaqa
     */
    public function getHalaqa()
    {
        return $this->halaqa;
    }

    /**
     * Set numero
     *
     * @param integer $numero
     *
     * @return AnneeContratClasse
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return integer
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set jour
     *
     * @param integer $jour
     *
     * @return AnneeContratClasse
     */
    public function setJour($jour)
    {
        $this->jour = $jour;

        return $this;
    }

    /**
     * Get jour
     *
     * @return integer
     */
    public function getJour()
    {
        return $this->jour;
    }

    /**
     * Set heure
     *
     * @param integer $heure
     *
     * @return AnneeContratClasse
     */
    public function setHeure($heure)
    {
        $this->heure = $heure;

        return $this;
    }

    /**
     * Get heure
     *
     * @return integer
     */
    public function getHeure()
    {
        return $this->heure;
    }
}
