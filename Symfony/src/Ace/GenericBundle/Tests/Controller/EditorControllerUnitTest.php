<?php

namespace Ace\GenericBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Ace\GenericBundle\Controller\EditorController;
use Ace\UtilitiesBundle\Controller\BoardController;

class EditorControllerUnitTest extends \PHPUnit_Framework_TestCase
{
	public function testEditAction_NoPerms()
	{
		$this->initParameters($em, $mf, $df, $sc);
		/** @var SketchController $projectmanager */
		$projectmanager = $this->getMock("Ace\ProjectBundle\Controller\SketchController", array("checkWriteProjectPermissionsAction"), array($em, $mf, $df, $sc, "disk"));
		$projectmanager->expects($this->once())->method('checkWriteProjectPermissionsAction')->with($this->equalTo(1))->will($this->returnValue(new Response('{"success":false}')));

		/** @var EditorController $controller */
		$controller = $this->getMock("Ace\GenericBundle\Controller\EditorController", array("get", "forward"));
		$controller->expects($this->once())->method('get')->with($this->equalTo("ace_project.sketchmanager"))->will($this->returnValue($projectmanager));
		$controller->expects($this->once())->method('forward')->with($this->equalTo("AceGenericBundle:Default:project"), $this->equalTo(array("id" => 1)))->will($this->returnValue(new Response("forwarded_response")));

		$response = $controller->editAction(1);
		$this->assertEquals($response->getContent(), "forwarded_response");
	}

	public function testEditAction_Success()
	{
		$this->initParameters($em, $mf, $df, $sc);
		/** @var SketchController $projectmanager */
		$projectmanager = $this->getMock("Ace\ProjectBundle\Controller\SketchController", array("checkWriteProjectPermissionsAction", "getNameAction", "getPrivacyAction", "listFilesAction"), array($em, $mf, $df, $sc, "disk"));
		$projectmanager->expects($this->once())->method('checkWriteProjectPermissionsAction')->with($this->equalTo(1))->will($this->returnValue(new Response('{"success":true}')));
		$projectmanager->expects($this->once())->method('getNameAction')->with($this->equalTo(1))->will($this->returnValue(new Response('{"success":true, "response": "test_project"}')));
		$projectmanager->expects($this->once())->method('getPrivacyAction')->with($this->equalTo(1))->will($this->returnValue(new Response('{"success":true, "response": true}')));
		$projectmanager->expects($this->once())->method('listFilesAction')->with($this->equalTo(1))->will($this->returnValue(new Response('{"success":true, "list":[{"filename":"test_project.ino", "code":"nothing"}]}')));

		/** @var BoardController $projectmanager */
		$boardcontroller = $this->getMock("Ace\UtilitiesBundle\Controller\BoardController", array("listAction"), array($em));
		$boardcontroller->expects($this->once())->method('listAction')->will($this->returnValue(new Response('fake_boards_list')));

		/** @var EditorController $controller */
		$controller = $this->getMock("Ace\GenericBundle\Controller\EditorController", array("get", "forward", "render"));
		$controller->expects($this->at(0))->method('get')->with($this->equalTo("ace_project.sketchmanager"))->will($this->returnValue($projectmanager));
		$controller->expects($this->at(1))->method('get')->with($this->equalTo("ace_board.defaultcontroller"))->will($this->returnValue($boardcontroller));
		$controller->expects($this->once())->method('render')->with($this->equalTo('AceGenericBundle:Editor:editor.html.twig'), $this->equalTo(array('project_id' => 1, 'project_name' => "test_project", 'files' => array(array("filename" => "test_project.ino", "code" => "nothing")), 'boards' => "fake_boards_list", 'is_public' => true)))->will($this->returnValue(new Response("excellent")));

		$response = $controller->editAction(1);
		$this->assertEquals($response->getContent(), "excellent");
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
