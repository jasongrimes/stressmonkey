<?php

namespace AppBundle\Test;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\Container;

trait DoctrineTraits
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var EntityManager
     */
    protected $em;

    /** @var Loader */
    protected $loader;

    /** @var  Container */
    protected $container;

    /**
     */
    protected function setUpDoctrine()
    {
        static::bootKernel();
        $this->container = static::$kernel->getContainer();

        $this->doctrine = $this->createDoctrineRegistry();
        $this->em = $this->doctrine->getManager();
        $this->loader = new Loader();
    }

    /**
     * @after
     */
    protected function tearDownDoctrine()
    {
        $this->doctrine = null;
        if ($this->em) {
            $this->em->close();
            $this->em = null;
        }
        $this->loader = null;
        $this->container = null;
    }

    protected function loadFixtures($truncate = true)
    {
        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($this->loader->getFixtures(), !$truncate);
    }

    /**
     *
    protected function createSchema()
    {
        if ($metadata = $this->getMetadata()) {
            $schemaTool = new SchemaTool($this->em);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }
     */

    /**
     * Returns all metadata by default.
     *
     * Override to only build selected metadata.
     * Return an empty array to prevent building the schema.
     *
     * @return array
     */
    protected function getMetadata()
    {
        return $this->em->getMetadataFactory()->getAllMetadata();
    }

    /**
     * Override to build doctrine registry yourself.
     *
     * By default a Symfony container is used to create it. It requires the KernelTestCase
     *
     * @return ManagerRegistry
     */
    protected function createDoctrineRegistry()
    {
        return $this->container->get('doctrine');
    }
}