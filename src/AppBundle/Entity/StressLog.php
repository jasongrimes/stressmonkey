<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * StressLog
 *
 * @ORM\Table(name="stress_log")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StressLogRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class StressLog
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="datetime")
     */
    private $time;

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="smallint")
     */
    private $level;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="StressSource", mappedBy="stressLog", fetch="EAGER", cascade={"all"}, orphanRemoval=true)
     */
    private $sources;

    /**
     * @ORM\OneToMany(targetEntity="StressManifestation", mappedBy="stressLog", fetch="EAGER", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $manifestations;

    public function __construct()
    {
        $this->sources = new ArrayCollection();
        $this->manifestations = new ArrayCollection();
    }


    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

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
     * Set time
     *
     * @param \DateTime $time
     *
     * @return StressLog
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return StressLog
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return StressLog
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return StressLog
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
     * Add source
     *
     * @param \AppBundle\Entity\StressSource $source
     *
     * @return StressLog
     */
    public function addSource(\AppBundle\Entity\StressSource $source)
    {
        $this->sources->add($source);

        return $this;
    }

    /**
     * Remove source
     *
     * @param \AppBundle\Entity\StressSource $source
     */
    public function removeSource(\AppBundle\Entity\StressSource $source)
    {
        $this->sources->removeElement($source);
        $source->setStressLog(null);
    }

    /**
     * Get sources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * Update manifestations to match the given list of texts.
     *
     * @param array $texts
     */
    public function setManifestationTexts(array $texts)
    {
        // Remove tags that are no longer wanted.
        foreach ($this->manifestations as $i => $tag) { /** @var StressManifestation $tag */
            // Note that orphanRemoval=true in the annotation mapping tells Doctrine
            // to automatically remove these entities on flush().
            if (!in_array($tag->getText(), $texts)) {
                $this->manifestations->remove($i);
                $tag->setStressLog(null);
            }
        }

        // Add any new tags.
        foreach ($texts as $text) {
            // This check is not really necessary here, but it makes the code clearer.
            if ($this->hasManifestationText($text)) {
                continue;
            }

            $this->addManifestationText($text);
        }
    }

    /**
     * Add a new manifestation for the given text, if one does not already exist.
     *
     * @param string $text
     * @return bool True if manifestation was added, false if it already existed.
     */
    public function addManifestationText($text)
    {
        if ($this->hasManifestationText($text)) {
            return false;
        }

        // Note that cascade={"persist"} in the annotation mapping tells Doctrine
        // to automatically persist these new entities on flush().
        $tag = new StressManifestation();
        $tag->setText($text);
        $this->addManifestation($tag);

        return true;
    }

    /**
     * Test whether the log has a manifestation with the given text.
     *
     * @param string $text
     * @return bool
     */
    public function hasManifestationText($text)
    {
        return $this->manifestations->exists(
            function($k, $v) use ($text) {
                return $v->getText() == $text;
            }
        );
    }

    /**
     * Get an array of manifestation texts.
     */
    public function getManifestationTexts()
    {
        $texts = array();

        foreach ($this->manifestations as $tag) {
            $texts[] = $tag->getText();
        }

        return $texts;
    }

    /**
     * Add manifestation
     *
     * @param \AppBundle\Entity\StressManifestation $manifestation
     *
     * @return StressLog
     */
    public function addManifestation(\AppBundle\Entity\StressManifestation $manifestation)
    {
        $this->manifestations->add($manifestation);
        $manifestation->setStressLog($this);

        return $this;
    }

    /**
     * Remove manifestation
     *
     * @param \AppBundle\Entity\StressManifestation $manifestation
     */
    public function removeManifestation(\AppBundle\Entity\StressManifestation $manifestation)
    {
        $this->manifestations->removeElement($manifestation);
        $manifestation->setStressLog(null);
    }

    /**
     * Get manifestations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getManifestations()
    {
        return $this->manifestations;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return StressLog
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

}
