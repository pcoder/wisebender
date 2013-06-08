<?php

namespace Ace\GenericBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ace\ProjectBundle\Controller\SketchController;

class EditorController extends Controller
{		
	public function editAction($id, $fpath)
	{
		/** @var SketchController $projectmanager */
		$projectmanager = $this->get('ace_project.sketchmanager');

		$permissions = json_decode($projectmanager->checkWriteProjectPermissionsAction($id)->getContent(), true);
		if(!$permissions["success"])
		{
			return $this->forward('AceGenericBundle:Default:project', array("id"=> $id));
		}

		$name = $projectmanager->getNameAction($id)->getContent();
		$name = json_decode($name, true);
		$name = $name["response"];

		$is_public = json_decode($projectmanager->getPrivacyAction($id)->getContent(), true);
		$is_public = $is_public["response"];

//		$files = $projectmanager->listFilesAction($id)->getContent();
//		$files = json_decode($files, true);
//		$files = $files["list"];

        $files = $projectmanager->getFileCode($fpath)->getContent();
        $files = json_decode($files, true);
        $files = $files["list"];

		foreach($files as $key=>$file)
		{
			$files[$key]["code"] = htmlspecialchars($file["code"]);
		}

        $files_wiselib = $projectmanager->listWiselibDirAction()->getContent();
        $files_wiselib = json_decode($files_wiselib, true);

		$boardcontroller = $this->get('ace_board.defaultcontroller');
		$boards = $boardcontroller->listAction()->getContent();

		return $this->render('AceGenericBundle:Editor:editor.html.twig', array('project_id' => $id, 'project_name' => $name, 'files' => $files, 'boards' => $boards, "is_public" => $is_public, "files_wiselib" => $files_wiselib, "fpath" => $fpath));
	}
}
