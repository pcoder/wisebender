<?php
$request_body = file_get_contents('php://input');
$my_data = json_decode($request_body,true);

$PROJECTS_BASE_PATH="/wisebender/sketches/";
$basepath="/compiler/app/";
$uf_predicate="_app_" . @date('mdyHis');

$username=$my_data["username"];
$project=$my_data["project"];
$wiselibUUID=$my_data["wiselibUUID"];
$makeTarget=trim(strtolower($my_data["build"]));

switch($makeTarget){
	case "isense":
		$makeTarget = "isense";
		break;
	case "isense5148":
		$makeTarget = "isense.5148";
		break;
	case "shawn":
		$makeTarget = "shawn";
		break;
}

$user_com_folder = $basepath . $username . $uf_predicate;

if(!file_exists($user_com_folder)){
	@mkdir($user_com_folder);
    $makefile = file_get_contents($basepath . "Makefile");
    file_put_contents($user_com_folder. DIRECTORY_SEPARATOR . "Makefile", $makefile);
} 

foreach ($my_data['files'] as $key => $value){

if(strcmp($value["filename"], $project . "_app.cpp") ==0){
        file_put_contents($user_com_folder . DIRECTORY_SEPARATOR . "app.cpp", html_entity_decode($value['content']), LOCK_EX);
}else{
        file_put_contents($user_com_folder . DIRECTORY_SEPARATOR . $value["filename"], html_entity_decode($value['content']), LOCK_EX);
}
}

chdir($user_com_folder);

if(trim(strtolower($wiselibUUID)) == "default"){
    // compile the app. against the latest Wiselib source
	$PROJECTS_BASE_PATH="/var/www/wisebender/Symfony/wiselib/";
    	file_put_contents($user_com_folder. DIRECTORY_SEPARATOR . "Makefile.path" ,
	"export WISELIB_BASE=".$PROJECTS_BASE_PATH ,LOCK_EX);
}else{
	file_put_contents($user_com_folder.DIRECTORY_SEPARATOR . "Makefile.path" ,
	"export WISELIB_BASE=".$PROJECTS_BASE_PATH.$username."/".$wiselibUUID."/",LOCK_EX);
}
exec ("make ".$makeTarget." 2>&1",$retstr,$retval);

$tmpfname = "";
if($makeTarget == 'shawn'){
	$isense_sub_path="out/app";
	$tmpfname = tempnam($basepath . "out/binary/", "app_");
}else{
	$isense_sub_path="out/isense/app.bin";
	$tmpfname = tempnam($basepath . "out/binary/", "app_");
}
	$size = filesize ( $basepath.$isense_sub_path );

//    echo ("iss = " . $isense_sub_path . ", reading file =" .$user_com_folder. DIRECTORY_SEPARATOR. $isense_sub_path);
//    $app = file_get_contents($user_com_folder. DIRECTORY_SEPARATOR. $isense_sub_path);
 //   file_put_contents($tmpfname, $app);

copy( $user_com_folder. DIRECTORY_SEPARATOR. $isense_sub_path , $tmpfname );

$response["success"]=$retval===0;
$response["size"]=$size;
$response["message"]=implode("<br/>",$retstr);

#if($makeTarget == 'shawn'){
#	$response['message'] = "Not implemented yet. Please try again in some days.";
#	$response["success"] = false;
#}

$response["output"]= basename($tmpfname);
print_r(json_encode($response));
?>
