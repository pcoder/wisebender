<?php

namespace Ace\OwnerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Ace\OwnerBundle\Controller\DefaultController;

class DefaultControllerTest extends WebTestCase
{
	public function testNothing()
	{
		new DefaultController();
		$this->assertTrue(true);
	}
}
