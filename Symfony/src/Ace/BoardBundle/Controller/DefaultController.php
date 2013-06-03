<?php

namespace Ace\BoardBundle\Controller;

use Ace\BoardBundle\Entity\Board;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;


class DefaultController extends Controller
{

    protected $em;
    protected $sc;
    protected $container;

	public function listAction()
	{
		header('Access-Control-Allow-Origin: *');

		$boards = array();

        if ($this->get('security.context')->isGranted('ROLE_USER'))
        {
            $user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

            $db_boards = $this->em->getRepository('AceBoardBundle:Board')->findByOwner($user["id"]);

            foreach ($db_boards as $board)
            {
                $boards[] = array(
                    "name" => $board->getName(),
                    "upload" => json_decode($board->getUpload(), true),
                    "bootloader" => json_decode($board->getBootloader(), true),
                    "build" => json_decode($board->getBuild(), true),
                    "description" => $board->getDescription(),
                    "personal" => true,
                    "id" => $board->getId()
                );
            }
        }

		$db_boards = $this->em->getRepository('AceBoardBundle:Board')->findBy(array("owner" => null));

		foreach ($db_boards as $board)
		{
			$boards[] = array(
				"name" => $board->getName(),
				"upload" => json_decode($board->getUpload(), true),
				"bootloader" => json_decode($board->getBootloader(), true),
				"build" => json_decode($board->getBuild(), true),
				"description" => $board->getDescription(),
				"personal" => false,
                "id" => $board->getId()
			);
		}


		return new Response(json_encode($boards));
	}

    public function editAction($id, $name, $description)
    {
        $board = $this->getBoardById($id);
        $perm = json_decode($this->checkBoardPermissions($id), true);
        if(!$perm['success'])
        {
            return new Response(json_encode(array("success" => false, "message" => "Cannot edit board '".$board->getName()."'.")));
        }
        $board->setName($name);
        $board->setDescription($description);

        $em = $this->em;
        $em->persist($board);
        $em->flush();

        return new Response(json_encode(array("success" => true, "new_name" => $name, "new_desc" => $description)));
    }

    public function setNameAction($id, $name)
    {
        $board = $this->getBoardById($id);
        $perm = json_decode($this->checkBoardPermissions($id), true);
        if(!$perm['success'])
        {
            return new Response(json_encode(array("success" => false, "message" => "Cannot set name for board '".$board->getName()."'.")));
        }
        $board->setName($name);
        $em = $this->em;
        $em->persist($board);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }

    public function setDescriptionAction($id, $description)
    {
        $board = $this->getBoardById($id);
        $perm = json_decode($this->checkBoardPermissions($id), true);
        if(!$perm['success'])
        {
            return new Response(json_encode(array("success" => false, "message" => "Cannot set description for board '".$board->getName()."'.")));
        }
        $board->setDescription($description);
        $em = $this->em;
        $em->persist($board);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }

    public function addBoardAction($b, $user_id)
    {
        $owner = $this->em->getRepository('AceUserBundle:User')->find($user_id);

        $board = new Board();
        $board->setName($b["name"]);
        $board->setUpload(json_encode($b["upload"]));
        $board->setBootloader(json_encode($b["bootloader"]));
        $board->setBuild(json_encode($b["build"]));
        $board->setOwner($owner);
        $board->setDescription("Personal Board");

        $this->em->persist($board);
        $this->em->flush();
        return new Response(json_encode(array("success" => true)));

    }

    public function isValidBoardAction($b)
    {
        if(isset($b['name']) && isset($b["upload"]) && isset($b["bootloader"]) && isset($b["build"]))
        {
            return new Response(json_encode(array("success" => true)));
        }
        else
        {
            return new Response(json_encode(array("success" => false)));

        }
    }

    public function deleteBoardAction($id)
    {
        $perm = json_decode($this->checkBoardPermissions($id), true);

        if(!($perm['success']))
        {
            return new Response(json_encode(array("success" => false, "message" => "You have no persmissions to delete this board.")));
        }

        $board = $this->getBoardById($id);

        $this->em->remove($board);
        $this->em->flush($board);

        return new Response(json_encode(array("success" => true, "message" => "Board '".$board->getName()."' was successfully deleted.")));

    }

    public function canAddPersonalBoardAction($user_id)
    {
        return new Response($this->canAddPersonalBoard($user_id));
    }

    public function parsePropertiesFileAction($txtProperties) {
        $result = array();
        $lines = explode("\n", $txtProperties);
        $key = "";
        $isWaitingOtherLine = false;
        foreach ($lines as $i => $line) {
            if (empty($line) || (!$isWaitingOtherLine && strpos($line, "#") === 0))
                continue;

            if (!$isWaitingOtherLine) {
                $key = substr($line, 0, strpos($line, '='));
                $value = substr($line, strpos($line, '=')+1, strlen($line));
            }
            else {
                $value .= $line;
            }

            /* Check if ends with single '\' */
            if (strrpos($value, "\\") === strlen($value)-strlen("\\")) {
                $value = substr($value,0,strlen($value)-1)."\n";
                $isWaitingOtherLine = true;
            }
            else {
                $isWaitingOtherLine = false;
            }

            $keys = explode(".", $key);
            $local_result = $value;
            for($i = count($keys) -1; $i >= 0; $i--)
            {
                $local_result = array($keys[$i] => $local_result);
            }

            $result = array_merge_recursive($result, $local_result);
            unset($lines[$i]);
        }

        return new Response(json_encode(array("success" => true, "boards" => $result)));
    }

    protected function canAddPersonalBoard($user_id)
    {
        $boards = $this->em->getRepository('AceBoardBundle:Board')->findByOwner($user_id);
        $currentPersonal = count($boards);


        $prs= $this->em->getRepository('AceBoardBundle:PersonalBoards')->findByOwner($user_id);
        $maxPersonal = 0;
        foreach ($prs as $p)
        {
            $now = new \DateTime("now");
            if($now>= $p->getStarts() && ($p->getExpires()==NULL || $now < $p->getExpires()))
                $maxPersonal+=$p->getNumber();
        }

        if($currentPersonal >= $maxPersonal)
            return json_encode(array("success" => false, "error" => "Cannot add personal borad."));
        else
            return json_encode(array("success" => true, "available" => $maxPersonal - $currentPersonal));

    }

    protected function checkBoardPermissions($id)
    {
        $board = $this->getBoardById($id);
        $current_user = $this->sc->getToken()->getUser();

        if($board->getOwner()!== null && $current_user !== "anon." && $current_user->getId() === $board->getOwner()->getId())
            return json_encode(array("success" => true));
        else
            return json_encode(array("success" => false, "error" => "You have no permissions for this board.", "id" => $id));

    }

    protected function getBoardById($id)
    {
        $em = $this->em;
        $board = $this->em->getRepository('AceBoardBundle:Board')->find($id);
        if (!$board)
            throw $this->createNotFoundException('No board found with id '.$id);

        return $board;
    }


    public function __construct(EntityManager $entityManager, SecurityContext $securityContext, ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->sc = $securityContext;
        $this->container = $container;
    }
}
