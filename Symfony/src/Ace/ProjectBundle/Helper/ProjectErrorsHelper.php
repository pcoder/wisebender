<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Alex
 * Date: 14/5/13
 * Time: 3:49 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Ace\ProjectBundle\Helper;


use Assetic\Test\Factory\Resource\CoalescingDirectoryResourceTest;

class ProjectErrorsHelper {

    const SUCC_SAVE_MSG = "Saved successfully.";
    const FAIL_SAVE_MSG = "Save failed.";
    const SUCC_DELETE_PROJ_MSG = "Project deleted successfully.";
    const FAIL_DELETE_PROJ_MSG = "Project could not be deleted.";
    const SUCC_DELETE_FILE_MSG = "File deleted successfully.";
    const FAIL_DELETE_FILE_MSG = "File could not be deleted.";
    const SUCC_CREATE_PROJ_MSG = "Project created successfully.";

    const SUCC_CREATE_FILE_MSG = "File created successfully.";

    const SUCC_FILE_EXISTS_MSG = "File exists.";
    const FAIL_FILE_EXISTS_MSG = "File does not exist.";

    const SUCC_CAN_CREATE_FILE_MSG = "File can be created.";
    const FAIL_CAN_CREATE_FILE_MSG = "File cannot be created.";

    const SUCC_RENAME_FILE_MSG = "File renamed successfully.";
    const FAIL_RENAME_FILE_MSG = "File could not be renamed.";

    const SUCC_READ_PERM_MSG = "Read permissions granted.";
    const FAIL_READ_PERM_MSG = "Read permissions not granted.";

    const SUCC_WRITE_PERM_MSG = "Write permissions granted.";
    const FAIL_WRITE_PERM_MSG = "Write permissions not granted.";

	const SUCC_CUR_USER_PRIV_PROJ_RECORDS_MSG = "User records retreived successfuly.";
	const FAIL_CUR_USER_PRIV_PROJ_RECORDS_MSG = "User not logged in";

    const INITIAL_COMMIT_MESSAGE = "This is a [Wiselib](https://github.com/ibr-alg/wiselib/wiki)-based application created using [Wisebender](https://github.com/pcoder/wisebender).";

    const SUCC_DELETE_FOLDER_MSG = "Folder was deleted successfully.";
    const FAIL_DELETE_FOLDER_MSG = "Folder could not be deleted.";

	public static function success($msg, $info = array())
    {
        $toReturn = array("success" => true, "message" => $msg) + $info;
        return json_encode($toReturn);
    }

    public static function fail($msg, $info = array())
    {
        $toReturn = array("success" => false, "message" => $msg) + $info;
        return json_encode($toReturn);
    }


}