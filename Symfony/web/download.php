<?php
$basepath="/compiler/app/";
$isense_sub_path="out/binary/";

$fname=$_GET["fname"];
$pname=$_GET["pname"];
$platform=trim(strtolower($_GET["platform"]));

if(trim($pname) == "")
{
	$pname="app";
}

if($platform != "shawn")
	$pname.=".bin";

$ret = array();
$file = $basepath . $isense_sub_path . $fname; 
if(file_exists($file )){

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='. $pname);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
	unlink($file);
    exit;	
}else{
	$ret['success'] = false;
	$ret['message'] = "File not found";
	print_r(json_encode($ret));
}
?>
