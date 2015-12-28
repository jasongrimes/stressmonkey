<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * StressLog
 *
 * @ORM\Table(name="stress_logs")
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
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id") *
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     *
     * @ORM\Column(name="local_time", type="datetime")
     */
    private $localtime;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="timezone", type="string", length=50)
     */
    private $timezone;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\Type("digit")
     * @Assert\Range(min=0, max=10)
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
     * @ORM\OneToMany(targetEntity="StressLogFactor", mappedBy="stressLog", fetch="EAGER", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $factors;

    public function __construct()
    {
        $this->factors = new ArrayCollection();
    }

    /**
     * Factory method to create a new instance with useful defaults.
     *
     * @param User $user
     * @return StressLog
     */
    public static function create(User $user)
    {
        $log = new static;
        $log->setUser($user);
        $log->setTimezone($user->getTimezone() ?: 'UTC');
        $log->setLocaltime(new \DateTime(null, new \DateTimeZone($log->getTimezone())));
        $log->setLevel(5);

        return $log;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
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
        return (int) $this->level;
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
    public function setCreatedAt(\DateTime $createdAt)
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
     * Update factors to match the given list of texts.
     *
     * @param array $texts
     */
    public function setFactorTexts(array $texts)
    {
        // Remove tags that are no longer wanted.
        foreach ($this->factors as $i => $tag) { /** @var StressLogFactor $tag */
            // Note that orphanRemoval=true in the annotation mapping tells Doctrine
            // to automatically remove these entities on flush().
            if (!in_array($tag->getText(), $texts)) {
                $this->factors->remove($i);
                $tag->setStressLog(null);
            }
        }

        // Add any new tags.
        foreach ($texts as $text) {
            // This check is not really necessary here, but it makes the code clearer.
            if ($this->hasFactorText($text)) {
                continue;
            }

            $this->addFactorText($text);
        }
    }

    /**
     * Add a new factor for the given text, if one does not already exist.
     *
     * @param string $text
     * @return bool True if factor was added, false if it already existed.
     */
    public function addFactorText($text)
    {
        if (empty($text)) {
            return false;
        }

        if ($this->hasFactorText($text)) {
            return false;
        }

        // Note that cascade={"persist"} in the annotation mapping tells Doctrine
        // to automatically persist these new entities on flush().
        $tag = new StressLogFactor();
        $tag->setText(trim($text));
        $this->addFactor($tag);

        return true;
    }

    /**
     * Test whether the log has a factor with the given text.
     *
     * @param string $text
     * @return bool
     */
    public function hasFactorText($text)
    {
        return $this->factors->exists(
            function($k, $v) use ($text) {
                return $v->getText() == $text;
            }
        );
    }

    /**
     * Get an array of factor texts.
     *
     * @return array
     */
    public function getFactorTexts()
    {
        $texts = array();

        foreach ($this->factors as $tag) {
            $texts[] = $tag->getText();
        }

        return $texts;
    }

    /**
     * Add factor.
     *
     * @param StressLogFactor $factor
     * @return $this
     */
    public function addFactor(StressLogFactor $factor)
    {
        $this->factors->add($factor);
        $factor->setStressLog($this);

        return $this;
    }

    /**
     * Remove factor
     *
     * @param \AppBundle\Entity\StressLogFactor $factor
     */
    public function removeFactor(StressLogFactor $factor)
    {
        $this->factors->removeElement($factor);
        $factor->setStressLog(null);
    }

    /**
     * Get factors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFactors()
    {
        return $this->factors;
    }

    /**
     * Get factor texts as a comma-separated string.
     *
     * @return string
     */
    public function getFactorString()
    {
        return implode(', ', $this->getFactorTexts());
    }

    /**
     * Set factor texts as a comma-separated string.
     *
     * @param string $str
     */
    public function setFactorString($str)
    {
        $this->setFactorTexts(array_map('trim', explode(',', $str)));
    }

    /**
     * Set the local time of the log entry.
     *
     * Note that only the "clock time" is used; the timezone is ignored.
     * The timezone is assumed to be $this->timezone.
     *
     * @param \DateTime $localtime
     */
    public function setLocaltime(\DateTime $localtime)
    {
        $this->localtime = $localtime;
    }

    /**
     * @return \DateTime
     */
    public function getLocaltime()
    {
        return new \DateTime($this->localtime->format('Y-m-d H:i:s'), new \DateTimeZone($this->timezone));
    }

    /**
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;

    }

    /**
     * @return \DateTime
     */
    public function getTimeUtc()
    {
        $dt = $this->getLocaltime();
        $dt->setTimezone(new \DateTimeZone('UTC'));

        return $dt;
    }
}
