<?php

namespace Ace\ProjectBundle\Tests\Controller;

use Ace\ProjectBundle\Controller\MongoFilesController;

class MongoFilesControllerTester extends MongoFilesController
{
    public function call_listFiles($id)
    {
       return $this->listFiles($id);
    }
}
class MongoFilesControllerUnitTest extends \PHPUnit_Framework_TestCase
{
    protected $pf;
	public function testCreateAction()
    {
        $controller = $this->setUpController($dm,NULL);
        $dm->expects($this->once())->method("persist");
        $dm->expects($this->once())->method('flush');

        $response = $controller->createAction();
        $this->assertEquals($response, '{"success":true,"message":"Project created successfully.","id":null}');

    }

    public function testDeleteAction()
    {
        $controller = $this->setUpController($dm,array('getProjectById'));
        $controller->expects($this->once())->method('getProjectById')->with($this->equalTo(1234))->will($this->returnValue($this->pf));
        $dm->expects($this->once())->method("remove")->with($this->equalTo($this->pf));
        $dm->expects($this->once())->method('flush');

        $response = $controller->deleteAction(1234);
        $this->assertEquals($response, '{"success":true,"message":"Project deleted successfully."}');

    }

    public function testListFilesAction()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");
        $controller = $this->setUpController($dm,array('listFiles'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));
        $response = $controller->listFilesAction(1234);
        $this->assertEquals($response, '{"success":true,"list":[{"filename":"project.ino","code":"void setup(){}"},{"filename":"header.h","code":"void function(){}"}]}'
        );
    }

    public function testCreateFileAction_Yes()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");

        $controller = $this->setUpController($dm, array('listFiles', 'canCreateFile', 'setFilesById'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));
        $list[] = array("filename" => "header2.h", "code" => "void function2(){}");
        $controller->expects($this->once())->method('canCreateFile')->with($this->equalTo(1234), $this->equalTo('header2.h'))->will($this->returnValue('{"success":true}'));
        $controller->expects($this->once())->method('setFilesById')->with($this->equalTo(1234), $this->equalTo($list));
        $response = $controller->createFileAction(1234, 'header2.h', 'void function2(){}');
        $this->assertEquals($response, '{"success":true,"message":"File created successfully."}'
        );

    }

    public function testCreateFileAction_No()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");

        $controller = $this->setUpController($dm, array('listFiles', 'canCreateFile'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));

        $controller->expects($this->once())->method('canCreateFile')->with($this->equalTo(1234), $this->equalTo('header.h'))->will($this->returnValue('{"success":false,"id":1,"filename":"header1.h","error":"This file already exists"}'));

        $response = $controller->createFileAction(1234, 'header.h', 'void function2(){}');
        $this->assertEquals($response, '{"success":false,"id":1,"filename":"header1.h","error":"This file already exists"}'

        );

    }

    public function testGetFileAction_Yes()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");

        $controller = $this->setUpController($dm, array('listFiles'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));

        $response = $controller->getFileAction(1234, 'header.h');
        $this->assertEquals($response, '{"success":true,"code":"void function(){}"}'

        );
    }

    public function testGetFileAction_No()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");

        $controller = $this->setUpController($dm, array('listFiles'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));

        $response = $controller->getFileAction(1234, 'header2.h');
        $this->assertEquals($response, '{"success":false}'

        );
    }

    public function testSetFileAction_Yes()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");

        $controller = $this->setUpController($dm, array('listFiles', 'setFilesById'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));
        $list[1]['code'] =  "void function(int x){}";
        $controller->expects($this->once())->method('setFilesById')->with($this->equalTo(1234), $this->equalTo($list));

        $response = $controller->setFileAction(1234, 'header.h', "void function(int x){}");
        $this->assertEquals($response, '{"success":true,"message":"Saved successfully."}');
    }

    public function testSetFileAction_No()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");

        $controller = $this->setUpController($dm, array('listFiles', 'setFilesById'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));

        $response = $controller->setFileAction(1234, 'header2.h', "void function(int x){}");
        $this->assertEquals($response, '{"success":false,"message":"Save failed.","id":1234,"filename":"header2.h"}');
    }

    public function testDeleteFileAction_Exists()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");

        $controller = $this->setUpController($dm, array('fileExists', 'listFiles', 'setFilesById'));

        $controller->expects($this->once())->method('fileExists')->with($this->equalTo(1234), $this->equalTo('header.h'))->will($this->returnValue('{"success":true}'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));

        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");

        $controller->expects($this->once())->method('setFilesById')->with($this->equalTo(1234), $this->equalTo($list));

        $response = $controller->deleteFileAction(1234,'header.h');
        $this->assertEquals($response, '{"success":true,"message":"File deleted successfully."}');

    }
    public function testDeleteFileAction_DoesNotExist()
    {

        $controller = $this->setUpController($dm, array('fileExists', 'listFiles', 'setFilesById'));

        $controller->expects($this->once())->method('fileExists')->with($this->equalTo(1234), $this->equalTo('header2.h'))->will($this->returnValue('{"success":false,"filename":"header2.h","error":"File header2.h does not exist."}'));

        $response = $controller->deleteFileAction(1234,'header2.h');
        $this->assertEquals($response, '{"success":false,"filename":"header2.h","error":"File header2.h does not exist."}');

    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testDeleteFileAction_Exception()
    {

        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header1.h", "code" => "void function(){}");

        $controller = $this->setUpController($dm, array('fileExists', 'listFiles', 'setFilesById'));

        $controller->expects($this->once())->method('fileExists')->with($this->equalTo(1234), $this->equalTo('header.h'))->will($this->returnValue('{"success":true}'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));


        $response = $controller->deleteFileAction(1234,'header.h');


    }

    public function testRenameFileAction_Yes()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");
        $controller = $this->setUpController($dm, array('fileExists', 'canCreateFile', 'listFiles', 'setFilesById'));
        $controller->expects($this->once())->method('fileExists')->with($this->equalTo(1234), $this->equalTo('header.h'))->will($this->returnValue('{"success":true}'));
        $controller->expects($this->once())->method('canCreateFile')->with($this->equalTo(1234), $this->equalTo('newheader.h'))->will($this->returnValue('{"success":true}'));
        $controller->expects($this->once())->method('listFiles')->with($this->equalTo(1234))->will($this->returnValue($list));
        $list[1]["filename"]='newheader.h';
        $controller->expects($this->once())->method('setFilesById')->with($this->equalTo(1234), $this->equalTo($list));
        $response = $controller->renameFileAction(1234, 'header.h', 'newheader.h');
        $this->assertEquals($response, '{"success":true,"message":"File renamed successfully."}');
    }

    public function testRenameFileAction_DoesNotExist()
    {

        $controller = $this->setUpController($dm, array('fileExists'));
        $controller->expects($this->once())->method('fileExists')->with($this->equalTo(1234), $this->equalTo('header.h'))->will($this->returnValue('{"success":false,"filename":"header2.h","error":"File header2.h does not exist."}'));
        $response = $controller->renameFileAction(1234, 'header.h', 'newheader.h');
        $this->assertEquals($response, '{"success":false,"filename":"header2.h","error":"File header2.h does not exist."}');
    }

    public function testRenameFileAction_CannotCreateFile()
    {

        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");
        $controller = $this->setUpController($dm, array('fileExists', 'canCreateFile'));
        $controller->expects($this->once())->method('fileExists')->with($this->equalTo(1234), $this->equalTo('header.h'))->will($this->returnValue('{"success":true}'));
        $controller->expects($this->once())->method('canCreateFile')->with($this->equalTo(1234), $this->equalTo('newheader.h'))->will($this->returnValue('{"success":false,"id":1,"filename":"newheader.h","error":"This file already exists"}'));

        $response = $controller->renameFileAction(1234, 'header.h', 'newheader.h');
        $this->assertEquals($response,'{"success":false,"message":"File could not be renamed.","id":1234,"filename":"newheader.h","error":"This file already exists","old_filename":"header.h"}');
    }

    public function testSetFilesById()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");

        $controller = $this->setUpController($dm, array('getProjectById'));
        $controller->expects($this->once())->method('getProjectById')->with($this->equalTo(1234))->will($this->returnValue($this->pf));
        $this->pf->expects($this->once())->method('setFiles')->with($this->equalTo($list));
        $this->pf->expects($this->once())->method('setFilesTimestamp');
        $dm->expects($this->once())->method('persist')->with($this->equalTo($this->pf));
        $dm->expects($this->once())->method('flush');

        $controller->setFilesById(1234, $list);


    }

    public function testGetProjectById_Exists()
    {
        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array("find"))
            ->getMock();

        $controller = $this->setUpController($dm, NULL);

        $dm->expects($this->once())->method('getRepository')->with($this->equalTo('AceProjectBundle:ProjectFiles'))->will($this->returnValue($repo));
        $repo->expects($this->once())->method('find')->with($this->equalTo(1234))->will($this->returnValue($this->pf));
        $response = $controller->getProjectById(1234);
        $this->assertEquals($response, $this->pf);

    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testGetProjectById_DoesNotExist()
    {
        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array("find"))
            ->getMock();

        $controller = $this->setUpController($dm, NULL);

        $dm->expects($this->once())->method('getRepository')->with($this->equalTo('AceProjectBundle:ProjectFiles'))->will($this->returnValue($repo));
        $repo->expects($this->once())->method('find')->with($this->equalTo(1234))->will($this->returnValue(NULL));
        $controller->getProjectById(1234);

    }

    public function testListFiles()
    {
        $list = array();
        $list[] = array("filename" => "project.ino", "code" => "void setup(){}");
        $list[] = array("filename" => "header.h", "code" => "void function(){}");

        $controller = $this->setUpTesterController($dm, array('getProjectById'));
        $controller->expects($this->once())->method('getProjectById')->with($this->equalTo(1234))->will($this->returnValue($this->pf));
        $this->pf->expects($this->once())->method('getFiles')->will($this->returnValue($list));

        $response = $controller->call_listFiles(1234);
        $this->assertEquals($response, $list);

    }
    protected function setUp()
    {
        $this->pf = $this->getMockBuilder('Ace\ProjectBundle\Document\ProjectFiles')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function setUpController(&$dm, $m)
    {
        $dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMock('Ace\ProjectBundle\Controller\MongoFilesController', $methods = $m, $arguments = array($dm));
        return $controller;
    }

    private function setUpTesterController(&$dm, $m)
    {
        $dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMock('Ace\ProjectBundle\Tests\Controller\MongoFilesControllerTester', $methods = $m, $arguments = array($dm));
        return $controller;
    }
}
