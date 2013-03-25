<?php
//include('../db/db.php');

function show_state_dropdown($selected = NULL){
$states_arr = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");
$string = '';
foreach($states_arr as $k => $v){
	$s = "";
if($selected == $k){$s = "selected";}
	$string .= '<option value="'.$k.'"'.$s.' >'.$v.'</option>'."\n";
}
return $string;

}

function encode_base64($sData){
	$sBase64 = base64_encode($sData);
	return str_replace('=', '', strtr($sBase64, '+/', '-_'));
}

function decode_base64($sData){
	$sBase64 = strtr($sData, '-_', '+/');
	return base64_decode($sBase64.'==');
}
function encrypt($sData, $secretKey){
    $sResult = '';
    for($i=0;$i<strlen($sData);$i++){
        $sChar    = substr($sData, $i, 1);
        $sKeyChar = substr($secretKey, ($i % strlen($secretKey)) - 1, 1);
        $sChar    = chr(ord($sChar) + ord($sKeyChar));
        $sResult .= $sChar;
    }
    return encode_base64($sResult);
} 
function decrypt($sData, $secretKey){
    $sResult = '';
    $sData   = decode_base64($sData);
    for($i=0;$i<strlen($sData);$i++){
        $sChar    = substr($sData, $i, 1);
        $sKeyChar = substr($secretKey, ($i % strlen($secretKey)) - 1, 1);
        $sChar    = chr(ord($sChar) - ord($sKeyChar));
        $sResult .= $sChar;
    }
    return $sResult;
}



function location_search($term){
	global $page;
	$search_results = search($term);
	if($search_results->RecordCount() == 1){
			$checklist_id = $search_results->fields[5];
			$client = $search_results->fields[8];
			$client_id = $search_results->fields[6];
			$location_id = $search_results->fields[0];
			$location_code = $search_results->fields[1];
			$location_name = $search_results->fields[2];
			$city = $search_results->fields[3];
			$state = $search_results->fields[4];
			$date = $search_results->fields[7];
			$completed = $search_results->fields[9];
			
			if($completed == 1){
				$link = "?page=client&type=overview&id={$client_id}&cl_id={$checklist_id}&node={$location_id}#view2";
			}
			else {
				$link = "?page=client&type=overview&id={$client_id}&cl_id={$checklist_id}&node={$location_id}#view1";
			}
		header("Location: {$link}");
	}
	else{
		$page = "searchresults";
		return $search_results;
	}
	
}

function delete_checklist($ChecklistID){
	global $upload_root_directory;
		$docs = get_doc_path($ChecklistID);
		while(!$docs->EOF){
			$path = $docs->fields[0];
			@unlink($path);
			echo $path."<br>";
			$docs->MoveNext();
		}

		
		$folders = get_checklist_questions($ChecklistID);
		while(!$folders->EOF){
			@rmdir($upload_root_directory.$ChecklistID."/".$folders->fields[0]);

			$folders->MoveNext();
		}
		@rmdir($upload_root_directory.$ChecklistID);
		delete_checklist_database($ChecklistID);
	
}



function checklist_notification($ChecklistID,$ccemail = false){
	global $root_directory,$site_url,$secretCryptKey,$notification_email;
	
	$linkdata = get_link_data_by_checklist($ChecklistID);
	$client = $linkdata['ClientID'];
	$location = $linkdata['LocationID'];
	
	$token = "CLIENTID|".$client."|LOCATIONID|".$location."|CHECKLISTID|".$ChecklistID;
	$encrypted = encrypt($token, $secretCryptKey);
	





$url = $site_url.'/main.php?page=download&token='.$encrypted;



	
	include($root_directory.'include/pdf.php');
	completed_pdf_checklist($ChecklistID, 'F');
	$tocount = count($linkdata['Users']);
	$to = "";
	foreach($linkdata['Users'] as $user){
	$tocount = $tocount - 1;
	$to .= $user[1];
	if($tocount != 0){
		$to .= ", ";
	}
	}
	$pos = strpos($to, "@");
	if ($pos !== false){
		$to .= ", ";
	}
	if($ccemail !== false){
		$ccemail = str_replace (" ", "", $ccemail);
		$ccs = explode(",",$ccemail);
		$cc_count = count($ccs);
		foreach($ccs as $cc){
			$cc_count = $cc_count - 1;
			$to .= $cc;
			
		if($cc_count != 0){
		$to .= ", ";
		}
		}
	
	}
	
	
    $from = $notification_email;



if(!$linkdata['Notes']){
	
	$subject = "PST Compliance - Report Complete - Location Code: {$linkdata['Location']['LOCATION_REF_ID']}";
	
	
	// Begin of Text Message
	$textmessage = "Your report is now complete and ready to be viewed.\n";
	$textmessage .= "Project Name: {$linkdata['Location']['NAME']}\n";
	$textmessage .= "Location Code: {$linkdata['Location']['LOCATION_REF_ID']}\n\n";
	$textmessage .= "You can now log in at https://client.pstcompliance.com to view project documents."."\n\n";
	$textmessage .= "To download the completed checklist in PDF format visit this URL:\n";
	$textmessage .= $url."&ftype=pdf"."\n\n";
	$textmessage .= "To download the report and all supporting documents as a ZIP file visit this URL:\n";
	$textmessage .= $url."&ftype=zip"."\n\n";
	$textmessage .= "Thank you for choosing PST Compliance!";

    // begin of HTML message
	$htmlbody = '
	Your report is now complete and ready to be viewed.<br />
	Project Name: '.$linkdata['Location']['NAME'].'<br />
	Location Code: '.$linkdata['Location']['LOCATION_REF_ID'].'<br /><br />
	You can now log in at <a href="https://client.pstcompliance.com">https://client.pstcompliance.com</a> to view project documents.<br /><br />
	To download the completed checklist in PDF format please visit this URL:<br />
	<a href="'.$url.'&ftype=pdf">'.$url.'&ftype=pdf</a><br /><br />
	To download the report and all supporting documents as a ZIP file visit this URL:<br />
	<a href="'.$url.'&ftype=zip">'.$url.'&ftype=zip</a><br /><br />
	Thank you for choosing PST Compliance!<br /><br />
	Tim Way
';
}

if($linkdata['Notes']){
	
	$subject = "**Action Required** PST Compliance - Location Code: {$linkdata['Location']['LOCATION_REF_ID']}";
	
	// Begin of Action Text Message
	$textmessage = "*** ACTION REQUIRED! ***\n\n";
	$textmessage .= "Please note the item(s) listed below which require your attention\n\n";
	foreach($linkdata['Notes'] as $note){
		$textmessage .= $note."\n";
	}
	$textmessage .= "\n";
	$textmessage .= "Your report is now complete and ready to be viewed.\n";
	$textmessage .= "Project Name: {$linkdata['Location']['NAME']}\n";
	$textmessage .= "Location Code: {$linkdata['Location']['LOCATION_REF_ID']}\n\n";
	$textmessage .= "You can now log in at https://client.pstcompliance.com to view project documents."."\n\n";
	$textmessage .= "To download the completed checklist(pdf) visit this URL:\n";
	$textmessage .= $url."&ftype=pdf"."\n\n";
	$textmessage .= "To download the report and all supporting documents in a zip file visit this URL:\n";
	$textmessage .= $url."&ftype=zip"."\n\n";
	$textmessage .= "Thank you for choosing PST Compliance!";


    // begin of Action HTML message
	$htmlbody = '
	<span style="color: #D50000;text-decoration: underline; font-weight: bold;">Action Required!</span><br /><br />
	Please note the item(s) listed below which require your attention.<br />
	<ul style="font-weight: bold; color: #D50000;">';
	foreach($linkdata['Notes'] as $note){
		$htmlbody .= '
	<li>'.$note.'</li>';
	}
	$htmlbody .= '
	</ul>
	<br /><br />
	Your report is now complete and ready to be viewed.<br />
	Project Name: '.$linkdata['Location']['NAME'].'<br />
	Location Code: '.$linkdata['Location']['LOCATION_REF_ID'].'<br /><br />
	You can now log in at <a href="https://client.pstcompliance.com">https://client.pstcompliance.com</a> to view project documents.<br /><br />
	To download the completed checklist in PDF format please visit this URL:<br />
	<a href="'.$url.'&ftype=pdf">'.$url.'&ftype=pdf</a><br /><br />
	To download the report and all supporting documents as a ZIP file visit this URL:<br />
	<a href="'.$url.'&ftype=zip">'.$url.'&ftype=zip</a><br /><br />
	Thank you for choosing PST Compliance!<br /><br />
	Tim Way
';
   //end of message
   }


//Assemble html message
$htmlmessage = "<html>
<body>";
$htmlmessage .= $htmlbody;
$htmlmessage .= "  </body>
</html>";

// now lets send the email if there are any users to send to
$pos = strpos($to, "@");
if ($pos !== false){
	send_email($to, $from, $subject, $htmlmessage, $textmessage, $headers);

//Assemble html message
$htmlmessage = "<html>
<body>";
$htmlmessage .= '--Copy of email message sent to '.$to.'--'.'<br /><br />';
$htmlmessage .= $htmlbody;
$htmlmessage .= "  </body>
</html>";
$textmessage = "--Copy of email message sent to ".$to."--"."\n\n".$textmessage;



send_email($notification_email, $from, $subject, $htmlmessage, $textmessage, $headers);
//send_email('brian.mccormick@webworkscorp.com', $from, $subject, $htmlmessage, $textmessage, $headers);
	}
}


function send_email($to='', $from='', $subject='', $html_content='', $text_content='', $headers='') { 
	# Setup mime boundary
	$mime_boundary = 'Multipart_Boundary_x'.md5(time()).'x';

	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\r\n";
	$headers .= "Content-Transfer-Encoding: 7bit\r\n";

	$body	 = "This is a multi-part message in mime format.\n\n";

	# Add in plain text version
	$body	.= "--$mime_boundary\n";
	$body	.= "Content-Type: text/plain; charset=\"charset=us-ascii\"\n";
	$body	.= "Content-Transfer-Encoding: 7bit\n\n";
	$body	.= $text_content;
	$body	.= "\n\n";

	# Add in HTML version
	$body	.= "--$mime_boundary\n";
	$body	.= "Content-Type: text/html; charset=\"UTF-8\"\n";
	$body	.= "Content-Transfer-Encoding: 7bit\n\n";
	$body	.= $html_content;
	$body	.= "\n\n";


	# End email
	$body	.= "--$mime_boundary--\n"; # <-- Notice trailing --, required to close email body for mime's

	# Finish off headers
	$headers .= "From: $from\r\n";
	$headers .= "Return-Path:<$from>\r\n";
	$headers .= "X-Sender-IP: $_SERVER[SERVER_ADDR]\r\n";
	$headers .= 'Date: '.date('n/d/Y g:i A')."\r\n";

	# Mail it out
	return mail($to, $subject, $body, $headers);
}


function get_directory_list($directory){
	
	// Create an array to hold directory list
	$results = array();
	
	// Create a handler for the directory
	$handler = opendir($directory);
	
	// Open directory and walk through the filenames
	while ($file = readdir($handler)){
		// If file isnt this directory or its parent add it to the array
		if($file != "." && $file != ".."){
			$results[] = $file;
		}
	}
	// Close the handler
	closedir($handler);
	
	return $results;
	
}




function forceDownload($archiveName) {
                if(ini_get('zlib.output_compression')) {
                        ini_set('zlib.output_compression', 'Off');
                }

                // Security checks
                if( $archiveName == "" ) {
                        
                        echo "<html><title>PST Compliance Reporting - Download </title><body><BR><B>ERROR:</B> The download file was NOT SPECIFIED.</body></html>";
                        exit;
                }
                elseif ( ! file_exists( $archiveName ) ) {
                        echo "<html><title>PST Compliance Reporting - Download </title><body><BR><B>ERROR:</B> File not found.</body></html>";
                        exit;
                }
		if(strstr($_SERVER["HTTP_USER_AGENT"],"MSIE")==false) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private",false);
		}
		else {
			header("Pragma: ");
			header("Cache-Control: ");
			header("Content-type: application/force-download");
		}
                header("Content-Type: application/zip");
                header("Content-Disposition: attachment; filename=".basename($archiveName).";" );
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: ".filesize($archiveName));
                readfile("$archiveName");
                
}


function prepare_client_zip($ChecklistID){
	global $file_prep_directory,$upload_root_directory;
	
	$ldata = get_link_data_by_checklist($ChecklistID);
	$location_code = $ldata['Location']['LOCATION_REF_ID'];
	

	$filename = 'PST_Compliance_LOC_'.$location_code.'.zip';
	if (file_exists($file_prep_directory.$filename)) {
    	forceDownload($file_prep_directory.$filename);
	}
	else {
	
	$zip = new ZipArchive();
	
	if ($zip->open($file_prep_directory.$filename, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <$filename>\n");
	}
	$rootfolder = "PST_Compliance_".$location_code;
	$zip->addEmptyDir($rootfolder);
	
	$cats = get_doc_folders($ChecklistID);
	while (!$cats->EOF) {
	
	$folder = $cats->fields[0];
		
		$zip->addEmptyDir($rootfolder."/".$folder);
		
		$cats->MoveNext();
	}
	

	$docfolders = get_doc_subfolders($ChecklistID);
	while (!$docfolders->EOF) {
	$folder = $docfolders->fields[0]."/";
	$subfolder = $docfolders->fields[1]."/";
	
	$zip->addEmptyDir($rootfolder."/".$folder."/".$subfolder);
		
	$docfolders->MoveNext();
	}
	
	$docpaths = get_doc_path($ChecklistID);
	while (!$docpaths->EOF) {
	
	$targetpath = $docpaths->fields[0];
	$folder = $docpaths->fields[1];
	$subfolder = $docpaths->fields[2];
	$filename2 = $docpaths->fields[3];
	
	$linkpath = $rootfolder."/".$folder."/".$subfolder."/".$filename2;
		
	$zip->addFile($targetpath, $linkpath);
	
	$docpaths->MoveNext();
	}
	if (file_exists($upload_root_directory.$ChecklistID.'/Closeout_Checklist.pdf')) {
	$zip->addFile($upload_root_directory.$ChecklistID.'/Closeout_Checklist.pdf',$rootfolder.'/Closeout_Checklist.pdf');
	}
	$tp = title_page_docs($ChecklistID);
	if($tp!==false){
		while(!$tp->EOF){
			$zip->addFile($tp->fields[3],$rootfolder.'/'.$tp->fields[4]);
		$tp->MoveNext();
		}
	}
	
	$tc = table_of_contents_docs($ChecklistID);
	if($tc!==false){
		while(!$tc->EOF){
			$zip->addFile($tc->fields[3],$rootfolder.'/'.$tc->fields[4]);
		$tc->MoveNext();
		}
	}
	
	$ps = project_summary_docs($ChecklistID);
	if($ps!==false){
		while(!$ps->EOF){
			$zip->addFile($ps->fields[3],$rootfolder.'/'.$ps->fields[4]);
		$ps->MoveNext();
		}
	}
	$zip->close();
	forceDownload($file_prep_directory.$filename);

	
	//@unlink($file_prep_directory.$filename); 
	}
}


function new_prepare_client_zip($ChecklistID){
	global $file_prep_directory,$upload_root_directory;
	
	$ldata = get_link_data_by_checklist($ChecklistID);
	$location_code = $ldata['Location']['LOCATION_REF_ID'];

	$zip = new ZipArchive();
	$filename = 'PST_Compliance_LOC_'.$location_code.'.zip';
	
	
	if ($zip->open($file_prep_directory.$filename, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <$filename>\n");
	}
	$rootfolder = "PST_Compliance_".$location_code;
	$zip->addEmptyDir($rootfolder);
	
	$zip->addEmptyDir($rootfolder."/Photographs");
	
	$docpaths = db_get_doc_photos($ChecklistID);
	while (!$docpaths->EOF) {
	
	$targetpath = $docpaths->fields[0];
	$filename2 = $docpaths->fields[3];
	
	$linkpath = $rootfolder."/Photographs/".$filename2;
		
	$zip->addFile($targetpath, $linkpath);
	
	$docpaths->MoveNext();
	}

//Start new script

$command = "";
$allfiles = array();

	$tp = title_page_docs($ChecklistID);
	if($tp!==false){
		while(!$tp->EOF){
			//$command = $command.' '.$tp->fields[3];
			$allfiles[] = $tp->fields[3];
		$tp->MoveNext();
		}
	}
	
	$ps = project_summary_docs($ChecklistID);
	if($ps!==false){
		while(!$ps->EOF){
			//$command = $command.' '.$ps->fields[3];
			$allfiles[] = $ps->fields[3];
		$ps->MoveNext();
		}
	}
	
if (file_exists($upload_root_directory.$ChecklistID.'/Closeout_Checklist.pdf')) {
	$allfiles[] = $upload_root_directory.$ChecklistID.'/Closeout_Checklist.pdf';
	}
	
	$tc = table_of_contents_docs($ChecklistID);
	if($tc!==false){
		while(!$tc->EOF){
			//$command = $command.' '.$tc->fields[3];
			$allfiles[] = $tc->fields[3];
		$tc->MoveNext();
		}
	}
	

	
$files = db_merge_pdf($ChecklistID);

while (!$files->EOF) {
//$command = $command.' '.$files->fields[0];
$allfiles[] = $files->fields[0];
	$files->MoveNext();
	}

foreach($allfiles as $af){
	$ispdf = substr($af,-3,3);  //make sure it's a PDF file    
    $ispdf = strtolower($ispdf); 
	if ($af && $ispdf=='pdf') {
		$command = $command.' '.$af;
	}
}


$command = base64_encode($command); //encode and then decode the command string
$command = base64_decode($command); 

$output = $file_prep_directory."/merged-pdf".time().".pdf"; //set name of output file

$command = "/usr/bin/pdftk $command output $output";
//echo $command;exit();
passthru($command); //run the command


//Back to Original Script
	$zip->addFile($output, $rootfolder.'/Completed_Report.pdf');
	$zip->close();
	forceDownload($file_prep_directory.$filename);

	
	@unlink($file_prep_directory.$filename); 
	@unlink($output);
}


function create_checklist_docfolder($ChecklistID){
	$path = "uploads/".$ChecklistID;
	$directory = mkdir($path, 0777);
	return $directory;
	
	
}

function create_question_docfolder($ChecklistID, $QuestionID){
	$path = "uploads/".$ChecklistID."/".$QuestionID;
	$directory = mkdir($path, 0777);
	return $directory;
	
}
function forgot_password($email = NULL, $username = NULL){
	if(is_null($email) && is_null($username)){
		return false;
	}
	if(!is_null($username)){
		$user_id = get_userid_from_username($username);
	}
	if(!is_null($email)){
	$user_id = get_userid_from_email($email);
	}
	
if($user_id === false){
	return false;
}
else if($user_id > 0) {
	$ui = password_reset_token($user_id);
	
	$to      = $ui['email'];
	$subject = 'PST Compliance - Password Reset Information';
	$message = 'Hi '.$ui['first_name'].','."\n\n";
	$message .= "Please use the link below to create a new password for your account.\n\n";
	$message .= "http://client.pstcompliance.com/main.php?page=forgotpassword&token=".$ui['token']."\n\n";
	$message .= "Your Username is: ".$ui['username'];
	$headers = 'From: noreply@pstcompliance.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();


mail($to, $subject, $message, $headers);

return true;

}
else return false;

}

function verify_token_pw_reset($token, $password){
	$verify = token_check($token);
	if($verify == false){
		return "badtoken";
	}
	else {
	update_password($verify['user_id'], $password);
	delete_token($verify['token_id']);	
	return "pwchanged";
		
	}
}


function checklist_access_check($checklist_id, $user_id){
	$auth = verify_checklist_access($checklist_id, $user_id);
	if($auth == false){
		$auth = check_single_checklist_access($checklist_id, $user_id);
	}
	return $auth;
}

function page_chooser($page = "home", $user_id = NULL, $auth_type = NULL){
	if($user_id == NULL || $auth_type == NULL){
		switch($page){
			case "forgotpassword":
				return "forgotpassword";
				break;
			case "download":
				return "download";
				break;
			default:
				return "login";
				break;
		}
	}
	if($auth_type == 3){
		$adminpages = array('download', 'profile', 'dashboard', 'search', 'client', 'viewclients', 'newclient', 'addclient', 'editclient', 'overviewclient', 'deleteclient', 'user', 'viewusers', 'newuser', 'edituser', 'deleteuser', 'location', 'newlocation', 'editlocation', 'deletelocation', 'checklist', 'newchecklist', 'add_q_checklist', 'deletechecklist', 'searchresults');
		if (in_array($page, $adminpages)) {
			return $page;
		}
		else return "home";
	}
	if($auth_type == 2){
		$clientpages = array('download', 'profile', 'search', 'overviewlocations', 'documents', 'checklist');
		if (in_array($page, $clientpages)) {
			return $page;
		}
		else return "overviewlocations";
	}
	if($auth_type == 1){
		$clientpages = array('download','profile', 'search',  'overviewlocations', 'documents', 'checklist');
		if (in_array($page, $clientpages)) {
			return $page;
		}
		else return "overviewlocations";
	}

}

function authenticated($session){
	if((isset($session['Username']) && ($session['Username'] != NULL)) && (isset($session['UserID']) && ($session['UserID'] != NULL)) && (isset($session['UserGroup']) && ($session['UserGroup'] != NULL)) && (isset($session['ClientGroup']) && ($session['ClientGroup'] != NULL))){
		return true;
	}
	else return false;


}

function generate_checklist_table($id){
	$recordSet = get_checklist_questions($id);
		//checklist category names query
	$category = get_category('names', $id);
	//Count questions in each category and set category names
	$catqcount = get_category('qcount', $id);
	$qsum = 1;
	$table = '';
		while (!$recordSet->EOF) {
			$previous_category = $current_category;
			if($previous_category == ''){
				$table .= "<div id='left'>\n";
				$current_column = '1'; }
			$current_category = $recordSet->fields[2];
			if($previous_category != $current_category){
					if(($qsum + $catqcount[$current_category]) > 50){
						if($current_column == '2')
						{
							$table .= "</div>\n";
							$table .= "<div id='right'>\n";
							$current_column++;
						}
						if($current_column == '1')
						{
							$table .= "</div>\n";
							$table .= "<div id='center'>\n";
						$current_column++;	
						}
				//echo "<BR><BR>NEW PAGE HERE <BR><BR>";
				$qsum = 1;
			}
						$table .= "<br /><class='cattop'>".$category[$current_category]."</class><br /><br />"."\n";
				$q=1;
				
				
				
			}
		
		$table .= "<div id='qdiv'>"."\n";	
		$table .= '<input type="radio" name="'.$recordSet->fields[0].'" value="1" ';
		if($recordSet->fields[1]==1){$table .= 'checked';}
		$table .= ' > <input type="radio" name="'.$recordSet->fields[0].'" value="2" ';
		if($recordSet->fields[1]==2){$table .= 'checked';}
		$table .= ' > <input type="radio" name="'.$recordSet->fields[0].'" value="3" ';
		if($recordSet->fields[1]==3){$table .= 'checked';}
		$table .= ' > ';
		$table .= "<span id='question'>";
		if($q<10){
			$table .= $q.'.&nbsp;&nbsp;';}
		else {$table .= $q.'.&nbsp;';}
		if($recordSet->fields[4] != ''){
		$table .= strtoupper($recordSet->fields[3].' -- '.$recordSet->fields[4]).'</span></div>';}
		else {
		$table .= strtoupper($recordSet->fields[3]).'</span></div>';}
		$table .= "<br />"."\n";

		$q++;
		$qsum++;

		$recordSet->MoveNext();
		
		}
		$table .= "</div>";
		$table .= "<div class='clear'></div>";
	
	return $table;
	
}

function generate_checklist_accordian($checklist_id){
	//checklist questions query
	$recordSet = get_checklist_questions($checklist_id);
	
	//checklist category names query
	$category = get_category('names', $checklist_id);
	
	//Count questions in each category and set category names
	$catqcount = get_category('qcount', $checklist_id);
	
	$docarray = count_checklist_doc($checklist_id);

	
	$qsum = 1;
	$actable .= '<form method="POST" name="checklistForm" action="?page=checklist&action=savechecklist">'."\n";
	$actable .= '<input type="submit" name="Submit" value="Save">'."\n";
	$actable .= '<div id="accordion" width="800">'."\n";
	
	$actable .= '<h3>PROJECT SUMMARY</h3>';
	$actable .= '<table width="700">'."\n";
	if (check_title_page($checklist_id)){
		$docimage = "./images/icons/documents.png";
		}
		else $docimage = "./images/icons/documents_empty.png";
	$actable .= '<tr>'."\n".'<td width="85" >&nbsp;</td><td width="570">&nbsp;&nbsp;&nbsp;&nbsp;TITLE PAGE</td><td width="45">';
	$actable .= '<a href="include/chip_upload/psummary.php?cl_id='.$checklist_id.'&q_id='.$recordSet->fields[0].'&pstype=TP&KeepThis=true&TB_iframe=true&height=400&width=600" class="thickbox" title="Manage Documents" ><img src="'.$docimage.'" alt="Manage Documents" height="20" width="20"/></a>';
	$actable .= '</td>'."\n".'</tr>';
	if (check_table_of_contents($checklist_id)){
		$docimage = "./images/icons/documents.png";
		}
		else $docimage = "./images/icons/documents_empty.png";
	$actable .= '<tr>'."\n".'<td width="85" >&nbsp;</td><td width="570">&nbsp;&nbsp;&nbsp;&nbsp;TABLE OF CONTENTS</td><td width="45">';
	$actable .= '<a href="include/chip_upload/psummary.php?cl_id='.$checklist_id.'&q_id='.$recordSet->fields[0].'&pstype=TC&KeepThis=true&TB_iframe=true&height=400&width=600" class="thickbox" title="Manage Documents" ><img src="'.$docimage.'" alt="Manage Documents" height="20" width="20"/></a>';
	$actable .= '</td>'."\n".'</tr>';
	if (check_project_summary($checklist_id)){
		$docimage = "./images/icons/documents.png";
		}
		else $docimage = "./images/icons/documents_empty.png";
	$actable .= '<tr>'."\n".'<td width="85" >&nbsp;</td><td width="570">&nbsp;&nbsp;&nbsp;&nbsp;PROJECT SUMMARY</td><td width="45">';
	$actable .= '<a href="include/chip_upload/psummary.php?cl_id='.$checklist_id.'&q_id='.$recordSet->fields[0].'&pstype=PS&KeepThis=true&TB_iframe=true&height=400&width=600" class="thickbox" title="Manage Documents" ><img src="'.$docimage.'" alt="Manage Documents" height="20" width="20"/></a>';
	$actable .= '</td>'."\n".'</tr>';
	$actable .= '</table>';
	while (!$recordSet->EOF) {
		$previous_category = $current_category;
		$current_category = $recordSet->fields[2];
		if($previous_category != $current_category){
					if($current_category != '1'){
					$actable .= "</table>"."\n";
					$actable .= "</div>";	
					}
					$actable .= '<h3>'.$category[$current_category].'</h3>'."\n";
					$actable .= "<div>"."\n";
					$actable .= '<table width="700">'."\n";
					$actable .= '<tr>'."\n".'<td width="85" >&nbsp;Y&nbsp;&nbsp;N&nbsp;N/A</td><td>&nbsp;</td><td>&nbsp;</td>'."\n".'</tr>';
			$q=1;
			
			
			
		}
		$actable .= '<tr>'."\n".'<td width="85" >';
		$actable .='<input type="radio" name="'.$recordSet->fields[0].'" id="rg_'.$recordSet->fields[0].'_0" value="" style="display:none;">';
		$actable .= '<input type="radio" name="'.$recordSet->fields[0].'" id="rg_'.$recordSet->fields[0].'_1" value="1" ';
		if($recordSet->fields[1]==1){$actable .= 'checked';}
		$actable .= ' /> <input type="radio" name="'.$recordSet->fields[0].'" id="rg_'.$recordSet->fields[0].'_2" value="2" ';
		if($recordSet->fields[1]==2){$actable .= 'checked';}
		$actable .= ' /> <input type="radio" name="'.$recordSet->fields[0].'" id="rg_'.$recordSet->fields[0].'_3" value="3" ';
		if($recordSet->fields[1]==3){$actable .= 'checked';}
		$actable .= ' />&nbsp;&nbsp;&nbsp;<a href="javascript:clearCheck(\''.$recordSet->fields[0].'\')" title="Clear Selection" ><img src="./images/icons/reset_icon.png" height="18" width="18"/></a>';
		$actable .= '</td>';
		if($q<10){
			$actable .= '<td>'.$q.'.&nbsp;&nbsp;'.'</td>';}
		else {$actable .= '<td>'.$q.'.&nbsp;'.'</td>';}
		if($recordSet->fields[4] != ''){
		$actable .= '<td>'.strtoupper($recordSet->fields[3].' -- '.$recordSet->fields[4]).'</td>';}
		else {
		$actable .= '<td width="570" >'.strtoupper($recordSet->fields[3]).'</td>';}
		
		if (array_key_exists($recordSet->fields[0], $docarray)){
		$docimage = "./images/icons/documents.png";
		}
		else $docimage = "./images/icons/documents_empty.png";
		
		$actable .= '<td width="45" ><a href="include/chip_upload/index.php?cl_id='.$checklist_id.'&q_id='.$recordSet->fields[0].'&KeepThis=true&TB_iframe=true&height=400&width=600" class="thickbox" title="Manage Documents" ><img src="'.$docimage.'" alt="Manage Documents" height="20" width="20"/></a>';
		$actable .= '&nbsp;<a href="#" onclick="confirmation(\''.$checklist_id.'\',\''.$recordSet->fields[0].'\')" title="Delete Line Item"><img src="./images/icons/Delete.png" alt="Delete Line Item" height="18" width="18"/></a>';
		$actable .= '</td>';
		$actable .= "\n".'</tr>'."\n";
		
		$q++;
		$qsum++;
		         $recordSet->MoveNext();
		
		}
		$actable .= "</table></div>"."\n";
		$actable .= '<input type="hidden" name="ChecklistID" value="'.$checklist_id.'" />'."\n";
		$actable .= '<input type="submit" name="Submit" value="Save">'."\n";
		$actable .= '</form>'."\n";
		$actable .= "<div class='clear'></div>";
	


return $actable;
}

function generate_client_checklist($checklist_id){
	//checklist questions query
	$recordSet = get_checklist_questions($checklist_id);
	
	//checklist category names query
	$category = get_category('names', $checklist_id);
	
	//Count questions in each category and set category names
	$catqcount = get_category('qcount', $checklist_id);
	$qsum = 1;
	$actable .= '<div id="accordion" width="800">'."\n";
	while (!$recordSet->EOF) {
		$previous_category = $current_category;
		$current_category = $recordSet->fields[2];
		if($previous_category != $current_category){
					if($current_category != '1'){
					$actable .= "</table>"."\n";
					$actable .= "</div>";	
					}
					$actable .= '<h3><a href="#">'.$category[$current_category].'</a></h3>'."\n";
					$actable .= "<div>"."\n";
					$actable .= '<table width="700">'."\n";
					$actable .= '<tr>'."\n".'<td width="72" >&nbsp;Y&nbsp;&nbsp;&nbsp;N/A</td><td>&nbsp;</td><td>&nbsp;</td>'."\n".'</tr>';
			$q=1;
			
			
			
		}
		$actable .= '<tr>'."\n".'<td width="72" >';
		if($recordSet->fields[1]==1){
			$actable .= '<img src="./images/icons/checked_box.png" height="20" width="20" />&nbsp;<img src="./images/icons/check_box.png" height="20" width="20" />';
		}
		if($recordSet->fields[1]==2){
			$actable .= '<img src="./images/icons/check_box.png" height="20" width="20" />&nbsp;<img src="./images/icons/check_box.png" height="20" width="20" />';
		}
		if($recordSet->fields[1]==3){
			$actable .= '<img src="./images/icons/check_box.png" height="20" width="20" />&nbsp;<img src="./images/icons/checked_box.png" height="20" width="20" />';
		}
		if($recordSet->fields[1]==0){
			$actable .= '<img src="./images/icons/check_box.png" height="20" width="20" />&nbsp;<img src="./images/icons/check_box.png" height="20" width="20" />';
		}
		$actable .= '</td>';
		if($q<10){
			$actable .= '<td>'.$q.'.&nbsp;&nbsp;'.'</td>';}
		else {$actable .= '<td>'.$q.'.&nbsp;'.'</td>';}
		if($recordSet->fields[4] != ''){
		$actable .= '<td>'.strtoupper($recordSet->fields[3].' -- '.$recordSet->fields[4]).'</td>';}
		else {
		$actable .= '<td width="618" >'.strtoupper($recordSet->fields[3]).'</td>';}
		$actable .= '<td width="10" >&nbsp;</td>';
		$actable .= "\n".'</tr>'."\n";
		
		$q++;
		$qsum++;
		         $recordSet->MoveNext();
		
		}
		$actable .= "</table></div>"."\n";
		$actable .= "<div class='clear'></div>";
	


return $actable;
}

function file_size($path) {

    $bytes = array("B", "KB", "MB", "GB", "TB", "PB");
    $file_with_path = $path;
    // replace (possible) double slashes with a single one
    $file_with_path = str_replace("//", "/", $file_with_path);
    $size = filesize($file_with_path);
    $i = 0;
    while ($size >= 1024) { //divide the filesize (in bytes) with 1024 to get "bigger" bytes
        $size = $size/1024;
        $i++;
    }
    if ($i > 1) {
        // you can change this number if you like (for more precision)
        return round($size,1)." ".$bytes[$i];
    } else {
        return round($size,0)." ".$bytes[$i];
    }
}


function old_client_files_table($ChecklistID){
	
	
if(client_doc_tree($ChecklistID)->RecordCount() > 0){
$filenode = "1000";
$table = <<< EOF
<table class="example" id="dnd-example">
  <thead>
    <tr>
      <th>Title</th>
      <th>Size</th>
      <th>Kind</th>

    </tr>
  </thead>
  <tbody>
EOF;
	$cats = get_doc_folders($ChecklistID);
	while (!$cats->EOF) {
	
	$folder = $cats->fields[0];
	$node = $cats->fields[1];
		$table .= <<< EOF
    <tr id="node-{$node}">
      <td><span class="folder">{$folder}</span></td>
      <td>--</td>
      <td>Folder</td>
    </tr>
EOF;
		
		$cats->MoveNext();
	}
	

	$docfolders = get_doc_subfolders($ChecklistID);
	while (!$docfolders->EOF) {
	$node = $docfolders->fields[2];
	$subnode = $docfolders->fields[3];
	$subfolder = $docfolders->fields[1];

$table .= <<< EOF
    <tr id="node-{$subnode}" class="child-of-node-{$node}">
      <td><span class="folder">{$subfolder}</span></td>
      <td>--</td>
      <td>Folder</td>
    </tr>
EOF;
		
	$docfolders->MoveNext();
	}
	
	$docpaths = get_doc_path($ChecklistID);
	while (!$docpaths->EOF) {
	
	$targetpath = $docpaths->fields[0];
	$folder = $docpaths->fields[1];
	$subfolder = $docpaths->fields[2];
	$filename2 = $docpaths->fields[3];
	$subnode = $docpaths->fields[4];
	$doc_id = $docpaths->fields[5];
	
	$filesize = file_size($targetpath);
	$table .= <<< EOF
    <tr id="node-{$filenode}" class="child-of-node-{$subnode}">
      <td><span class="file">{$filename2}</span></td>
      <td>{$filesize}</td>
      <td><a href="?action=download&file={$doc_id}">Download</a></td>

    </tr>
EOF;
	$filenode = $filenode - 1;
	$docpaths->MoveNext();
	}
$table .= <<< EOF
  </tbody>
</table>
EOF;
}
else $table = '<div align="center">No Documents Available</div>';

return $table;
}

function doc_search($ChecklistID, $query){
	
	
}


function client_files_table($ChecklistID){
	
$doctree = client_doc_tree($ChecklistID);	
if($doctree->RecordCount() > 0){
$filenode = "1";

$table = <<< EOF
<table class="example" id="dnd-example">
  <thead>
    <tr>
      <th>Title</th>
      <th>Size</th>
      <th>Kind</th>

    </tr>
  </thead>
  <tbody>
EOF;

$tp = title_page_docs($ChecklistID);

if($tp){
	$table .= <<< EOF
    <tr id="node-50">
      <td><span class="folder">Title Page</span></td>
      <td>--</td>
      <td>Folder</td>
    </tr>
EOF;
	$tpnode = "51";

	while(!$tp->EOF){
		$docID = $tp->fields[0];
		$path = $tp->fields[3];
		$filename = $tp->fields[4];
		$filesize = file_size($path);
	$table .= <<< EOF
	<tr id="node-{$tpnode}" class="child-of-node-50">
      <td><span class="file">{$filename}</span></td>

      <td>{$filesize}</td>
      <td><a href="?action=download&file={$docID}">Download</a></td>
    </tr>
EOF;
$tpnode++;
	$tp->MoveNext();
	}
}

$tc = table_of_contents_docs($ChecklistID);

if($tc){
	$table .= <<< EOF
    <tr id="node-60">
      <td><span class="folder">Table of Contents</span></td>
      <td>--</td>
      <td>Folder</td>
    </tr>
EOF;
	$tcnode = "61";

	while(!$tc->EOF){
		$docID = $tc->fields[0];
		$path = $tc->fields[3];
		$filename = $tc->fields[4];
		$filesize = file_size($path);
	$table .= <<< EOF
	<tr id="node-{$tcnode}" class="child-of-node-60">
      <td><span class="file">{$filename}</span></td>

      <td>{$filesize}</td>
      <td><a href="?action=download&file={$docID}">Download</a></td>
    </tr>
EOF;
$tcnode++;
	$tc->MoveNext();
	}
}

$ps = project_summary_docs($ChecklistID);

if($ps!==false){
	$table .= <<< EOF
    <tr id="node-70">
      <td><span class="folder">Project Summary</span></td>
      <td>--</td>
      <td>Folder</td>
    </tr>
EOF;
	$psnode = "71";

	while(!$ps->EOF){
		$docID = $ps->fields[0];
		$path = $ps->fields[3];
		$filename = $ps->fields[4];
		$filesize = file_size($path);
	$table .= <<< EOF
	<tr id="node-{$psnode}" class="child-of-node-70">
      <td><span class="file">{$filename}</span></td>

      <td>{$filesize}</td>
      <td><a href="?action=download&file={$docID}">Download</a></td>
    </tr>
EOF;
$psnode++;
	$ps->MoveNext();
	}
}

$cd = completed_checklist_doc($ChecklistID);

if($cd!==false){
	while(!$cd->EOF){
		$docID = $cd->fields[0];
		$path = $cd->fields[3];
		$filename = $cd->fields[4];
		$filesize = file_size($path);
		$table .= <<< EOF
	<tr id="node-49">
      <td><span class="file">{$filename}</span></td>

      <td>{$filesize}</td>
      <td><a href="?action=download&file={$docID}">Download</a></td>
    </tr>
EOF;
		
		$cd->MoveNext();
	}
}

$previousCat = "0";
$previousQ = "0";
	while(!$doctree->EOF){
		$docID = $doctree->fields[0];
		$catID = $doctree->fields[2] + 1000;
		$qID = $doctree->fields[3] + 2000;
		$qName = $doctree->fields[4];
		$catName = $doctree->fields[5];
		$path = $doctree->fields[6];
		$filename = $doctree->fields[7];
		$filesize = file_size($path);
		
	if($previousCat != $doctree->fields[2]){
		$table .= <<< EOF
    <tr id="node-{$catID}">
      <td><span class="folder">{$catName}</span></td>
      <td>--</td>
      <td>Folder</td>
    </tr>
EOF;
	}
	if($previousQ != $doctree->fields[3]){
		$table .= <<< EOF
    <tr id="node-{$qID}" class="child-of-node-{$catID}">
      <td><span class="folder">{$qName}</span></td>
      <td>--</td>
      <td>Folder</td>
    </tr>
EOF;
	}
		$table .= <<< EOF
	<tr id="node-{$filenode}" class="child-of-node-{$qID}">
      <td><span class="file">{$filename}</span></td>

      <td>{$filesize}</td>
      <td><a href="?action=download&file={$docID}">Download</a></td>
    </tr>
EOF;
$previousQ = $doctree->fields[3];
$previousCat = $doctree->fields[2];
$filenode++;
		
	$doctree->MoveNext();
	}
$table .= <<< EOF
  </tbody>
</table>
EOF;
}
else $table = '<div align="center">No Documents Available</div>';

return $table;
}

function dropdown_locations(){
	$loc = dropdown_location_data();
	$output = <<< EOF
<select name="jumpmenu" onChange="jumpto(document.search.jumpmenu.options[document.search.jumpmenu.options.selectedIndex].value)">
<option>Jump to Location...</option>
EOF;
	while(!$loc->EOF){
		$name = $loc->fields[0];
		$client = $loc->fields[1];
		$id = $loc->fields[2];
		$city = $loc->fields[3];
		$output .= <<< EOF

<option value="?page=client&type=overview&id={$client}&node={$id}">{$name} - {$city}</option>
EOF;

	$loc->MoveNext();
	}
	$output .= '</select>';
	return $output;
	
}

function action_message($page, $notify, $override = NULL, $custom = NULL){
	if(!is_null($page)){	
	if(substr($page, -1) == "s"){
		$subjectname = substr(ucfirst($page), 0, -1);
	}
	else{
	$subjectname = ucfirst($page);
	}
	}
if(!is_null($override)){
	$subjectname = ucfirst($override);
}
if(!is_null($notify)){
	switch($notify){
		case "1":
			$message = "Successfully Added";
			break;
		case "2":
			$message = "Successfully Edited";
			break;
		case "3":
			$message = "Successfully Deleted";
			break;
		case "4":
			$subjectname = "";
			$message = "An Error Has Occurred, Please Try Again";
			break;
		case "5":
			$subjectname = "";
			$message = "Passwords do not match!";
			break;
	}
}
if(is_null($notify)){
	$subjectname = "";
	$message = $custom;
}
	
	
	
	$html = <<< EOF
	  <tr>
    <td>
	<hr />
	<div align="center">
{$subjectname} {$message}
    </div>
	<hr />
    </td>
  </tr>
EOF;

return $html;	
	
	
}

?>