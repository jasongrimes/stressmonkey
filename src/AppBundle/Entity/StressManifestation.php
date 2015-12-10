<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StressManifestation
 *
 * @ORM\Table(name="stress_manifestation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StressManifestationRepository")
 */
class StressManifestation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=255)
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity="StressLog", inversedBy="manifestations")
     * @ORM\JoinColumn(name="stress_log_id", referencedColumnName="id", nullable=false)
     */
    private $stressLog;

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
     * Set text
     *
     * @param string $text
     *
     * @return StressManifestation
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set stressLog
     *
     * @param \AppBundle\Entity\StressLog $stressLog
     *
     * @return StressManifestation
     */
    public function setStressLog(\AppBundle\Entity\StressLog $stressLog = null)
    {
        $this->stressLog = $stressLog;

        return $this;
    }

    /**
     * Get stressLog
     *
     * @return \AppBundle\Entity\StressLog
     */
    public function getStressLog()
    {
        return $this->stressLog;
    }
}
