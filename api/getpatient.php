<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
$patient = new Manager();
mysql_select_db ( $database_dbconnection, $dbconnection );

//if searching for patients
if(isset($_GET['filter']['filters'][0]['value'])){ $filter = $_GET['filter']['filters'][0]['value'];}
if(isset($_GET['search'])){ $filter = $_GET['search'];}
//for patients in a particular program
if(isset($_REQUEST['program'])){$program = $_REQUEST['program'];}

//list all patients
if (!isset($_GET['pid'])){
    if(isset($filter)){
        $sql = "SELECT *, CONCAT_WS('+',patient_ID,fname,lname,mname, phonenumber) AS fullName FROM patient_demograph WHERE CONCAT_WS('+',patient_ID,fname,lname,mname, phonenumber) LIKE '%".$filter."%'";
        if(isset($program)){
            $sql .= " AND patient_ID IN (SELECT patient_id FROM enrollments_".$program.")";
        } // list those not enrolled in a particular program
        else if(isset($_REQUEST['enrolled'])){
            $sql .= " AND patient_ID NOT IN (SELECT patient_id FROM enrollments_".$_REQUEST['enrolled'].")";
        }
        #LIMIT " .$_GET['pageSize'];
    }else {
        $sql = "SELECT *, CONCAT_WS('+',patient_ID,fname,lname,mname) AS fullName FROM patient_demograph ";
        if(isset($program)){
            $sql .= " WHERE patient_ID IN (SELECT patient_id FROM enrollments_".$program.")";
        }
        else if(isset($_REQUEST['enrolled'])){
            $sql .= " WHERE patient_ID NOT IN (SELECT patient_id FROM enrollments_".$_REQUEST['enrolled'].")";
        }
         #LIMIT ".$_GET['pageSize'];
    }



//    exit($sql);
// get a particular patient
}else if (isset($_GET['pid'])){
    $sql = "SELECT patient_ID, legacy_patient_id, fname, lname, mname, date_of_birth, sex, address, lga.name AS LGA, CONCAT_WS(' ',KinsFirstName, KinsLastName) AS KinsName, KinsPhone, KinsAddress, registered_By, phonenumber, bloodgroup, bloodtype,
     basehospital, transferedto, enrollment_date, socio_economic_status.name AS socio_economic FROM patient_demograph LEFT OUTER JOIN lga ON lga.id=lga_id LEFT OUTER JOIN socio_economic_status ON socio_economic=socio_economic_status.id WHERE patient_ID = '".mysql_real_escape_string($_GET['pid'])."'";
    if(isset($program)){
        $sql .= " AND patient_ID IN (SELECT patient_id FROM enrollments_".$program.")";
    }
}

$chk = mysql_query ( $sql, $dbconnection );

$data = array();
$count = 0;
while($row=mysql_fetch_assoc($chk)){
    $row['thumb_url']=$patient->getPatientImage($row['patient_ID']);
    if (isset($_GET['pid'])){
        $row['age']=$patient->getPatientAgeInMonths($row['patient_ID']);
        $row['state']=$patient->getStateLGA($row['patient_ID']);
        $data = $row;
    }else {
        $data[] = $row;
    }
    $count += 1;
}
//$data['count'] = $count;
if (isset($_GET['pid'])){
    //visits / notes
    $visitsArr = $labsArr = $regimensArr = $vitals = $diagnoses = array();
    $visits = "SELECT patient_visit_notes.id AS visit_id, patient_visit_notes.date_of_entry AS w, patient_visit_notes.`note-type` AS ntype,
            patient_visit_notes.description AS description,	patient_visit_notes.noted_by AS who
            FROM patient_visit_notes WHERE patient_visit_notes.patient_ID ='" . mysql_real_escape_string($_GET['pid']) . "'
            ORDER BY w DESC";
    $chk2 = mysql_query($visits, $dbconnection);
    while($visits_row=mysql_fetch_assoc($chk2)){
        $visitsArr[]=$visits_row;
    }
    $data['visits']=$visitsArr; 

    //lab requests
    $labs = "SELECT /*pl.patient_id, */pl.lab_group_id, pl.test_id, pl.test_label,lc.name AS testType,
	 pl.test_value, pl.test_specimen, pl.test_date, rq.time_entered FROM lab_requests rq,patient_labs pl, labtests_config lc WHERE
	 rq.patient_id = pl.patient_id AND pl.test_label = lc.id AND
	 rq.lab_group_id = pl.lab_group_id AND pl.patient_id = '".mysql_real_escape_string($_GET['pid'])."' ORDER BY rq.lab_group_id ";
    $chk_lab = mysql_query($labs, $dbconnection);
    while($labs_row=mysql_fetch_assoc($chk_lab)){
		$labsArr[] = $labs_row;
	}
	$data['labs']=$labsArr;
    //regimens
    $regimens = "select p.id, r.regimen_id, r.status, d.drug_name, d.type, p.note, p.when as request_date, p.requested_by, r.quantity, r.dose, r.frequency, d.weight FROM regimens_full_data r left join patient_regimens p on r.regimen_id = p.regimen_group_id LEFT JOIN drugs d ON d.drug_id=r.drugName WHERE p.patient_ID = '".mysql_real_escape_string($_GET['pid'])."'";
	$chk_regimen = mysql_query($regimens, $dbconnection);
    while($regimens_row=mysql_fetch_assoc($chk_regimen)){
		$regimensArr[] = $regimens_row;
	}
	$data['regimens']=$regimensArr;
    //diagnoses
    $diag_sql = "SELECT `date_of_entry`, `diagnosed_by`, `diagnosisNote`, /*`diag-type`,*/ d.case AS `diagnosis`, `hospital_diagnosed` FROM patient_diagnoses p LEFT JOIN diagnoses d ON d.id=p.diagnosis WHERE patient_ID = '".mysql_real_escape_string($_GET['pid'])."'";
    $diag_chk = mysql_query($diag_sql, $dbconnection);
    while($diag_rows = mysql_fetch_assoc($diag_chk)){
        $diagnoses[] = $diag_rows;
    }
    $data['diagnoses']=$diagnoses;
    //counsellings

    $data['counsellings'] = $patient->getPatientCounsellings($_GET['pid']);

    ////vitals signs
    //weight
    $weight_data = array();
    $weight_sql = "SELECT `readdate`, `value` FROM `v_weight` WHERE `patient_id` = '".mysql_real_escape_string($_GET['pid'])."'";
    $chk_weight = mysql_query($weight_sql, $dbconnection);
    while($weight_row = mysql_fetch_assoc($chk_weight)){
        $weight_data[] = $weight_row;
    }
    $vitals['weight'] = $weight_data;
    //height
    $height_data = array();
    $height_sql = "SELECT `readdate`, `value` FROM `v_height` WHERE `patient_id` = '".mysql_real_escape_string($_GET['pid'])."'";
    $chk_height = mysql_query($height_sql, $dbconnection);
    while($height_row = mysql_fetch_assoc($chk_height)){
        $height_data[] = $height_row;
    }
    $vitals['height'] = $height_data;
    //temperature
    $temp_data = array();
    $temp = "SELECT readdate, value FROM v_temp WHERE patient_ID = ".mysql_real_escape_string($_GET['pid']);
    $chk_v_temp = mysql_query($temp, $dbconnection);
    while($temp_row=mysql_fetch_assoc($chk_v_temp)){
        $temp_data[] = $temp_row;
    }
    $vitals['temp']=$temp_data;

    //blood pressure
    $bp_data = array();
    $bp = "SELECT readdate, systolic, diastolic FROM v_bp WHERE patient_ID = ".mysql_real_escape_string($_GET['pid']);
    $chk_bp = mysql_query($bp, $dbconnection);
    while($bp_row=mysql_fetch_assoc($chk_bp)){
        $bp_data[]= $bp_row;
    }
    $vitals['bp']=$bp_data;

    //pulse
    $pulse_data = array();
    $pulse = "SELECT readdate, value FROM v_pulse WHERE patient_ID = ".mysql_real_escape_string($_GET['pid']);
    $chk_v_pulse = mysql_query($pulse, $dbconnection);
    while($pulse_row=mysql_fetch_assoc($chk_v_pulse)){
        $pulse_data[] = $pulse_row;
    }
    $vitals['pulse']=$pulse_data;
    
    //respiratory p???
    $rp_data = array();
    $rp = "SELECT readdate, value FROM v_rp WHERE patient_ID = ".mysql_real_escape_string($_GET['pid']);
    $chk_v_rp = mysql_query($rp, $dbconnection);
    while($rp_row=mysql_fetch_assoc($chk_v_rp)){
        $rp_data[] = $rp_row;
    }
    $vitals['rp']=$rp_data;
    //now add the vitals upp
    $data['vital_signs']=$vitals;
    //clinical _status if hiv program
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.clinicalstatus.php';
    $C_STATUS = new ClinicalStatus();
    $data['clinical_statuses'] = $C_STATUS->getPatientClinicalStatuses($_GET['pid']);
    //insurance details
    require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/class.insurance.php';
    $ins=new Insurance();
    $ins_mgr = new InsuranceManager();
    $insurance = array();
    $insurance['type']=$ins->getPatientInsurance(mysql_real_escape_string($_GET['pid']), 'type');
        $prov = $ins_mgr->getInsuranceSchemeOwnerName($ins->getPatientInsurance(mysql_real_escape_string($_GET['pid']), 'scheme'));
    $insurance['provider']=($prov='self')?'N/A':$prov;
        $scheme =  $ins_mgr->getInsuranceSchemeName($ins->getPatientInsurance(mysql_real_escape_string($_GET['pid']), 'scheme') );
    $insurance['scheme']=($scheme ='self')?'N/A':$scheme;
        $insurance_expiry = $ins->getPatientInsurance(mysql_real_escape_string($_GET['pid']), 'expiration');
    $insurance['insurance_expiry'] = $insurance_expiry;
    $data['insurance']=$insurance;

    //health center details
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
    $patient=new Manager();
    $data['health_center'] = $patient->getPatientPrimaryHealthCareCenterMobile(mysql_real_escape_string($_GET['pid']));
    /*$data['health_center']= preg_replace('/<a\s.*?>.*?<\/a>/s', '', $health_center);*/
    //remove the "Fix this" link for mobiles, it should only work on pc.
}else {
    //$data['count']=1872;
}

//sleep(1);//use this to test what happens when the patient profile is loading
if(isset($_GET['pid'])){
    echo '['. json_encode($data) . ']';
}else{
    echo json_encode($data);
}

// fix json return when we are fetching data for one patient
exit;
