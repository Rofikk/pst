<?php
require($root_directory.'fpdf/fpdf.php');
include($root_directory.'include/config.php');


class PDF extends FPDF
{
// Page header
function Header()
{
	global $root_directory;
    // Logo
    $this->Image($root_directory.'images/logo.jpg',10,6,30);
    // Arial bold 15
    $this->SetFont('Arial','BU',13);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(30,0,'FUEL SYSTEM/TANK MONITORING',0,1,'C');
    // Line break
    $this->Cell(80);
	$this->Cell(30,12,'PROJECT CLOSEOUT PACKAGE',0,1,'C');
	$this->Ln(5);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','B',8);
	$this->SetX(80);
	$this->Cell(0,10,'AT&T Proprietary (Internal Use Only)',0,0);
    // Page number

    $this->Cell(0,10,'Page '.$this->PageNo().' of {nb}',0,0,'R');
}

function checklistTable($header, $data)
{
	global $root_directory;
    // Column widths
    $w = array(7, 7, 5, 0);
    // Header
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],0,0,'L');
    $this->Ln();
    // Data
    $dataw = array(7,21,0);
    foreach($data as $row)
    {
    	if($row[0]===TRUE){
    	$curX = $this->GetX();
		$curY = $this->GetY();
    	$this->Image($root_directory.'images/icons/checked_box.png',NULL,NULL,5,5);
		$this->SetXY($curX+7, $curY);
		}
		else{
		$curX = $this->GetX();
		$curY = $this->GetY();
		$this->Image($root_directory.'images/icons/check_box.png',NULL,NULL,5,5);
		$this->SetXY($curX+7, $curY);
		}
    	if($row[1]===TRUE){
    	$curX = $this->GetX();
		$curY = $this->GetY();
    	$this->Image($root_directory.'images/icons/checked_box.png',NULL,NULL,5,5);
		$this->SetXY($curX+21, $curY);
		}
		else{
		$curX = $this->GetX();
		$curY = $this->GetY();
		$this->Image($root_directory.'images/icons/check_box.png',NULL,NULL,5,5);
		$this->SetXY($curX+21, $curY);
		}
        $this->Cell($dataw[2],6,$row[2],0,0,'L');
        $this->Ln();
    }
    // Closing line
    
}


function Rotate($angle,$x=-1,$y=-1) {

        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)

        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            
            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    } 
}

function completed_pdf_checklist($ChecklistID, $FileType = 'D'){
	global $root_directory;
$pdfchecklist = checklist_pdf($ChecklistID);
if($pdfchecklist){
	$info = $pdfchecklist['info'];
	$ptype = unserialize($info['PROJECT_TYPE']);
	$ttype = unserialize($info['TANK_TYPE']);

// Instanciation of inherited class
$pdf = new PDF('P','mm','letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Project Type',0,0);
$pdf->SetFont('Times','U',12);

if(is_array($ptype)){
foreach($ptype as $v){
	switch($v){
		case "ptcr":
			$pttext = "Permanent Tank Closure/Removal";
			break;
		case "putc":
			$pttext = "Repair/Upgrade/Temporary Closure";
			break;
		case "tms":
			$pttext = "Tank Monitoring System";
			break;
		case "nti":
			$pttext = "New Tank Install";
			break;
	}
$pdf->SetXY(75.00125,$pdf->GetY());
$pdf->Cell(0,5,$pttext,0,1);
}}
if(!is_array($ptype)){
$pdf->SetXY(75.00125,$pdf->GetY());
$pdf->Cell(0,5,'',0,1);
}

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Tank Type',0,0);
$pdf->SetFont('Times','U',12);
if(is_array($ttype)){
foreach($ttype as $v){
	switch($v){
		case "ust":
			$tttext = "Underground Storage Tank";
			break;
		case "ast":
			$tttext = "Aboveground Storage Tank";
			break;
	}
$pdf->SetXY(75.00125,$pdf->GetY());
$pdf->Cell(0,5,$tttext,0,1);
}}
if(!is_array($ttype)){
$pdf->SetXY(75.00125,$pdf->GetY());
$pdf->Cell(0,5,'',0,1);
}

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Project Name/Description',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['NAME'],0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Location Code',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['LOCATION_REF_ID'],0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Address',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['ADDRESS'].', '.$info['CITY'].', '.$info['STATE'].' '.$info['ZIP'],0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Engagement Number',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['ENGAGEMENT'],0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Project Drawing Number',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['PROJECT_DRAWING_NUMBER'],0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'ATT Project Manager',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['PROJECT_MANAGER'],0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Architect',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['ARCHITECT'],0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Alliance/Fuel System Contractor',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['AF_CONTRACTOR'],0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Checklist Prepared By',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['PREPARED_BY_NAME'].' - '.$info['PREPARED_BY_PHONE'],0,1);

$pdf->SetFont('Times','B',12);
$pdf->Cell(65,5,'Project Completion Date',0,0);
$pdf->SetFont('Times','U',12);
$pdf->Cell(0,5,$info['COMPLETED_DATE'],0,1);
$pdf->Ln();
$pdf->SetFont('Times','U',12);
$pdf->Cell(80);
$pdf->Cell(30,15,'CHECKLIST',0,1);

$pdf->SetFont('Times','',12);
$pdf->Ln(5);
$tableStartX = $pdf->GetX();
$tableStartY = $pdf->GetY();
$pdf->Rotate(90);
$pdf->Write(4,"Included");
$pdf->Ln(4);
$pdf->Write(10, 'N/A');
$pdf->Rotate(0);
$tableStartX = $pdf->GetX();
$tableStartY = $pdf->GetY();
$pdf->SetXY($tableStartX,$tableStartY-10);

	$currentcat = $pdfchecklist['questions']->fields[4];
	$previouscat = $currentcat;
	$i = 1;
	$l = 'A';
	$catarray = array();
	$qarray = array();
	$catqcount = get_category('qcount', $ChecklistID);

	$total_cats = max( array_keys( $catqcount ) );
	while($i <= count($catqcount)){
		// Convert row array to field names
		$row = $pdfchecklist['questions']->GetRowAssoc();
		
		// Set the current category
		$currentcat = $row['Q_CAT_ID'];

		// If new category, then save array 
		if($currentcat != $previouscat){

				if(($pdf->GetY() + ($catqcount[$previouscat]*6)) > 250){
					
					$pdf->AddPage();
					$pdf->Ln(15);
					$tableStartX = $pdf->GetX();
					$tableStartY = $pdf->GetY();
					$pdf->Rotate(90);
					$pdf->Write(4,"Included");
					$pdf->Ln(4);
					$pdf->Write(10, 'N/A');
					$pdf->Rotate(0);
					$tableStartX = $pdf->GetX();
					$tableStartY = $pdf->GetY();
					$pdf->SetXY($tableStartX,$tableStartY-10);
				}
		$pdf->checklistTable($catarray, $qarray);
		$catarray = array();
		$qarray = "";
		$i++;
		$l = 'A';
		}
		switch($row['Q_VALUE']){
			case 0:
				$cb1 = FALSE;
				$cb2 = FALSE;
				break;
			case 1:
				$cb1 = TRUE;
				$cb2 = FALSE;
				break;
			case 2:
				$cb1 = FALSE;
				$cb2 = FALSE;
				break;
			case 3:
				$cb1 = FALSE;
				$cb2 = TRUE;
				break;
		}
		$qarray[] = array($cb1,$cb2,$l.". ".$row['Q_NAME']);
	
		// Set Category Info Array
		$catarray = array('','',$i.'.',$row['Q_CAT']);
		$l++;
		$previouscat = $row['Q_CAT_ID'];
		$qsum++;
	$pdfchecklist['questions']->MoveNext();
	}

switch($FileType){
	case 'D':
		$pdfname = 'Closeout_Checklist.pdf';
		$SaveAs = 'D';
		break;
	case 'F':
		$pdfname = $root_directory.'uploads/'.$ChecklistID.'/'.'Closeout_Checklist.pdf';
		$SaveAs = 'F';
		add_question_doc($ChecklistID, '0', $pdfname, 'Closeout_Checklist.pdf', 'pdf');
		break;
}


$pdf->Output($pdfname, $SaveAs);
}
}

?>