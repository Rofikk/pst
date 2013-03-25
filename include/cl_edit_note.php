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
if( $_POST ){
	
	edit_checklist_note($_POST['checklist_note_id'], $_POST['note'], $_POST['date'], $_POST['alert']);
	header( "Location: cl_options.php?cl_id={$_POST['checklist_id']}" );

}

$note = get_note($_GET['note']);

?>
<?php 
if(isset($_GET['action']) && $_GET['action'] == 'delete'){
	
	delete_checklist_note($_GET['note']);
	header( "Location: cl_options.php?cl_id={$_GET['checklist_id']}" );
}
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
            <h2 class="margin0">Edit Checklist Note</h2>
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
            <h2 class="margin0"></h2>
          </div>
        </div>
        <?php endif; ?>
        
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">
          	<form method="post" action="" >
			<table width="800">
				<tr>
					<td>Date</td><td><input name="date" type="text" id="date" value="<?php echo $note['Date']; ?>" /></td>
				</tr>
				<tr>
					<td>Note</td><td><input name="note" type="text" id="note" size="50" value="<?php echo $note['Note']; ?>" /></td>
				</tr>
				<tr>
					<td>Alert</td><td><input name="alert" type="checkbox" id="alert" value="1" <?php echo ($note['Alert'] == '1' ? 'checked="checked"' : '') ?> </td>
				</tr>
			</table>
<div align="center"><input type="submit" name="submit" value="Update Note" /></div>
<input type="hidden" name="checklist_note_id" value="<?php echo $note['NoteID']; ?>" />
<input type="hidden" name="checklist_id" value="<?php echo $note['ChecklistID']; ?>" />
          	</form>        
          </div>
        </div>
        
      </div>
    </div>
  </div>
</div>

</body>
</html>