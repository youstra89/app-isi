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
     * @ORM\Column(name="reference_langue", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $referenceLangue;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_langue", type="string", length=255, nullable=false)
     */
    private $libelleLangue;



    /**
     * Get referenceLangue
     *
     * @return string
     */
    public function getReferenceLangue()
    {
        return $this->referenceLangue;
    }

    /**
     * Set libelleLangue
     *
     * @param string $libelleLangue
     *
     * @return Languematiere
     */
    public function setLibelleLangue($libelleLangue)
    {
        $this->libelleLangue = $libelleLangue;

        return $this;
    }

    /**
     * Get libelleLangue
     *
     * @return string
     */
    public function getLibelleLangue()
    {
        return $this->libelleLangue;
    }
}
