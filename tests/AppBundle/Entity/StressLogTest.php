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
        $this->log->setLocaltime(new \DateTime());
        $this->log->setLevel(6);
        $this->log->setUser($this->user);
    }

    public function testSettingFactors()
    {
        $this->log->setFactorTexts(array());
        $this->assertEmpty($this->log->getFactors());

        $this->log->setFactorTexts(array('one', 'two'));
        $this->assertCount(2, $this->log->getFactorTexts());
        $this->assertContains('one', $this->log->getFactorTexts());
        $this->assertContains('two', $this->log->getFactorTexts());

        $this->log->setFactorTexts(array('three'));
        $this->assertCount(1, $this->log->getFactorTexts());
        $this->assertContains('three', $this->log->getFactorTexts());
    }

    public function testAddFactorIgnoresDuplicates()
    {
        $this->log->setFactorTexts(array('one'));
        $factor = $this->log->getFactors()->first();

        $result = $this->log->addFactorText('one');
        $this->assertFalse($result);
        $this->assertCount(1, $this->log->getFactors());
        $this->assertSame($factor, $this->log->getFactors()->first());
    }

    public function testSetFactorsIgnoresDuplicates()
    {
        $this->log->setFactorTexts(array('one'));
        $factor_before = $this->log->getFactors()->first();

        $this->log->setFactorTexts(array('one', 'two'));
        $this->assertCount(2, $this->log->getFactors());

        $factor_after = null;
        foreach ($this->log->getFactors() as $factor) {
            if ($factor->getText() == 'one') {
                $factor_after = $factor;
                break;
            }
        }

        // The original factor object is preserved,
        // instead of being overwritten by a new one with the same value.
        $this->assertSame($factor_before, $factor_after);
    }

}
