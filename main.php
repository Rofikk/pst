<?php
/*
if(!$_POST){
$ip=$_SERVER['REMOTE_ADDR'];
if($ip != "209.249.63.133"){
	echo "Site is in maintance mode, please try again later";
	exit;
}
}

 * 
 */


session_start();
if(isset($_GET['page'])){
$page = $_GET['page'];}

include('include/config.php');
error_reporting(E_ALL); # report all errors
ini_set("display_errors", "0"); # but do not echo the errors
define('ADODB_ERROR_LOG_TYPE',3);
define('ADODB_ERROR_LOG_DEST',$root_directory.'db/dberrors.log');
include('db/adodb-errorhandler.inc.php');

include('db/adodb.inc.php');
include('db/db.php');
include ('include/functions.php');



if(isset($_GET['type']) && ($_GET['type'] != '')){
	$type = $_GET['type'];	
}
if(isset($_GET['action'])){
if($_GET['action']){
	include('include/action_handler.php');
	action_handler($_GET['action'], $_POST);
}}



if($_SESSION['UserGroup'] == 3){
	include('pages/admin_html.php');
}
else if($_SESSION['UserGroup'] == 2){
	include('pages/client_html.php');
}
else if($_SESSION['UserGroup'] == 1){
	include('pages/client_html.php');
}
else {
include('pages/page_html.php');
}

//Set Page Content and custom page variables here
$pagefunction = page_chooser($type.$page, $_SESSION['UserID'], $_SESSION['UserGroup']).'page';


$mainpage = $pagefunction();
//$mainpage = ${page_chooser($type.$_GET['page'], $_SESSION['UserID'], $_SESSION['UserGroup'])};


include('pages/page_base.php');




echo $html;

?>