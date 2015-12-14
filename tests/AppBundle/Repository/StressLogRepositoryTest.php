<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Entity\StressLog;
use AppBundle\Entity\User;
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

    public function testSavingManifestations()
    {
        $texts = array('one', 'two', 'three');

        $log = new StressLog();
        $log->setTime(new \DateTime());
        $log->setUser($this->user);
        $log->setLevel(5);
        $log->setManifestationTexts($texts);

        $this->em->persist($log);
        $this->em->flush();
        $this->em->clear();

        $log2 = $this->em->find('AppBundle:StressLog', $log->getId()); /** @var StressLog $log2 */
        $this->assertEquals($texts, $log2->getManifestationTexts());

        // Add one and delete one.
        $texts2 = array('two', 'three', 'four');
        $log2->setManifestationTexts($texts2);
        $this->em->flush();
        $this->em->clear();

        $log3 = $this->em->find('AppBundle:StressLog', $log->getId()); /** @var StressLog $log3 */
        $this->assertEquals($texts2, $log3->getManifestationTexts());

        // Ensure that the "one" manifestation was really removed from the database,
        // (instead of just getting a null stress_log_id).
        $manifestations = $this->em
            ->getRepository('AppBundle:StressManifestation')
            ->findBy(array('text' => 'one'));

        $this->assertEmpty($manifestations);
    }
}