<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Entity\StressLog;
use AppBundle\Entity\User;
use AppBundle\Repository\StressLogRepository;
use AppBundle\Test\DoctrineTraits;
use AppBundle\Test\Fixture\LoadUserData;
use AppBundle\Test\Fixture\LoadStressLogData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StressLogRepositoryTest extends KernelTestCase
{
    use DoctrineTraits;

    /** @var  User */
    protected $user;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->setUpDoctrine();

        $this->loader->addFixture(new LoadUserData());
        $this->loader->addFixture(new LoadStressLogData());
        $this->loadFixtures();

        $this->user = $this->em
            ->getRepository('AppBundle:User')
            ->findOneBy(array('email' => 'test@example.com'));
    }

    public function testFindByUser()
    {
        $logs = $this->em
            ->getRepository('AppBundle:StressLog')
            ->findBy(array('user' => $this->user));

        $this->assertCount(2, $logs);
    }

    public function testSavingFactors()
    {
        $texts = array('one', 'two', 'three');

        $log = StressLog::create($this->user);
        $log->setLevel(5);
        $log->setFactorTexts($texts);

        $this->em->persist($log);
        $this->em->flush();
        $this->em->clear();

        $log2 = $this->em->find('AppBundle:StressLog', $log->getId()); /** @var StressLog $log2 */
        $this->assertEquals($texts, $log2->getFactorTexts());

        // Add one and delete one.
        $texts2 = array('two', 'three', 'four');
        $log2->setFactorTexts($texts2);
        $this->em->flush();
        $this->em->clear();

        $log3 = $this->em->find('AppBundle:StressLog', $log->getId()); /** @var StressLog $log3 */
        $this->assertEquals($texts2, $log3->getFactorTexts());

        // Ensure that the "one" manifestation was really removed from the database,
        // (instead of just getting a null stress_log_id).
        $manifestations = $this->em
            ->getRepository('AppBundle:StressLogFactor')
            ->findBy(array('text' => 'one'));

        $this->assertEmpty($manifestations);
    }

    public function testReorderObjectsById()
    {
        /** @var StressLogRepository $repo */
        $repo = $this->em->getRepository('AppBundle:StressLog');

        $obs = array();
        foreach(range(1, 5) as $i) {
            $obs[]= new TestClassWithGetId($i);
        }

        $newOrder = array(3, 2, 1, 4, 5);
        $orderedObs = $repo->reorderObjectsById($obs, $newOrder);
        foreach($newOrder as $i => $id) {
            $this->assertEquals($id, $orderedObs[$i]->getId());
        }

        // When the ID sort order doesn't include all IDs,
        // the objects with the missing IDs are appended to the end.
        $newOrder = array(2, 3, 4, 5);
        $orderedObs = $repo->reorderObjectsById($obs, $newOrder);
        $this->assertCount(5, $orderedObs);
        $lastOrderedOb = array_pop($orderedObs);
        $this->assertEquals(1, $lastOrderedOb->getId());

        // When the ID sort order includes an ID that is not in the list of objects,
        // the extra ID is ignored.
        $newOrder = array(6, 5, 4, 3, 2, 1);
        $orderedObs = $repo->reorderObjectsById($obs, $newOrder);
        $this->assertCount(5, $orderedObs);
        foreach ($orderedObs as $ob) {
            $this->assertNotEquals(6, $ob->getId());
        }
    }

    public function testFindByFactors()
    {
        /** @var StressLogRepository $repo */
        $repo = $this->em->getRepository('AppBundle:StressLog');

        // Confirm there are pre-existing logs.
        $logs = $repo->findFiltered($this->user);
        $this->assertGreaterThan(0, count($logs));

        // Add test fixtures.
        $log1 = StressLog::create($this->user);
        $log1->setFactorTexts(array('one', 'two'));
        $this->em->persist($log1);
        $this->em->flush();

        // Add test fixtures.
        $log2 = StressLog::create($this->user);
        $log2->setFactorTexts(array('two', 'three'));
        $this->em->persist($log2);
        $this->em->flush();

        // Search with "or" operation.
        $logs = $repo->findFiltered($this->user, array('factors' => 'one,two', 'factorOp' => 'or'));
        $this->assertEquals(2, count($logs));

        $logs = $repo->findFiltered($this->user, array('factors' => 'one,three', 'factorOp' => 'or'));
        $this->assertEquals(2, count($logs));

        $logs = $repo->findFiltered($this->user, array('factors' => 'one,five', 'factorOp' => 'or'));
        $this->assertEquals(1, count($logs));

        // Search with "and" operation.
        $logs = $repo->findFiltered($this->user, array('factors' => 'one,two', 'factorOp' => 'and'));
        $this->assertEquals(1, count($logs));
        $this->assertEquals($log1->getId(), reset($logs)->getId());

        $logs = $repo->findFiltered($this->user, array('factors' => 'one,three', 'factorOp' => 'and'));
        $this->assertEquals(0, count($logs));
    }

    public function testFindByTime()
    {
        /** @var StressLogRepository $repo */
        $repo = $this->em->getRepository('AppBundle:StressLog');

        // Add test fixtures.
        $log1 = StressLog::create($this->user);
        $log1->setLocaltime(new \DateTime('2001-02-03 04:05:06'));
        $this->em->persist($log1);

        $log2 = StressLog::create($this->user);
        $log2->setLocaltime(new \DateTime('2002-02-03 04:05:06'));
        $this->em->persist($log2);
        $this->em->flush();

        $logs = $repo->findFiltered($this->user, array(
            'from' => new \DateTime('2002-01-01 00:00:00'),
            'to' => new \DateTime('2002-12-31 00:00:00')));
        $this->assertEquals(1, count($logs));
        $this->assertEquals($logs[0]->getId(), $log2->getId());

        $logs = $repo->findFiltered($this->user, array(
            'to' => new \DateTime('2001-12-31 00:00:00')));
        $this->assertEquals(1, count($logs));
        $this->assertEquals($logs[0]->getId(), $log1->getId());

    }
}

class TestClassWithGetId
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}