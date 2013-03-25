<?php
/*
|-----------------
| Chip Error Manipulation
|------------------
*/
session_start();
error_reporting(E_ALL); # report all errors
ini_set("display_errors", "0"); # but do not echo the errors

include('../config.php');
include('../functions.php');
include('../../db/adodb.inc.php');
include('../../db/db.php');

if(!authenticated($_SESSION)){
print "Not Authorized to view this page";
	exit();	
}
$ChecklistID = $_GET['cl_id'];
$QuestionID = $_GET['q_id'];
$FileName = urldecode($_GET['file']);
if(isset($_GET['pstype'])){
$deleteLink = '../../main.php?pstype='.$_GET['pstype'].'&action=deletedoc&cl_id='.$_GET['cl_id'].'&q_id=0&file='.$_GET['file'];
$cancelLink = 'psummary.php?pstype='.$_GET['pstype'].'&cl_id='.$_GET['cl_id'].'&q_id=0';

}
else{
$deleteLink = '../../main.php?action=deletedoc&cl_id='.$_GET['cl_id']."&q_id=".$_GET['q_id'].'&file='.$_GET['file'];
$cancelLink = 'index.php?cl_id='.$_GET['cl_id'].'&q_id='.$_GET['q_id'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="all" href="style.css" />
<title>Document Management</title>
</head>

<body>

<div id="wrap">
  <div id="wrapdata">
    
    
    <div id="header">
      <div id="headerdata">      
        
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">          
            <h2 class="margin0">Delete File</h2>
          </div>
        </div>
        
            
      </div>
    </div>
    
    <div id="content">
      <div id="contentdata">
        
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">          
<strong>Are you sure you want to remove the file: <?php echo $FileName; ?>?</strong><br><br>
<div align="center">
<strong><a href="<?php echo $deleteLink; ?>">Delete</a><br><br>
<a href="<?php echo $cancelLink; ?>">Cancel</a></strong>
</div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</div>

</body>
</html>