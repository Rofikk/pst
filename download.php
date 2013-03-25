<?
session_start();
include('./include/config.php');
error_reporting(E_ALL); # report all errors
ini_set("display_errors", "0"); # but do not echo the errors
define('ADODB_ERROR_LOG_TYPE',3);
define('ADODB_ERROR_LOG_DEST',$root_directory.'db/dberrors.log');
include('db/adodb-errorhandler.inc.php');

include('db/adodb.inc.php');
include('db/db.php');
include ('include/functions.php');

$decrypted = decrypt($_GET['token'], $secretCryptKey);

$download = explode("|", $decrypted);



if(is_numeric($download[1]) && is_numeric($download[3]) && is_numeric($download[5])){
	$ClientID = $download[1];
	$LocationID = $download[3];
	$ChecklistID = $download[5];
	
	$type = $_GET['type'];
	if($type == "pdf"){
		ob_end_clean ();
	include($root_directory.'include/pdf.php');
	completed_pdf_checklist($ChecklistID, 'D');
	}
	if($type == "zip"){
		prepare_client_zip($ChecklistID);
	}
	if($type == "newzip"){
		new_prepare_client_zip($ChecklistID);
	}
}
?>