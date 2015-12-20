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
        $log = StressLog::create($this->getReference('test-user'));
        $log->setLevel(6);
        $manager->persist($log);

        $log = StressLog::create($this->getReference('test-user'));
        $log->setLevel(3);
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
