<?php

namespace Ace\GenericBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ace\ProjectBundle\Controller\SketchController;

class EditorController extends Controller
{		
	public function editAction($id, $fpath="")
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

        $git_url = $projectmanager->getGitUrlAction($id)->getContent();
        $git_url = json_decode($git_url, true);
        $git_url = $git_url["response"];

        $git_commit_sha = $projectmanager->getGitCommitSHAAction($id)->getContent();
        $git_commit_sha = json_decode($git_commit_sha, true);
        $git_commit_sha = $git_commit_sha["response"];

        $is_wiselib_clone = $projectmanager->getIsWiselibClone($id)->getContent();
        $is_wiselib_clone = json_decode($is_wiselib_clone, true);
        $is_wiselib_clone = $is_wiselib_clone["response"];

        $projectfiles_id = $projectmanager->getProjectFilesIdAction($id)->getContent();
        $projectfiles_id = json_decode($projectfiles_id, true);
        $projectfiles_id = $projectfiles_id["response"];

        if(strstr($projectfiles_id, "/")){
            $projectfiles_id = explode("/", $projectfiles_id);
        }
        $projectfiles_id = trim($projectfiles_id[1]);

		$is_public = json_decode($projectmanager->getPrivacyAction($id)->getContent(), true);
		$is_public = $is_public["response"];

        $project = $projectmanager->getProjectById($id);
        $files = $projectmanager->getFileCode($fpath, $project->getProjectfilesId())->getContent();
        $files = json_decode($files, true);
        $files = $files["list"];

		foreach($files as $key=>$file)
		{
			$files[$key]["code"] = htmlspecialchars($file["code"]);
		}

        $owner = json_decode($projectmanager->getOwnerAction($id)->getContent(), true);
        $owner = $owner["response"];
        $owner_id = $owner['id'];

        $files_wiselib = $projectmanager->listWiselibDirAction($owner_id, $id, $fpath)->getContent();
        $files_wiselib = json_decode($files_wiselib, true);

        $user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);

		$boardcontroller = $this->get('ace_board.defaultcontroller');
		$boards = $boardcontroller->listAction()->getContent();

        $wiselib_clones = json_decode($projectmanager->getWiselibCloneProjects($user['id'])->getContent(), true);


        if(isset($wiselib_clones["success"]) && !$wiselib_clones["success"]){
            $wiselib_clones = array();
        }

		return $this->render('AceGenericBundle:Editor:editor.html.twig', array('project_id' => $id, 'project_name' => $name, 'files' => $files, 'boards' => $boards, "is_public" => $is_public, "files_wiselib" => $files_wiselib, "fpath" => $fpath, "git_url" => $git_url, "git_commit_sha" => $git_commit_sha, "github_access_token" => $projectmanager->getUserAccessTokenAction(), "is_wiselib_clone" => $is_wiselib_clone, 'pf_id' => $projectfiles_id, 'user' => $user["username"], 'clones' => $wiselib_clones));
	}
}
