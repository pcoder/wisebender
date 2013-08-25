<?php
// src/Ace/ProjectBundle/Controller/DiskFilesController.php

namespace Ace\ProjectBundle\Controller;

use Ace\ProjectBundle\Helper\ProjectErrorsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


abstract class FilesController extends Controller
{

    public abstract function createAction();

    public abstract function deleteAction($id);

    public abstract function listFilesAction($id);

    public abstract function getFileCode($file, $project_filesId);

    public abstract function getFilesCode($project_filesId);

    public abstract function createFileAction($id, $filename, $code);

    public abstract function createWiselibFileAction($id, $rdir, $filename, $code, $folder=false);

    public abstract function getFileAction($id, $filename);

    public abstract function setFileAction($id, $filename, $code);

    public abstract function deleteFileAction($id, $filename);

    public abstract function renameFileAction($id, $filename, $new_filename);

    protected abstract function listFiles($id);

    public abstract function listWiselibFiles($id);

    public abstract function copyWiselibFiles($id);

    protected function fileExists($id, $filename)
    {
        $list = $this->listFiles($id);
        foreach($list as $file)
        {
            if($file["filename"] == $filename)
                return ProjectErrorsHelper::success(ProjectErrorsHelper::SUCC_FILE_EXISTS_MSG);
        }

        return ProjectErrorsHelper::fail(ProjectErrorsHelper::FAIL_FILE_EXISTS_MSG, array("filename" => $filename, "error" => "File ".$filename." does not exist."));
    }

    protected function canCreateFile($id, $filename)
    {
        $fileExists = json_decode($this->fileExists($id,$filename),true);
        if(!$fileExists["success"])
            return ProjectErrorsHelper::success(ProjectErrorsHelper::SUCC_CAN_CREATE_FILE_MSG);
        else
            return ProjectErrorsHelper::fail(ProjectErrorsHelper::FAIL_CAN_CREATE_FILE_MSG, array("id" => $id, "filename" => $filename, "error" => "This file already exists"));


    }

}

