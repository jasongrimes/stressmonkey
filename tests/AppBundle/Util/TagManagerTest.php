<?php

namespace Tests\AppBundle\Util;

use AppBundle\Entity\StressLog;
use AppBundle\Entity\User;
use AppBundle\Test\DoctrineTraits;
use AppBundle\Test\Fixture\LoadUserData;
use AppBundle\Util\TagManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TagManagerTest extends KernelTestCase
{
    use DoctrineTraits;

    /** @var  User */
    protected $user;

    /** @var TagManager */
    protected $tagManager;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->setUpDoctrine();

        $this->loader->addFixture(new LoadUserData());
        $this->loadFixtures();

        $this->user = $this->em
            ->getRepository('AppBundle:User')
            ->findOneBy(array('email' => 'test@example.com'));

        $this->tagManager = new TagManager($this->em);
    }

    protected function generateTags(array $data)
    {
        foreach ($data as $logData) {
            $level = array_key_exists('level', $logData) ? $logData['level'] : null;
            $time = array_key_exists('time', $logData) ? $logData['time'] : null;
            $log = $this->createLog($logData['texts'], $level, $time);
            $this->em->persist($log);
        }

        $this->em->flush();
    }

    protected function createLog(array $texts, $level = null, \DateTime $time = null)
    {
        if ($level === null) {
            $level = 5;
        }

        if ($time === null) {
            $time = new \DateTime();
        }

        $log = StressLog::create($this->user);
        $log->setFactorTexts($texts);
        $log->setLevel($level);
        $log->setCreatedAt($time);

        return $log;
    }

    public function testGetUserTags()
    {
        $this->generateTags(array(
            array('texts' => array('alpha', 'bravo')),
            array('texts' => array('alpha', 'charlie', 'delta')),
            array('texts' => array('alpha', 'delta')),
        ));

        $tags = $this->tagManager->getTextsByUser($this->user);

        $this->assertEquals(array('alpha', 'bravo', 'charlie', 'delta'), $tags);
    }

    public function testGetSuggestions()
    {
        //
        // Set up tags with the following level/recency/frequency:
        //
        // low level:
        //     bravo (now, 2)
        //     alpha (now, 1)
        //     delta (yesterday, 2)
        //     charlie (yesterday, 1)
        //     echo (last week, 3)
        // high level:
        //     yankee (now, 2)
        //     zebra (now, 1)
        //     x-ray (yesterday, 2)
        //     whiskey (yesterday, 1)
        //     victor (last week, 3)
        //
        $this->generateTags(array(
            array('level' => 0, 'texts' => array('alpha', 'bravo'), 'time' => new \DateTime()),
            array('level' => 1, 'texts' => array('bravo'), 'time' => new \DateTime()),
            array('level' => 2, 'texts' => array('delta', 'charlie'), 'time' => new \DateTime('yesterday')),
            array('level' => 3, 'texts' => array('delta'), 'time' => new \DateTime('2 days ago')),
            array('level' => 4, 'texts' => array('echo'), 'time' => new \DateTime('last week')),
            array('level' => 0, 'texts' => array('echo'), 'time' => new \DateTime('last week')),
            array('level' => 1, 'texts' => array('echo'), 'time' => new \DateTime('last week')),

            array('level' => 5, 'texts' => array('zebra', 'yankee'), 'time' => new \DateTime()),
            array('level' => 6, 'texts' => array('yankee'), 'time' => new \DateTime()),
            array('level' => 7, 'texts' => array('x-ray', 'whiskey'), 'time' => new \DateTime('yesterday')),
            array('level' => 8, 'texts' => array('x-ray'), 'time' => new \DateTime('2 days ago')),
            array('level' => 9, 'texts' => array('victor'), 'time' => new \DateTime('last week')),
            array('level' => 10, 'texts' => array('victor'), 'time' => new \DateTime('last week')),
            array('level' => 9, 'texts' => array('victor'), 'time' => new \DateTime('last week')),
        ));


        $suggestions = $this->tagManager->getSuggestions($this->user, array('limit' => 5));
        $this->assertCount(2, $suggestions);

        $expectedLow = array('bravo', 'alpha', 'delta', 'charlie', 'echo');
        $this->assertEquals($expectedLow, $suggestions['low']);

        $expectedHigh = array('yankee', 'zebra', 'x-ray', 'whiskey', 'victor');
        $this->assertEquals($expectedHigh, $suggestions['high']);

        // When too few previous tags exist,
        // suggestions should be padded out with defaults.
        $suggestions = $this->tagManager->getSuggestions($this->user, array('limit' => 10));
        $this->assertCount(10, $suggestions['high']);
        $this->assertCount(10, $suggestions['low']);
    }
}
