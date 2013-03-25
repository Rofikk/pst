<?php

$style = "";
$js = "";
$treeconfig = "";
$nodeconfig = "";
$pc_js = "";
$title = "";


function downloadpage(){
	$token = $_GET['token'];
	$type = $_GET['ftype'];
	if(isset($token) && isset($type)){
$top = <<< EOF
<br /><br />
<div align="center"><strong><a href="https://client.pstcompliance.com/download.php?token={$token}&type={$type}">Click here to download your file.</a><br /><br />
Please be patient as some files are quite large</strong>
<br /><br />
To download individual files for this project please log in below.<div>
EOF;
}
else $top = "";
$download = <<< EOF
{$top}
<br /><br />

<form name="login" method="POST" action="?action=authenticate">
<table border="0" cellpadding="3" cellspacing="1" style="margin-left:30px;">
<tr>
	<td colspan="3"><h1 class="header">Client Login</h1></td>
</tr>
<tr><td colspan="3">&nbsp;</td></tr>
<tr>
    <td><strong>Username:</strong></td>
    <td><input name="username" type="text" id="username" class="input" /></td>
</tr>
<tr>
    <td><strong>Password:</strong></td>
    <td><input name="password" type="password" id="password" class="input" /></td>
</tr>
<tr><td colspan="3"><div style="color:red">{$message}&nbsp;</div></td></tr>
<tr>
	<td colspan="3"><input type="submit" name="Submit" value="Login" /></td>
</tr>
<tr>
	<td colspan="3">&nbsp;</td>
</tr>
<tr>
	{$pwlink}
</tr>
</table>
</form>


<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
EOF;

return $download;
}

function profilepage() {
$profile = <<< EOF
<form method="POST" action="?page=profile&action=saveprofile">
<table border="0" cellpadding="3" cellspacing="1">
<tr>
<td colspan="3"><h1 class="header">Edit User Profile</h1></td>
</tr>
<tr><td colspan="3">&nbsp;</td></tr>
<tr>
<td align="right"><strong>First Name:<strong></td>
<td><input name="firstname" type="text" class="input" id="firstname" value="{$_SESSION['FirstName']}" /></td>
</tr>
<tr>
<td align="right"><strong>Last Name:<strong></td>
<td><input name="lastname" type="text" id="lastname" class="input" value="{$_SESSION['LastName']}" /></td>
</tr>
<tr>
<td align="right"><strong>Email:<strong></td>
<td><input name="email" type="text" id="email" class="input" value="{$_SESSION['Email']}" /></td>
</tr>
<tr>
<td align="right"><strong>Change Password:<strong></td>
<td><input name="password1" type="password" class="input" id="password1" /></td>
</tr>
<tr>
<td align="right"><strong>Confirm Password:<strong></td>
<td><input name="password2" type="password" class="input" id="password2" /></td>
</tr>
<tr><td colspan="3">&nbsp;</td></tr>
<tr>
<td colspan="3" align="right"><input type="submit" name="Submit" value="Save Profile"></td>
</tr>
</table>
</form>
EOF;

return $profile;
}


function documentspage(){
	global $js, $style;
	
	
	$js .= '<link href="css/filedir.treeTable.css" rel="stylesheet" type="text/css" />';
	
	$js .= <<< EOF
		<script type="text/javascript" src="js/tt.jquery.js"></script>
		<script type="text/javascript" src="js/jquery.ui.js"></script>
		<link href="css/jquery.treeTable.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="js/jquery.treeTable.js"></script>

		
  <script type="text/javascript">

  $(document).ready(function() {
    // TODO Fix issue with multiple treeTables on one page, each with different options
    // Moving the #example3 treeeTable call down will break other treeTables that are expandable...
    $("#example3").treeTable({
      expandable: false
    });

    $(".example").treeTable();



    // Make visible that a row is clicked
    $("table#dnd-example tbody tr").mousedown(function() {
      $("tr.selected").removeClass("selected"); // Deselect currently selected rows
      $(this).addClass("selected");
    });

    // Make sure row is selected when span is clicked
    $("table#dnd-example tbody tr span").mousedown(function() {
      $($(this).parents("tr")[0]).trigger("mousedown");
    });
  });

  </script>

	
EOF;
	
	
	if(checklist_access_check($_GET['id'], $_SESSION['UserID']))
	{
		$file_table = client_files_table($_GET['id']);
		$page = <<< EOF
{$file_table}
EOF;


	}
else{
	$page = "You are not authorized to view this page";
	
	}
return $page;
}

function checklistpage(){
	$checklist_id = GetSQLValueString($_GET['cl_id'], "int");
	
	$notes = get_checklist_note($checklist_id);
	if($notes){
		$notetable = <<< EOF
<table width="100%" class="clients">
<tr><th>Notes</th></tr>
<tr><td><ul>
EOF;
	while(!$notes->EOF){
		$date = $notes->fields[0];
		$text = $notes->fields[1];
		$notetable .= <<< EOF
<li>{$date} - {$text}</li>
EOF;
		$notes->MoveNext();
	}
		$notetable .= '</ul></td></tr></table><hr />';
		
	}
	else {
		$notetable = "";
	}
	
	$table .= generate_client_checklist($checklist_id);
	$table .= "<br /><br /><br /><br /><br /><br />";
$checklist = <<< EOF
{$notetable}
{$table}
EOF;
return $checklist;
	
	
}

function overviewlocationspage(){
		global $js, $style;

		$style .= <<< EOF
		<link href="css/jquery.treeTable.css" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="css/tabcontent.css" type="text/css" />
EOF;

		$ClientData = get_client_info($_SESSION['ClientGroup']);
		$clientID = $_SESSION['ClientGroup'];
		$loc = get_client_locations($clientID);
		$completed = get_client_locations_status($clientID);
		$r = 1;
		
		$table = '<ul class="tabs" persist="true">';
		$table .= '<li><a href="#" rel="view1">Open</a></li>'."\n";
		$table .= '<li><a href="#" rel="view2">Completed</a></li>'."\n";
		$table .= '</ul>'."\n";
		$table .= '<div class="tabcontents">'."\n";
		$table .= '<div id="view1" class="tabcontent">'."\n";
		if($loc){
		$table .= '<table id="tree" width="100%" class="clients">'."\n";
        $table .= '<col width="45%" />'."\n";
        $table .= '<col width="35%" />'."\n";
        $table .= '<col width="20%" />'."\n";
		$table .= '<thead>'."\n".'<tr>'."\n".'<th align="left" >Location</th>'."\n".'<th align="left" >Progress</th>'."\n".'<th align="center">Options</th>'."\n".'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
		$r = 1;
		while (!$loc->EOF) {
		$viewable = FALSE;
		foreach($completed[$loc->fields[0]] as $k => $v){
				if($v != 1){
					$viewable = TRUE;
				}
			}
		if(!$completed[$loc->fields[0]]){$viewable = TRUE;}
		if($viewable === TRUE){
			
		$office = '';
		if(strlen($loc->fields[2])>38){
			$office = substr($loc->fields[2],0,38).'...';
			}
		else {
			$office = $loc->fields[2];
			}
		$table .= '<tr id="node-'.$loc->fields[0].'">'. "\n";
		$table .= '<td align="left"><strong>'.$loc->fields[1].'</strong><br />'.$office.'<br />'.$loc->fields[3].'<br />'.$loc->fields[4].' '.$loc->fields[5].'</td>'. "\n";
		$table .= '<td align="left">&nbsp;</td>'."\n";
		$table .= '<td align="center">&nbsp;</td>'. "\n";
		$table .= '</tr>'."\n";
		}
		$clist = get_client_checklist_data($loc->fields[0]);
		$clc = 1;
		if($clist){
		while (!$clist->EOF) {
			if($completed[$loc->fields[0]][$clist->fields[0]] != 1){
				if ($clc % 2) {
				$rowcolor = "#FAFAD2";
				}
			else $rowcolor = "#F5DEB3";
			if(isset($_GET['node']) && isset($_GET['cl_id']) && $_GET['cl_id'] == $clist->fields[0]){
				$rowcolor = "#4BB44B";
			}
				if($clist->fields[2]==1){
					$progress = "Completed: ".$clist->fields[3];
				}
				else $progress = '<span id="element'.$clist->fields[0].'">[ Loading Progress Bar ]</span>';
			$table .= '<tr id="node-'.$loc->fields[0].'.'.$clc.'" class="child-of-node-'.$loc->fields[0].'"  bgcolor="'.$rowcolor.'">';
			$table .= '<td><a href="?page=checklist&cl_id='.$clist->fields[0].'" >'.$clist->fields[1].'</a></td>';
			$table .= '<td align="left">'.$progress.'</td>'."\n";
			$table .= '<td align="center"><a href="?page=documents&id='.$clist->fields[0].'" title="View Project Documents"><img src="./images/icons/folder.png" /></a>';
			if($clist->fields[2]==1){
			$table .= '&nbsp;<a href="?action=downloadpdf&id='.$clist->fields[0].'" title="Download Project Checklist PDF" ><img src="./images/icons/file-pdf.png" /></a>';
			$table .= '&nbsp;<a href="?action=downloadzip&id='.$clist->fields[0].'" title="Download Zip File"><img src="./images/icons/file-zip.png" /></a></td>';
			}
			else{
			$table .= '&nbsp;<img src="./images/icons/file-pdf-inactive.png" title="Not Available" />';
			$table .= '&nbsp;<img src="./images/icons/file-zip-inactive.png" title="Not Available" /></td>';
			}
			$table .= '</tr>';
			$clc++;
			$percent_complete = checklist_percent_complete($clist->fields[0]);
			if($clist->fields[2]!=1){
		$pc_js .= <<< EOF
		
							manualPB{$clist->fields[0]} = new JS_BRAMUS.jsProgressBar(
										$('element{$clist->fields[0]}'),
										{$percent_complete},
										{
											animate     : false,
											barImage	: Array(
												'images/bramus/percentImage_back4.png',
												'images/bramus/percentImage_back3.png',
												'images/bramus/percentImage_back2.png',
												'images/bramus/percentImage_back1.png')});
					
EOF;
					}
			}
		$clist->MoveNext();
			}
			$r = 1;
		}
		$loc->MoveNext();
		}
		$table .= '</tbody>'."\n".'</table>'."\n";
		
		$table .= '</div>'."\n";
		
		// Create div with closed projects
		$ClientData = get_client_info($_SESSION['ClientGroup']);
		$clientID = $_SESSION['ClientGroup'];
		$loc = get_client_locations($clientID);
		$completed = get_client_locations_status($clientID);
		$r = 1;
		$table .= '<div id="view2" class="tabcontent">'."\n";
		if($loc){
		$table .= '<table id="tree2" width="100%" class="clients">'."\n";
        $table .= '<col width="45%" />'."\n";
        $table .= '<col width="35%" />'."\n";
        $table .= '<col width="20%" />'."\n";
		$table .= '<thead>'."\n".'<tr>'."\n".'<th align="left" >Location</th>'."\n".'<th align="left" >Progress</th>'."\n".'<th align="center">Options</th>'."\n".'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
		$r = 1;
		while (!$loc->EOF) {
		$viewable = FALSE;
		foreach($completed[$loc->fields[0]] as $k => $v){
				if($v == 1){
					$viewable = TRUE;
				}
			}
		if($viewable === TRUE){
			
		$office = '';
		if(strlen($loc->fields[2])>38){
			$office = substr($loc->fields[2],0,38).'...';
			}
		else {
			$office = $loc->fields[2];
			}
		$table .= '<tr id="node-'.$loc->fields[0].'">'. "\n";
		$table .= '<td align="left"><strong>'.$loc->fields[1].'</strong><br />'.$office.'<br />'.$loc->fields[3].'<br />'.$loc->fields[4].' '.$loc->fields[5].'</td>'. "\n";
		$table .= '<td align="left">&nbsp;</td>'."\n";
		$table .= '<td align="center">&nbsp;</td>'. "\n";
		$table .= '</tr>'."\n";
		}
		$clist = get_client_checklist_data($loc->fields[0]);
		$clc = 1;
		if($clist){
		while (!$clist->EOF) {
			if($completed[$loc->fields[0]][$clist->fields[0]] == 1){
				if ($clc % 2) {
				$rowcolor = "#FAFAD2";
				}
			else $rowcolor = "#F5DEB3";
			if(isset($_GET['node']) && isset($_GET['cl_id']) && $_GET['cl_id'] == $clist->fields[0]){
				$rowcolor = "#4BB44B";
			}
				if($clist->fields[2]==1){
					$progress = "Completed: ".$clist->fields[3];
				}
				else $progress = '<span id="element'.$clist->fields[0].'">[ Loading Progress Bar ]</span>';
			$table .= '<tr id="node-'.$loc->fields[0].'.'.$clc.'" class="child-of-node-'.$loc->fields[0].'"  bgcolor="'.$rowcolor.'">';
			$table .= '<td><a href="?page=checklist&cl_id='.$clist->fields[0].'" >'.$clist->fields[1].'</a></td>';
			$table .= '<td align="left">'.$progress.'</td>'."\n";
			$table .= '<td align="center"><a href="?page=documents&id='.$clist->fields[0].'" title="View Project Documents"><img src="./images/icons/folder.png" /></a>';
			if($clist->fields[2]==1){
			$table .= '&nbsp;<a href="?action=downloadpdf&id='.$clist->fields[0].'" title="Download Project Checklist PDF" ><img src="./images/icons/file-pdf.png" /></a>';
			$table .= '&nbsp;<a href="?action=downloadzip&id='.$clist->fields[0].'" title="Download Zip File"><img src="./images/icons/file-zip.png" /></a></td>';
			}
			else{
			$table .= '&nbsp;<img src="./images/icons/file-pdf-inactive.png" title="Not Available" />';
			$table .= '&nbsp;<img src="./images/icons/file-zip-inactive.png" title="Not Available" /></td>';
			}
			$table .= '</tr>';
			$clc++;
			$percent_complete = checklist_percent_complete($clist->fields[0]);
			if($clist->fields[2]!=1){
		$pc_js .= <<< EOF
		
							manualPB{$clist->fields[0]} = new JS_BRAMUS.jsProgressBar(
										$('element{$clist->fields[0]}'),
										{$percent_complete},
										{
											animate     : false,
											barImage	: Array(
												'images/bramus/percentImage_back4.png',
												'images/bramus/percentImage_back3.png',
												'images/bramus/percentImage_back2.png',
												'images/bramus/percentImage_back1.png')});
					
EOF;
					}
			}
		$clist->MoveNext();
			}
			$r = 1;
		}
		$loc->MoveNext();
		}
		$table .= '</tbody>'."\n".'</table>'."\n";
		}
		
		
		$table .= '</div>'."\n";
		
		
		$table .= "<script type=\"text/javascript\">
						document.observe('dom:loaded', function() {";
		$table .= $pc_js;
		$table .= '}, false);
		</script>';
		}
		if(isset($_GET['node'])){
			$treeconfig = "initialState: 'collapsed',\n		  	persist: false,";
			$nodeconfig = '$("#node-'.$_GET['node'].'").toggleBranch();';
		}
		else{
			$treeconfig = "initialState: 'collapsed',\n		  	persist: false,";
		}
		
			$js .= <<< EOF
		<!-- jsProgressBarHandler prerequisites : prototype.js -->
	<script type="text/javascript" src="js/prototype/prototype.js"></script>
	<!-- jsProgressBarHandler core -->
	<script type="text/javascript" src="js/bramus/jsProgressBarHandler.js"></script>
		<script type="text/javascript" src="js/jquery-1.6.4.min.js"></script>
		<script type="text/javascript" src="js/jquery.treeTable.min.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		<script type="text/javascript" src="js/cl_options_thickbox.js"></script>
		<script type="text/javascript" src="js/tabcontent.js"></script>
		<script type="text/javascript">
		
		$.noConflict();
		jQuery(document).ready(function($) {
		  $("#tree").treeTable(
		  {
		  	persist: true,
		  	persistCookiePrefix: "treeTable{$_GET['id']}_",
		  	{$treeconfig}
		  	clickableNodeNames: true
			});


		  $("#tree2").treeTable(
		  {
		  	persist: true,
		  	persistCookiePrefix: "treeTable2{$_GET['id']}_",
		  	{$treeconfig}
		  	clickableNodeNames: true
			});
			
		{$nodeconfig}
		}
		
		);
		
		</script>

		
EOF;
$overviewclient = <<< EOF
{$table}<br />


EOF;
return $overviewclient;
}


function homepage(){
$home = <<< EOF
<h1>Welcome {$_SESSION['FirstName']}</h1><br />
<a href="?page=profile">Profile</a><br />
<a href="?page=client">Clients</a><br />
<a href="?page=user">Users</a><br />
<a href="?page=location">Locations</a><br />
<a href="?action=logout">Logout</a>
EOF;
return $home;
}


function topbar(){
	if(isset($_SESSION['FirstName'])){
$date = date('l, F jS, Y');
$topbar = <<< EOF
<tr>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
<colgroup>
	<col width="30%" />
    <col width="40%" />
    <col width="30%" />
</colgroup>
      <tr>
        <th scope="row"><div align="left">Welcome {$_SESSION['FirstName']}</div></th>
        <th><div align="center">{$date}</div></th>
        <th><div align="right"><a href="?action=logout">Logout</a></div></th>
      </tr>
    </table>
</tr>
EOF;

return $topbar;
}
else return "";

}
function navbartop(){
	
	$navbar = <<< EOF
  <tr>
    <td>
    <table width="790" border="0" cellspacing="2" cellpadding="2">
      <tr>
        <td width="135" valign="top">
        <ul class="sidelinks">
          	<li><a href="?page=dashboard">Home</a></li>
          	<li><a href="?page=profile">Edit Profile</a></li>
        </ul>
        </td>
        <td width="641" valign="top">&nbsp;
EOF;
if(isset($_SESSION['FirstName'])){
return $navbar;	
}
else return "";
}


function navbarend(){
	
	$navbar = <<< EOF
	</td>
        </tr>
    </table>
    </td>
  </tr>
EOF;
if(isset($_SESSION['FirstName'])){
return $navbar;	
}
else return "";
}

function searchbar(){
	$dropdown = dropdown_locations();
	$searchbar = <<< EOF
	  <tr>
    <td>
	<hr />
	<form id="search" name="search" method="post" action="?action=loc_search">
	<table width="100%">
	<tr>
	<td width="70">&nbsp;</td>
	<td>
	<div align="center">
      Location Code:
      <input type="text" name="location" class="input" />
        <input type="submit" name="Submit" value="Search" />
      </div>
    </td>
    <td width="70">
    <div align="right">
    {$dropdown}
    </div>
    </td>
    </tr>
    </table>
    </form>
	<hr />
    </td>
  </tr>
EOF;

return $searchbar;	
	
}


$body_onload = <<< EOF
 onload="MM_preloadImages('images/1_on.png','images/2_on.png','images/3_on.png','images/4_on.png')"
EOF;

$style .= <<< EOF
<link href="css/styles.css" rel="stylesheet" type="text/css" />
EOF;


$js .= <<< EOF
<script type="text/javascript">
<!--
function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

//-->
</script>

<script type="text/javascript">


function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
//-->
</script>
EOF;

$footer = <<< EOF
<div id="footer">
<a href="http://www.pstcompliance.com/index.html">Home</a> &bull; 
<a href="http://www.pstcompliance.com/site-inspections-fuel-system-removals-dallas-fort-worth-arlington-texas.html">Services</a> &bull; 
<a href="https://client.pstcompliance.com">Client Login</a> &bull; 
<a href="http://www.pstcompliance.com/pst-compliance-dallas-fort-worth-arlington-texas.php">Contact Us</a><br />
<a href="http://www.webworkscorp.com/" target="_blank">Web design and hosting brought to you by Web Works</a>
</div>
EOF;


?>