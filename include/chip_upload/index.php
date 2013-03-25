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

/*
|-----------------
| Chip Constant Manipulation
|------------------
*/


define( "CHIP_DEMO_FSROOT",				__DIR__ . "/" );
$upload_directory = $upload_root_directory.$_GET['cl_id']."/".$_GET['q_id']."/";



/*
|-----------------
| POST
|------------------
*/

if( $_POST ) {
	
	/*
	|-----------------
	| Chip Upload Class
	|------------------
	*/
	
	require_once("class.chip_upload.php");
	
	/*
	|-----------------
	| Upload(s) Directory
	|------------------
	*/
	
	//$upload_directory = "/Applications/XAMPP/xamppfiles/htdocs/pstcompliance/uploads/".$_GET['cl_id']."/".$_GET['q_id'];
	
	/*
	|-----------------
	| Class Instance
	|------------------
	*/
	
	$object = new chip_upload();
	
	/*
	|-----------------
	| $_FILES Manipulation
	|------------------
	*/
	
	$files = $object->get_upload_var( $_FILES['upload_file'] );
	//$object->chip_print( $files );
	
	/*
	|-----------------
	| Upload File
	|------------------
	*/
	
	foreach( $files as $file ) {
	
		/*
		|---------------------------
		| Upload Inputs
		|---------------------------
		*/
		
		$args = array(
			  'upload_file'			=>	$file,
			  'upload_directory'	=>	$upload_directory,
			  'allowed_size'		=>	52428800,
			  'extension_check'		=>	FALSE,
			  'upload_overwrite'	=>	FALSE,
		  );
		  
		$allowed_extensions = array(
			'pdf'	=> FALSE,
			'psd'	=> FALSE,
			'csv'	=> FALSE,
		);
		
		/*
		|---------------------------
		| Upload Hook
		|---------------------------
		*/		
		
		$upload_hook = $object->get_upload( $args, $allowed_extensions );		
		//$object->chip_print( $upload_hook );
		//exit;
		
		/*
		|---------------------------
		| Move File
		|---------------------------
		*/
		
		if( $upload_hook['upload_move'] == TRUE ) {
			
			/*
			|---------------------------
			| Any Logic by User
			|---------------------------
			*/
			
			//add_question_doc($_GET['cl_id'], $_GET['q_id'], $upload_directory, $filetype);
			
			/*
			|---------------------------
			| Move File
			|---------------------------
			*/
			
			$upload_output[] = $object->get_upload_move();
			//$object->chip_print( $upload_output );
		
		} else {
		
			/*$temp['uploaded_status'] = FALSE;
			$temp['uploaded_file'] = $upload_hook['upload_file']['name'] ;
			
			$upload_output[] = $temp;*/
		
		}
		
	
	} // foreach( $files as $file )
	
	//$object->chip_print( $upload_output );
foreach($upload_output as $fileinfo){
	if($fileinfo['uploaded_status'] == 1){
		add_question_doc($_GET['cl_id'], $_GET['q_id'], $fileinfo['uploaded_directory'], $fileinfo['uploaded_file'].".".$fileinfo['uploaded_extension'], $fileinfo['uploaded_extension']);
	}
}


} // if( $_POST )

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="all" href="style.css" />
<title>Document Upload</title>
</head>

<body>

<div id="wrap">
  <div id="wrapdata">
    
    
    <div id="header">
      <div id="headerdata">      
        
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">          
            <h2 class="margin0">Document Management</h2>
          </div>
        </div>
        
            
      </div>
    </div>
    
    <div id="content">
      <div id="contentdata">
        <?php if( !empty($upload_output) ): ?>
        <?php
        //$object->chip_print( $upload_output );
		foreach( $upload_output as $val ):
		?>
        <div class="chipboxw1 chipstyle2">
          <div class="chipboxw1data">          
            <h2 class="margin0"><?php echo $val['uploaded_file'] . "." . $val['uploaded_extension'] . " Uploaded"; ?></h2>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php 
        if(isset($_GET['notify']))
        if( $_GET['notify'] == 'success' ): ?>
        <div class="chipboxw1 chipstyle2">
          <div class="chipboxw1data">          
            <h2 class="margin0"><?php echo urldecode($_GET['file']) . " Deleted"; ?></h2>
          </div>
        </div>
        <?php endif; ?>
        
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">          
              <form method="post" action="" enctype="multipart/form-data">
                <p>Upload File: <input name="upload_file[]" id="upload_file[]" type="file" class="inputtext" /></p> 
                <p>Upload File: <input name="upload_file[]" id="upload_file[]" type="file" class="inputtext" /></p>
                <p>Upload File: <input name="upload_file[]" id="upload_file[]" type="file" class="inputtext" /></p>
                <p>Upload File: <input name="upload_file[]" id="upload_file[]" type="file" class="inputtext" /></p>
                <p>Upload File: <input name="upload_file[]" id="upload_file[]" type="file" class="inputtext" /></p>                 
                <p></p>
                <input type="submit" name="submit" value="Upload File" />
                <p>Maximum size per upload: 20 M (total of all files combined)</p>
              </form>
          </div>
        </div>
        <div class="chipboxw1 chipstyle1">
          <div class="chipboxw1data">          
            <h2 class="margin0">Current Documents</h2>
            <?php
            $a = get_directory_list($upload_directory);
			if($a){
				echo '<table width="550">'."\n";
            foreach ($a as $v) {
            		echo '<tr><td>File Name: </td><td>'.$v.'</td><td><a href="../../uploads/'.$_GET['cl_id']."/".$_GET['q_id']."/".$v.'">Open</a></td>';
					echo '<td><a href="prompt.php?cl_id='.$_GET['cl_id']."&q_id=".$_GET['q_id'].'&file='.urlencode($v).'">Delete</a></td></tr>'."\n";
					//echo '<p>File Name: '.$v.'</p>'."\n";
				}
			echo '</table>';
			}
            ?>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</div>

</body>
</html>