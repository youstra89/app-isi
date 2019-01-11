<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Note
 *
 * @ORM\Table(name="note", uniqueConstraints={@ORM\UniqueConstraint(name="note", columns={"eleve_id", "matiere_id", "examen_id"})}, indexes={@ORM\Index(name="examen_id", columns={"examen_id"}), @ORM\Index(name="matiere_id", columns={"matiere_id"}), @ORM\Index(name="eleve_id", columns={"eleve_id"})})
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\NoteRepository")
 */
class Note
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
     * @var integer
     *
     * @ORM\Column(name="note", type="float", nullable=true)
     */
    private $note;

    /**
     * @var boolean
     *
     * @ORM\Column(name="participation", type="boolean", nullable=true)
     */
    private $participation;

    /**
     * @var string
     *
     * @ORM\Column(name="appreciation_fr", type="string", length=255, nullable=true)
     */
    private $appreciationFr;

    /**
     * @var string
     *
     * @ORM\Column(name="appreciation_ar", type="string", length=255, nullable=true)
     */
    private $appreciationAr;

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
     * @var \Examen
     *
     * @ORM\ManyToOne(targetEntity="Examen")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="examen_id", referencedColumnName="examen_id")
     * })
     */
    private $examen;

    /**
     * @var \Eleve
     *
     * @ORM\ManyToOne(targetEntity="Eleve")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eleve_id", referencedColumnName="eleve_id")
     * })
     */
    private $eleve;

    /**
     * @var \Matiere
     *
     * @ORM\ManyToOne(targetEntity="Matiere")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="matiere_id", referencedColumnName="matiere_id")
     * })
     */
    private $matiere;



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
     * Set note
     *
     * @param integer $note
     *
     * @return Note
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return integer
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set participation
     *
     * @param boolean $participation
     *
     * @return Note
     */
    public function setParticipation($participation)
    {
        $this->participation = $participation;

        return $this;
    }

    /**
     * Get participation
     *
     * @return boolean
     */
    public function getParticipation()
    {
        return $this->participation;
    }

    /**
     * Set appreciationFr
     *
     * @param string $appreciationFr
     *
     * @return Note
     */
    public function setAppreciationFr($appreciationFr)
    {
        $this->appreciationFr = $appreciationFr;

        return $this;
    }

    /**
     * Get appreciationFr
     *
     * @return string
     */
    public function getAppreciationFr()
    {
        return $this->appreciationFr;
    }

    /**
     * Set appreciationAr
     *
     * @param string $appreciationAr
     *
     * @return Note
     */
    public function setAppreciationAr($appreciationAr)
    {
        $this->appreciationAr = $appreciationAr;

        return $this;
    }

    /**
     * Get appreciationAr
     *
     * @return string
     */
    public function getAppreciationAr()
    {
        return $this->appreciationAr;
    }

    /**
     * Set dateSave
     *
     * @param \DateTime $dateSave
     *
     * @return Note
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
     * @return Note
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
     * Set examen
     *
     * @param \ISI\ISIBundle\Entity\Examen $examen
     *
     * @return Note
     */
    public function setExamen(\ISI\ISIBundle\Entity\Examen $examen = null)
    {
        $this->examen = $examen;

        return $this;
    }

    /**
     * Get examen
     *
     * @return \ISI\ISIBundle\Entity\Examen
     */
    public function getExamen()
    {
        return $this->examen;
    }

    /**
     * Set eleve
     *
     * @param \ISI\ISIBundle\Entity\Eleve $eleve
     *
     * @return Note
     */
    public function setEleve(\ISI\ISIBundle\Entity\Eleve $eleve = null)
    {
        $this->eleve = $eleve;

        return $this;
    }

    /**
     * Get eleve
     *
     * @return \ISI\ISIBundle\Entity\Eleve
     */
    public function getEleve()
    {
        return $this->eleve;
    }

    /**
     * Set matiere
     *
     * @param \ISI\ISIBundle\Entity\Matiere $matiere
     *
     * @return Note
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

    // Fonction de tri de tableau d'objets
    static function cmp_obj($a, $b)
    {
        $al = strtolower($a->getMoyenne());
        $bl = strtolower($b->getMoyenne());
        if ($al == $bl)
        {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }
}
