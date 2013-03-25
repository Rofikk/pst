<?php
function edit_user_profile($firstname, $lastname, $email, $password1 = NULL, $password2 = NULL){
	if(isset($_SESSION['UserID'])){
		$UserID = $_SESSION['UserID'];
	}
	else {
		return false;
		exit;
	}
	$UserInfo = array();
	$UserInfo['email'] = $email;
	$UserInfo['firstname'] = $firstname;
	$UserInfo['lastname'] = $lastname;
	update_user($UserInfo, $UserID);
	
	if($password1 != NULL && $password2 != NULL && $password1 != '' && $password1 == $password2){
		
		update_password($UserID, $password1);
	}
	$_SESSION['FirstName'] = $UserInfo['firstname'];
	$_SESSION['LastName'] = $UserInfo['lastname'];
	$_SESSION['Email'] = $UserInfo['email'];
	if($password1 != NULL && $password2 != NULL && $password1 != '' && $password1 != $password2){
		
		header("Location: ?page=profile&notify=5");
		exit;
	}
	header("Location: ?page=profile&notify=2");
}

function add_new_client($data, $UserGroup){
	if($UserGroup == 3){
	$client_id = create_client($data['BusinessName'], $data['address'], $data['city'], $data['state'], $data['zipcode'], $data['phone'], $data['contact'], $data['active']);
	header("Location: ?page=client?type=overview&id={$client_id}&notify=1");
	}
}

function add_new_location($data, $UserGroup){
	if($UserGroup == 3){
		create_new_location($data['client_id'], $data['location'], $data['name'], $data['address'], $data['city'],
			$data['state'], $data['zipcode'], $data['engagement'], $data['project_drawing_number'], $data['project_manager'],
			$data['architect'], $data['af_contractor']);
		header("Location: ?page=client&type=overview&id={$data['client_id']}&notify=1&cnotify=location");
	}
}

function add_new_checklist($LocationID, $UserGroup){
	if($UserGroup == 3){
	$client_id = get_client_by_location($LocationID);
	$checklist_id = create_checklist($LocationID, $client_id);
	create_checklist_docfolder($checklist_id);
	
	$maxq = get_checklist_max_question_id($checklist_id);
	$startid = $maxq - 46;
	$i = 1;
	while($i <= 47 ){
		create_question_docfolder($checklist_id, $startid);
		$i++;
		$startid++;
	}
	header("Location: ?page=client&type=overview&id={$client_id}&node={$LocationID}&notify=1&cnotify=checklist");
	}
}
function edit_client_location($data, $UserGroup){
	if($UserGroup == 3){
	$client_id = get_client_by_location($data['LocationID']);
	update_location($data, $UserGroup);

	header("Location: ?page=client&type=overview&id={$client_id}&notify=2&cnotify=location");
	}
	
}

function delete_client_location($LocationID, $UserGroup){
	if($UserGroup == 3){
	$client_id = get_client_by_location($LocationID);
	delete_location($LocationID, $client_id);

	header("Location: ?page=client&type=overview&id={$client_id}&notify=3&cnotify=location");
	}
	
}

function new_checklist_question($data, $UserGroup){
	if($UserGroup == 3){
	$client_id = get_client_by_checklist($data['ChecklistID']);
	$question_id = add_checklist_question($data['cat_id'], $data['question'], $data['ChecklistID']);

	create_question_docfolder($data['ChecklistID'], $question_id);
	
	header("Location: ?page=client&type=overview&id={$client_id}&notify=1&cnotify=question");
	}
	
}

function save_checklist($data, $UserGroup){
	if($UserGroup == 3){
	$client_id = get_client_by_checklist($data['ChecklistID']);
	save_checklist_update($data['ChecklistID'], $data);

	header("Location: ?page=client&type=overview&id={$client_id}");
	}
	
}

function save_client_update($data, $UserGroup){
	if($UserGroup == 3){
	update_client($data['ClientID'], $data['Name'], $data['Address'], $data['City'], $data['State'], $data['Zip'], $data['Phone'], $data['Contact'], $data['Active']);

	header("Location: ?page=client&type=overview&id={$data['ClientID']}&notify=2");
	}
	
}

function delete_document($ChecklistID, $QuestionID, $Filename, $UserGroup){
	global $upload_root_directory;
	if($UserGroup == 3){
	if($QuestionID == '0'){
		unlink($upload_root_directory.$ChecklistID."/".urldecode($Filename));
	}
	else{
	unlink($upload_root_directory.$ChecklistID."/".$QuestionID."/".urldecode($Filename));}
	delete_question_doc($ChecklistID, $QuestionID, urldecode($Filename));
	if($QuestionID == '0'){
	header("Location: include/chip_upload/psummary.php?pstype=".$_GET['pstype']."&cl_id={$ChecklistID}&file={$Filename}&notify=success");
	}
	else
	header("Location: include/chip_upload/index.php?cl_id={$ChecklistID}&q_id={$QuestionID}&file={$Filename}&notify=success");
	}
	
}

function save_new_user($data, $UserGroup){
	global $root_directory,$validation_error;
	if($UserGroup == 3){
	include($root_directory.'include/validation.php');

	if(validateName($data['FirstName']) && validateName($data['LastName']) && validateName($data['Username']) && validateEmail($data['Email']) && validatePasswords($data['password1'], $data['password2']))
		{
	create_user($data['Username'], $data['password1'], $data['Email'], $data['FirstName'], $data['LastName'], $data['ClientID'], $data['AuthType']);
	header("Location: ?page=users&type=view&notify=1");
		}

	$validation_error = '<div id="error"><ul>';
	if(!validateName($data['FirstName'])):
		$validation_error .= '<li>First Name Required</li>';
	endif;
	if(!validateName($data['LastName'])):
		$validation_error .= '<li>Last Name Required</li>';
	endif;
	if(!validateEmail($data['Email'])):
		$validation_error .= '<li>Invalid Email Address</li>';
	endif;
	if(!validateName($data['Username'])):
		$validation_error .= '<li>Username Required</li>';
	endif;
	if(!validatePasswords($data['password1'], $data['password2'])):
		$validation_error .= '<li>Passwords Invalid or do not match</li>';
	endif;
	$validation_error .= '</ul></div>';

	}
	
}

function save_user_update($data, $UserGroup){
	if($UserGroup == 3){
	admin_update_user($data['Username'], $data['Email'], $data['FirstName'], $data['LastName'], $data['AuthType'], $data['UserID']);
		if($data['password1'] != NULL && $data['password2'] != NULL && $data['password1'] != '' && $data['password1'] == $data['password2']){
		
		update_password($data['UserID'], $data['password1']);
		}
		
	}
	header("Location: ?page=users&type=view&notify=2");
	
}
function admin_delete_user($data, $UserGroup){
	if($UserGroup == 3){
	delete_user($data, $_SESSION['UserGroup']);
	header("Location: ?page=users&type=view&notify=3");
	}
}

function delete_location_checklist($ChecklistID, $UserGroup){
	if($UserGroup == 3){
	$client_id = get_client_by_checklist($ChecklistID);
	delete_checklist($ChecklistID, $_SESSION['UserGroup']);
	header("Location: ?page=client&type=overview&id={$client_id}");
	}
}

function login($username, $password){


	$MM_redirectLoginSuccess = "?page=home";
	$MM_redirectLoginFailed = "?page=login&error=authfail";
	$MM_redirecttoReferrer = false;
	
	$ulogin = authenticate_user($username, $password);
	
	if ($ulogin) {
	$loginStrGroup = "";
	
	$_SESSION['Username'] = $username;
	$_SESSION['UserID'] = $ulogin['user_id'];
	$_SESSION['UserGroup'] = $ulogin['auth_type'];
	$_SESSION['ClientGroup'] = $ulogin['client_id'];
	$_SESSION['FirstName'] = $ulogin['first_name'];
	$_SESSION['LastName'] = $ulogin['last_name'];
	$_SESSION['Email'] = $ulogin['email'];
	if (isset($_SESSION['PrevUrl'])) {
	  $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
	}
	header("Location: " . $MM_redirectLoginSuccess );
	  }
	  else {
		$_SESSION['Username'] = NULL;
		$_SESSION['UserID'] = NULL;
		$_SESSION['UserGroup'] = NULL;
		$_SESSION['ClientGroup'] = NULL;
		$_SESSION['FirstName'] = NULL;
		$_SESSION['LastName'] = NULL;
	    header("Location: ". $MM_redirectLoginFailed );
	  }
}	

function logout($logoutGoTo = NULL){
	$_SESSION['Username'] = NULL;
	$_SESSION['UserID'] = NULL;
	$_SESSION['UserGroup'] = NULL;
	$_SESSION['ClientGroup'] = NULL;
	$_SESSION['FirstName'] = NULL;
	$_SESSION['LastName'] = NULL;
	unset($_SESSION['Username']);
	unset($_SESSION['UserID']);
	unset($_SESSION['UserGroup']);
	unset($_SESSION['ClientGroup']);
	unset($_SESSION['FirstName']);
	unset($_SESSION['LastName']);
	if ($logoutGoTo != "") {header("Location: $logoutGoTo");
	exit;
	}
	header("Location: main.php");
}

function username_check($username){
	print check_username(strtolower($username));
	exit;
}

function email_check($email){
	print check_email($email);
	exit();
}

function delete_question($ChecklistID,$QuestionID,$UserGroup){
	if($UserGroup == 3){
	delete_checklist_question($QuestionID);
	header("Location: ?page=checklist&cl_id=".$ChecklistID."&notify=3&cnotify=question");
	}
}

function action_handler($action, $data){
	global $root_directory;
	if($action == 'authenticate'){
		login($data['username'], $data['password']);
	}
	if($action == 'logout'){
		logout();
	}
	if($action == 'saveprofile'){
		edit_user_profile($data['firstname'], $data['lastname'], $data['email'], $data['password1'], $data['password2']);
	}
	if($action == 'addclient'){
	add_new_client($data, $_SESSION['UserGroup']);
	}
	if($action == 'addlocation'){
	add_new_location($data, $_SESSION['UserGroup']);
	}
	if($action == 'editlocation'){
	edit_client_location($data, $_SESSION['UserGroup']);
	}
	if($action == 'deletelocation'){
	delete_client_location($_GET['location'], $_SESSION['UserGroup']);
	}
	if($action == 'newchecklist'){
	add_new_checklist($_GET['location'], $_SESSION['UserGroup']);
	}
	if($action == 'savechecklist'){
	save_checklist($data, $_SESSION['UserGroup']);
	}
	if($action == 'addquestion'){
	new_checklist_question($data, $_SESSION['UserGroup']);
	}
	if($action == 'updateclient'){
	save_client_update($data, $_SESSION['UserGroup']);	
	}
	if($action == 'deletedoc'){
	delete_document($_GET['cl_id'], $_GET['q_id'], $_GET['file'], $_SESSION['UserGroup']);
	}
	if($action == 'newuser'){
	save_new_user($_POST, $_SESSION['UserGroup']);
	}
	if($action == 'updateuser'){
	save_user_update($_POST, $_SESSION['UserGroup']);
	}
	if($action == 'deleteuser'){
	admin_delete_user($_GET['UserID'], $_SESSION['UserGroup']);
	}
	if($action == 'loc_search'){
	global $search_results;
	$search_results = location_search($_POST['location']);
	}
	if($action == 'deletechecklist'){
	delete_location_checklist($_GET['cl_id'],$_SESSION['UserGroup']);
	}
	if($action == 'deletequestion'){
	delete_question($_GET['cid'],$_GET['qid'],$_SESSION['UserGroup']);
	}
	if($action == 'username_check'){
	username_check($_POST['Username']);
	exit();
	}
	if($action == 'passwordreset'){
		$email = $_POST['email'];
		$uname = $_POST['uname'];
		if($email == ""){$email = NULL;}
		if($uname == ""){$uname = NULL;}
		$pw_reset = forgot_password($email, $uname);
		if($pw_reset){
			header( 'Location: ?page=resetsent' );
		}
		else{
			header( 'Location: ?page=forgotpassword&error=notfound' );
		}

	}
	if($action == 'changepassword'){
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];
		$token = $_POST['token'];
		if($password1 == "" || $password2 == "" || $password1 != $password2){
			header( 'Location: ?page=forgotpassword&token='.$token.'&error=mismatch' );
		}
		else{
			$pwmessage = verify_token_pw_reset($token, $password1);
			header( 'Location: ?page=login&error='.$pwmessage );
		}

	}
	if($action == 'email_check'){
	check_email($_POST['Email']);
	}
	if($action == 'download'){
		if(verify_doc_access($_GET['file'], $_SESSION['UserID']))
			{
			$file = download_file_path($_GET['file']);
			$path = $file->fields[0];
			$name = $file->fields[1];
			$type = $file->fields[2];
			$mime = mimetype($type);
			if(!file_exists($path))
				{

		   			 die('Error: File not found.');
				}
			else
				{
				    // Set headers
				    header("Pragma: ");
				    header("Cache-Control: ");
				    header("Content-Description: File Transfer");
					header("Content-length: ".filesize($path));
				    header("Content-Disposition: attachment; filename=$name");
				    header("Content-Type: $mime");
				    header("Content-Transfer-Encoding: binary");
					readfile($path);
					exit();
				}
			}
	}
	
	if($action == 'downloadpdf'){
		if(verify_checklist_access($_GET['id'], $_SESSION['UserID'])){
			ob_end_clean ();
			include($root_directory.'include/pdf.php');
			completed_pdf_checklist($_GET['id'], 'D');
		}

	}
	if($action == 'downloadzip'){
		if(verify_checklist_access($_GET['id'], $_SESSION['UserID'])){
			prepare_client_zip($_GET['id']);
		}
	}
}
?>