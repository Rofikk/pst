<?php

session_start();


include('./config.php');
include('./functions.php');
include('../db/adodb.inc.php');
include('../db/db.php');
if(!authenticated($_SESSION)){
print "Not Authorized to view this page";
	exit();	
}
function dateconvert($date,$func) {
	if($date != ""){
if ($func == 1){ //insert conversion
list($month, $day, $year) = split('[/.-]', $date);
$date = "$year-$month-$day";
return $date;
}
if ($func == 2){ //output conversion
list($year, $month, $day) = split('[-.]', $date);
$date = "$month-$day-$year";
return $date;
}}

} 
if( $_POST ){
	if($_POST['formsubmit'] == "details"){
$project_type = $_POST['project_type'];  // Retrieve POST data
$tank_type = $_POST['tank_type'];
if(isset($project_type)){  // Check if selections were made

// Serialize

$project_type = serialize($project_type);
}
if(isset($tank_type)){
	
	$tank_type = serialize($tank_type);
}
update_checklist_options($_POST['checklist_id'], $project_type, $tank_type, $_POST['prepared_by_name'], $_POST['prepared_by_phone'], $_POST['completed'], dateconvert($_POST['date_completed'], 1), $_POST['ccemail']);
	if($_POST['completed_status'] == 0 && $_POST['completed'] == 1){
	$ccemail = $_POST['ccemail'];
	if(strlen($ccemail)>0){
		$ccemail = $_POST['ccemail'];
	}
	else {
		$ccemail === false;	
	}
		
	checklist_notification($_POST['checklist_id'],$ccemail);
	
	}
	
	}

if($_POST['formsubmit'] == "notes"){
	
if(isset($_POST['alert']) && $_POST['alert'] == "1"){
	$alertbox = "1";
}
else $alertbox = "0";
add_checklist_note($_POST['checklist_id'], $_POST['note'], $_POST['date'], $alertbox);
	
}

}
$cl = get_checklist_options($_GET['cl_id']);

$ptype = unserialize($cl->fields[0]);
$ttype = unserialize($cl->fields[1]);
$prepared_name = $cl->fields[2];
$prepared_phone = $cl->fields[3];
$completed_date = dateconvert($cl->fields[4],2);
$completed = $cl->fields[5];
$ccemail = $cl->fields[6];

function p_type($field){
	global $ptype;
	if(is_array($ptype)){
if (in_array($field, $ptype)) {
    return "checked";
}	}
else return "";
}

function t_type($field){
	global $ttype;
	if(is_array($ttype)){
if (in_array($field, $ttype)) {
    return "checked";
}	}
else return "";
}



$notes = get_checklist_note($_GET['cl_id']);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="all" href="cl_style.css" />
<title>Checklist Details</title>
</head>

<body>

<div id="wrap">
  <div id="wrapdata">
    
    
    <div id="header">
      <div id="headerdata">      
        
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">          
            <h2 class="margin0">Checklist Details</h2>
          </div>
        </div>
        
            
      </div>
    </div>
    
    <div id="content">
      <div id="contentdata">
        <?php 
        if(isset($_GET['notify']))
        if( $_GET['notify'] == 'success' ): ?>
        <div class="chipboxw1 chipstyle2">
          <div class="chipboxw1data">          
            <h2 class="margin0">Checklist Successfully Updated</h2>
          </div>
        </div>
        <?php endif; ?>
        
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">
          	<form method="post" name="details" action="?notify=success&amp;cl_id=<?php echo $_GET['cl_id']; ?>" >
<table width="800" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td width="608" valign="top"><table width="600" border="0" cellspacing="2" cellpadding="2">
      <tr>
        <td>Project Type </td>
        <td><input name="project_type[]" type="checkbox" id="project_type" value="ptcr" <?php echo p_type('ptcr'); ?> /></td>
        <td>Permanent Tank Closure/Removal </td>
      </tr>
      <tr>
        <td>(check all that apply) </td>
        <td><input name="project_type[]" type="checkbox" id="project_type" value="putc" <?php echo p_type('putc'); ?>/></td>
        <td>Repair/Upgrade/Temporary Closure </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><input name="project_type[]" type="checkbox" id="project_type" value="tms" <?php echo p_type('tms'); ?>/></td>
        <td>Tank Monitoring System (any work affecting system) </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><input name="project_type[]" type="checkbox" id="project_type" value="nti" <?php echo p_type('nti'); ?>/></td>
        <td>New Tank Install </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Tank Type </td>
        <td><input name="tank_type[]" type="checkbox" id="tank_type" value="ust" <?php echo t_type('ust'); ?>/></td>
        <td>Underground Storage Tank </td>
      </tr>
      <tr>
        <td>(check all that apply) </td>
        <td><input name="tank_type[]" type="checkbox" id="tank_type" value="ast" <?php echo t_type('ast'); ?>/></td>
        <td>Aboveground Storage Tank </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Checklist Prepared By </td>
        <td>Name</td>
        <td><input name="prepared_by_name" type="text" id="prepared_by_name" value="<?php echo $prepared_name; ?>" /></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>Phone</td>
        <td><input name="prepared_by_phone" type="text" id="prepared_by_phone" value="<?php echo $prepared_phone; ?>" /></td>
      </tr>
    </table></td>
    <td width="178" valign="top"><table width="174" border="0" cellspacing="2" cellpadding="2">
      <tr>
        <td width="70">Completed</td>
        <td width="90"><input name="completed" type="checkbox" id="completed" value="1" <?php if($completed == 1) echo "checked"; ?> /></td>
      </tr>
      <tr>
        <td>CC Email:</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2"><input name="ccemail" type="text" id="ccemail" value="<?php echo $ccemail; ?>" /></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>Date (MM-DD-YYYY)</td>
        <td><input name="date_completed" type="text" id="date_completed" size="10" value="<?php echo $completed_date; ?>" /></td>
      </tr>
    </table>      <p>&nbsp;</p>
    </td>
  </tr>
</table>
<div align="center"><input type="submit" name="submit" value="Save Checklist Details" /></div>
<input type="hidden" name="checklist_id" value="<?php echo $_GET['cl_id']; ?>" />
<input type="hidden" name="completed_status" value="<?php echo $completed; ?>" />
<input type="hidden" name="formsubmit" value="details" />
          	</form>
          	<!--End chipboxw1data div-->
          </div>
          <!--End chipboxw1 chipstyle1  div-->
        </div>
        
       </div>
    </div>
        
            <div id="header">
      <div id="headerdata">      
        
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">          
            <h2 class="margin0">Notes</h2>
          </div>
        </div>
        
            
      </div>
    </div>
    
    <div id="content">
      <div id="contentdata">
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">          
            <form method="post" name="notes" id="notes" action="" >
            	<table width="800">
            		<tr>
            			<td>Add Note</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>
            		</tr>
            		<tr>
            			<td>Enter new note:</td><td><input type="text" name="note" size="50" /></td><td>Alert: <input type="checkbox" name="alert" value="1" /></td><td><input type="submit" name="submit" value="Add Note" /></td>
            		</tr>
            	</table>
            	<input type="hidden" name="checklist_id" value="<?php echo $_GET['cl_id']; ?>" />
            	<input type="hidden" name="formsubmit" value="notes" />
            	<input type="hidden" name="date" value="<?php echo date("Y-m-d"); ?>" />
            </form>
            <hr />
            <?php if($notes){ ?>
            <table width="800" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td width="77">Date</td>
    <td width="590">Note</td>
    <td width="28">Alert</td>
    <td width="39">&nbsp;</td>
    <td width="50">&nbsp;</td>
  </tr>
<?php
            	while(!$notes->EOF){
            		echo '<tr>'."\n";
					echo '<td>'.$notes->fields[0].'</td><td>'.$notes->fields[1].'</td><td>'.($notes->fields[3] == '1' ? 'Yes' : 'No').'</td><td><a href="cl_edit_note.php?note='.$notes->fields[2].'">Edit</a></td><td><a href="cl_edit_note.php?note='.$notes->fields[2].'&action=delete&checklist_id='.$_GET['cl_id'].'">Delete</a></td>'."\n";
					echo '</tr>'."\n";
            		$notes->MoveNext();
				} ?>
				</table>
				<?php
			}
			?>
          </div>
        </div>
        
      </div>
    </div>
  <!--End wrapdata div-->
  </div>
  <!--End  div-->
</div>

</body>
</html>