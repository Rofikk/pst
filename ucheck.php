<?php

include('include/config.php');
error_reporting(E_ALL); # report all errors
ini_set("display_errors", "1"); # but do not echo the errors
define('ADODB_ERROR_LOG_TYPE',3);
define('ADODB_ERROR_LOG_DEST',$root_directory.'db/dberrors.log');
include('db/adodb-errorhandler.inc.php');

include('db/adodb.inc.php');
include('db/db.php');
include ('include/functions.php');



if(isset($_POST['Username'])){
	print check_username($_POST['Username']);
	
}
?>