<?php

namespace ISI\ISIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Appreciation
 *
 * @ORM\Table(name="appreciation")
 * @ORM\Entity(repositoryClass="ISI\ISIBundle\Repository\AppreciationRepository")
 */
class Appreciation
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
     * @ORM\Column(name="appreciation_fr", type="string", length=255, nullable=false)
     */
    private $appreciationFr;

    /**
     * @var string
     *
     * @ORM\Column(name="appreciation_ar", type="string", length=255, nullable=false)
     */
    private $appreciationAr;


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
}
