<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\StressLog;
use AppBundle\Entity\User;

class StressLogTest extends \PHPUnit_Framework_TestCase
{
    /** @var User */
    protected $user;

    /** @var StressLog */
    protected $log;

    public function setUp()
    {
        $this->user = new User();
        $this->user->setUsername('test');
        $this->user->setEmail('test@example.com');
        $this->user->setPassword('test');

        $this->log = new StressLog();
        $this->log->setTime(new \DateTime());
        $this->log->setLevel(6);
        $this->log->setUser($this->user);
    }

    public function testSettingManifestations()
    {
        $this->log->setManifestationTexts(array());
        $this->assertEmpty($this->log->getManifestations());

        $this->log->setManifestationTexts(array('one', 'two'));
        $this->assertCount(2, $this->log->getManifestationTexts());
        $this->assertContains('one', $this->log->getManifestationTexts());
        $this->assertContains('two', $this->log->getManifestationTexts());

        $this->log->setManifestationTexts(array('three'));
        $this->assertCount(1, $this->log->getManifestationTexts());
        $this->assertContains('three', $this->log->getManifestationTexts());
    }

    public function testAddManifestationIgnoresDuplicates()
    {
        $this->log->setManifestationTexts(array('one'));
        $manifestation = $this->log->getManifestations()->first();

        $result = $this->log->addManifestationText('one');
        $this->assertFalse($result);
        $this->assertCount(1, $this->log->getManifestations());
        $this->assertSame($manifestation, $this->log->getManifestations()->first());
    }

    public function testSetManifestationsIgnoresDuplicates()
    {
        $this->log->setManifestationTexts(array('one'));
        $manifestation_before = $this->log->getManifestations()->first();

        $this->log->setManifestationTexts(array('one', 'two'));
        $this->assertCount(2, $this->log->getManifestations());

        $manifestation_after = null;
        foreach ($this->log->getManifestations() as $mani) {
            if ($mani->getText() == 'one') {
                $manifestation_after = $mani;
                break;
            }
        }

        // The original manifestation object is preserved,
        // instead of being overwritten by a new one with the same value.
        $this->assertSame($manifestation_before, $manifestation_after);
    }

}
