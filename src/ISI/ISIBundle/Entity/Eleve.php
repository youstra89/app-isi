<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Eleve
 *
 * @ORM\Table(name="eleve", indexes={@ORM\Index(name="contact_pere", columns={"contact_pere"}), @ORM\Index(name="contact_mere", columns={"contact_mere"}), @ORM\Index(name="contact", columns={"contact"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\EleveRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Eleve
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
     * @ORM\Column(name="matricule", type="string", length=255, nullable=false)
     */
    private $matricule;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_fr", type="string", length=255, nullable=true)
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
     * @ORM\Column(name="pnom_fr", type="string", length=255, nullable=true)
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
     * @ORM\Column(name="sexe", type="string", length=255, nullable=false)
     */
    private $sexe;

    /**
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=255, nullable=true)
     */
    private $profession;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_extrait", type="string", length=255, nullable=false)
     */
    private $refExtrait;

    /**
     * @var string
     *
     * @ORM\Column(name="grp_sanguin", type="string", length=1, nullable=true)
     */
    private $grpSanguin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_naissance", type="date", nullable=false)
     */
    private $dateNaissance;

    /**
     * @var string
     *
     * @ORM\Column(name="lieu_naissance", type="string", length=255, nullable=false)
     */
    private $lieuNaissance;

    /**
     * @var string
     *
     * @ORM\Column(name="lieu_naissance_ar", type="string", length=255, nullable=true)
     */
    private $lieuNaissanceAr;

    /**
     * @var string
     *
     * @ORM\Column(name="nationalite", type="string", length=255, nullable=false)
     */
    private $nationalite;

    /**
     * @var string
     *
     * @ORM\Column(name="nationalite_ar", type="string", length=255, nullable=true)
     */
    private $nationaliteAr;

    /**
     * @var string
     *
     * @ORM\Column(name="commune", type="string", length=255, nullable=false)
     */
    private $commune;

    /**
     * @var string
     *
     * @ORM\Column(name="residence", type="string", length=255, nullable=false)
     */
    private $residence;

    /**
     * @var string
     *
     * @ORM\Column(name="contact", type="string", length=255, nullable=true)
     */
    private $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="ets_origine", type="string", length=255, nullable=true)
     */
    private $etsOrigine;

    /**
     * @var string
     *
     * @ORM\Column(name="etat_sante", type="string", length=255, nullable=true)
     */
    private $etatSante = 'Apte';

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_pere", type="string", length=255, nullable=false)
     */
    private $nomPere;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_pere", type="string", length=255, nullable=true)
     */
    private $contactPere;

    /**
     * @var string
     *
     * @ORM\Column(name="profession_pere", type="string", length=255, nullable=true)
     */
    private $professionPere;

    /**
     * @var string
     *
     * @ORM\Column(name="situation_pere", type="string", length=255, nullable=false)
     */
    private $situationPere;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_mere", type="string", length=255, nullable=false)
     */
    private $nomMere;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_mere", type="string", length=255, nullable=true)
     */
    private $contactMere;

    /**
     * @var string
     *
     * @ORM\Column(name="profession_mere", type="string", length=255, nullable=true)
     */
    private $professionMere;

    /**
     * @var string
     *
     * @ORM\Column(name="situation_mere", type="string", length=255, nullable=false)
     */
    private $situationMere;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_tuteur", type="string", length=255, nullable=true)
     */
    private $nomTuteur = 'En famille';

    /**
     * @var string
     *
     * @ORM\Column(name="contact_tuteur", type="string", length=255, nullable=true)
     */
    private $contactTuteur;

    /**
     * @var string
     *
     * @ORM\Column(name="profession_tuteur", type="string", length=255, nullable=true)
     */
    private $professionTuteur;

    /**
     * @var string
     *
     * @ORM\Column(name="regime", type="string", length=255, nullable=false)
     */
    private $regime;

    /**
     * @var boolean
     *
     * @ORM\Column(name="renvoye", type="boolean", nullable=false)
     */
    private $renvoye = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_renvoi", type="datetime", nullable=true)
     */
    private $dateRenvoi;

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
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\Frequenter", mappedBy="eleve")
     */
    private $frequenter;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\EleveGroupeComposition", mappedBy="eleve")
     */
    private $elevegroupecomposition;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\Moyenne", mappedBy="eleve")
     */
    private $moyenne; 

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\Memoriser", mappedBy="eleve")
     */
    private $memoriser;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\Interner", mappedBy="eleve")
     */
    private $interner;

    /**
     * @ORM\OneToMany(targetEntity="ISI\ISIBundle\Entity\Eleverenvoye", mappedBy="eleve")
     */
    private $eleveRenvoye;

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
     * Constructor
     */
    public function __construct()
    {
        $this->frequenter = new \Doctrine\Common\Collections\ArrayCollection();
        $this->elevegroupecomposition = new \Doctrine\Common\Collections\ArrayCollection();
        $this->moyenne = new \Doctrine\Common\Collections\ArrayCollection();
        $this->memoriser = new \Doctrine\Common\Collections\ArrayCollection();
        $this->interner = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eleveRenvoye = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @return Eleve
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
     * @return Eleve
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
     * @return Eleve
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
     * @return Eleve
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

    public function getNom()
    {
        return $this->pnomFr ." ". $this->nomFr;
    }
    /**
     * Set pnomAr
     *
     * @param string $pnomAr
     *
     * @return Eleve
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
     * @return Eleve
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
     * Set profession
     *
     * @param string $profession
     *
     * @return Eleve
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;

        return $this;
    }

    /**
     * Get profession
     *
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * Set refExtrait
     *
     * @param string $refExtrait
     *
     * @return Eleve
     */
    public function setRefExtrait($refExtrait)
    {
        $this->refExtrait = $refExtrait;

        return $this;
    }

    /**
     * Get refExtrait
     *
     * @return string
     */
    public function getRefExtrait()
    {
        return $this->refExtrait;
    }

    /**
     * Set grpSanguin
     *
     * @param string $grpSanguin
     *
     * @return Eleve
     */
    public function setGrpSanguin($grpSanguin)
    {
        $this->grpSanguin = $grpSanguin;

        return $this;
    }

    /**
     * Get grpSanguin
     *
     * @return string
     */
    public function getGrpSanguin()
    {
        return $this->grpSanguin;
    }

    /**
     * Set dateNaissance
     *
     * @param \DateTime $dateNaissance
     *
     * @return Eleve
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
     * @return Eleve
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
     * Set lieuNaissanceAr
     *
     * @param string $lieuNaissanceAr
     *
     * @return Eleve
     */
    public function setLieuNaissanceAr($lieuNaissanceAr)
    {
        $this->lieuNaissanceAr = $lieuNaissanceAr;

        return $this;
    }

    /**
     * Get lieuNaissanceAr
     *
     * @return string
     */
    public function getLieuNaissanceAr()
    {
        return $this->lieuNaissanceAr;
    }

    /**
     * Set nationalite
     *
     * @param string $nationalite
     *
     * @return Eleve
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
     * Set nationaliteAr
     *
     * @param string $nationaliteAr
     *
     * @return Eleve
     */
    public function setNationaliteAr($nationaliteAr)
    {
        $this->nationaliteAr = $nationaliteAr;

        return $this;
    }

    /**
     * Get nationaliteAr
     *
     * @return string
     */
    public function getNationaliteAr()
    {
        return $this->nationaliteAr;
    }

    /**
     * Set commune
     *
     * @param string $commune
     *
     * @return Eleve
     */
    public function setCommune($commune)
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * Get commune
     *
     * @return string
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Set residence
     *
     * @param string $residence
     *
     * @return Eleve
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
     * Set contact
     *
     * @param string $contact
     *
     * @return Eleve
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
     * Set etsOrigine
     *
     * @param string $etsOrigine
     *
     * @return Eleve
     */
    public function setEtsOrigine($etsOrigine)
    {
        $this->etsOrigine = $etsOrigine;

        return $this;
    }

    /**
     * Get etsOrigine
     *
     * @return string
     */
    public function getEtsOrigine()
    {
        return $this->etsOrigine;
    }

    /**
     * Set etatSante
     *
     * @param string $etatSante
     *
     * @return Eleve
     */
    public function setEtatSante($etatSante)
    {
        $this->etatSante = $etatSante;

        return $this;
    }

    /**
     * Get etatSante
     *
     * @return string
     */
    public function getEtatSante()
    {
        return $this->etatSante;
    }

    /**
     * Set photo
     *
     * @param string $photo
     *
     * @return Eleve
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set nomPere
     *
     * @param string $nomPere
     *
     * @return Eleve
     */
    public function setNomPere($nomPere)
    {
        $this->nomPere = $nomPere;

        return $this;
    }

    /**
     * Get nomPere
     *
     * @return string
     */
    public function getNomPere()
    {
        return $this->nomPere;
    }

    /**
     * Set contactPere
     *
     * @param string $contactPere
     *
     * @return Eleve
     */
    public function setContactPere($contactPere)
    {
        $this->contactPere = $contactPere;

        return $this;
    }

    /**
     * Get contactPere
     *
     * @return string
     */
    public function getContactPere()
    {
        return $this->contactPere;
    }

    /**
     * Set professionPere
     *
     * @param string $professionPere
     *
     * @return Eleve
     */
    public function setProfessionPere($professionPere)
    {
        $this->professionPere = $professionPere;

        return $this;
    }

    /**
     * Get professionPere
     *
     * @return string
     */
    public function getProfessionPere()
    {
        return $this->professionPere;
    }

    /**
     * Set situationPere
     *
     * @param string $situationPere
     *
     * @return Eleve
     */
    public function setSituationPere($situationPere)
    {
        $this->situationPere = $situationPere;

        return $this;
    }

    /**
     * Get situationPere
     *
     * @return string
     */
    public function getSituationPere()
    {
        return $this->situationPere;
    }

    /**
     * Set nomMere
     *
     * @param string $nomMere
     *
     * @return Eleve
     */
    public function setNomMere($nomMere)
    {
        $this->nomMere = $nomMere;

        return $this;
    }

    /**
     * Get nomMere
     *
     * @return string
     */
    public function getNomMere()
    {
        return $this->nomMere;
    }

    /**
     * Set contactMere
     *
     * @param string $contactMere
     *
     * @return Eleve
     */
    public function setContactMere($contactMere)
    {
        $this->contactMere = $contactMere;

        return $this;
    }

    /**
     * Get contactMere
     *
     * @return string
     */
    public function getContactMere()
    {
        return $this->contactMere;
    }

    /**
     * Set professionMere
     *
     * @param string $professionMere
     *
     * @return Eleve
     */
    public function setProfessionMere($professionMere)
    {
        $this->professionMere = $professionMere;

        return $this;
    }

    /**
     * Get professionMere
     *
     * @return string
     */
    public function getProfessionMere()
    {
        return $this->professionMere;
    }

    /**
     * Set situationMere
     *
     * @param string $situationMere
     *
     * @return Eleve
     */
    public function setSituationMere($situationMere)
    {
        $this->situationMere = $situationMere;

        return $this;
    }

    /**
     * Get situationMere
     *
     * @return string
     */
    public function getSituationMere()
    {
        return $this->situationMere;
    }

    /**
     * Set nomTuteur
     *
     * @param string $nomTuteur
     *
     * @return Eleve
     */
    public function setNomTuteur($nomTuteur)
    {
        $this->nomTuteur = $nomTuteur;

        return $this;
    }

    /**
     * Get nomTuteur
     *
     * @return string
     */
    public function getNomTuteur()
    {
        return $this->nomTuteur;
    }

    /**
     * Set contactTuteur
     *
     * @param string $contactTuteur
     *
     * @return Eleve
     */
    public function setContactTuteur($contactTuteur)
    {
        $this->contactTuteur = $contactTuteur;

        return $this;
    }

    /**
     * Get contactTuteur
     *
     * @return string
     */
    public function getContactTuteur()
    {
        return $this->contactTuteur;
    }

    /**
     * Set professionTuteur
     *
     * @param string $professionTuteur
     *
     * @return Eleve
     */
    public function setProfessionTuteur($professionTuteur)
    {
        $this->professionTuteur = $professionTuteur;

        return $this;
    }

    /**
     * Get professionTuteur
     *
     * @return string
     */
    public function getProfessionTuteur()
    {
        return $this->professionTuteur;
    }

    /**
     * Set regime
     *
     * @param string $regime
     *
     * @return Eleve
     */
    public function setRegime($regime)
    {
        $this->regime = $regime;

        return $this;
    }

    /**
     * Get regime
     *
     * @return string
     */
    public function getRegime()
    {
        return $this->regime;
    }

    /**
     * Set renvoye
     *
     * @param boolean $renvoye
     *
     * @return Eleve
     */
    public function setRenvoye($renvoye)
    {
        $this->renvoye = $renvoye;

        return $this;
    }

    /**
     * Get renvoye
     *
     * @return boolean
     */
    public function getRenvoye()
    {
        return $this->renvoye;
    }

    /**
     * Set dateRenvoi
     *
     * @param \DateTime $dateRenvoi
     *
     * @return Eleve
     */
    public function setDateRenvoi($dateRenvoi)
    {
        $this->dateRenvoi = $dateRenvoi;

        return $this;
    }

    /**
     * Get dateRenvoi
     *
     * @return \DateTime
     */
    public function getDateRenvoi()
    {
        return $this->dateRenvoi;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Eleve
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
     * @return Eleve
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
     * Set createdBy
     *
     * @param \UserBundle\Entity\User $createdBy
     *
     * @return Eleve
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
     * @return Eleve
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
     * Add frequenter
     *
     * @param \ISI\ISIBundle\Entity\Frequenter $frequenter
     *
     * @return Eleve
     */
    public function addFrequenter(\ISI\ISIBundle\Entity\Frequenter $frequenter)
    {
        $this->frequenter[] = $frequenter;

        return $this;
    }

    /**
     * Remove frequenter
     *
     * @param \ISI\ISIBundle\Entity\Frequenter $frequenter
     */
    public function removeFrequenter(\ISI\ISIBundle\Entity\Frequenter $frequenter)
    {
        $this->frequenter->removeElement($frequenter);
    }

    /**
     * Get frequenter
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFrequenter()
    {
        return $this->frequenter;
    }

    /**
     * Add elevegroupecomposition
     *
     * @param \ISI\ISIBundle\Entity\EleveGroupeComposition $elevegroupecomposition
     *
     * @return Eleve
     */
    public function addElevegroupecomposition(\ISI\ISIBundle\Entity\EleveGroupeComposition $elevegroupecomposition)
    {
        $this->elevegroupecomposition[] = $elevegroupecomposition;

        return $this;
    }

    /**
     * Remove elevegroupecomposition
     *
     * @param \ISI\ISIBundle\Entity\EleveGroupeComposition $elevegroupecomposition
     */
    public function removeElevegroupecomposition(\ISI\ISIBundle\Entity\EleveGroupeComposition $elevegroupecomposition)
    {
        $this->elevegroupecomposition->removeElement($elevegroupecomposition);
    }

    /**
     * Get elevegroupecomposition
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getElevegroupecomposition()
    {
        return $this->elevegroupecomposition;
    }

    /**
     * Add moyenne
     *
     * @param \ISI\ISIBundle\Entity\Moyenne $moyenne
     *
     * @return Eleve
     */
    public function addMoyenne(\ISI\ISIBundle\Entity\Moyenne $moyenne)
    {
        $this->moyenne[] = $moyenne;

        return $this;
    }

    /**
     * Remove moyenne
     *
     * @param \ISI\ISIBundle\Entity\Moyenne $moyenne
     */
    public function removeMoyenne(\ISI\ISIBundle\Entity\Moyenne $moyenne)
    {
        $this->moyenne->removeElement($moyenne);
    }

    /**
     * Get moyenne
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMoyenne()
    {
        return $this->moyenne;
    }

    /**
     * Add memoriser
     *
     * @param \ISI\ISIBundle\Entity\Memoriser $memoriser
     *
     * @return Eleve
     */
    public function addMemoriser(\ISI\ISIBundle\Entity\Memoriser $memoriser)
    {
        $this->memoriser[] = $memoriser;

        return $this;
    }

    /**
     * Remove memoriser
     *
     * @param \ISI\ISIBundle\Entity\Memoriser $memoriser
     */
    public function removeMemoriser(\ISI\ISIBundle\Entity\Memoriser $memoriser)
    {
        $this->memoriser->removeElement($memoriser);
    }

    /**
     * Get memoriser
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMemoriser()
    {
        return $this->memoriser;
    }

    /**
     * Add interner
     *
     * @param \ISI\ISIBundle\Entity\Interner $interner
     *
     * @return Eleve
     */
    public function addInterner(\ISI\ISIBundle\Entity\Interner $interner)
    {
        $this->interner[] = $interner;

        return $this;
    }

    /**
     * Remove interner
     *
     * @param \ISI\ISIBundle\Entity\Interner $interner
     */
    public function removeInterner(\ISI\ISIBundle\Entity\Interner $interner)
    {
        $this->interner->removeElement($interner);
    }

    /**
     * Get interner
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterner()
    {
        return $this->interner;
    }

    /**
     * Add eleveRenvoye
     *
     * @param \ISI\ISIBundle\Entity\Eleverenvoye $eleveRenvoye
     *
     * @return Eleve
     */
    public function addEleveRenvoye(\ISI\ISIBundle\Entity\Eleverenvoye $eleveRenvoye)
    {
        $this->eleveRenvoye[] = $eleveRenvoye;

        return $this;
    }

    /**
     * Remove eleveRenvoye
     *
     * @param \ISI\ISIBundle\Entity\Eleverenvoye $eleveRenvoye
     */
    public function removeEleveRenvoye(\ISI\ISIBundle\Entity\Eleverenvoye $eleveRenvoye)
    {
        $this->eleveRenvoye->removeElement($eleveRenvoye);
    }

    /**
     * Get eleveRenvoye
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEleveRenvoye()
    {
        return $this->eleveRenvoye;
    }

    /**
     * Set annexe
     *
     * @param \ISI\ISIBundle\Entity\Annexe $annexe
     *
     * @return Eleve
     */
    public function setAnnexe(\ISI\ISIBundle\Entity\Annexe $annexe)
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
