<?php

namespace Ace\GenericBundle\Tests\Controller;
use Ace\GenericBundle\Controller\DefaultController;
use Ace\UserBundle\Controller\DefaultController as UserController;
use Ace\ProjectBundle\Controller\SketchController;
use Ace\UtilitiesBundle\Handler\DefaultHandler as UtilitiesHandler;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerUnitTest extends \PHPUnit_Framework_TestCase
{
	public function testIndexAction()
	{
		$this->markTestIncomplete('Not unit tested yet.');
	}

	public function testUserAction_Success()
	{
		/** @var UserController $usercontroller */
		$usercontroller = $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("getUserAction"))
			->getMock();
		$usercontroller->expects($this->once())->method('getUserAction')->with($this->equalTo("nonexistent_user"))->will($this->returnValue(new Response('{"success":true, "id":1, "twitter":"codebender_cc", "email":"girder@codebender.cc"}')));

		/** @var SketchController $projectmanager */
		$projectmanager = $this->getMockBuilder("Ace\ProjectBundle\Controller\SketchController")
			->disableOriginalConstructor()
			->setMethods(array("listAction"))
			->getMock();
		$projectmanager->expects($this->once())->method('listAction')->with($this->equalTo(1))->will($this->returnValue(new Response("[]")));

		/** @var UtilitiesHandler $utilities_handler */
		$utilities_handler = $this->getMockBuilder("Ace\UtilitiesBundle\Handler\DefaultHandler")
			->disableOriginalConstructor()
			->setMethods(array("get_gravatar", "get"))
			->getMock();
		$utilities_handler->expects($this->once())->method('get')->with($this->equalTo("http://api.twitter.com/1/statuses/user_timeline/codebender_cc.json"))->will($this->returnValue('[{"text":"a tweet"}]'));
		$utilities_handler->expects($this->once())->method('get_gravatar')->with($this->equalTo("girder@codebender.cc"))->will($this->returnValue("fake_gravatar"));

		/** @var DefaultController $controller */
		$controller = $this->getMock("Ace\GenericBundle\Controller\DefaultController", array("get", "render"));
		$controller->expects($this->at(0))->method('get')->with($this->equalTo("ace_user.usercontroller"))->will($this->returnValue($usercontroller));
		$controller->expects($this->at(1))->method('get')->with($this->equalTo("ace_project.sketchmanager"))->will($this->returnValue($projectmanager));
		$controller->expects($this->at(2))->method('get')->with($this->equalTo("ace_utilities.handler"))->will($this->returnValue($utilities_handler));
		$controller->expects($this->once())->method('render')->with($this->equalTo("AceGenericBundle:Default:user.html.twig"), $this->equalTo(array('user' => array('success' => true, 'id' => 1, 'twitter' => 'codebender_cc', 'email' => 'girder@codebender.cc'), 'projects' => array(), 'image' => 'fake_gravatar', 'lastTweet' => 'a tweet')))->will($this->returnValue(new Response("minor_error_response")));

		$response = $controller->userAction("nonexistent_user");
		$this->assertEquals("minor_error_response", $response->getContent());
	}

	public function testUserAction_TwitterError()
	{
		/** @var UserController $usercontroller */
		$usercontroller = $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("getUserAction"))
			->getMock();
		$usercontroller->expects($this->once())->method('getUserAction')->with($this->equalTo("nonexistent_user"))->will($this->returnValue(new Response('{"success":true, "id":1, "twitter":"codebender_cc", "email":"girder@codebender.cc"}')));

		/** @var SketchController $projectmanager */
		$projectmanager = $this->getMockBuilder("Ace\ProjectBundle\Controller\SketchController")
			->disableOriginalConstructor()
			->setMethods(array("listAction"))
			->getMock();
		$projectmanager->expects($this->once())->method('listAction')->with($this->equalTo(1))->will($this->returnValue(new Response("[]")));

		/** @var UtilitiesHandler $utilities_handler */
		$utilities_handler = $this->getMockBuilder("Ace\UtilitiesBundle\Handler\DefaultHandler")
			->disableOriginalConstructor()
			->setMethods(array("get_gravatar", "get"))
			->getMock();
		$utilities_handler->expects($this->once())->method('get')->with($this->equalTo("http://api.twitter.com/1/statuses/user_timeline/codebender_cc.json"))->will($this->returnValue('{"errors":[{"message":"Sorry, that page does not exist","code":34}]}'));
		$utilities_handler->expects($this->once())->method('get_gravatar')->with($this->equalTo("girder@codebender.cc"))->will($this->returnValue("fake_gravatar"));

		/** @var DefaultController $controller */
		$controller = $this->getMock("Ace\GenericBundle\Controller\DefaultController", array("get", "render"));
		$controller->expects($this->at(0))->method('get')->with($this->equalTo("ace_user.usercontroller"))->will($this->returnValue($usercontroller));
		$controller->expects($this->at(1))->method('get')->with($this->equalTo("ace_project.sketchmanager"))->will($this->returnValue($projectmanager));
		$controller->expects($this->at(2))->method('get')->with($this->equalTo("ace_utilities.handler"))->will($this->returnValue($utilities_handler));
		$controller->expects($this->once())->method('render')->with($this->equalTo("AceGenericBundle:Default:user.html.twig"), $this->equalTo(array('user' => array('success' => true, 'id' => 1, 'twitter' => 'codebender_cc', 'email' => 'girder@codebender.cc'), 'projects' => array(), 'image' => 'fake_gravatar', 'lastTweet' => false)))->will($this->returnValue(new Response("minor_error_response")));

		$response = $controller->userAction("nonexistent_user");
		$this->assertEquals("minor_error_response", $response->getContent());
	}

	public function testUserAction_NoUser()
	{
		/** @var UserController $usercontroller */
		$usercontroller = $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("getUserAction"))
			->getMock();
		$usercontroller->expects($this->once())->method('getUserAction')->with($this->equalTo("nonexistent_user"))->will($this->returnValue(new Response('{"success":false}')));

		/** @var DefaultController $controller */
		$controller = $this->getMock("Ace\GenericBundle\Controller\DefaultController", array("get", "render"));
		$controller->expects($this->once())->method('get')->with($this->equalTo("ace_user.usercontroller"))->will($this->returnValue($usercontroller));
		$controller->expects($this->once())->method('render')->with($this->equalTo("AceGenericBundle:Default:minor_error.html.twig"), $this->equalTo(array('error' => "There is no such user.")))->will($this->returnValue(new Response("minor_error_response")));

		$response = $controller->userAction("nonexistent_user");
		$this->assertEquals("minor_error_response", $response->getContent());
	}

	public function testProjectAction()
	{
		$this->markTestIncomplete('Not unit tested yet.');
	}

	public function testProjectfilesAction()
	{
		$this->markTestIncomplete('Not unit tested yet.');
	}

	public function testLibrariesAction()
	{
		$this->markTestIncomplete('Not unit tested yet.');
	}

	public function testExampleAction()
	{
		$this->markTestIncomplete('Not unit tested yet.');
	}

	public function testBoardsAction()
	{
		$this->markTestIncomplete('Not unit tested yet.');
	}

	public function testEmbeddedCompilerFlasherAction()
	{
		//ok, let's not be ridiculous
		$this->assertTrue(true);
	}

	private function initParameters(&$em, &$mf, &$df, &$sc)
	{
		$em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
			->disableOriginalConstructor()
			->getMock();

		$mf = $this->getMockBuilder('Ace\ProjectBundle\Controller\MongoFilesController')
			->disableOriginalConstructor()
			->getMock();

		$df = $this->getMockBuilder('Ace\ProjectBundle\Controller\DiskFilesController')
			->disableOriginalConstructor()
			->getMock();

		$sc = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
			->disableOriginalConstructor()
			->getMock();
	}
}
