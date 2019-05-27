<?php

namespace ISI\ENSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Enseignant
 *
 * @ORM\Table(name="enseignant")
 * @ORM\Entity(repositoryClass="ISI\ENSBundle\Repository\EnseignantRepository")
 */
class Enseignant
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
     * @ORM\Column(name="matricule", type="string", length=255, nullable=false, unique=true)
     */
    private $matricule;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_fr", type="string", length=255, nullable=false)
     */
    private $nomFr;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_ar", type="string", length=255, nullable=true)
     */
    private $nomAr;

    /**
     * @var string
     *
     * @ORM\Column(name="pnom_fr", type="string", length=255, nullable=false)
     */
    private $pnomFr;

    /**
     * @var string
     *
     * @ORM\Column(name="pnom_ar", type="string", length=255, nullable=true)
     */
    private $pnomAr;

    /**
     * @var string
     *
     * @ORM\Column(name="sexe", type="string", length=255, nullable=true)
     */
    private $sexe;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_naissance", type="date", nullable=true)
     */
    private $dateNaissance;

    /**
     * @var string
     *
     * @ORM\Column(name="lieu_naissance", type="string", length=255, nullable=true)
     */
    private $lieuNaissance;

    /**
     * @var string
     *
     * @ORM\Column(name="reference_cni", type="string", length=255, nullable=true)
     */
    private $referenceCni;

    /**
     * @var string
     *
     * @ORM\Column(name="contact", type="string", length=255, nullable=true)
     */
    private $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="nationalite", type="string", length=255, nullable=true)
     */
    private $nationalite;

    /**
     * @var string
     *
     * @ORM\Column(name="niveau_etude", type="string", length=255, nullable=true)
     */
    private $niveauEtude;

    /**
     * @var string
     *
     * @ORM\Column(name="diplome_obtenu", type="string", length=255, nullable=true)
     */
    private $diplomeObtenu;

    /**
     * @var string
     *
     * @ORM\Column(name="autres_competences", type="string", length=255, nullable=true)
     */
    private $autresCompetences;

    /**
     * @var string
     *
     * @ORM\Column(name="situation_matrimoniale", type="string", length=255, nullable=true)
     */
    private $situationMatrimoniale;

    /**
     * @var string
     *
     * @ORM\Column(name="residence", type="string", length=255, nullable=true)
     */
    private $residence;

    /**
     * @var string
     *
     * @ORM\Column(name="langues_parlees", type="string", length=255, nullable=true)
     */
    private $languesParlees;

    /**
     * @var string
     *
     * @ORM\Column(name="exp_professionnelle", type="integer", length=11, nullable=true)
     */
    private $expProfessionnelle;

    /**
     * @var string
     *
     * @ORM\Column(name="annee_obtention", type="string", length=255, nullable=true)
     */
    private $anneeObtention;

    /**
     * @var boolean
     *
     * @ORM\Column(name="rupture", type="boolean", nullable=false)
     */
    private $rupture = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_rupture", type="datetime", nullable=true)
     */
    private $dateRupture;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set matricule
     *
     * @param string $matricule
     *
     * @return Enseignant
     */
     public function setMatricule($matricule)
     {
         $this->matricule = $matricule;
 
         return $this;
     }
 
     /**
      * Get matricule
      *
      * @return string
      */
     public function getMatricule()
     {
         return $this->matricule;
     }

    /**
     * Set nomFr
     *
     * @param string $nomFr
     *
     * @return Enseignant
     */
    public function setNomFr($nomFr)
    {
        $this->nomFr = $nomFr;

        return $this;
    }

    /**
     * Get nomFr
     *
     * @return string
     */
    public function getNomFr()
    {
        return $this->nomFr;
    }

    /**
     * Set nomAr
     *
     * @param string $nomAr
     *
     * @return Enseignant
     */
    public function setNomAr($nomAr)
    {
        $this->nomAr = $nomAr;

        return $this;
    }

    /**
     * Get nomAr
     *
     * @return string
     */
    public function getNomAr()
    {
        return $this->nomAr;
    }

    /**
     * Set pnomFr
     *
     * @param string $pnomFr
     *
     * @return Enseignant
     */
    public function setPnomFr($pnomFr)
    {
        $this->pnomFr = $pnomFr;

        return $this;
    }

    /**
     * Get pnomFr
     *
     * @return string
     */
    public function getPnomFr()
    {
        return $this->pnomFr;
    }

    /**
     * Set pnomAr
     *
     * @param string $pnomAr
     *
     * @return Enseignant
     */
    public function setPnomAr($pnomAr)
    {
        $this->pnomAr = $pnomAr;

        return $this;
    }

    /**
     * Get pnomAr
     *
     * @return string
     */
    public function getPnomAr()
    {
        return $this->pnomAr;
    }

    /**
     * Set sexe
     *
     * @param string $sexe
     *
     * @return Enseignant
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe
     *
     * @return string
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * Set dateNaissance
     *
     * @param \DateTime $dateNaissance
     *
     * @return Enseignant
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    /**
     * Get dateNaissance
     *
     * @return \DateTime
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * Set lieuNaissance
     *
     * @param string $lieuNaissance
     *
     * @return Enseignant
     */
    public function setLieuNaissance($lieuNaissance)
    {
        $this->lieuNaissance = $lieuNaissance;
 
        return $this;
    }
 
    /**
     * Get lieuNaissance
     *
     * @return string
     */
    public function getLieuNaissance()
    {
        return $this->lieuNaissance;
    }

    /**
     * Set nationalite
     *
     * @param string $nationalite
     *
     * @return Enseignant
     */
     public function setNationalite($nationalite)
     {
         $this->nationalite = $nationalite;
 
         return $this;
     }
 
     /**
      * Get nationalite
      *
      * @return string
      */
     public function getNationalite()
     {
         return $this->nationalite;
     }

    /**
     * Set referenceCni
     *
     * @param string $referenceCni
     *
     * @return Enseignant
     */
    public function setReferenceCni($referenceCni)
    {
        $this->referenceCni = $referenceCni;
  
        return $this;
    }
  
    /**
     * Get referenceCni
     *
     * @return string
     */
    public function getReferenceCni()
    {
        return $this->referenceCni;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return Enseignant
     */
     public function setContact($contact)
     {
         $this->contact = $contact;
 
         return $this;
     }
 
     /**
      * Get contact
      *
      * @return string
      */
     public function getContact()
     {
         return $this->contact;
     }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Enseignant
     */
     public function setEmail($email)
    {
        $this->email = $email;
 
        return $this;
    }
 
     /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set niveauEtude
     *
     * @param string $niveauEtude
     *
     * @return Enseignant
     */
    public function setNiveauEtude($niveauEtude)
    {
        $this->niveauEtude = $niveauEtude;

        return $this;
    }

    /**
     * Get niveauEtude
     *
     * @return string
     */
    public function getNiveauEtude()
    {
        return $this->niveauEtude;
    }

    /**
     * Set diplomeObtenu
     *
     * @param string $diplomeObtenu
     *
     * @return Enseignant
     */
    public function setDiplomeObtenu($diplomeObtenu)
    {
        $this->diplomeObtenu = $diplomeObtenu;

        return $this;
    }

    /**
     * Get diplomeObtenu
     *
     * @return string
     */
    public function getDiplomeObtenu()
    {
        return $this->diplomeObtenu;
    }

    /**
     * Set anneeObtention
     *
     * @param string $anneeObtention
     *
     * @return Enseignant
     */
     public function setAnneeObtention($anneeObtention)
    {
        $this->anneeObtention = $anneeObtention;
 
        return $this;
    }
 
    /**
     * Get anneeObtention
     *
     * @return string
     */
    public function getAnneeObtention()
    {
        return $this->anneeObtention;
    }

    /**
     * Set rupture
     *
     * @param boolean $rupture
     *
     * @return Enseignant
     */
     public function setRupture($rupture)
     {
         $this->rupture = $rupture;
 
         return $this;
     }
 
    /**
     * Get rupture
     *
     * @return boolean
     */
    public function getRupture()
    {
        return $this->rupture;
    }

    /**
     * Set dateRupture
     *
     * @param \DateTime $dateRupture
     *
     * @return Enseignant
     */
    public function setDateRupture($dateRupture)
    {
        $this->dateRupture = $dateRupture;
 
        return $this;
    }
 
    /**
     * Get dateRupture
     *
     * @return \DateTime
     */
    public function getDateRupture()
    {
        return $this->dateRupture;
    }

    /**
     * Set autresCompetences
     *
     * @param string $autresCompetences
     *
     * @return Enseignant
     */
    public function setAutresCompetences($autresCompetences)
    {
        $this->autresCompetences = $autresCompetences;

        return $this;
    }

    /**
     * Get autresCompetences
     *
     * @return string
     */
    public function getAutresCompetences()
    {
        return $this->autresCompetences;
    }

    /**
     * Set situationMatrimoniale
     *
     * @param string $situationMatrimoniale
     *
     * @return Enseignant
     */
    public function setSituationMatrimoniale($situationMatrimoniale)
    {
        $this->situationMatrimoniale = $situationMatrimoniale;

        return $this;
    }

    /**
     * Get situationMatrimoniale
     *
     * @return string
     */
    public function getSituationMatrimoniale()
    {
        return $this->situationMatrimoniale;
    }

    /**
     * Set residence
     *
     * @param string $residence
     *
     * @return Enseignant
     */
    public function setResidence($residence)
    {
        $this->residence = $residence;

        return $this;
    }

    /**
     * Get residence
     *
     * @return string
     */
    public function getResidence()
    {
        return $this->residence;
    }

    /**
     * Set languesParlees
     *
     * @param string $languesParlees
     *
     * @return Enseignant
     */
    public function setLanguesParlees($languesParlees)
    {
        $this->languesParlees = $languesParlees;

        return $this;
    }

    /**
     * Get languesParlees
     *
     * @return string
     */
    public function getLanguesParlees()
    {
        return $this->languesParlees;
    }

    /**
     * Set expProfessionnelle
     *
     * @param integer $expProfessionnelle
     *
     * @return Enseignant
     */
    public function setExpProfessionnelle($expProfessionnelle)
    {
        $this->expProfessionnelle = $expProfessionnelle;

        return $this;
    }

    /**
     * Get expProfessionnelle
     *
     * @return integer
     */
    public function getExpProfessionnelle()
    {
        return $this->expProfessionnelle;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Enseignant
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
     * @return Enseignant
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
}
