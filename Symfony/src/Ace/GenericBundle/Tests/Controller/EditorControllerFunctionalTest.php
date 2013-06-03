<?php

namespace Ace\GenericBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class EditorControllerFunctionalTest extends WebTestCase
{
	public function testEditAction()
	{
		$client = static::createClient(array(), array(
			'PHP_AUTH_USER' => 'tester',
			'PHP_AUTH_PW' => 'testerPASS',
		));

		$crawler = $client->request('GET', '/sketch:1');

		$this->assertEquals(1, $crawler->filter('html:contains("Save")')->count());
		$this->assertEquals(1, $crawler->filter('html:contains("test_project.ino")')->count());

		//TODO: Use selenium to make sure this works fine.
		$this->markTestIncomplete('Use selenium to make sure this works fine.');
	}

	public function testEditAction_PublicProjects() // Test homepage redirection for logged in users
	{
//		$client = static::createClient(array(), array(
//			'PHP_AUTH_USER' => 'tester',
//			'PHP_AUTH_PW' => 'testerPASS',
//		));
//
//		$crawler = $client->request('GET', '/sketch:1');
//
//		$this->assertEquals(1, $crawler->filter('span:contains("Project Type:")')->count());
//
//		$client = static::createClient(array(), array(
//			'PHP_AUTH_USER' => 'testacc',
//			'PHP_AUTH_PW' => 'testaccPWD',
//		));
//
//		$crawler = $client->request('GET', '/');
//
//		$this->assertEquals(0, $crawler->filter('span:contains("Project Type:")')->count());
		//TODO: check embeddable view exists
		$this->markTestIncomplete("Not functional tested yet");
	}

	public function testEditAction_PrivateProjects() // Test homepage redirection for logged in users
	{
//		$client = static::createClient(array(), array(
//			'PHP_AUTH_USER' => 'tester',
//			'PHP_AUTH_PW' => 'testerPASS',
//		));
//
//		$crawler = $client->request('GET', '/sketch:1');
//
//		$this->assertEquals(1, $crawler->filter('span:contains("Project Type:")')->count());
//
//		$client = static::createClient(array(), array(
//			'PHP_AUTH_USER' => 'testacc',
//			'PHP_AUTH_PW' => 'testaccPWD',
//		));
//
//		$crawler = $client->request('GET', '/');
//
//		$this->assertEquals(0, $crawler->filter('span:contains("Project Type:")')->count());
		//TODO: check embeddable view doesn't exist
		$this->markTestIncomplete("Not functional tested yet");
	}


}
