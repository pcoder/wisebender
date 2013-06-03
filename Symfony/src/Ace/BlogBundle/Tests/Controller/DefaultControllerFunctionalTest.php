<?php

namespace Ace\BlogBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends WebTestCase
{
	public function testBlogAction_Generic() // Test wether each page has 5 posts or less.
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/blog');

		$posts = $crawler->filter('.post')->children()->count();
		$this->assertLessThanOrEqual(5, $posts);
	}

	public function testBlogAction_Admin() // Test new blog post
	{
		$client = static::createClient(array(), array(
			'PHP_AUTH_USER' => 'tester',
			'PHP_AUTH_PW' => 'testerPASS',
		));

		$crawler = $client->request('GET', '/blog');

		$this->assertEquals(1, $crawler->filter('html:contains("New Post!")')->count());
	}

	public function testBlogAction_UserOrAnonymous() // Test new blog post
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/blog');

		$this->assertEquals(0, $crawler->filter('html:contains("New Post!")')->count());
	}

	public function testRssAction() // Test rss feed
	{
		$client = static::createClient();

		$client->request('GET', '/misc/blog/rss');
		/** @var $response Response */
		$response = $client->getResponse();
		$this->assertTrue($response->headers->contains('Content-Type', 'application/rss+xml'));

		$client->request('GET', '/blog/rss');
		/** @var $response Response */
		$response = $client->getResponse();
		$this->assertTrue($response->headers->contains('Content-Type', 'application/rss+xml'));
	}

	public function testClickPostAction() // Test post title link
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/blog');

		$link = $crawler->filter('#posts')->children()->children()->filter('div > a')->link();
		$crawler = $client->click($link);

		$matcher = array('id' => 'post');
		$this->assertTag($matcher, $client->getResponse());
		$this->assertCount(1, $crawler->filter('#post'));
	}

	public function testNewPostAction()   // Test new blog post
	{
		$client = static::createClient(array(), array(
			'PHP_AUTH_USER' => 'tester',
			'PHP_AUTH_PW' => 'testerPASS',
		));

		$client->followRedirects();
		$crawler = $client->request('GET', '/blog');

		$link = $crawler->selectLink('New Post!')->link();
		$crawler = $client->click($link);

		$buttonCrawlerNode = $crawler->selectButton('New Post!');

		$form = $buttonCrawlerNode->form();

		$title = (string) mt_rand();
		$form['title'] = $title;
		$form['msgpost'] = 'function testing...';

//		print_r($form->getValues());

		$crawler = $client->submit($form);

		$this->assertEquals(1, $crawler->filter('h3:contains("'.$title.'")')->count());
	}

	public function testViewpostAction() // Test post title link
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/blog/viewpost/1');

		$this->assertCount(1, $crawler->filter('#post'));
	}
}
