<?php

namespace Ace\SearchBundle\Tests\Controller;

use Ace\SearchBundle\Controller\DefaultController;

class DefaultControllerUnitTest extends \PHPUnit_Framework_TestCase
{
	public function testFindAction_RequestIsNotGET()
	{
		$request = $this->getMock("Symfony\Component\HttpFoundation\Request", array("getMethod"));
		$request->expects($this->once())->method('getMethod')->will($this->returnValue("POST"));

		$controller = $this->getMock("Ace\SearchBundle\Controller\DefaultController", array("getRequest", "redirect", "generateUrl"));
		$controller->expects($this->once())->method('getRequest')->will($this->returnValue($request));
		$controller->expects($this->once())->method('generateUrl')->will($this->returnValue("AceGenericBundle_index"));
		$controller->expects($this->once())->method('redirect')->will($this->returnValue("fake_redirect_url"));
		$this->assertEquals($controller->findAction(), "fake_redirect_url");
	}

	public function testFindAction_UserProjectExist()
	{
		$query = "search_string";

		$response_query = $this->getMock("Symfony\Component\HttpFoundation\ParameterBag", array("get"));
		$response_query->expects($this->once())->method('get')->with($this->equalTo('query'))->will($this->returnValue($query));

		$request = $this->getMock("Symfony\Component\HttpFoundation\Request", array("getMethod"));
		$request->query = $response_query;

		$request->expects($this->once())->method('getMethod')->will($this->returnValue("GET"));

		$uc_response = $this->getMock("Symfony\Component\HttpFoundation\Response", array("getContent"));
		$uc_response->expects($this->once())->method('getContent')->will($this->returnValue('{"1":{"firstname":"search_string","lastname":"alastname","username":"search_string","karma":50}}'));

		$usercontroller = $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("searchAction"))
			->getMock();
		$usercontroller->expects($this->once())->method('searchAction')->with($this->equalTo($query))->will($this->returnValue($uc_response));

		$pm_response = $this->getMock("Symfony\Component\HttpFoundation\Response", array("getContent"));
		$pm_response->expects($this->once())->method('getContent')->will($this->returnValue('{"1":{"name":"search_string","description":"adescription","owner":"anowner"}}'));

		$projectmanager = $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("searchAction"))
			->getMock();
		$projectmanager->expects($this->once())->method('searchAction')->with($this->equalTo($query))->will($this->returnValue($pm_response));

		$controller = $this->getMock("Ace\SearchBundle\Controller\DefaultController", array("get", "getRequest", "render"));

		$controller->expects($this->exactly(2))->method('getRequest')->will($this->returnValue($request));
		$controller->expects($this->at(2))->method('get')->with($this->equalTo('ace_user.usercontroller'))->will($this->returnValue($usercontroller));
		$controller->expects($this->at(3))->method('get')->with($this->equalTo('ace_project.sketchmanager'))->will($this->returnValue($projectmanager));
		$controller->expects($this->once())->method('render')->will($this->returnArgument(1));

		$this->assertEquals($controller->findAction(),
			array("query" => $query,
				"users" => array(1 => array("firstname" => "search_string",
											"lastname" => "alastname",
											"username" => "search_string",
											"karma" => 50)),
				"projects" => array(1 => array("name" => "search_string",
												"description" => "adescription",
												"owner" => "anowner"))
			));
	}

	public function testFindAction_UserExists()
	{
		$query = "search_string";

		$response_query = $this->getMock("Symfony\Component\HttpFoundation\ParameterBag", array("get"));
		$response_query->expects($this->once())->method('get')->with($this->equalTo('query'))->will($this->returnValue($query));

		$request = $this->getMock("Symfony\Component\HttpFoundation\Request", array("getMethod"));
		$request->query = $response_query;

		$request->expects($this->once())->method('getMethod')->will($this->returnValue("GET"));

		$uc_response = $this->getMock("Symfony\Component\HttpFoundation\Response", array("getContent"));
		$uc_response->expects($this->once())->method('getContent')->will($this->returnValue('{"1":{"firstname":"search_string","lastname":"alastname","username":"search_string","karma":50}}'));

		$usercontroller = $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("searchAction"))
			->getMock();
		$usercontroller->expects($this->once())->method('searchAction')->with($this->equalTo($query))->will($this->returnValue($uc_response));

		$pm_response = $this->getMock("Symfony\Component\HttpFoundation\Response", array("getContent"));
		$pm_response->expects($this->once())->method('getContent')->will($this->returnValue("[]"));

		$projectmanager = $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("searchAction"))
			->getMock();
		$projectmanager->expects($this->once())->method('searchAction')->with($this->equalTo($query))->will($this->returnValue($pm_response));

		$controller = $this->getMock("Ace\SearchBundle\Controller\DefaultController", array("get", "getRequest", "render"));

		$controller->expects($this->exactly(2))->method('getRequest')->will($this->returnValue($request));
		$controller->expects($this->at(2))->method('get')->with($this->equalTo('ace_user.usercontroller'))->will($this->returnValue($usercontroller));
		$controller->expects($this->at(3))->method('get')->with($this->equalTo('ace_project.sketchmanager'))->will($this->returnValue($projectmanager));
		$controller->expects($this->once())->method('render')->will($this->returnArgument(1));

		$this->assertEquals($controller->findAction(),
			array("query" => $query,
				"users" => array(1 => array("firstname" => "search_string",
											"lastname" => "alastname",
											"username" => "search_string",
											"karma" => 50)),
				"projects" => array()));
	}


	public function testFindAction_ProjectExists()
	{
		$query = "search_string";

		$response_query = $this->getMock("Symfony\Component\HttpFoundation\ParameterBag", array("get"));
		$response_query->expects($this->once())->method('get')->with($this->equalTo('query'))->will($this->returnValue($query));

		$request = $this->getMock("Symfony\Component\HttpFoundation\Request", array("getMethod"));
		$request->query = $response_query;

		$request->expects($this->once())->method('getMethod')->will($this->returnValue("GET"));

		$uc_response = $this->getMock("Symfony\Component\HttpFoundation\Response", array("getContent"));
		$uc_response->expects($this->once())->method('getContent')->will($this->returnValue("[]"));

		$usercontroller = $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("searchAction"))
			->getMock();
		$usercontroller->expects($this->once())->method('searchAction')->with($this->equalTo($query))->will($this->returnValue($uc_response));

		$pm_response = $this->getMock("Symfony\Component\HttpFoundation\Response", array("getContent"));
		$pm_response->expects($this->once())->method('getContent')->will($this->returnValue('{"1":{"name":"search_string","description":"adescription","owner":"anowner"}}'));

		$projectmanager = $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("searchAction"))
			->getMock();
		$projectmanager->expects($this->once())->method('searchAction')->with($this->equalTo($query))->will($this->returnValue($pm_response));

		$controller = $this->getMock("Ace\SearchBundle\Controller\DefaultController", array("get", "getRequest", "render"));

		$controller->expects($this->exactly(2))->method('getRequest')->will($this->returnValue($request));
		$controller->expects($this->at(2))->method('get')->with($this->equalTo('ace_user.usercontroller'))->will($this->returnValue($usercontroller));
		$controller->expects($this->at(3))->method('get')->with($this->equalTo('ace_project.sketchmanager'))->will($this->returnValue($projectmanager));
		$controller->expects($this->once())->method('render')->will($this->returnArgument(1));

		$this->assertEquals($controller->findAction(),
			array("query" => $query,
				"users" => array(),
				"projects" => array(1 => array("name" => "search_string",
					"description" => "adescription",
					"owner" => "anowner"))
			));
	}

	public function testFindAction_NoResults()
	{
		$query = "search_string";

		$response = $this->getMock("Symfony\Component\HttpFoundation\Response", array("getContent"));
		$response->expects($this->exactly(2))->method('getContent')->will($this->returnValue("[]"));

		$uc_pm= $this->getMockBuilder("Ace\UserBundle\Controller\DefaultController")
			->disableOriginalConstructor()
			->setMethods(array("searchAction"))
			->getMock();
		$uc_pm->expects($this->exactly(2))->method('searchAction')->with($this->equalTo($query))->will($this->returnValue($response));

		$response_query = $this->getMock("Symfony\Component\HttpFoundation\ParameterBag", array("get"));
		$response_query->expects($this->once())->method('get')->with($this->equalTo('query'))->will($this->returnValue($query));

		$request = $this->getMock("Symfony\Component\HttpFoundation\Request", array("getMethod"));
		$request->query = $response_query;

		$request->expects($this->once())->method('getMethod')->will($this->returnValue("GET"));

		$controller = $this->getMock("Ace\SearchBundle\Controller\DefaultController", array("get", "getRequest", "render"));

		$controller->expects($this->exactly(2))->method('getRequest')->will($this->returnValue($request));
		$controller->expects($this->exactly(2))->method('get')->will($this->returnValue($uc_pm));
		$controller->expects($this->once())->method('render')->will($this->returnArgument(1));

		$this->assertEquals($controller->findAction(), array("query" => $query, "users" => array(), "projects" => array()));
	}
}
