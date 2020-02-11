<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Languematiere
 *
 * @ORM\Table(name="languematiere")
 * @ORM\Entity
 */
class Languematiere
{
    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_langue", type="string", length=255, nullable=false)
     */
    private $libelle;



    /**
     * Get referenceLangue
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }


    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Languematiere
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }
}
