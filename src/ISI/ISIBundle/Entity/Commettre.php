<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Commettre
 *
 * @ORM\Table(name="commettre", uniqueConstraints={@ORM\UniqueConstraint(name="commettre", columns={"eleve_id", "annee_scolaire_id", "probleme_id"})}, indexes={@ORM\Index(name="probleme_id", columns={"probleme_id"}), @ORM\Index(name="annee_scolaire_id", columns={"annee_scolaire_id"}), @ORM\Index(name="eleve_id", columns={"eleve_id"})})
 * @ORM\Entity
 */
class Commettre
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
     * @var \Eleve
     *
     * @ORM\ManyToOne(targetEntity="Eleve")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eleve_id", referencedColumnName="eleve_id")
     * })
     */
    private $eleve;

    /**
     * @var \Probleme
     *
     * @ORM\ManyToOne(targetEntity="Probleme", inversedBy="commettre")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="probleme_id", referencedColumnName="probleme_id")
     * })
     */
    private $probleme;

    /**
     * @var \Anneescolaire
     *
     * @ORM\ManyToOne(targetEntity="Anneescolaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="annee_scolaire_id", referencedColumnName="annee_scolaire_id")
     * })
     */
    private $anneeScolaire;



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
     * Set eleve
     *
     * @param \ISI\ISIBundle\Entity\Eleve $eleve
     *
     * @return Commettre
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
     * Set probleme
     *
     * @param \ISI\ISIBundle\Entity\Probleme $probleme
     *
     * @return Commettre
     */
    public function setProbleme(\ISI\ISIBundle\Entity\Probleme $probleme = null)
    {
        $this->probleme = $probleme;

        return $this;
    }

    /**
     * Get probleme
     *
     * @return \ISI\ISIBundle\Entity\Probleme
     */
    public function getProbleme()
    {
        return $this->probleme;
    }

    /**
     * Set anneeScolaire
     *
     * @param \ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire
     *
     * @return Commettre
     */
    public function setAnneeScolaire(\ISI\ISIBundle\Entity\Anneescolaire $anneeScolaire = null)
    {
        $this->anneeScolaire = $anneeScolaire;

        return $this;
    }

    /**
     * Get anneeScolaire
     *
     * @return \ISI\ISIBundle\Entity\Anneescolaire
     */
    public function getAnneeScolaire()
    {
        return $this->anneeScolaire;
    }
}
