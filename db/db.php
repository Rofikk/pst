<?php
//include('adodb.inc.php');



$conn = &ADONewConnection($dbtype);
$conn->PConnect($dbhost,$dbuser,$dbpass,$dbname);


function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;
  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}


//Start Checklist Create, Update, Delete Functions

//Create new checklist with default questions
function create_checklist($location, $client){
	global $conn;
	$sql = "insert into checklist (location_id, client_id) ";
	$sql .= "values (".$location.", ".$client.")";
	$conn->Execute($sql);
	
	$checklist_id = &$conn->Insert_ID();
	
	$sql = "insert into checklist_questions (checklist_id,q_questions_id,value,extra) values";
	
	$i = 1;
	while($i <= 47 ){
		$sql .= " (".$checklist_id.", ".$i.", 0, '')";
		if($i < 47 ){
			$sql .= ", ";}
		$i++;
	}
	$conn->Execute($sql);
	
	return $checklist_id;
}

function get_checklist_max_question_id($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT MAX(checklist_question_id) from checklist_questions WHERE checklist_id = %s",
		GetSQLValueString($ChecklistID, "int"));
	
	$max_qid = $conn->Execute($sql);
	$max_qid = $max_qid->fields[0];

	return $max_qid;

	
}


//Delete single question
function delete_checklist_question($checklist_question_id){
	global $conn;
	$sql = sprintf("DELETE from checklist_questions WHERE checklist_question_id = %s",
			GetSQLValueString($checklist_question_id, "int")
	);
	$conn->Execute($sql);
	$sql = sprintf("DELETE from checklist_doc WHERE checklist_question_id = %s",
		GetSQLValueString($checklist_question_id, "int"));
	$conn->Execute($sql);
}

//Add single question
function add_checklist_question($q_cat_id, $question, $checklist_id){
	global $conn;
	$sql = sprintf("insert into q_questions (q_cat_id, value) values (%s, %s)",
			GetSQLValueString($q_cat_id, "int"),
			GetSQLValueString($question, "text")
	);
	$conn->Execute($sql);
	
	$q_question_id = &$conn->Insert_ID();
	
	$sql = sprintf("insert into checklist_questions (checklist_id,q_questions_id) values (%s, %s)",
			GetSQLValueString($checklist_id, "int"),
			GetSQLValueString($q_question_id, "int")
	);
	$conn->Execute($sql);
	$doc_question_id = &$conn->Insert_ID();
	return $doc_question_id;
}

//Add Custom Value to end of question
function add_question_note($checklist_question_id, $note){
	global $conn;
	$sql = sprintf("UPDATE checklist_questions SET extra = %s WHERE checklist_question_id = %s",
			GetSQLValueString($note, "text"),
			GetSQLValueString($checklist_question_id, "int")
	);
	$conn->Execute($sql);
}

//Lookup all checklist question ids
function cl_question_ids($checklist_id){
	global $conn;
	$UpdateSet = &$conn->Execute(sprintf('select checklist_question_id, value from checklist_questions where checklist_id = %s', GetSQLValueString($checklist_id, "int")));
	
	return $UpdateSet;
}

//Save update to question value to db
function save_checklist_value($checklist_question_id, $value){
	global $conn;
	$conn->Execute(sprintf('UPDATE checklist_questions SET value = %s WHERE checklist_question_id = %s', 
											GetSQLValueString($value, "int"),
											GetSQLValueString($checklist_question_id, "int")));
}

//Save update to checklist optimized to only save changed values
function save_checklist_update($checklist_id, $values){
	global $conn;
	
	//lookup expected post variables
	$UpdateSet = cl_question_ids($checklist_id);
	while (!$UpdateSet->EOF) {
		//if no change in value do not run update query
		if($UpdateSet->fields[1] != $values[$UpdateSet->fields[1]]){
			save_checklist_value($UpdateSet->fields[0], $values[$UpdateSet->fields[0]]);
		}
		
$UpdateSet->MoveNext();
	}
}

function update_checklist_options($checklist_id, $project_type, $tank_type, $prepared_by_name, $prepared_by_phone, $completed, $completed_date, $ccemail = ""){
	global $conn;
	
	$sql = sprintf("UPDATE checklist SET project_type = %s, tank_type = %s, prepared_by_name = %s, prepared_by_phone = %s, completed_date = %s, completed = %s, ccemail = %s WHERE checklist_id = %s",
	GetSQLValueString($project_type, "text"),
	GetSQLValueString($tank_type, "text"),
	GetSQLValueString($prepared_by_name, "text"),
	GetSQLValueString($prepared_by_phone, "text"),
	GetSQLValueString($completed_date, "text"),
	GetSQLValueString($completed, "int"),
	GetSQLValueString($ccemail, "text"),
	GetSQLValueString($checklist_id, "int")
	);
	$conn->Execute($sql);
	
}

function get_checklist_options($checklist_id){
	global $conn;
	
	$sql = sprintf("SELECT project_type, tank_type, prepared_by_name, prepared_by_phone, completed_date, completed, ccemail from checklist WHERE checklist_id = %s",
	GetSQLValueString($checklist_id, "int"));
	
	$data = $conn->Execute($sql);
	return $data;
	
	
}

//Retreieve all questions for a checklist
function get_checklist_questions($checklist_id){
	global $conn;
	$recordSet = $conn->Execute(sprintf('select cq.checklist_question_id, cq.value, q.q_cat_id, q.value, cq.extra from checklist_questions AS cq, q_questions AS q where cq.q_questions_id = q.q_question_id AND cq.checklist_id = %s ORDER BY q_cat_id, checklist_question_id', $checklist_id));

	return $recordSet;

}

function get_checklist_note($checklist_id, $alerts = FALSE){
	global $conn;
	$cid = GetSQLValueString($checklist_id, "int");
	$sql = "SELECT DATE_FORMAT(date,'%m-%d-%Y'), note, checklist_note_id, alert from checklist_notes where checklist_id = ".$cid." ORDER BY date DESC";
	
	if($alerts){
			$sql = "SELECT DATE_FORMAT(date,'%m-%d-%Y'), note, checklist_note_id, alert from checklist_notes where alert = 1 AND checklist_id = ".$cid." ORDER BY date DESC";
	}
	
	$data = $conn->Execute($sql);
	if($data->RecordCount() == 0){
		return false;}
	else return $data;
}

function get_note($checklist_note_id){
	global $conn;
	
	$sql = sprintf("SELECT * from checklist_notes where checklist_note_id = %s",
		GetSQLValueString($checklist_note_id, "int"));
	$data = $conn->Execute($sql);
	$note['NoteID'] = $data->fields[0];
	$note['ChecklistID'] = $data->fields[1];
	$note['Note'] = $data->fields[3];
	$note['Date'] = $data->fields[2];
	$note['Alert'] = $data->fields[4];
	
	return $note;
	
}

 

function add_checklist_note($checklist_id, $note, $date, $alert = "0"){
	global $conn;
	
	$sql = sprintf("INSERT into checklist_notes (checklist_id, date, note, alert) VALUES (%s, %s, %s, %s)",
		GetSQLValueString($checklist_id, "int"),
		GetSQLValueString($date, "text"),
		GetSQLValueString($note, "text"),
		GetSQLValueString($alert, "int"));
		
	$conn->Execute($sql);
	
}

function edit_checklist_note($checklist_note_id, $note, $date, $alert){
	global $conn;
	
	$sql = sprintf("UPDATE checklist_notes SET date = %s, note = %s, alert = %s WHERE checklist_note_id = %s",
		GetSQLValueString($date, "text"),
		GetSQLValueString($note, "text"),
		GetSQLValueString($alert, "int"),
		GetSQLValueString($checklist_note_id, "int"));
		
	$conn->Execute($sql);
	
}

function delete_checklist_note($checklist_note_id){
	global $conn;
	
	$sql = sprintf("DELETE from checklist_notes WHERE checklist_note_id = %s",
		GetSQLValueString($checklist_note_id, "int"));
		
	$conn->Execute($sql);
}

// Get all category names and count questions for checklist in each category  type=names for category names type=qcount for questions in each category
function get_category($type, $checklist_id){
	global $conn;
	$category_set = &$conn->Execute('select name from q_cat order by q_cat_id');
	$category = array();
	$c = 1;
	
	while (!$category_set->EOF) {
		
				$category[$c] = strtoupper($category_set->fields[0]);
				
			//count questions in each category
				$catqcountset = &$conn->Execute(sprintf('select count(cq.checklist_question_id) from checklist_questions AS cq, q_questions AS q where cq.q_questions_id = q.q_question_id AND cq.checklist_id = %s AND q.q_cat_id = %s', $checklist_id, $c));
				$catqcount[$c] = $catqcountset->fields[0];
				$c++;

		$category_set->MoveNext();
	}
if($type == "names"){
	return $category;}
if($type == "qcount"){
	return $catqcount;}
}

function categories(){
	global $conn;
	
	$sql = "SELECT * from q_cat";
	$data = $conn->Execute($sql);
	
	return $data;
	
}

//Percent of checklist completed in whole number
function checklist_percent_complete($checklist_id){
	global $conn;
	$num_questions = &$conn->Execute(sprintf('SELECT COUNT(checklist_question_id) AS checklist_total_q from checklist_questions WHERE checklist_id = %s', $checklist_id));
	$num_completed = &$conn->Execute(sprintf('SELECT COUNT(value) AS checklist_total_qa from checklist_questions WHERE value > 0 AND checklist_id = %s', $checklist_id));

$num_questions = $num_questions->fields[0];
$num_completed = $num_completed->fields[0];
if($num_questions == $num_completed){
	$percent_complete = "99"; }
else
$percent_complete = substr((($num_completed/$num_questions)*100),0,2);

return $percent_complete;

}

//Delete checklist and all checklist questions
function delete_checklist_database($checklist_id){
	global $conn;
	$sql = sprintf("DELETE from checklist WHERE checklist_id = %s",
		GetSQLValueString($checklist_id, "int"));
	$conn->Execute($sql);
	$sql = sprintf("DELETE from checklist_questions WHERE checklist_id = %s",
		GetSQLValueString($checklist_id, "int"));
	$conn->Execute($sql);
	$sql = sprintf("DELETE from checklist_auth WHERE checklist_id = %s",
		GetSQLValueString($checklist_id, "int"));
	$conn->Execute($sql);
	$sql = sprintf("DELETE from checklist_doc WHERE checklist_id = %s",
		GetSQLValueString($checklist_id, "int"));
	$conn->Execute($sql);
	$sql = sprintf("DELETE from checklist_notes WHERE checklist_id = %s",
		GetSQLValueString($checklist_id, "int"));
	$conn->Execute($sql);
	
}

function create_new_location($ClientID, $Ref = "", $Name = "", $Address = "", $City = "", $State = "", $Zip = "", $Engagement = "", $DrawingNumber = "", $ProjectManager = "", $Architect = "", $AF_Contractor = ""){
	global $conn;
	
	$sql = sprintf("INSERT into location (location_ref_id, client_id, name, address, city, state, zip, engagement, project_drawing_number, project_manager, architect, af_contractor) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
		GetSQLValueString($Ref, "text"),
		GetSQLValueString($ClientID, "int"),
		GetSQLValueString($Name, "text"),
		GetSQLValueString($Address, "text"),
		GetSQLValueString($City, "text"),
		GetSQLValueString($State, "text"),
		GetSQLValueString($Zip, "text"),
		GetSQLValueString($Engagement, "text"),
		GetSQLValueString($DrawingNumber, "text"),
		GetSQLValueString($ProjectManager, "text"),
		GetSQLValueString($Architect, "text"),
		GetSQLValueString($AF_Contractor, "text")
		);
	$conn->Execute($sql);
	
}

function update_location($data){
	global $conn;
	
	$sql = sprintf("UPDATE location SET location_ref_id = %s, name = %s, address = %s, city = %s, state = %s, zip = %s, engagement = %s, project_drawing_number = %s, project_manager = %s, architect = %s, af_contractor = %s WHERE location_id = %s",
		GetSQLValueString($data['location'], "text"),
		GetSQLValueString($data['name'], "text"),
		GetSQLValueString($data['address'], "text"),
		GetSQLValueString($data['city'], "text"),
		GetSQLValueString($data['state'], "text"),
		GetSQLValueString($data['zipcode'], "text"),
		GetSQLValueString($data['engagement'], "text"),
		GetSQLValueString($data['project_drawing_number'], "text"),
		GetSQLValueString($data['project_manager'], "text"),
		GetSQLValueString($data['architect'], "text"),
		GetSQLValueString($data['af_contractor'], "text"),
		GetSQLValueString($data['LocationID'], "int")
	);
	$conn->Execute($sql);
	
}

function delete_location($LocationID){
	global $conn;
	
	$Checklists = get_client_checklist_data($LocationID);
	
	if($Checklists){
		while(!$Checklists->EOF){
		delete_checklist($Checklists->fields[0]);

		$Checklists->MoveNext();
		}
	}
	$sql = sprintf("DELETE from location WHERE location_id = %s",
		GetSQLValueString($LocationID, "int"));
	$conn->Execute($sql);

}

function filetypes(){
	global $conn;
	
	$sql = "select * from filetype";
	$types = $conn->Execute($sql);
	
	
	return $types->GetArray();
}

function mimetype($extension){
	global $conn;
	
	$sql = sprintf("SELECT filetype_mime FROM filetype where filetype_name = %s",
		GetSQLValueString($extension, "text"));
		
	$data = $conn->Execute($sql);
	$data = $data->fields[0];
	return $data;
}

function add_question_doc($checklist_id, $checklist_question_id, $path, $filename, $filetype){
	global $conn;
	
	if($filename=="Closeout_Checklist.pdf" && $checklist_question_id=="0"){
		$deletesql = sprintf("DELETE FROM checklist_doc WHERE name = %s AND checklist_question_id = %s AND checklist_id = %s",
			GetSQLValueString($filename, "text"),
			GetSQLValueString($checklist_question_id, "int"),
			GetSQLValueString($checklist_id, "int"));
		$conn->Execute($deletesql);
		
	}
	$sql = sprintf("insert into checklist_doc (checklist_id, checklist_question_id, path, name, filetype) values (%s, %s, %s, %s, %s)",
		GetSQLValueString($checklist_id, "int"),
		GetSQLValueString($checklist_question_id, "int"),
		GetSQLValueString($path, "text"),
		GetSQLValueString($filename, "text"),
		GetSQLValueString($filetype, "text"));
		
	$conn->Execute($sql);


}

function delete_question_doc($checklist_id, $checklist_question_id, $filename){
	global $conn;
	
	$sql = sprintf("DELETE from checklist_doc WHERE checklist_id = %s AND checklist_question_id = %s AND name = %s",
		GetSQLValueString($checklist_id, "int"),
		GetSQLValueString($checklist_question_id, "int"),
		GetSQLValueString($filename, "text")
		);
		
	$conn->Execute($sql);
	
}

function count_checklist_doc($ChecklistID){
	global $conn;

	$sql = sprintf("SELECT checklist_question_id, COUNT(*) as 'total_docs' FROM checklist_doc GROUP BY checklist_question_id",
		GetSQLValueString($ChecklistID, "int"));

	$data = $conn->Execute($sql);
	$darray = array();
		while (!$data->EOF) {
			$darray[$data->fields[0]] = $data->fields[1];
		$data->MoveNext();
		}
	
	return $darray;
}

function check_title_page($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT * FROM checklist_doc WHERE checklist_id = %s AND filetype = 'TP'",
		GetSQLValueString($ChecklistID, "int"));
	$data = $conn->Execute($sql);
	$data = $data->RecordCount();
	if($data > 0){
	return true;}
	else return false;
}

function title_page_docs($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT * FROM checklist_doc WHERE checklist_id = %s AND filetype = 'TP'",
		GetSQLValueString($ChecklistID, "int"));
	$data = $conn->Execute($sql);
	$count = $data->RecordCount();
	
	if($count > 0){
	return $data;
	}
	else return false;
}

function check_table_of_contents($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT * FROM checklist_doc WHERE checklist_id = %s AND filetype = 'TC'",
		GetSQLValueString($ChecklistID, "int"));
	$data = $conn->Execute($sql);
	$data = $data->RecordCount();
	
	if($data > 0){
	return true;
	}
	else return false;
}

function table_of_contents_docs($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT * FROM checklist_doc WHERE checklist_id = %s AND filetype = 'TC'",
		GetSQLValueString($ChecklistID, "int"));
	$data = $conn->Execute($sql);
	$count = $data->RecordCount();
	
	if($count > 0){
	return $data;
	}
	else return false;
}

function check_project_summary($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT * FROM checklist_doc WHERE checklist_id = %s AND filetype = 'PS'",
		GetSQLValueString($ChecklistID, "int"));
	$data = $conn->Execute($sql);
	$data = $data->RecordCount();
	
	if($data > 0){
	return true;
	}
	else return false;
}

function project_summary_docs($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT * FROM checklist_doc WHERE checklist_id = %s AND filetype = 'PS'",
		GetSQLValueString($ChecklistID, "int"));
	$data = $conn->Execute($sql);
	$count = $data->RecordCount();
	
	if($count > 0){
	return $data;
	}
	else return false;
}

function project_summary_files($ChecklistID, $type){
	global $conn;
	
	$sql = sprintf("SELECT name FROM checklist_doc WHERE checklist_id = %s AND filetype = %s",
		GetSQLValueString($ChecklistID, "int"),
		GetSQLValueString($type, "text"));
	$data = $conn->Execute($sql);
	$count = $data->RecordCount();
	
	if($count > 0){
	return $data;
	}
	else return false;

}

function completed_checklist_doc($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT * from checklist_doc WHERE checklist_id = %s AND checklist_question_id = '0' AND name = 'Closeout_Checklist.pdf'",
		GetSQLValueString($ChecklistID, "int"));
	$data = $conn->Execute($sql);
	
	return $data;
		
}

function get_doc_folders($checklist_id){
	global $conn;
	
	$sql = sprintf("SELECT DISTINCT c_name, q_cat_id FROM doc_to_questions WHERE checklist_id = %s",
		GetSQLValueString($checklist_id, "int"));
	
	$data = $conn->Execute($sql);
	return $data;

}

function client_doc_tree($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT * from doc_to_questions WHERE checklist_id = %s ORDER BY q_cat_id, checklist_question_id",
		GetSQLValueString($ChecklistID, "int"));
	$data = $conn->Execute($sql);

	return $data;
}

function get_doc_subfolders($checklist_id){
	global $conn;
	
	$sql = sprintf("SELECT DISTINCT c_name, q_name, q_cat_id, checklist_question_id FROM doc_to_questions WHERE checklist_id = %s",
		GetSQLValueString($checklist_id, "int"));
	
	$data = $conn->Execute($sql);
	return $data;

}

function get_doc_path($checklist_id){
	global $conn;
	
	$sql = sprintf("SELECT path, c_name, q_name, filename, checklist_question_id, checklist_doc_id from doc_to_questions WHERE checklist_id = %s",
		GetSQLValueString($checklist_id, "int"));
	$data = $conn->Execute($sql);
	
	return $data;
	
}

function db_get_doc_photos($checklist_id){
	global $conn;
	
	$sql = sprintf("SELECT path, c_name, q_name, filename, checklist_question_id, checklist_doc_id from doc_to_questions WHERE checklist_id = %s  AND q_cat_id = '9'",
		GetSQLValueString($checklist_id, "int"));
	$data = $conn->Execute($sql);
	
	return $data;
	
}

function db_merge_pdf($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT path FROM doc_to_questions WHERE checklist_id = %s AND q_cat_id < 9 ORDER BY q_cat_id, filename",
		GetSQLValueString($ChecklistID, "int"));
		
	$data = $conn->Execute($sql);
	
	return $data;
}

function download_file_path($DocID){
	global $conn;
	
	$sql = sprintf("SELECT path, name, filetype FROM checklist_doc WHERE checklist_doc_id = %s",
		GetSQLValueString($DocID, "int"));
	
	$data = $conn->Execute($sql);
	
	return $data;

}


function create_client($business_name, $address, $city, $state, $zip, $phone, $contact_name, $active = 1){
	global $conn;
	$sql = sprintf("insert into client (business_name, address, city, state, zip, phone, contact_name, active) values (%s, %s, %s, %s, %s, %s, %s, %s)",
			GetSQLValueString($business_name, "text"),
			GetSQLValueString($address, "text"),
			GetSQLValueString($city, "text"),
			GetSQLValueString($state, "text"),
			GetSQLValueString($zip, "text"),
			GetSQLValueString($phone, "text"),
			GetSQLValueString($contact_name, "text"),
			GetSQLValueString($active, "int")
	);
	$conn->Execute($sql);
	$client_id = $conn->Insert_ID();
	
	return $client_id;
}

function suspend_client($client_id){
	global $conn;
	
	$sql = sprintf("UPDATE client SET active = 0 WHERE client_id = %s LIMIT 1",
		GetSQLValueString($client_id, "int"));
	$conn->Execute($sql);

}

function update_client($client_id, $business_name, $address, $city, $state, $zip, $phone, $contact_name, $active){
	global $conn;
	$sql = sprintf("UPDATE client SET business_name = %s, address = %s, city = %s, state = %s, zip = %s, phone = %s, contact_name = %s, active = %s WHERE client_id = %s",
			GetSQLValueString($business_name, "text"),
			GetSQLValueString($address, "text"),
			GetSQLValueString($city, "text"),
			GetSQLValueString($state, "text"),
			GetSQLValueString($zip, "text"),
			GetSQLValueString($phone, "text"),
			GetSQLValueString($contact_name, "text"),
			GetSQLValueString($active, "int"),
			GetSQLValueString($client_id, "int")
	);
	$conn->Execute($sql);
}

function get_all_clients(){
	global $conn;
	
	$sql = "SELECT client_id, business_name, city, state, contact_name, active FROM client";
	$results = $conn->Execute($sql);
	
	return $results;
	
}

function get_client_info($client_id){
	global $conn;
	$sql = sprintf("SELECT * from client WHERE client_id = %s",
		GetSQLValueString($client_id, 'int')
		);
	$data = $conn->Execute($sql);
	$clientdata = array();
	$clientdata['ClientID'] = $data->fields[0];
	$clientdata['Name'] = $data->fields[1];
	$clientdata['Address'] = $data->fields[2];
	$clientdata['City'] = $data->fields[3];
	$clientdata['State'] = $data->fields[4];
	$clientdata['Zip'] = $data->fields[5];
	$clientdata['Phone'] = $data->fields[6];
	$clientdata['Contact'] = $data->fields[7];
	$clientdata['Active'] = $data->fields[8];
	
	return $clientdata;
	
}

function get_location_info($LocationID){
	global $conn;
	
	$sql = sprintf("SELECT * FROM location WHERE location_id = %s",
		GetSQLValueString($LocationID, "int"));
	$data = $conn->Execute($sql);	
	
	if($data->RecordCount() == 0){
		return false;}
	else {
		$locdata = array();
		$locdata['LocationID'] = $data->fields[0];
		$locdata['RefName'] = $data->fields[1];
		$locdata['ClientID'] = $data->fields[2];
		$locdata['Name'] = $data->fields[3];
		$locdata['Address'] = $data->fields[4];
		$locdata['City'] = $data->fields[5];
		$locdata['State'] = $data->fields[6];
		$locdata['Zip'] = $data->fields[7];
		$locdata['Engagement'] = $data->fields[8];
		$locdata['DrawingNumber'] = $data->fields[9];
		$locdata['ProjectManager'] = $data->fields[10];
		$locdata['Architect'] = $data->fields[11];
		$locdata['AF_Contractor'] = $data->fields[12];
	return $locdata;
	}
	
	
}

function get_client_locations($client_id){
	global $conn;
	$sql = sprintf("SELECT location_id, location_ref_id, name, address, city, state, zip from location WHERE client_id = %s ORDER BY location_ref_id",
		GetSQLValueString($client_id, "int")
		);
	$LocationData = $conn->Execute($sql);
	
	if($LocationData->RecordCount() == 0){
		return false;}
	else {

	return $LocationData;
	}
}

function get_client_locations_status($client_id){
	global $conn;
	
	$sql = sprintf("SELECT location_id, checklist_id, completed FROM checklist WHERE client_id = %s",
		GetSQLValueString($client_id, "int"));
		
	$ld = $conn->Execute($sql);
	
	if($ld->RecordCount() == 0){
		return false;}
	else {
		$status = array();
		while(!$ld->EOF){
			$status[$ld->fields[0]][$ld->fields[1]] = $ld->fields[2];

			$ld->MoveNext();
		}
	return $status;
	}
}

function get_client_by_location($LocationID){
	global $conn;
	
	$sql = sprintf("SELECT client_id FROM location WHERE location_id = %s",
		GetSQLValueString($LocationID, "int")
		);
		
	$data = $conn->Execute($sql);
	$data = $data->fields[0];
	
	return $data;
	
}

function get_client_by_checklist($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT client_id FROM checklist WHERE checklist_id = %s",
		GetSQLValueString($ChecklistID, "int")
		);
		
	$data = $conn->Execute($sql);
	$data = $data->fields[0];
	
	return $data;
	
}

function get_location_by_checklist($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT location_id FROM checklist WHERE checklist_id = %s",
		GetSQLValueString($ChecklistID, "int")
		);
		
	$data = $conn->Execute($sql);
	$data = $data->fields[0];
	
	return $data;
	
}

function get_link_data_by_checklist($ChecklistID){
	global $conn;
	
	$sql = sprintf("SELECT client_id, location_id FROM checklist WHERE checklist_id = %s",
		GetSQLValueString($ChecklistID, "int")
		);
		
	$data = $conn->Execute($sql);
	$rdata['ClientID'] = $data->fields[0];
	$rdata['LocationID'] = $data->fields[1];
	$rdata['Users'] = array();
	$rdata['Notes'] = FALSE;
	
	// Get checklist notes
	$notes = get_checklist_note($ChecklistID, TRUE);
	if($notes){
		while(!$notes->EOF){
			$rdata['Notes'][] = $notes->fields[1];
			$notes->MoveNext();
		}
	}
	
	
	
	
	$sql = sprintf("SELECT first_name, email FROM users WHERE client_id = %s AND type = %s",
		$rdata['ClientID'],
		"2");
	$results = $conn->Execute($sql);

	if($results){
		while(!$results->EOF){
			$rdata['Users'][] = array($results->fields[0], $results->fields[1]);
			$results->MoveNext();
		}
	}
	
	$locsql = sprintf("SELECT name, location_ref_id FROM location WHERE location_id = %s",
		$rdata['LocationID']);
		
	$locdata = $conn->Execute($locsql);
	$rdata['Location'] = $locdata->GetRowAssoc();
	
	return $rdata;
	
}

function get_client_checklist_data($LocationID){
	global $conn;
	
	$sql = sprintf("SELECT checklist_id, DATE_FORMAT(date, %s) AS date, completed, DATE_FORMAT(completed_date, %s) AS completed_date FROM checklist WHERE location_id = %s ORDER BY date",
		$string = "'%m-%d-%Y'",
		$string = "'%m-%d-%Y'",
		GetSQLValueString($LocationID, "int")
		);
	$checklistdata = $conn->Execute($sql);
	if($checklistdata->RecordCount() == 0){
		return false; }
	else 

	return $checklistdata;
}

function delete_client($client_id){
	global $conn;
	$sql = sprintf("DELETE from client WHERE client_id = %s LIMIT 1",
		GetSQLValueString($client_id, "int"));
		
	$sql = sprintf("DELETE from users WHERE client_id = %s",
		GetSQLValueString($client_id, "int"));
		
	$sql = sprintf("DELETE from checklist_auth WHERE client_id = %s",
		GetSQLValueString($client_id, "int"));
		
	$sql = sprintf("DELETE from checklist where client_id = %s",
		GetSQLValueString($client_id, "int"));
		
	
}

//Start User Auth and Security Functions

function get_client_users($ClientID){
	global $conn;
	
	$sql = sprintf("SELECT * from users where client_id = %s",
		GetSQLValueString($ClientID, "int"));
	$data = $conn->Execute($sql);
	if($data->RecordCount() == 0){
		return false;}
	else {
	return $data;
	}
}

function get_user_info($UserID){
	global $conn;
	
	$sql = sprintf("SELECT * FROM users WHERE user_id = %s",
		GetSQLValueString($UserID, "int"));
		
	$data = $conn->Execute($sql);
	
	return $data;
	
}

function check_username($username){
	global $conn;
	
	$sql = sprintf("SELECT username FROM users WHERE username = %s",
		GetSQLValueString(strtolower($username), "text"));
		
	$result = $conn->Execute($sql);
	
	$count = $result->RecordCount();
	
	return $count;
	
}

function check_email($email){
	global $conn;
	
	$sql = sprintf("SELECT email FROM users WHERE email = %s",
		GetSQLValueString(trim(strtolower($email)), "text"));
		
	//return GetSQLValueString(strtolower($email), "text");
	//exit;
	$result = $conn->Execute($sql);
	
	$count = $result->RecordCount();
	
	return $count;
	
}

function create_user($username, $password, $email, $firstname, $lastname, $client_id, $type){
	global $conn;
	$sql = sprintf("insert into users (username, password, email, first_name, last_name, client_id, type) values (%s, %s, %s, %s, %s, %s, %s)",
			GetSQLValueString($username, "text"),
			GetSQLValueString(md5($password), "text"),
			GetSQLValueString($email, "text"),
			GetSQLValueString($firstname, "text"),
			GetSQLValueString($lastname, "text"),
			GetSQLValueString($client_id, "int"),
			GetSQLValueString($type, "int")
	);
	$conn->Execute($sql);
}

function delete_user($user_id){
	global $conn;
	$sql = sprintf("DELETE from users WHERE user_id = %s",
		GetSQLValueString($user_id, "int"));
	$conn->Execute($sql);
	$sql = sprintf("DELETE from checklist_auth WHERE user_id = %s",
		GetSQLValueString($user_id, "int"));
	$conn->Execute($sql);
}

function update_user($userinfo, $UserID){
	global $conn;
	
	$sql = sprintf("UPDATE users SET email = %s, first_name = %s, last_name = %s WHERE user_id = %s",
		GetSQLValueString($userinfo['email'], "text"),
		GetSQLValueString($userinfo['firstname'], "text"),
		GetSQLValueString($userinfo['lastname'], "text"),
		GetSQLValueString($UserID, "int"));
	$conn->Execute($sql);

}	

function admin_update_user($Username, $Email, $FirstName, $LastName, $AuthType, $UserID){
	global $conn;
	
	$sql = sprintf("UPDATE users SET username = %s, email = %s, first_name = %s, last_name = %s, type = %s WHERE user_id = %s",
		GetSQLValueString($Username, "text"),
		GetSQLValueString($Email, "text"),
		GetSQLValueString($FirstName, "text"),
		GetSQLValueString($LastName, "text"),
		GetSQLValueString($AuthType, "int"),
		GetSQLValueString($UserID, "int"));
	$conn->Execute($sql);

}	

function add_user_checklist_auth($user_id, $checklist_id){
	global $conn;
	
	$sql = sprintf("select client_id from users where user_id = %s",
		GetSQLValueString($user_id, "int"));
	$client_id = &$conn->Execute($sql);
	$client_id = $client_id->fields[0];
	
	$sql = sprintf("insert into checklist_auth (user_id, checklist_id, client_id) values (%s, %s, %s)",
			GetSQLValueString($user_id, "int"),
			GetSQLValueString($checklist_id, "int"),
			GetSQLValueString($client_id, "int")
	);
	$conn->Execute($sql);
}

function remove_user_checklist_auth($user_id, $checklist_id){
	global $conn;
	
	$sql = sprintf("DELETE from checklist_auth WHERE user_id = %s AND checklist_id = %s",
		GetSQLValueString($user_id, "int"),
		GetSQLValueString($checklist_id, "int"));
	$conn->Execute($sql);
}

function get_userid_from_email($email){
	global $conn;
	
	$sql = sprintf("select user_id from users where email = %s",
		GetSQLValueString($email, "text"));
	$user_id = $conn->Execute($sql);
	
	if($user_id->RecordCount() == 0){
		return false;}
	else {
	return $user_id->fields[0];}
}

function get_userid_from_username($username){
	global $conn;
	
	$sql = sprintf("select user_id from users where username = %s",
		GetSQLValueString($username, "text"));
	$user_id = $conn->Execute($sql);
	
	if($user_id->RecordCount() == 0){
		return false;}
	else {
	return $user_id->fields[0];}
}

function verify_checklist_access($checklist_id, $user_id){
		global $conn;
		
		$sql = sprintf("SELECT users.user_id, checklist.checklist_id FROM checklist LEFT OUTER JOIN users ON checklist.client_id = users.client_id WHERE user_id = %s AND checklist_id = %s",
			GetSQLValueString($user_id, "int"),
			GetSQLValueString($checklist_id, "int"));
		$auth = $conn->Execute($sql);
		$auth = $auth->RecordCount();
		if($auth < 1){
		return false;	
		}
		else return true;
		
}

function verify_doc_access($DocID, $UserID){
	global $conn;
	
	$sql = sprintf("SELECT checklist_id FROM checklist_doc WHERE checklist_doc_id = %s",
		GetSQLValueString($DocID, "int"));
		
	$data = $conn->Execute($sql);
	$ChecklistID = $data->fields[0];
	
	return verify_checklist_access($ChecklistID, $UserID);
	
}

function check_single_checklist_access($checklist_id, $user_id){
	global $conn;
	
	$sql = sprintf("SELECT user_id from checklist_auth where user_id = %s AND checklist_id = %s",
		GetSQLValueString($user_id, "int"),
		GetSQLValueString($checklist_id, "int"));
	$auth = $conn->Execute($sql);
	$auth = $auth->RecordCount();
	if($auth < 1){
		return false;	
		}
	else return true;
}

//Generate password reset token, remove any old password reset tokens and return user info
function password_reset_token($user_id){
	global $conn;

	$token = substr(md5(uniqid(rand())), 0, 75);
	$pw_reset = array();
	$pw_reset['token'] = $token;
	$user_info = $conn->Execute(sprintf('select username, email, first_name, last_name from users where user_id = %s', $user_id));
	$pw_reset['username'] = $user_info->fields[0];
	$pw_reset['email'] = $user_info->fields[1];
	$pw_reset['first_name'] = $user_info->fields[2];
	$pw_reset['last_name'] = $user_info->fields[3];
	
	$sql = sprintf("DELETE from pw_reset WHERE user_id = %s",
		GetSQLValueString($user_id, "int"));
	$conn->Execute($sql);
	
	$sql = sprintf("insert into pw_reset (user_id, token) values (%s, %s)",
		GetSQLValueString($user_id, "int"),
		GetSQLValueString($token, "text"));
	$conn->Execute($sql);

	return $pw_reset;
}

function token_check($token){
	global $conn;
	
	$sql = sprintf("select id, user_id from pw_reset where token = %s",
		GetSQLValueString($token, "text"));
	$tc = $conn->Execute($sql);
	if($tc->RecordCount() == 0){
		return false;}
	else {
	$t = array();
	$t['token_id'] = $tc->fields[0];
	$t['user_id'] = $tc->fields[1];
	return $t;	
		
	}
}

function delete_token($token_id){
	global $conn;
	
	$sql = sprintf("DELETE from pw_reset where id = %s",
		GetSQLValueString($token_id, "int"));
	$conn->Execute($sql);
	
}
function update_password($user_id, $password){
	global $conn;
	
	$sql = sprintf("UPDATE users SET password = %s WHERE user_id = %s",
		GetSQLValueString(md5($password), "text"),
		GetSQLValueString($user_id, "int"));
	$conn->Execute($sql);
	
}
function authenticate_user($username, $password){
	global $conn;
	
	$sql = sprintf("select user_id, first_name, last_name, client_id, type, email from users where username = %s AND password = %s",
		GetSQLValueString($username, "text"),
		GetSQLValueString(md5($password), "text"));
	$userset = $conn->Execute($sql);
	if($userset->RecordCount() == 0){
		return false;}
	else {
	$user = array();
	$user['user_id'] = $userset->fields[0];
	$user['first_name'] = $userset->fields[1];
	$user['last_name'] = $userset->fields[2];
	$user['client_id'] = $userset->fields[3];
	$user['auth_type'] = $userset->fields[4];
	$user['email'] = $userset->fields[5];
	return $user;	
		
	}
	
}

function search($searchterm){
	global $conn;
	$lookup = GetSQLValueString($searchterm, "text");
	$lookup = str_replace("'", "", $lookup);
	$sql = "SELECT * from search_table WHERE location_ref_id LIKE '%".$lookup."%'";
		
	$results = $conn->Execute($sql);
	return $results;


}

function dropdown_location_data(){
	global $conn;
	$sql = "SELECT DISTINCT location_ref_id, client_id, location_id, city FROM search_table ORDER BY location_ref_id";
	
	$data = $conn->Execute($sql);
	
	return $data;
}
function checklist_pdf($ChecklistID){
	global $conn;

	$sql = sprintf("SELECT cq.checklist_question_id AS checklist_question_id, cq.value as q_value, cq.extra AS q_extra, qq.value AS q_name, qc.q_cat_id, qc.name AS q_cat FROM `checklist_questions` AS cq, q_questions AS qq, q_cat AS qc WHERE qq.q_cat_id = qc.q_cat_id AND qq.q_question_id = cq.q_questions_id AND cq.checklist_id = %s ORDER BY qc.q_cat_id ASC, qq.q_question_id ASC",
		GetSQLValueString($ChecklistID, "int"));
	$data['questions'] = $conn->Execute($sql);
	
	$sql = sprintf("SELECT c.project_type, c.tank_type, l.name, l.location_ref_id, l.address, l.city, l.state, l.zip, l.engagement, l.project_drawing_number, l.project_manager, l.architect, l.af_contractor, c.prepared_by_name, c.prepared_by_phone, DATE_FORMAT(c.completed_date, %s) AS completed_date FROM checklist AS c, location AS l WHERE l.location_id = c.location_id AND c.checklist_id = %s",
		$string = "'%m-%d-%Y'",
		GetSQLValueString($ChecklistID, "int"));
	$data['info'] = ($conn->Execute($sql));
	$data['info'] = $data['info']->GetRowAssoc();

	return $data;
}

?>