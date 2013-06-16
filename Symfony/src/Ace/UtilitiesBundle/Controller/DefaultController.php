<?php

namespace Ace\UtilitiesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ace\UtilitiesBundle\Handler\DefaultHandler;
use Symfony\Component\HttpFoundation\Response;
use Ace\UtilitiesBundle\Handler\UploadHandler;
use ZipArchive;


class DefaultController extends Controller
{
	public function newprojectAction()
	{
		syslog(LOG_INFO, "new project");

		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$project_name = $this->getRequest()->request->get('project_name');
        $is_public = true;

		if($this->getRequest()->request->get('isPublic') !== null)
		{
			$is_public = $this->getRequest()->request->get('isPublic') === 'true' ? true : false;
		}

		$text = "";
		if($this->getRequest()->request->get('code'))
		{
			 $text = htmlspecialchars_decode($this->getRequest()->request->get('code'));
		}
		else
		{
			$utilities = new DefaultHandler();
			$text = $utilities->default_text();
		}

		$response = $this->get('ace_project.sketchmanager')->createprojectAction($user["id"], $project_name, $text, $is_public)->getContent();
		$response=json_decode($response, true);
		if($response["success"])
		{
			return $this->redirect($this->generateUrl('AceGenericBundle_project',array('id' => $response["id"])));
		}

		$this->get('session')->setFlash('error', "Error: ".$response["error"]);
		return $this->redirect($this->generateUrl('AceGenericBundle_index'));
	}

	public function deleteprojectAction($id)
	{

		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$projectmanager = $this->get('ace_project.sketchmanager');
		$response = $projectmanager->deleteAction($id)->getContent();
		$response=json_decode($response, true);
		return $this->redirect($this->generateUrl('AceGenericBundle_index'));
	}

	public function listFilenamesAction($id, $show_ino)
	{
		$projectmanager = $this->get('ace_project.sketchmanager');
		$files = $projectmanager->listFilesAction($id)->getContent();
		$files=json_decode($files, true);
		$files=$files["list"];

		if($show_ino == 0)
		{
			foreach($files as $key=>$file)
			if(strpos($file['filename'], ".ino") !== false)
			{
				unset($files[$key]);
			}
		}

		return $this->render('AceUtilitiesBundle:Default:list_filenames.html.twig', array('files' => $files));
	}

	public function changePrivacyAction($id)
	{
		$projectmanager = $this->get('ace_project.sketchmanager');

		$is_public = json_decode($projectmanager->getPrivacyAction($id)->getContent(), true);
		$is_public = $is_public["response"];

		if($is_public)
			$response = $projectmanager->setProjectPrivateAction($id)->getContent();
		else
			$response = $projectmanager->setProjectPublicAction($id)->getContent();

		return new Response($response);
	}

    //TODO fix Response
	public function getDescriptionAction($id)
	{
		$projectmanager = $this->get('ace_project.sketchmanager');
		$response = $projectmanager->getDescriptionAction($id)->getContent();
		$response=json_decode($response, true);
		if($response["success"])
			return new Response($response["response"]);
		else
			return new Response("");
	}

	public function setDescriptionAction($id)
	{

		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$description = $this->getRequest()->request->get('data');

		$projectmanager = $this->get('ace_project.sketchmanager');
		$response = $projectmanager->setDescriptionAction($id, $description)->getContent();
        return new Response(json_encode($response));
	}

	public function setNameAction($id)
	{

		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$new_name = $this->getRequest()->request->get('data');

		$projectmanager = $this->get('ace_project.sketchmanager');
		$response = $projectmanager->renameAction($id, $new_name)->getContent();
		return new Response($response);
	}

	public function renameFileAction($id)
	{

		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$old_filename = $this->getRequest()->request->get('oldFilename');
		$new_filename = $this->getRequest()->request->get('newFilename');

		$projectmanager = $this->get('ace_project.sketchmanager');
		$response = $projectmanager->renameFileAction($id, $old_filename, $new_filename)->getContent();
		return new Response($response);
	}

	public function sidebarAction()
	{
		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);
		$projectmanager = $this->get('ace_project.sketchmanager');
		$files = $projectmanager->listAction($user["id"])->getContent();
		$files=json_decode($files, true);
        //$files_wiselib = $projectmanager->listAction($user["id"])->getContent();
        //$files_wiselib = json_decode($files_wiselib, true);
        //var_dump($files_wiselib);

        //$project_id = "10";
        //$fw= $projectmanager->listWiselibDirAction($user["id"], $project_id, "")->getContent();
        //$fw = json_decode($fw, true);
        //echo("<hr/>");
        //var_dump($fw);
        //die();
		return $this->render('AceUtilitiesBundle:Default:sidebar.html.twig', array('files' => $files));
	}

	public function downloadAction($id)
	{
		syslog(LOG_INFO, "project download");

		$htmlcode = 200;
		$value = "";

		$projectmanager = $this->get('ace_project.sketchmanager');

		$name = $projectmanager->getNameAction($id)->getContent();
		$name = json_decode($name, true);
		$name = $name["response"];

		$files = $projectmanager->listFilesAction($id)->getContent();
		$files = json_decode($files, true);
		$files = $files["list"];

		if(isset($files[0]))
		{
			// Create a temporary file in the temporary
			// files directory using sys_get_temp_dir()
			$filename = tempnam(sys_get_temp_dir(), 'cb_');

			$zip = new ZipArchive();

			if ($zip->open($filename, ZIPARCHIVE::CREATE)!==true)
			{
				$value = "";
				$htmlcode = 404;
			}
			else
			{
				if($zip->addEmptyDir($name)!==true)
				{
					$value = "";
					$htmlcode = 404;
				}
				else
				{
					foreach($files as $file)
					{
						$zip->addFromString($name."/".$file["filename"], $file["code"]);
					}
					$zip->close();
					$value = file_get_contents($filename);
				}
				unlink($filename);
			}
		}
		else
		{
			$value = "";
			$htmlcode = 404;
		}

		$headers = array('Content-Type'		=> 'application/octet-stream',
			'Content-Disposition' => 'attachment;filename="'.$name.'.zip"');

		return new Response($value, $htmlcode, $headers);
	}

	public function saveCodeAction($id)
	{

		syslog(LOG_INFO, "editor save");
		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);
        $pid = $this->getRequest()->request->get('project_id');
        //return new Response("id = " . $id . " and projectid=" . $pid);
		$files = $this->getRequest()->request->get('data');
		$files = json_decode($files, true);
        $response;
		$projectmanager = $this->get('ace_project.sketchmanager');
		foreach($files as $key => $file)
		{
			$response = $projectmanager->setWiselibFileAction($id, $pid, $key, htmlspecialchars_decode($file))->getContent();
			$response = json_decode($response, true);
            $response["project_id"] = $pid;
			if($response["success"] ==  false)
				return new Response(json_encode($response));
		}
        return new Response(json_encode($response));
	}

	public function cloneAction($id)
	{
		syslog(LOG_INFO, "project cloned");

		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$name = $this->getRequest()->request->get('name');

		$projectmanager = $this->get('ace_project.sketchmanager');
		$response = $projectmanager->cloneAction($user["id"], $id)->getContent();
		$response = json_decode($response, true);
        if($response['success'])
        {
            return $this->redirect($this->generateUrl('AceGenericBundle_project',array('id' => $response["id"])));
        }
		else
        {
            $this->get('session')->setFlash('error', "Error: ".$response['error']);
            return $this->redirect($this->generateUrl('AceGenericBundle_index'));
        }
	}

    public function cloneWiselibAction()
    {
        syslog(LOG_INFO, "wiselib project cloned");

        $user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);
        //$name = $this->getRequest()->request->get('name');
        $projectmanager = $this->get('ace_project.sketchmanager');
        $response = $projectmanager->cloneWiselibAction($user["id"])->getContent();
        $response = json_decode($response, true);
        if($response['success'])
        {
            return $this->redirect($this->generateUrl('AceGenericBundle_edit',array('fpath' => 'main.cpp', 'project_id' => $response["id"])));
        }
        else
        {
            $this->get('session')->setFlash('error', "Error: ".$response['error']);
            return $this->redirect($this->generateUrl('AceGenericBundle_index'));
        }
    }

    public function addBoardAction()
    {
        $boardsmanager = $this->get('ace_board.defaultcontroller');

        if($_FILES["boards"]["error"]>0)
        {
            $this->container->get('session')->setFlash("error","Error: Upload failed with error code ".$_FILES["boards"]["error"].".");
            return $this->redirect($this->generateUrl("AceGenericBundle_boards"));
        }
        if($_FILES["boards"]["type"]!== "text/plain")
        {
            $this->container->get('session')->setFlash("error","Error: File type should be .txt.");
            return $this->redirect($this->generateUrl("AceGenericBundle_boards"));
        }
        $current_user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

        $canAdd = json_decode($boardsmanager->canAddPersonalBoardAction($current_user['id'])->getContent(), true);

        if(!$canAdd["success"])
        {
            $this->container->get('session')->setFlash("error","Error: Cannot add personal board.");
            return $this->redirect($this->generateUrl("AceGenericBundle_boards"));
        }

        $available = $canAdd["available"];
        $parsed = json_decode($boardsmanager->parsePropertiesFileAction(file_get_contents( $_FILES["boards"]["tmp_name"]))->getContent(), true);
        if(!$parsed["success"])
       {
            $this->container->get('session')->setFlash("error","Error: Could not read Board Properties File.");
            return $this->redirect($this->generateUrl("AceGenericBundle_boards"));
        }

        $boards = $parsed['boards'];

        if(count($boards)>$available)
        {
            $this->container->get('session')->setFlash("error","Error: You can add up to ".$available." boards (tried to add ".count($boards).").");
            return $this->redirect($this->generateUrl("AceGenericBundle_boards"));
        }

        foreach ($boards as $b)
        {
            $isBoard = json_decode($boardsmanager->isValidBoardAction($b)->getContent(), true);
            if(!$isBoard["success"])
            {
                $this->container->get('session')->setFlash("error","Error: File does not have the required structure.");
                return $this->redirect($this->generateUrl("AceGenericBundle_boards"));
            }
        }

        foreach ($boards as $b)
        {
            $added = json_decode($boardsmanager->addBoardAction($b, $current_user['id'])->getContent(), true);
            if(!$added["success"])
            {
                $this->container->get('session')->setFlash("error","Error: Could not add board '".$b["name"]."'. Process stopped.");
                return $this->redirect($this->generateUrl("AceGenericBundle_boards"));
            }
        }
        $this->container->get('session')->setFlash("notice",count($boards)." boards were successfully added.");
        return $this->redirect($this->generateUrl("AceGenericBundle_boards"));

    }

    public function deleteBoardAction($id)
    {
        $boardsmanager = $this->get('ace_board.defaultcontroller');
        $response = $boardsmanager->deleteBoardAction($id)->getContent();
        $response = json_decode($response, true);
        if($response['success'])
        {
            $this->container->get('session')->setFlash("notice",$response['message']);
            return $this->redirect($this->generateUrl("AceGenericBundle_boards"));
        }
        else
        {
            $this->get('session')->setFlash('error', "Error: ".$response['message']);
            return $this->redirect($this->generateUrl('AceGenericBundle_boards'));
        }

    }

    public function editBoardAction()
    {

        $id = $this->getRequest()->request->get('id');
        $description = $this->getRequest()->request->get('desc');
        $name = $this->getRequest()->request->get('name');
        $boardsmanager = $this->get('ace_board.defaultcontroller');

        $response = json_decode($boardsmanager->editAction($id, $name, $description)->getContent(), true);
        return new Response(json_encode($response));
    }

	public function createFileAction($id)
	{
		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$data = $this->getRequest()->request->get('data');
		$data = json_decode($data, true);

		$projectmanager = $this->get('ace_project.sketchmanager');
		$response = $projectmanager->createFileAction($id, $data["filename"], "")->getContent();
		$response = json_decode($response, true);
		return new Response(json_encode($response));
	}

	public function deleteFileAction($id)
	{
		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$data = $this->getRequest()->request->get('data');
		$data = json_decode($data, true);

		$projectmanager = $this->get('ace_project.sketchmanager');
		$response = $projectmanager->deleteFileAction($id, $data["filename"])->getContent();
		$response = json_decode($response, true);
        return new Response(json_encode($response));
	}

	public function imageAction()
	{
		$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$utilities = $this->get('ace_utilities.handler');
		$image = $utilities->get_gravatar($user["email"]);

		return $this->render('AceUtilitiesBundle:Default:image.html.twig', array('user' => $user["username"],'image' => $image));
	}

	public function uploadAction()
	{

		if ($this->getRequest()->getMethod() === 'POST')
		{

			$upload_handler = new UploadHandler(null, null, $this);

			if (!preg_match('/^[a-z0-9\p{P}]*$/i', $_FILES["files"]["name"][0])){

					$info = $upload_handler->post("Invalid filename.");
					$json = json_encode($info);
					return new Response($json);
				}

			$file_name = $_FILES["files"]["name"][0];
			$pinfo = pathinfo($_FILES["files"]["name"][0]);
			$project_name =  basename($_FILES["files"]["name"][0],'.'.$pinfo['extension']);
			$ext = $pinfo['extension'];

			if($ext == "ino" || $ext == "pde"){

				if (substr(exec("file -bi -- ".escapeshellarg($_FILES["files"]["tmp_name"][0])), 0, 4) !== 'text'){

					$info = $upload_handler->post("Filetype not allowed.");
					$json = json_encode($info);
					return new Response($json);
				}

				 $info = $upload_handler->post(null);
				 $file = fopen($_FILES["files"]["tmp_name"][0], 'r');
				 $code = fread($file, filesize($_FILES["files"]["tmp_name"][0]));
				 fclose($file);

			     $sketch_id = $upload_handler->createUploadedProject($project_name, $code);
					if(!isset($sketch_id)){
							$info = $upload_handler->post("Error creating Project.");
							$json = json_encode($info);
							return new Response($json);
					}

				$updated_info = array();
				$updated_info[] = $upload_handler->fixFile($info, $sketch_id, $project_name, $ext);
				$json = json_encode($updated_info);
				return new Response($json);

			}
			else if($ext == "zip"){

				$info = $upload_handler->post(null);

				$code = '';
			     $z = new \ZipArchive();
				 $headers = array();
				 $cpps = array();
				 $count = 0;

				 if ($z->open($_FILES["files"]["tmp_name"][0])) {

					 for ($i = 0; $i < $z->numFiles; $i++) {

						$nameIndex = $z->getNameIndex($i);

				 if (!preg_match('/^[a-z0-9\p{P}]*$/i', $nameIndex)){

					     $info = $upload_handler->post("Invalid filename.");
						 $json = json_encode($info);
						 return new Response($json);
						}

						$exp = explode('.', $nameIndex);
						$exp2 = explode('/', $nameIndex);
						$ext2 = end($exp);
						$end = end($exp2);
						// $folderName = prev($exp2);
						// $fileName = basename($end,".pde");

						 if( $ext2 == "pde"){
							 //if( $folderName == $fileName || count($exp) == 2)
						     if(mb_detect_encoding($z->getFromIndex($i), 'UTF-8', true) !== false){
								$count++;
								$code = $z->getFromIndex($i);
								$project_name = $end;
							 }
						 } else if($ext2 == "ino" /*&& count($exp) == 2*/){

								if(mb_detect_encoding($z->getFromIndex($i), 'UTF-8', true) !== false){
								$count++;
								$code = $z->getFromIndex($i);
								$project_name = $end;
							 }
						 } else if($ext2 == "h"){
								$headers[$end] = $z->getFromIndex($i);
						 }
						 else if($ext2 == "cpp"){
								$cpps[$end] = $z->getFromIndex($i);
						 }
						 // $code .= $z->getNameIndex($i)."\r\n";
					}

				} else {$code = 'ERROR opening file';}

			if($count == 1){

				if(mb_detect_encoding($code, 'UTF-8', true) !== false){
					$sketch_id = $upload_handler->createUploadedProject($project_name, $code);
					if(!isset($sketch_id)){
							$info = $upload_handler->post("Error creating Project.");
							$json = json_encode($info);
							return new Response($json);
					}
				} else {
						$info = $upload_handler->post("Filetype not allowed.");
						$json = json_encode($info);
						return new Response($json);
				}

				foreach($headers as $key => $value){

					if(mb_detect_encoding($value, 'UTF-8', true) !== false){
						if(!$upload_handler->createUploadedFile($sketch_id, $key, $value)){
							$info = $upload_handler->post("Error creating file.");
							$json = json_encode($info);
							return new Response($json);
						}
					}
				}

				foreach($cpps as $key => $value){

					if(mb_detect_encoding($value, 'UTF-8', true) !== false){
						if(!$upload_handler->createUploadedFile($sketch_id, $key, $value)){
							$info = $upload_handler->post("Error creating file.");
							$json = json_encode($info);
							return new Response($json);
						}
					}
				}

			} else {
					$sketch_id = null;
				}

				$updated_info = array();
				$updated_info[] = $upload_handler->fixFile($info, $sketch_id, $project_name, $ext);
				$json = json_encode($updated_info);
				return new Response($json);

			}else {
				$info = $upload_handler->post(null);
				$json = json_encode($info);
				return new Response($json);
			}
		}
		 else if($this->getRequest()->getMethod() === 'GET')
		{
			return new Response('200');
		}
	}

	public function uploadfilesAction($id){

		$sketch_id = $id;

		if ($this->getRequest()->getMethod() === 'POST')
		{

			$upload_handler = new UploadHandler(array('accept_file_types' => '/(\.|\/)(h|cpp)$/i'), null, $this);

			if (!preg_match('/^[a-z0-9\p{P}]*$/i', $_FILES["files"]["name"][0])){

					$info = $upload_handler->post("Invalid filename.");
					$json = json_encode($info);
					return new Response($json);
				}

			$file_name = $_FILES["files"]["name"][0];
			$pinfo = pathinfo($_FILES["files"]["name"][0]);
			$project_name =  basename($_FILES["files"]["name"][0],'.'.$pinfo['extension']);
			$ext = $pinfo['extension'];

			if($ext == "cpp" || $ext == "h"){

				if (substr(exec("file -bi -- ".escapeshellarg($_FILES["files"]["tmp_name"][0])), 0, 4) !== 'text'){

					$info = $upload_handler->post("Filetype not allowed.");
					$json = json_encode($info);
					return new Response($json);
				}

				 $info = $upload_handler->post(null);
				 $file = fopen($_FILES["files"]["tmp_name"][0], 'r');
				 $code = fread($file, filesize($_FILES["files"]["tmp_name"][0]));
				 fclose($file);

					if(!$upload_handler->createUploadedFile($sketch_id, $project_name, $code)){
						$info = $upload_handler->post("Error creating file.");
						$json = json_encode($info);
						return new Response($json);
					}

				$updated_info = array();
				$updated_info[] = $upload_handler->fixFile($info, $sketch_id, $project_name, $ext);
				$json = json_encode($updated_info);
				return new Response($json);

			}else {
				$info = $upload_handler->post(null);
				$json = json_encode($info);
				return new Response($json);
			}
		}
		 else if($this->getRequest()->getMethod() === 'GET')
		{
			return new Response('200');
		}
	}

	public function logAction($message)
	{
		header('Access-Control-Allow-Origin: *');

		syslog(LOG_INFO, "codebender generic log: ".$message);
		return new Response("OK");
	}
}
