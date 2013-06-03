<?php

namespace Ace\UtilitiesBundle\Tests\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ace\UtilitiesBundle\Controller\BoardController;

class BoardControllerUnitTest extends \PHPUnit_Framework_TestCase
{
	public function testListBoards()
	{

		$em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
			->disableOriginalConstructor()
			->getMock();


		$repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
			->disableOriginalConstructor()
			->getMock();

		$board = $this->getMockBuilder('Ace\UtilitiesBundle\Entity\Board')
			->disableOriginalConstructor()
			->getMock();


		$board->expects($this->once())->method('getName')->will($this->returnValue("Arduino Skata"));
		$board->expects($this->once())->method('getUpload')->will($this->returnValue('{"protocol":"arduino","maximum_size":"32256","speed":"115200"}'));
		$board->expects($this->once())->method('getBootloader')->will($this->returnValue('{"low_fuses":"0xff","high_fuses":"0xde","extended_fuses":"0x05","path":"optiboot","file":"optiboot_atmega328.hex","unlock_bits":"0x3F","lock_bits":"0x0F"}'));
		$board->expects($this->once())->method('getBuild')->will($this->returnValue('{"mcu":"atmega328p","f_cpu":"16000000L","core":"arduino","variant":"standard"}'));
		$board->expects($this->once())->method('getDescription')->will($this->returnValue("KAI GAMW TA ARDUINA"));

		$repo->expects($this->once())->method('findAll')->will($this->returnValue(array($board)));
		$em->expects($this->once())->method('getRepository')->will($this->returnValue($repo));

		$controller = new BoardController($em);
		$response = $controller->listAction(new Request);
		$this->assertEquals($response->getContent(), '[{"name":"Arduino Skata","upload":{"protocol":"arduino","maximum_size":"32256","speed":"115200"},"bootloader":{"low_fuses":"0xff","high_fuses":"0xde","extended_fuses":"0x05","path":"optiboot","file":"optiboot_atmega328.hex","unlock_bits":"0x3F","lock_bits":"0x0F"},"build":{"mcu":"atmega328p","f_cpu":"16000000L","core":"arduino","variant":"standard"},"description":"KAI GAMW TA ARDUINA"}]');
	}
}
