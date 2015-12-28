<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Test\DoctrineTraits;
use AppBundle\Test\Fixture\LoadStressLogData;
use AppBundle\Test\Fixture\LoadUserData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StressLogControllerTest extends WebTestCase
{
    use DoctrineTraits;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->setUpDoctrine();

        $this->loader->addFixture(new LoadUserData());
        $this->loadFixtures();
    }

    /**
     * @dataProvider urlProvider
     */
    public function testPageLoadIsSuccessful($url)
    {
        $client = self::createClient(array(), array(
            'PHP_AUTH_USER' => 'testuser',
            'PHP_AUTH_PW'   => 'testpass',
        ));

        $client->request('GET', $url);


        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        return array(
            array('/new'),
            array('/log'),
        );
    }

    public function testCreateStressLog()
    {
        $client = self::createClient(array(), array(
            'PHP_AUTH_USER' => 'testuser',
            'PHP_AUTH_PW'   => 'testpass',
        ));

        // Request the page for making a new log entry
        $crawler = $client->request('GET', '/new');

        // Parse the form out of the result.
        $buttonCrawlerNode = $crawler->selectButton('submit');
        $form = $buttonCrawlerNode->form();

        // Submit the form with its default values,
        // along with a note that we can use to identify it later.
        $note = uniqid('Test new entry');
        $client->submit($form, array('stress_log_form[notes]' => $note));
        $response = $client->getResponse();

        // On success, it redirects to the "show" page.
        $this->assertTrue($response->isRedirect());
        $url = $response->headers->get('location');
        $this->assertRegExp('/^\/log\/\d+$/', $url);

        $client->followRedirect();

        // Ensure that the log entry contains the unique note string.
        $this->assertContains($note, $client->getResponse()->getContent());
    }
}
