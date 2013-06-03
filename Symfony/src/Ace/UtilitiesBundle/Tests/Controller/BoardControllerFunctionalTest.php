<?php

namespace Ace\UtilitiesBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BoardControllerFunctionalTest extends WebTestCase
{
	public function testListBoards()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/utilities/listboards');

		$this->assertEquals(1, $crawler->filter('html:contains("Arduino Uno")')->count());

		$response = json_decode($client->getResponse()->getContent(), true);
		$this->assertGreaterThan(0, count($response));
	}

}
