<?php

namespace Ace\SearchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerFunctionalTest extends WebTestCase
{
	public function testFindAction_None()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/search/find/?query=nonexistent');

		$this->assertEquals(1, $crawler->filter('html:contains("No results found :(")')->count());
	}

	public function testFindAction_UserExists()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/search/find/?query=tester');

		$this->assertEquals(1, $crawler->filter('h2:contains("Users")')->count());
		$this->assertEquals(0, $crawler->filter('h2:contains("Projects")')->count());
		$this->assertEquals(1, $crawler->filter('html:contains("myfirstname mylastname")')->count());
	}

	public function testFindAction_ProjectExists()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/search/find/?query=test_project');

		$this->assertEquals(0, $crawler->filter('h2:contains("Users")')->count());
		$this->assertEquals(1, $crawler->filter('h2:contains("Projects")')->count());
		$this->assertEquals(1, $crawler->filter('html:contains("tester")')->count());
	}

	public function testFindAction_BothExist()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/search/find/?query=test');

		$this->assertEquals(1, $crawler->filter('h2:contains("Users")')->count());
		$this->assertEquals(1, $crawler->filter('h2:contains("Projects")')->count());
		$this->assertEquals(1, $crawler->filter('html:contains("tester")')->count());
		$this->assertEquals(1, $crawler->filter('html:contains("myfirstname mylastname")')->count());
	}
}
