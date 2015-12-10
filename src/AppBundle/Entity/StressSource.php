<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StressSource
 *
 * @ORM\Table(name="stress_source")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StressSourceRepository")
 */
class StressSource
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
     * @ORM\ManyToOne(targetEntity="StressLog", inversedBy="sources")
     * @ORM\JoinColumn(name="stress_log_id", referencedColumnName="id")
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
     * @return StressSource
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
     * @return StressSource
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
