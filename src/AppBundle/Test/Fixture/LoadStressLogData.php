<?php

namespace AppBundle\Test\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\StressLog;

class LoadStressLogData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $log = new StressLog();
        $log->setTime(new \DateTime());
        $log->setLevel(6);
        $log->setUser($this->getReference('test-user'));
        // $this->addReference('stress-log-1', $log);
        $manager->persist($log);

        $log = new StressLog();
        $log->setTime(new \DateTime());
        $log->setLevel(3);
        $log->setUser($this->getReference('test-user'));
        // $this->addReference('stress-log-1', $log);
        $manager->persist($log);

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}
