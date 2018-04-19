<?php class RegimenFull{
function getDose_Frequency($regimen_id){
		require $_SERVER['DOCUMENT_ROOT'] ."/Connections/dbconnection.php";
    if (isset($database_dbconnection,$dbconnection)) {
        mysql_select_db($database_dbconnection, $dbconnection);
    }
		$sql = "SELECT * FROM regimens_full_data WHERE regimen_id ='".$regimen_id."'";
		$chk=mysql_query($sql,$dbconnection);
		$row_data = mysql_fetch_assoc($chk);
		$ret = '';
		do{
			$ret .= $row_data['dose'].' '. $row_data['frequency'];
		}while($row_data = mysql_fetch_assoc($chk));
		return $ret;
	}
	
function getStatus($regimen_id){
		require $_SERVER['DOCUMENT_ROOT'] ."/Connections/dbconnection.php";
    if (isset($database_dbconnection,$dbconnection)) {
        mysql_select_db($database_dbconnection, $dbconnection);
    }
		$sql = "SELECT * FROM regimens_full_data WHERE regimen_id ='".$regimen_id."'";
		$chk=mysql_query($sql,$dbconnection);
		$row_data = mysql_fetch_assoc($chk);
		return $row_data['status'];
}

//function getRegimenSummarizedStatus($r_groupID){
//	require $_SERVER['DOCUMENT_ROOT'] ."/Connections/dbconnection.php";
//	mysql_select_db($database_dbconnection, $dbconnection);
////	$sql = "SELECT * FROM `patient_regimens` WHERE regimen_group_id = '".$r_groupID."'";
//    $sql1 = "SELECT regimen_id, filled_by, completed_by FROM regimens_full_data WHERE regimen_id ='".$r_groupID."'";
//    $sql2 = $sql1." GROUP BY filled_by";
//    $sql3 = $sql1." GROUP BY completed_by";
//
//    $res1 = mysql_query($sql1, $dbconnection);
//    $res2 = mysql_query($sql2, $dbconnection);
//    $res3 = mysql_query($sql3, $dbconnection);
//
//
//	$chk=mysql_query($sql,$dbconnection);
//	$row_data = mysql_fetch_assoc($chk);
//
//// 	return $sql;
//	if($row_data['filled_by']!=NULL && $row_data['completed_by']==NULL){
//		return 'Filled but not completed';
//	}else if ($row_data['completed_by']!= NULL) {
//		return 'Completed';
//	}else{
//		return 'Not Filled, Not completed';
//	}
//}

function getRegimenDrugID($reg_id){
		require $_SERVER['DOCUMENT_ROOT'] ."/Connections/dbconnection.php";
    if (isset($database_dbconnection,$dbconnection)) {
        mysql_select_db($database_dbconnection, $dbconnection);
    }
		$sql = "SELECT * FROM regimens_full_data WHERE regimen_id ='".$reg_id."'";
		$chk=mysql_query($sql,$dbconnection);
		$row_data = mysql_fetch_assoc($chk);
		return $row_data['drugName'];
	}
	
function updateRegimen($reg, $drug,$action, $operator,$quantity=NULL){
	
	if(!isset($_SESSION)){session_start();}
	require $_SERVER['DOCUMENT_ROOT'] ."/Connections/dbconnection.php";
    if (isset($database_dbconnection,$dbconnection)) {
        mysql_select_db($database_dbconnection, $dbconnection);
    }
	if($action=='fill'){
		$sql="UPDATE regimens_full_data SET quantity=".$quantity.",status='filled',filled_by='".mysql_real_escape_string($operator)."' WHERE reg_id = ".mysql_real_escape_string($reg)." AND drugName = '".mysql_real_escape_string($drug)."'";
		//echo $sql;
		@mysql_query($sql,$dbconnection);
		return 'Fill updated';
	}
	else if($action=='complete') {
		$sql="UPDATE regimens_full_data SET status='completed',completed_by='".mysql_real_escape_string($operator)."' WHERE reg_id = ".mysql_real_escape_string($reg)." AND drugName = '".mysql_real_escape_string($drug)."'";
		@mysql_query($sql,$dbconnection);
		return 'Regimen Completed';
		}
	}

function searchRegimen($patientInfo){
	$ret ='<table class="table table-bordered table-hover">';
	require $_SERVER['DOCUMENT_ROOT'] ."/Connections/dbconnection.php";
    if (isset($database_dbconnection,$dbconnection)) {
        mysql_select_db($database_dbconnection, $dbconnection);
    }
	$sql = "SELECT r . * , d.fname, d.lname
	FROM `patient_regimens` r, patient_demograph d
	WHERE r.patient_ID = d.patient_ID
	AND (
			r.patient_ID LIKE '%".mysql_real_escape_string($patientInfo)."%'
			OR d.fname LIKE '%".mysql_real_escape_string($patientInfo)."%'
			OR d.lname LIKE '%".mysql_real_escape_string($patientInfo)."%'
			OR d.mname LIKE '%".mysql_real_escape_string($patientInfo)."%'
			OR d.phonenumber LIKE '%".mysql_real_escape_string($patientInfo)."%'
	) GROUP BY patient_ID";
	$chk=mysql_query($sql);
	if(mysql_num_rows($chk)>0){
		$ret.= '<thead><tr><th>Prescription Status</th><th>Patient EMR ID</th><th>Patient Name</th><th>Gender</th></tr></thead>';
		$row=mysql_fetch_assoc($chk);
		require 'class.patient.php';
		$pt = new Manager();
		do {
			$ret.= '<tr>';
//			$ret.= '<td>'. $this->getRegimenSummarizedStatus($row['regimen_group_id']) .'</td>';
            $ret.= '<td>'. strtoupper($this->getStatus($row['regimen_group_id'])) .'</td>';
			$ret.= '<td><a href="../patient_profile.php?id='.$row['patient_ID'].'">'.$row['patient_ID'].'</a></td>';
			$ret.= '<td>'.$pt->getPatientName($row['patient_ID']).'</td>';
			$info_ = $pt->getPatientInfo($row['patient_ID']);
			$info = explode("|", $info_);
			$ret.= '<td>'.ucfirst($info[2]).'</td>';
			$ret.= '</tr>';
		} while($row=mysql_fetch_assoc($chk));
	} else {
		$ret.= '<tr><td>No Prescription Found <!--for<em>'.$patientInfo.'</em><br/><small>Please use keywords for search</small>--></td></tr>';
	}
	$ret.= '</table>';
	return $ret;
}

function listRegimens($filter=NULL, $type=NULL){ //$type = search or incomplete
    $sql = "SELECT p.*,r.* FROM patient_regimens p LEFT JOIN regimens_full_data r ON p.regimen_group_id = r.regimen_id ";
    if(isset($filter)){
        $sql .= " AND (p.regimen_group_id LIKE '%".$filter."%' OR p.patient_ID LIKE '%".$filter."%')";
    }if(isset($type)){
        $sql .= " AND r.status = 'open'";
    }
    $sql .= " ORDER BY `when` DESC, patient_ID DESC";
    return $sql;
    require $_SERVER['DOCUMENT_ROOT'] ."/Connections/dbconnection.php";
    if (isset($database_dbconnection,$dbconnection)) {
        mysql_select_db($database_dbconnection, $dbconnection);
    }
    $rst = mysql_query($sql);
    $data = array();
    while ($row = mysql_fetch_assoc($rst)){
        $data[] = $row;
    };
    return $data;
}

    function formatPrescriptionList($row, $patient, $drug, $sql_minor){
        $staff = $patient->STAFF;
        $str = '<tr style="cursor:pointer" class="head-link" id="reg' . $row ['regimen_id'] . '">
    <td class="ui-bar-d"><img class="profile_thumbnail" style="display:inline;float:left" src="'.$patient->getPatientImage($row['pid']).'" height="32" />
    <span><em class="fadedText push block">'.$row['pid'].'</em> ['.$patient->getPatientName($row['pid']).'], <em>'.$row ['sex'].'</em></span></td>
    <td class="ui-bar-d"><span class="fadedText push block">D.O.B</span><span data-date="true">' . $row ['dob'] . '</span></td>
   <td class="ui-bar-d"><span class="fadedText push block">Weight</span>' .(($row['weight']==NULL)?'N/A':$row['weight'].' kg'). '</td>
    <td class="ui-bar-d"><span class="fadedText push block">insurance</span>' . $row ['insurance'] . '</td>
    <td class="ui-bar-d"><span class="fadedText push block">prescription id</span>' . $row ['regimen_id'] . '</td>
    <td class="ui-bar-d"><span class="fadedText push block">prescribed by</span>' . $staff->getDoctorNameFromID($row ['requested_by']) . '</td>
    <td class="ui-bar-d"><span class="fadedText push block">prescribed on</span>' . date("d M, Y", strtotime($row ['when'])) . '</td><td class="ui-bar-d">
    <span class="fadedText">' . $row ['note'] . '</span></td> <td><a href="javascript:;" onclick="printPres(this)" data-reg="'.$row ['regimen_id'].'"><i class="icon-print"></i>print</a></td></tr>';
        $chk2 = mysql_query($sql_minor);
        $str .= '<tr class="reg' . $row ['regimen_id'] . '" style="display:none"><td colspan="9"><table class="table table-hover">';

        $str .= '<tr class="menu-head fadedText"><td>Drug/type</td><td>dose / quantity</td><td>status</td><td>*</td></tr>';
        $row2 = mysql_fetch_assoc($chk2);
        do {
            $row2 ['quantity'] = (isset($row2 ['quantity'])?$row2 ['quantity']:0);
            $str .= '<tr><td>' . (!is_null($row2 ['drug_id'])?$drug->getDrugName($row2 ['drug_id']) . ' (' . $drug->getDrugType($row2 ['drug_id']) . ')':$drug->getGenericName($row2['drug_generic_id']));
//            if ($row ['drugweight'] != NULL) {
//                $str .= ' [' . $row2 ['drugweight'] . ']';
//            }
            $str .= '</td><td>' . $row2 ['dose'] . ' ' . strtolower($drug->getDrugType($row2 ['drug_id'])) . (($row2 ['dose'] > 1) ? 's' : '');
            $str .= ' ' . $row2['frequency'] . '
                     (' . $row2 ['quantity'] . ' dispensed)</td><td>' . $row2['status'] . '</td> ';
            if (trim($row2 ['filled_by']) != '' && $row2 ['status'] == 'filled') {
                $str .= '<td><a onclick="Boxy.load($(this).attr(\'data-href\'));return false" href="#" data-href="/boxy.fillprescription.php?regid=' . $row2 ['id'] . '&drug=' . $row2 ['drug_id'] . '&pid=' . $row['pid'] . '&action=complete" data-title="Complete Prescription">COMPLETE</a>';
            } else if ($row2 ['filled_by'] == NULL) {
                $str .= '<td><a onclick="Boxy.load($(this).attr(\'data-href\'),{\'title\':$(this).attr(\'data-title\')});return false" href="#" data-href="/boxy.fillprescription.php?gid='.$row2['drug_generic_id'].'&regid=' . $row2 ['id'] . '&drug=' . $row2 ['drug_id'] . '&pid=' . $row['pid'] . '&action=fill" data-title="Fill Prescription">FILL</a>';
            } else {
                $str .= '<td>COMPLETED</td>';
            }
            $str .= '</td></tr>';
        } while ($row2 = mysql_fetch_assoc($chk2));
        $str .= '</table></td></tr>';
        return $str;
    }
}