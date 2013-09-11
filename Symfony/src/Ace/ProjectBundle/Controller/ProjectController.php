<?php

namespace Ace\ProjectBundle\Controller;

use Ace\ProjectBundle\Entity\PrivateProjects;
use Ace\ProjectBundle\Helper\ProjectErrorsHelper;
use Doctrine\DBAL\Platforms\Keywords\ReservedKeywordsValidator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Ace\ProjectBundle\Entity\Project as Project;
use Doctrine\ORM\EntityManager;

use Symfony\Component\Security\Core\SecurityContext;


class ProjectController extends Controller
{
    protected $em;
    protected $fc;
    protected $sc;
    protected $sl = "unknown";
    protected $wiselib_src_dir = "";


    public function createprojectAction($user_id, $project_name, $code, $isPublic)
    {

        if (!$isPublic) {
            $canCreate = json_decode($this->canCreatePrivateProject($user_id), true);
        } else {
            $canCreate = array("success" => true);
        }

        if ($canCreate["success"]) {
            $response = $this->createAction($user_id, $project_name, "", $isPublic)->getContent();
            $response = json_decode($response, true);
        } else {
            $response = $canCreate;
        }

        return new Response(json_encode($response));

    }

    public function createWiselibProjectAction($user_id, $project_name, $code, $isPublic, $description="", $purl="")
    {

        if (!$isPublic) {
            $canCreate = json_decode($this->canCreatePrivateProject($user_id), true);
        } else {
            $canCreate = array("success" => true);
        }

        if ($canCreate["success"]) {
            $response = $this->createAction($user_id, $project_name,  $description, $isPublic, $purl)->getContent();
            $response = json_decode($response, true);
        } else {
            $response = $canCreate;
        }

        return new Response(json_encode($response));

    }

    public function listAction($owner)
    {
        $private_access = false;
        $current_user = $this->sc->getToken()->getUser();
        if ($current_user !== "anon." && $current_user->getID() == $owner)
            $private_access = true;

        $projects = $this->em->getRepository('AceProjectBundle:Project')->findByOwner($owner);
        $list = array();
        foreach ($projects as $project) {
            if ($project->getIsPublic() || $private_access)
                $list[] = array("id" => $project->getId(), "name" => $project->getName(), "description" => $project->getDescription(), "is_public" => $project->getIsPublic());
        }
        return new Response(json_encode($list));
    }

    function endsWith($needle, $haystack)
    {
        return preg_match('/' . preg_quote($needle, '/') . '$/', $haystack);
    }

    function dirToArray($dir, $selected_dir = "")
    {

        $result = array();
        $cdir = scandir($dir);
        //var_dump($checked_folders);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", "..", "doc", "apps", "util", ".git"))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $str = substr($selected_dir, 0, strlen($value));
                    //var_dump("Comparing  " . $str . " and " . $value);
                    if (strcmp($str, $value) == 0 && substr($selected_dir, strlen($value), 1) == "/") {
                        //var_dump($selected_dir . " has " . $value . " at 0th position?? and ends with a slash with length of " . $value);
                        $selected_dir = substr($selected_dir, strlen($value) + 1);
                        //var_dump("Folder " . $dir . DIRECTORY_SEPARATOR . $value . " isChecked");
                        $result[$value] = array($this->dirToArray($dir . DIRECTORY_SEPARATOR . $value, $selected_dir), "isdir" => true, "isChecked" => true);
                    } else {
                        $result[$value] = array($this->dirToArray($dir . DIRECTORY_SEPARATOR . $value, $selected_dir), "isdir" => true);
                    }
                } else {
                    if ($this->endsWith(".cpp", $value) || $this->endsWith(".h", $value))
                        $result[] = $value;
                }
            }
        }
        return $result;
    }

    public function listWiselibDirAction($owner, $id, $selected_dir = "")
    {
        $src_dir = "";
        if ($id == "") {
            $private_access = false;
            $current_user = $this->sc->getToken()->getUser();
            if ($current_user !== "anon." && $current_user->getID() == $owner)
                $private_access = true;

            $projects = $this->em->getRepository('AceProjectBundle:Project')->findByOwner($owner);
            $list = array();
            foreach ($projects as $project) {
                if ($project->getIsPublic() || $private_access)
                    $list[] = array("id" => $project->getId(), "name" => $project->getName(), "description" => $project->getDescription(), "is_public" => $project->getIsPublic(), "git_url" => $project->getGitUrl(), "git_commit_sha" => $project->getGitCommitSHA());
            }

        }
        $project = $this->getProjectById($id);
        $directory = $this->fc->getDir($project->getProjectfilesId());
        $array_items = $this->dirToArray($directory, $selected_dir);
        return new Response(json_encode($array_items));
    }

    public function createAction($owner, $name, $description, $isPublic, $purl="")
    {
        $validName = json_decode($this->nameIsValid($name), true);
        if (!$validName["success"])
            return new Response(json_encode($validName));

        $project = new Project();
        $user = $this->em->getRepository('AceUserBundle:User')->find($owner);
        $project->setOwner($user);
        $project->setName($name);
        $project->setDescription($description);
        $project->setGitUrl($purl);
        $project->setGitCommitSHA($purl);
        $project->setIsPublic($isPublic);
        $project->setType($this->sl);
        $project->setIsWiselibClone(false);
        $response = json_decode($this->fc->createAction(), true);

        if ($response["success"]) {
            $id = $response["id"];
            $project->setProjectfilesId($id);

            $em = $this->em;
            $em->persist($project);
            $em->flush();

            return new Response(json_encode(array("success" => true, "id" => $project->getId())));
        } else
            return new Response(json_encode(array("success" => false, "owner_id" => $user->getId(), "name" => $name)));
    }

    public function deleteAction($id)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $deletion = json_decode($this->fc->deleteAction($project->getProjectfilesId()), true);

        if ($deletion["success"] == true) {
            $em = $this->em;
            $em->remove($project);
            $em->flush();
            return new Response(json_encode(array("success" => true)));
        } else {
            return new Response(json_encode(array("success" => false, "id" => $project->getProjectfilesId())));
        }

    }

    public function cloneAction($owner, $id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $new_name = $project->getName();
        $nameExists = json_decode($this->nameExists($owner, $new_name), true);
        while ($nameExists["success"]) {
            $new_name = $new_name . "-copy";
            $nameExists = json_decode($this->nameExists($owner, $new_name), true);
        }
        $response = json_decode($this->createAction($owner, $new_name, $project->getDescription(), true)->getContent(), true);

        if ($response["success"] == true) {
            $new_project = $this->getProjectById($response["id"]);
            $new_project->setParent($project->getId());
            $em = $this->em;
            $em->persist($new_project);
            $em->flush();

            $list = json_decode($this->listFilesAction($project->getId())->getContent(), true);
            return new Response(json_encode(array("success" => true, "id" => $response["id"], "list" => $list["list"], "name" => $new_name)));
        } else {
            return new Response(json_encode(array("success" => false, "id" => $id)));
        }
    }

    public function cloneWiselibAction($owner)
    {
        //$perm = json_decode($this->checkReadProjectPermissions($id), true);
        //if(!$perm['success'])
        //{
        //    return new Response(json_encode($perm));
        //}

        $new_name = "Wiselib";
        $nameExists = json_decode($this->nameExists($owner, $new_name), true);
        while ($nameExists["success"]) {
            $new_name = $new_name . "-copy";
            $nameExists = json_decode($this->nameExists($owner, $new_name), true);
        }
        $response = json_decode($this->createAction($owner, $new_name, "The default clone of Wiselib. See https://github.com/ibr-alg/wiselib for more information.", true)->getContent(), true);
        if ($response["success"] == true) {
            $new_project = $this->getProjectById($response["id"]);
            $new_project->setParent("");
            $new_project->setIsWiselibClone(true);
            $em = $this->em;
            $em->persist($new_project);
            $em->flush();

            return new Response(json_encode(array("success" => true, "id" => $response["id"], "name" => $new_name)));
        } else {
            //TODO write tests for this case
            return new Response(json_encode(array("success" => false)));
        }
    }

    public function renameAction($id, $new_name)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $validName = json_decode($this->nameIsValid($new_name), true);
        if ($validName["success"]) {
            $project = $this->getProjectById($id);
            $list = json_decode($this->listFilesAction($project->getId())->getContent(), true);
            return new Response(json_encode(array("success" => true, "list" => $list["list"])));
        } else {
            return new Response(json_encode($validName));
        }
    }

    public function setProjectPublicAction($id)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        if ($project->getIsPublic()) {
            return new Response(json_encode(array("success" => false, "error" => "This project is already public.")));
        } else {
            $project->setIsPublic(true);
            $this->em->persist($project);
            $this->em->flush();
            return new Response(json_encode(array("success" => true)));
        }

    }

    public function setProjectPrivateAction($id)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $current_user_id = $this->sc->getToken()->getUser()->getID();
        $canCreate = json_decode($this->canCreatePrivateProject($current_user_id), true);
        if (!$canCreate['success'])
            return new Response(json_encode($canCreate));

        $project = $this->getProjectById($id);
        if (!$project->getIsPublic()) {
            return new Response(json_encode(array("success" => false, "error" => "This project is already private.")));
        } else {
            $project->setIsPublic(false);
            $this->em->persist($project);
            $this->em->flush();
            return new Response(json_encode(array("success" => true)));
        }

    }

    public function getNameAction($id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $name = $project->getName();
        return new Response(json_encode(array("success" => true, "response" => $name)));
    }

    public function getGitUrlAction($id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $name = $project->getGitUrl();
        return new Response(json_encode(array("success" => true, "response" => $name)));
    }

    /*
     *  This function returns the projectFilesId of the first WiselibClone of the user
     */

    public function getProjectFilesIdAction($id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $name = $project->getProjectfilesId();
        return new Response(json_encode(array("success" => true, "response" => $name)));
    }

    public function getGitCommitSHAAction($id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $name = $project->getGitCommitSHA();
        return new Response(json_encode(array("success" => true, "response" => $name)));
    }

    public function getParentAction($id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $parent = $project->getParent();
        if ($parent != NULL) {
            $exists = $this->checkExistsAction($parent)->getContent();
            $exists = json_decode($exists, true);
            if ($exists["success"]) {

                $response = array("id" => $parent, "owner" => $project->getOwner()->getUsername(), "name" => $project->getName());
                return new Response(json_encode(array("success" => true, "response" => $response)));
            }
        }

        return new Response(json_encode(array("success" => false)));
    }

    public function getOwnerAction($id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $user = $project->getOwner();
        $response = array("id" => $user->getId(), "username" => $user->getUsername(), "firstname" => $user->getFirstname(), "lastname" => $user->getLastname());
        return new Response(json_encode(array("success" => true, "response" => $response)));
    }

    public function getUserAccessTokenAction()
    {
        return $this->sc->getToken()->getUser()->getAccessToken();
    }

    public function getIsWiselibClone($id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $response = $project->getIsWiselibClone();
        return new Response(json_encode(array("success" => true, "response" => $response)));
    }

    public function getDescriptionAction($id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $response = $project->getDescription();
        return new Response(json_encode(array("success" => true, "response" => $response)));
    }

    public function getPrivacyAction($id)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $response = $project->getIsPublic();
        return new Response(json_encode(array("success" => true, "response" => $response)));
    }

    public function setDescriptionAction($id, $description)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $project->setDescription($description);
        $em = $this->em;
        $em->persist($project);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }

    public function setGitUrlAction($id, $git_url)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $project->setGitUrl($git_url);
        $em = $this->em;
        $em->persist($project);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }

    public function setGitCommitSHAAction($id, $git_commit_sha)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);
        $project->setGitCommitSHA($git_commit_sha);
        $em = $this->em;
        $em->persist($project);
        $em->flush();
        return new Response(json_encode(array("success" => true)));
    }

    public function listFilesAction($id)
    {
        $project = $this->getProjectById($id);
        $permissions = json_decode($this->checkReadProjectPermissions($id), true);
        if ($permissions["success"]) {
            $list = $this->fc->listFilesAction($project->getProjectfilesId());
            return new Response($list);
        } else return new Response(json_encode($permissions));
    }

    public function listWiselibFilesAction($id)
    {
        $project = $this->getProjectById($id);
        $permissions = json_decode($this->checkReadProjectPermissions($id), true);
        if ($permissions["success"]) {
            $list = $this->fc->listWiselibFiles($project->getProjectfilesId());
            $arr = array();
            if ($list) {
                $arr = array("success" => true, "file" => $list);
            } else {
                $arr = array("success" => false, "file" => "");
            }
            return new Response(json_encode($arr));
        } else return new Response(json_encode($permissions));
    }

    public function getFileCode($file, $project_filesId)
    {
        $list = $this->fc->getFileCode($file, $project_filesId);
        return new Response($list);
    }

    public function getFilesCode($file, $project_filesId)
    {
        $list = $this->fc->getFilesCode($project_filesId);
        return new Response($list);
    }

    public function createFileAction($id, $filename, $code)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);

        $canCreate = json_decode($this->canCreateFile($project->getId(), $filename), true);
        if ($canCreate["success"]) {
            $create = $this->fc->createFileAction($project->getProjectfilesId(), $filename, $code);
            $retval = $create;
        } else {
            $retval = json_encode($canCreate);
        }
        return new Response($retval);

    }

    /*
     *  $rdir: This is the relative path of the file in the project without the beginning or trailing slash
     *         Example: For a file 'wiselib.stable/algorithms/routing/dsdv/dsdv.h'
     *                  $rdir = 'wiselib.stable/algorithms/routing/dsdv'
     */

    public function createWiselibFileAction($id, $rdir, $filename, $code, $folder = false)
    {
        //$perm = json_decode($this->checkWriteProjectPermissions($id), true);
        //if(!$perm['success'])
        //{
        //    return new Response(json_encode($perm));
        //}
        $project = $this->getProjectById($id);

        $canCreate = json_decode($this->canCreateFile($project->getId(), $filename), true);
        //return new Response(json_encode($canCreate));
        if ($canCreate["success"]) {
            $create = $this->fc->createWiselibFileAction($project->getProjectfilesId(), $rdir, $filename, $code, $folder);
            $retval = $create;
        } else {
            $retval = json_encode($canCreate);
        }
        return new Response($retval);
    }


    public function deleteWiselibFolderAction($id, $fpath){
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }

        $project = $this->getProjectById($id);
        $deletion = json_decode($this->fc->deleteFolderAction($project->getProjectfilesId(), $fpath), true);

        if ($deletion["success"] == true) {
            return new Response(json_encode(array("success" => true)));
        } else {
            return new Response(json_encode(array("success" => false, "id" => $project->getProjectfilesId())));
        }
    }

    public function deleteWiselibFileAction($id, $fpath){
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }

        $project = $this->getProjectById($id);
        $deletion = json_decode($this->fc->deleteWiselibFileAction($project->getProjectfilesId(), $fpath), true);

        if ($deletion["success"] == true) {
            return new Response(json_encode(array("success" => true)));
        } else {
            return new Response(json_encode(array("success" => false, "id" => $project->getProjectfilesId())));
        }
    }

    public function getFileAction($id, $filename)
    {
        $perm = json_decode($this->checkReadProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);

        $get = $this->fc->getFileAction($project->getProjectfilesId(), $filename);
        return new Response($get);

    }

    public function setFileAction($id, $filename, $code)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);

        $set = $this->fc->setFileAction($project->getProjectfilesId(), $filename, $code);
        return new Response($set);

    }

    public function setWiselibFileAction($id, $project_id, $filename, $code,$is_wiselib_clone=true)
    {
        $project = $this->getProjectById($project_id);
        $set = $this->fc->setWiselibFileAction($id, $project->getProjectfilesId(), $filename, $code,$is_wiselib_clone);
        return new Response($set);
    }

    public function deleteFileAction($id, $filename)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);

        $delete = $this->fc->deleteFileAction($project->getProjectfilesId(), $filename);
        return new Response($delete);
    }

    public function renameFileAction($id, $filename, $new_filename)
    {
        $perm = json_decode($this->checkWriteProjectPermissions($id), true);
        if (!$perm['success']) {
            return new Response(json_encode($perm));
        }
        $project = $this->getProjectById($id);

        $delete = $this->fc->renameFileAction($project->getProjectfilesId(), $filename, $new_filename);
        return new Response($delete);
    }

    public function searchAction($token)
    {
        $results_name = json_decode($this->searchNameAction($token)->getContent(), true);
        $results_desc = json_decode($this->searchDescriptionAction($token)->getContent(), true);
        $results = $results_name + $results_desc;
        return new Response(json_encode($results));
    }

    public function searchNameAction($token)
    {
        $em = $this->em;
        $repository = $this->em->getRepository('AceProjectBundle:Project');
        $projects = $repository->createQueryBuilder('p')->where('p.name LIKE :token')->setParameter('token', "%" . $token . "%")->getQuery()->getResult();
        $result = array();
        foreach ($projects as $project) {
            $permission = json_decode($this->checkReadProjectPermissions($project->getId()), true);
            if ($permission["success"]) {
                $owner = json_decode($this->getOwnerAction($project->getId())->getContent(), true);
                $owner = $owner["response"];
                $proj = array("name" => $project->getName(), "description" => $project->getDescription(), "owner" => $owner);
                $result[$project->getId()] = $proj;
            }
        }
        return new Response(json_encode($result));
    }

    public function searchDescriptionAction($token)
    {
        $em = $this->em;
        $repository = $this->em->getRepository('AceProjectBundle:Project');
        $qb = $em->createQueryBuilder();
        $projects = $repository->createQueryBuilder('p')->where('p.description LIKE :token')->setParameter('token', "%" . $token . "%")->getQuery()->getResult();
        $result = array();
        foreach ($projects as $project) {
            $permission = json_decode($this->checkReadProjectPermissions($project->getId()), true);
            if ($permission["success"]) {
                $owner = json_decode($this->getOwnerAction($project->getId())->getContent(), true);
                $owner = $owner["response"];
                $proj = array("name" => $project->getName(), "description" => $project->getDescription(), "owner" => $owner);
                $result[$project->getId()] = $proj;
            }
        }
        return new Response(json_encode($result));
    }

    public function checkExistsAction($id)
    {
        $em = $this->em;
        $project = $this->em->getRepository('AceProjectBundle:Project')->find($id);
        if (!$project)
            return new Response(json_encode(array("success" => false)));
        return new Response(json_encode(array("success" => true)));
    }

    public function getProjectById($id)
    {
        $em = $this->em;
        $project = $this->em->getRepository('AceProjectBundle:Project')->find($id);
        if (!$project)
            throw $this->createNotFoundException('No project found with id ' . $id);
        // return new Response(json_encode(array(false, "Could not find project with id: ".$id)));

        return $project;
    }


    public function getWiselibCloneProjects($user_id)
    {
        $em = $this->em;
        $projects = $this->em->getRepository('AceProjectBundle:Project')->findBy(array("owner" => $user_id, 'is_wiselib_clone' => '1'));
        $result = array();
        if (!$projects) {
            //return $result;
            //throw $this->createNotFoundException('No project found');
            return new Response(json_encode(array("success" => false, "message" => "No Wiselib clones found for user : ".$user_id)));
        }

        foreach ($projects as $project) {
            $permission = json_decode($this->checkReadProjectPermissions($project->getId()), true);
            if ($permission["success"]) {
                $owner = json_decode($this->getOwnerAction($project->getId())->getContent(), true);
                $owner = $owner["response"];

                $projectfiles_id = $project->getProjectfilesId();
                if(strstr($projectfiles_id, "/")){
                    $projectfiles_id = explode("/", $projectfiles_id);
                }
                $projectfiles_id = trim($projectfiles_id[1]);

                $proj = array("name" => $project->getName(), "pf_id" => $projectfiles_id, "description" => $project->getDescription(), "owner" => $owner);
                $result[$project->getId()] = $proj;
            }
        }
        return new Response(json_encode($result));
    }

    public function checkWriteProjectPermissionsAction($id)
    {
        $perm = $this->checkWriteProjectPermissions($id);
        return new Response($perm);
    }

    public function checkReadProjectPermissionsAction($id)
    {
        $perm = $this->checkReadProjectPermissions($id);
        return new Response($perm);
    }

    public function currentPrivateProjectRecordsAction()
    {
        $current_user = $this->sc->getToken()->getUser();

        if ($current_user !== "anon.") {
            $prv = $this->em->getRepository('AceProjectBundle:PrivateProjects')->findByOwner($current_user->getId());
            $records = array();
            foreach ($prv as $p) {
                $now = new \DateTime("now");
                if ($now >= $p->getStarts() && ($p->getExpires() == NULL || $now < $p->getExpires()))
                    $records[] = array("description" => $p->getDescription(),
                        "expires" => $p->getExpires() === null ? $p->getExpires() : $p->getExpires()->format('Y-m-d'),
                        "number" => $p->getNumber());
            }
            return new Response(ProjectErrorsHelper::success(ProjectErrorsHelper::SUCC_CUR_USER_PRIV_PROJ_RECORDS_MSG, array("list" => $records)));
        } else
            return new Response(ProjectErrorsHelper::fail(ProjectErrorsHelper::FAIL_CUR_USER_PRIV_PROJ_RECORDS_MSG));

    }

    public function canCreatePrivateProjectAction($owner)
    {
        $canCreate = $this->canCreatePrivateProject($owner);
        return new Response($canCreate);
    }


    protected function canCreatePrivateProject($owner)
    {
        $projects = $this->em->getRepository('AceProjectBundle:Project')->findByOwner($owner);
        $currentPrivate = 0;
        foreach ($projects as $p) {
            if (!$p->getIsPublic()) {
                $currentPrivate++;
            }
        }

        $prv = $this->em->getRepository('AceProjectBundle:PrivateProjects')->findByOwner($owner);
        $maxPrivate = 0;
        foreach ($prv as $p) {
            $now = new \DateTime("now");
            if ($now >= $p->getStarts() && ($p->getExpires() == NULL || $now < $p->getExpires()))
                $maxPrivate += $p->getNumber();
        }

        if ($currentPrivate >= $maxPrivate)
            return json_encode(array("success" => false, "error" => "Cannot create private project."));
        else
            return json_encode(array("success" => true, "available" => $maxPrivate - $currentPrivate));

    }

    protected function canCreateFile($id, $filename)
    {
        return json_encode(array("success" => true));
    }

    protected function nameIsValid($name)
    {
        $project_name = str_replace(".", "", trim(basename(stripslashes($name)), ".\x00..\x20"));
        if ($project_name == $name)
            return json_encode(array("success" => true));
        else
            return json_encode(array("success" => false, "error" => "Invalid Name. Please enter a new one."));
    }

    protected function checkReadProjectPermissions($id)
    {
        $project = $this->getProjectById($id);
        $current_user = $this->sc->getToken()->getUser();

        if ($project->getIsPublic() ||
            ($current_user !== "anon." && $current_user->getID() === $project->getOwner()->getID())
        )
            return ProjectErrorsHelper::success(ProjectErrorsHelper::SUCC_READ_PERM_MSG);
        else
            return ProjectErrorsHelper::fail(ProjectErrorsHelper::FAIL_READ_PERM_MSG, array("error" => "You have no read permissions for this project.", "id" => $id));


    }

    protected function checkWriteProjectPermissions($id)
    {
        $project = $this->getProjectById($id);
        $current_user = $this->sc->getToken()->getUser();

        if (($current_user !== "anon." && $current_user->getID() === $project->getOwner()->getID()))
            return ProjectErrorsHelper::success(ProjectErrorsHelper::SUCC_WRITE_PERM_MSG);
        else
            return ProjectErrorsHelper::fail(ProjectErrorsHelper::FAIL_WRITE_PERM_MSG, array("error" => "You have no write permissions for this project.", "id" => $id));

    }

    protected function nameExists($owner, $name)
    {
        $userProjects = json_decode($this->listAction($owner)->getContent(), true);

        foreach ($userProjects as $p) {
            if ($p["name"] == $name) {
                return json_encode(array("success" => true));
            }
        }
        return json_encode(array("success" => false));
    }

    public function __construct(EntityManager $entityManager, FilesController $filesController, SecurityContext $securitycontext)
    {
        $this->em = $entityManager;
        $this->sc = $securitycontext;
        $this->fc = $filesController;
    }
}
