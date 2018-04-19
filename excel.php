<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/12/15
 * Time: 2:45 PM
 */
ini_set('memory_limit', '2G');
//turn off error_reporting for undefined indexes, Hehe :)
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/json2csv.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
//$data has to be the filtered data from a DAO with date filters;
$data = [];
$source = $_GET['dataSource'];
switch ($source) {
	case "scans":
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
		$raw_data = (new PatientScanDAO())->findScansByDateCategory($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['category'], $page = 0, $pageSize = 9999999999)->data;
		$data = [];
		
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		$iItem = new InsuranceItemsCostDAO();
		
		foreach ($raw_data as $report) {
			$row = [];
			$row['Request Date'] = date(MainConfig::$dateTimeFormat, strtotime($report->request_date));
			$row['Approved Date'] = $report->approved_date ? date(MainConfig::$dateTimeFormat, strtotime($report->approved_date)) : "";
			$row['Scan'] = $report->scanName;
			$row['Staff'] = $report->staffFullName;
			$row['EMR ID'] = $report->patient_id;
			$row['Patient'] = $report->patientFullName;
			$row['Scheme'] = $report->scheme_name;
			$row['Amount'] = $iItem->getItemPriceByCode($report->billing_code, $report->patient_id, true);
			$row['Business Unit'] = $report->service_center ? $report->service_center : '--';
			$data[] = $row;
		}
		break;
	case 'labs':
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
		$raw_data = (new PatientLabDAO())->exportLabRequestsByDateCategory($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['category'], $page = 0, $pageSize = 9999999999, true)->data;
		
		$data = [];
		foreach ($raw_data as $report) {
			$row = [];
			$row['Request Date'] = $report->Date;
			$row['Specimen Date'] = $report->specimen_date;
			$row['Approved Date'] = $report->approved_date;
			$row['Lab'] = $report->Lab;
			$row['EMR'] = $report->PatientID;
			$row['Patient'] = $report->Patient;
			$row['Scheme'] = $report->Scheme;
			$row['Referral'] = $report->referral ? $report->referral : '--' ;
			$row['Amount'] = $report->Amount;
			$row['Staff'] = $report->Staff;
			$row['Business Unit'] = $report->BusinessUnit ? $report->BusinessUnit : '--';
			$data[] = $row;
		}
		break;
	case 'pharmacy_sales':
		require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php');
		require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php');
		$iItem = (new InsuranceItemsCostDAO());
		$raw_data = (new PrescriptionDataDAO())->getCompletedPrescriptionsByDateRange($page = 0, $pageSize = 9999999999, $_REQUEST['pharmacy'], true, $_REQUEST['from'], $_REQUEST['to'])->data;
		$data = [];
		foreach ($raw_data as $report) {
			$row = [];
			$row['Request Date'] = date(MainConfig::$dateFormat, strtotime($report->when));
			$row['Filled Date'] =  date(MainConfig::$dateFormat,strtotime($report->filled_on));
			$row['Filled By'] =  $report->filled_by ? (new StaffDirectoryDAO())->getStaff($report->filled_by, FALSE)->getFullname() : '- -';
			$row['Completed Date'] =  date(MainConfig::$dateFormat,strtotime($report->completed_on));
			$row['Completed By'] = $report->completed_by ? (new StaffDirectoryDAO())->getStaff($report->completed_by, FALSE)->getFullname() : '- -';
			$row['EMR ID'] = $report->patient_id;
			$row['Patient'] = $report->patientName;
			$row['Drug'] = (is_null($report->drug_id)) ? $report->generic_name : $report->drug_name;
			$row['Quantity'] = $report->quantity;
			$row['Scheme'] = $report->scheme_name;
			$row['Amount'] = !is_null($report->drug_id) ? $report->quantity * $iItem->getItemPriceByCode($report->drug_code, $report->patient_id, true) : 'N/A';
			$data[] = $row;
		}
		break;
	case 'procedures':
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
		$raw_data = (new PatientProcedureDAO())->exportProceduresReport($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['category_id'], $page = 0, $pageSize = 9999999999)->data;
		$data = [];
		foreach ($raw_data as $report) {
			$row = [];
			$row['Date'] = $report->Date;
			$row['Patient'] = $report->Patient;
			$row['EMR ID'] = $report->PatientId;
			$row['Age'] = $report->Age;
			$row['Procedure'] = $report->Procedure;
			$row['Body Part'] = $report->bodypart;
			$row['Diagnosis'] = $report->Diagnosis;
			$row['Participants'] = $report->Participants;
			$row['Business Unit'] = $report->ServiceCenter;
			$data[] = $row;
		}
		break;

    case 'procedureStarted':
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
        $raw_data = (new PatientProcedureDAO())->exportProceduresReportByStartDate($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['category_id'], $page = 0, $pageSize = 9999999999)->data;
        $data = [];
        foreach ( $raw_data as $report){
            $row = [];
            $row['Date'] = $report->Date;
            $row['Patient'] = $report->Patient;
            $row['EMR ID'] = $report->PatientId;
            $row['Age'] = $report->Age;
            $row['Procedure'] = $report->Procedure;
            $row['Body Part'] = $report->bodypart;
            $row['Diagnosis'] = $report->Diagnosis;
            $row['Participants'] = $report->Participants;
            $data[] = $row;
        }
        break;
	case 'consultant':
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';
		//OPTIMIZED NOW CORRECTLY
		$raw_data = (new StaffManager())->getDoctorWhoSawWho($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['specialty_id'], $_REQUEST['staff_id'], $page = 0, $pageSize = 9999999999)->data;
		$data = [];
		foreach ($raw_data as $report) {
			$row = [];
			$row['Date'] = $report->Date;
			$row['Doctor'] = $report->Doctor;
			$row['Specialization'] = $report->Specialization;
			$row['Department'] = $report->Department;
			$row['EMR'] = $report->PatientID;
			$row['Patient'] = $report->Patient;
			$row['Sex'] = ucwords($report->Sex);
			$row['Scheme'] = $report->Scheme;
			$row['Amount'] = $report->Amount;
			$data[] = $row;
		}
		break;

	case 'bill':
		//OPTIMIZED NOW CORRECTLY
		require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		$raw_data = (new BillDAO())->exportBillReport($_REQUEST['from'], $_REQUEST['to'], array_filter(explode(",", @$_REQUEST['transaction_type'])), $_REQUEST['payment_method_id'], $_REQUEST['cost_centre_id'], $_REQUEST['bill_source_id'], $_REQUEST['insurance_scheme_id'], $_REQUEST['provider'], $_REQUEST['insurance_type_id'], $page = 0, $pageSize = 9999999999)->data;
		$data = [];
		foreach ($raw_data as $report) {
			$row = [];
			$row['Date'] = $report->Date;
			$row['EMR ID'] = $report->PatientID;
			$row['Patient'] = $report->Patient;
			$row['Enrollee Number'] = $report->EnrolleeNumber;
			$row['Description'] = $report->Description;
			$row['P. A. Code'] = $report->AuthCode;
			$row['Coverage'] = $report->Coverage;
			$row['Service'] = $report->Service;
			$row['Transaction Type'] = $report->TransactionType;
			$row['Payment Method'] = $report->PaymentMethod;
			$row['Amount'] = $report->Amount;
			$row['Cost Centre'] = $report->CostCentre;
			$row['Responsible'] = $report->Responsible;
			$data[] = $row;
		}
		break;
	
	case 'claim':
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsurerDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProgressNoteDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
		
		$claimsReport = array();
		$data = [];
		$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? TRUE : FALSE);
		if ($date === TRUE) {
			$data_ = (new ClaimDAO())->getClaimsReport($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['insurance_scheme_id'], $_REQUEST['provider'], $page=0, $pageSize=99999999);
			$totalSearch = $data_->total;
			$claimsReport = $data_->data;
		}
		foreach ($claimsReport as $lines) {
			$pnotes_ = "";
			$diagCode = "";
			$line_ids = array_filter(explode(',', $lines->line_ids));
			if($lines->type == 'op'){
				$opnotes = (new VisitNotesDAO())->getEncounterNotes($lines->encounter_id, 'a');
				foreach ($opnotes as $note){
					$pnotes_  .= $note->description;
					$dCode = preg_match("^\[(.*)\]^", "$note->description", $matches);
					$diagCode .= $matches[0];
					$diagCode .= ', ';
				}
				
			}else if($lines->type == 'ip') { // use progress note table and pass in_patient_id
				$ipnotes = (new ProgressNoteDAO())->getProgressNotes($lines->encounter_id, True, 'g');
				foreach ($ipnotes as $note){
					$pnotes_ .= $note->note;
					$dCode = preg_match("^\[(.*)\]^", "$note->note", $matches );
					$diagCode .= $matches[0];
					$diagCode .= ', ';
				}
			}
			$foloioNumber = (new EncounterDAO())->getClaimed($lines->encounter_id);
			
			foreach ($line_ids as $id) {
				$line = (new BillDAO())->getBill($id, true);
				$re = new stdClass();
				$re->claimId = $lines->id;
				$re->claimDate = $lines->create_date;
				$re->type_ = $lines->type == 'op' ? 'Out Patient': 'In Patient';
				$re->reason = $lines->reason;
				$re->coverage_type = $lines->coverage_type;
				$re->insurance_type = $lines->insurance_type;
				if($lines->type == 'op'){
					$re->EncounterDate1 = ($line && $line->getDueDate()) ? $line->getDueDate() : "";
					$re->EncounterDate2 = ($line && $line->getDueDate()) ? $line->getDueDate() : "";
				}else if($lines->type == 'ip'){
					//$ip = $line->getInPatient() ? (new InPatientDAO())->getInPatientFlat($line->getInPatient()->getId(), FALSE) : '';
					$re->EncounterDate1 = "";
					$re->EncounterDate2 = "";
					
				}
				if($line){
					$re->DrFolio = ($foloioNumber && $foloioNumber->getSignedBy()) ? $foloioNumber->getSignedBy()->getFolioNUmber() : "";
					$re->Diagnosis = $pnotes_;
					$re->DiagnosisCode = $diagCode;
					$re->item_code = $line->getItemCode();
					$re->transaction_date = $line->getTransactionDate();
					$re->Description = $line->getDescription();
					$re->BillSource = $line->getSource()->getName();
					$re->unitCharge = floatval($line->getAmount()/$line->getQuantity());
					$re->Amount = $line->getAmount();
					$re->Code = $line->getAuthCode();
					$re->quantity = $line->getQuantity();
					$re->insurance = $line->getBilledTo()->getName();
					$re->Patient = $line->getPatient()->getFullName();
					$re->Phone = $line->getPatient()->getPhoneNumber();
					$re->cliniId = $line->getPatient()->getId();
					$re->errolleeId = (new PatientDemographDAO())->getPatient($line->getPatient()->getId(), TRUE)->getInsurance()->getEnrolleeId();
					
				}
				$return[] = $re;
			}
		}
		
		
		foreach ($return as $report) {
			$row = [];
			//if($line){
				$row['Claim Id'] =  $report->claimId;
				$row['Claim Date'] = $report->claimDate;
				$row['Transaction Date'] = $report->transaction_date;
				$row['Hospital Id'] =  $report->cliniId;
				$row['Patient Name'] = $report->Patient;
				$row['Phone Number'] = $report->Phone;
			  $row['Insurance Type'] = $report->insurance_type;
			  $row['Scheme'] =  $report->insurance;
				$row['Enrolment Number'] = $report->errolleeId;
				$row['Coverage'] = $report->coverage_type;
				$row['Type'] =   $report->type_;
				$row['Encounter Date From'] = $report->EncounterDate1;
				$row['Encounter Date To'] = $report->EncounterDate2;
				$row['Service'] =  $report->BillSource;
				$row['Description'] =  $report->Description;
				$row['Item Code'] = $report->item_code;
				$row['Reason'] =  $report->reason;
				$row['INSURANCE (HMIS) CODE'] = "";
			  $row['Diagnosis Code'] = $report->DiagnosisCode;
				$row['Diagnosis'] = $report->Diagnosis;
				$row['PA Code'] = $report->Code;
				$row['Physician Folio #'] = $report->DrFolio;
				$row['Quantity'] =  $report->quantity;
			  $row['unitCharge'] = $report->unitCharge;
				$row['Amount'] = $report->Amount;
			//}
			$data[] = $row;
		}
		
		break;
	
	case 'unclaimed_bills':
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsurerDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProgressNoteDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
		
		
		$currency = (new CurrencyDAO())->getDefault();
		
		$insurance_id = (new InsuranceSchemeDAO())->getInsuranceSchemes();
		$provider = (new InsurerDAO())->getInsurers(FALSE);
		$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? TRUE : FALSE);
		$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
		$pageSize =  (isset($_REQUEST['pageSize'])) ? $_REQUEST['pageSize'] : 100;
		$totalSearch = 0;
		
		
		$bills = array();
		if ($date === TRUE) {
			$data = (new BillDAO())->getUnclaimedBills($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['insurance_scheme_id'], $_REQUEST['provider_id'], $page, $pageSize);
			$totalSearch = $data->total;
			$bills = $data->data;
		}
		$return = [];
		foreach ($bills as $lines) {
			$pnotes_ = "";
			
			$type = "";
			$line = (new BillDAO())->getBill($lines->bill_id, true);
			$re = new stdClass();
			if($lines->type == 'op'){
				$re->EncounterDate1 = ($line && $line->getDueDate()) ? $line->getDueDate() : "";
				$re->EncounterDate2 = ($line && $line->getDueDate()) ? $line->getDueDate() : "";
			}else if($lines->type == 'ip'){
				//$ip = $line->getInPatient() ? (new InPatientDAO())->getInPatientFlat($line->getInPatient()->getId(), FALSE) : '';
				$re->EncounterDate1 = "";
				$re->EncounterDate2 = "";
				
			}
			$re->Diagnosis = $pnotes_;
			$re->ClaimId = $line->getId();
			$re->item_code = $line->getItemCode();
			$re->transaction_date = $line->getTransactionDate();
			$re->Description = $line->getDescription();
			$re->BillSource = $line->getSource()->getName();
			$re->Amount = $line->getAmount();
			$re->Code = $line->getAuthCode();
			$re->quantity = $line->getQuantity();
			$re->insurance = $line->getBilledTo()->getName();
			$re->Patient = $line->getPatient()->getFullName();
			$re->Phone = $line->getPatient()->getPhoneNumber();
			$re->cliniId = $line->getPatient()->getId();
			$re->errolleeId = (new PatientDemographDAO())->getPatient($line->getPatient()->getId(), TRUE)->getInsurance()->getEnrolleeId();
			$return[] = $re;
		}
		
		if(is_array($return)) {
			$data = [];
			foreach ($return as $uc) {
				$row = [];
				$row['Claim Id'] =  "N/A";
				$row['Claim Date'] = "N/A";
				$row['Transaction Date'] = $uc->transaction_date;
				$row['Hospital Id'] = $uc->cliniId;
				$row['Patient Name'] = $uc->Patient;
				$row['Phone Number'] = $uc->Phone;
				$row['Insurance Type'] = "";
				$row['Scheme'] = $uc->insurance;
				$row['Enrolment Number'] = $uc->errolleeId;
				$row['Coverage Type'] =   "";
				$row['Type'] =   "N/A";
				$row['Encounter Date From'] = "";
				$row['Encounter Date To'] = " ";
				$row['Service'] = $uc->BillSource;
				$row['Description'] = $uc->Description;
				$row['Item Code'] = $uc->item_code;
				$row['INSURANCE (HMIS) CODE'] = "";
				$row['Diagnosis Code'] = "";
				$row['Diagnosis'] = $uc->Diagnosis;
				$row['PA Code'] = $uc->Code;
				$row['Physician Folio Number'] = $uc->Code;
				$row['Quantity'] = $uc->quantity;
				$row['Unit Charge'] = floatval($uc->Amount / $uc->quantity);
				$row['Amount'] = $uc->Amount;
				$data[] = $row;
				
			}
		}
		
		break;
	
	case 'ins_statement':
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
		$currency = (new CurrencyDAO())->getDefault();
		$data = [];
		//sleep(1);
		$page = isset($_GET['page']) ? $_GET['page'] : 0;
		$pageSize = isset($_GET['PageSize']) ? $_GET['PageSize'] : 20;
		$scheme = $_GET['insurance_scheme_id'];
		$from 		= 	$_GET['date_from'];
		$to 		=	$_GET['date_to'];
		$tType		=  $_GET['tType'];
		$provider = $_GET['provider_id'];
		
		$patientId = !is_blank($_GET['patient_id']) ? " AND b.patient_id = " . $_GET['patient_id'] : "";
		$sources = !is_blank($_GET['bill_source_ids']) && isset($_GET['bill_source_ids'])  ? " AND b.bill_source_id IN (". implode(", ", $_GET['bill_source_ids']) .")" : "";
		$unclaimed = "";
		if($_GET['claimed_state'] && $_GET['claimed_state'] == "claimed"){
			$unclaimed = "AND b.claimed=TRUE";
			
		}else if ($_GET['claimed_state'] && $_GET['claimed_state'] == "unclaimed"){
			$unclaimed = "AND b.claimed=FALSE";
		}
		$extraFilter = "";
		$schemeId = "";
		$ic = "";
		$iso = "";
		$insurer_id = "";
		$bills = array();
		if($scheme != "" && $provider == ""){
			$schemeId = "b.billed_to=$scheme";
			$extraFilter = "LEFT JOIN insurance_schemes ic ON b.billed_to=ic.id";
			$ic = "ic.*,";
		}else if ($scheme && $provider != ""){
			
			$schemeId = "b.billed_to=$scheme";
		}
		if($provider != NULL && $provider != "" && $provider != ""){
			$ic = "ic.*,";
			$iso = "iso.*,";
			if($schemeId == ""){
				$insurer_id = "iso.id=$provider";
			}else if ($schemeId != ""){
				$insurer_id = "AND iso.id=$provider";
			}
			$extraFilter ="LEFT JOIN insurance_schemes ic ON b.billed_to=ic.id LEFT JOIN insurance_owners iso ON ic.scheme_owner_id=iso.id";
		}
		
		
		$sql = "SELECT b.*, $ic $iso concat_ws(' ', pd.lname, pd.mname, pd.fname) AS fullname, concat_ws(' ', pd.lname, substr(pd.fname, 1, 1)) as shortname, pd.active FROM bills b LEFT JOIN patient_demograph pd ON pd.patient_ID=b.patient_id $extraFilter WHERE $schemeId $insurer_id AND cancelled_on IS NULL {$sources}{$patientId}{$unclaimed}";
		
		if ($tType != "---" && $tType != "") {
			$sql .= " AND transaction_type IN ('".implode("','", $tType)."')";
		}
		
		
		if($from!=NULL && $to==NULL){
			$sql.=" AND DATE(transaction_date) BETWEEN DATE('$from') AND DATE(NOW())";}
		else if ($from==NULL && $to!=NULL){
			$sql.=" AND DATE(transaction_date) BETWEEN DATE(NOW()) AND DATE('$to')";
		}else if($from!=NULL && $to!=NULL){
			$sql.=" AND DATE(transaction_date) BETWEEN DATE('$from') AND DATE('$to')";
		}
		$sql .= " ORDER BY patient_id, billed_to, transaction_date DESC";
		$outstanding_total = 0;
		require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sid = trim ( escape($sid) );
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$sql .= " LIMIT $offset, $pageSize";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$rows = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		while ($rows = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
			
			$bill = (object)null;
			$bill->BillId = $rows['bill_id'];
			$bill->Patient = $rows['patient_id'] != NULL  ? $rows['fullname'] : '';
			$bill->Item = $rows['description'];
			$bill->Date = date("d/M/Y", strtotime($rows['transaction_date']));
			$bill->Type = $rows['transaction_type'];
			$bill->Amount = $rows['amount'];
			$bill->Billed_to = $rows['scheme_name'] ? $rows['scheme_name'] : "N/A";
			$bill->AuthCode = $rows['auth_code'];
			$bill->Responsible = ($rows['receiver']=='')? '': (new StaffDirectoryDAO())->getStaff($rows['receiver'])->getShortname();
			$bills[] = $bill;
		}
		$stmt = null;
		$results = (object)null;
		$results = $bills;
  
		$data = [];
		foreach ($results as $report){
			$row = [];
			$row['Bill Item Code'] = $report->BillId;
			$row['Patient'] = $report->Patient;
			$row['Item'] = $report->Item;
			$row['Date'] = $report->Date;
			$row['Type'] = $report->Type;
			$row['Amount'] = $report->Amount;
			$row['Responsible'] = $report->Responsible;
			$row['Billed To'] = $report->Billed_to;
			$row['Auth Code'] = $report->AuthCode;
			$data[] = $row;
			
		}
		break;
	
	case 'outstanding_bill':
		require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		$insScheme = isset($_REQUEST['insurance_scheme_id']) && !is_blank($_REQUEST['insurance_scheme_id']) ? $_REQUEST['insurance_scheme_id'] : null;
		$admitted = isset($_REQUEST['admitted']) && $_REQUEST['admitted']=='admitted' ? 'admitted' : null;
		$raw_data = (new BillDAO())->outstandingBills(null, $page = 0, $pageSize = 9999999999, $insScheme, $_REQUEST['sort'], $admitted)->data;
		$data = [];
		foreach ($raw_data as $report) {
			$row = [];
			$row['Patient'] = $report->Patient;
			$row['EMR'] = $report->PatientID;
			$row['Coverage'] = $report->Scheme;
			$row['Amount'] = $report->Outstanding;
			
			$data[] = $row;
		}
		break;

	case 'diagnoses':
		require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
		$diagnosis = (isset($_REQUEST['diagnosis']) && !is_blank($_REQUEST['diagnosis'])) ? $_REQUEST['diagnosis'] : null;
		$from = (isset($_REQUEST['from']) && !is_blank($_REQUEST['from'])) ? $_REQUEST['from'] : null;
		$to = (isset($_REQUEST['to']) && !is_blank($_REQUEST['to'])) ? $_REQUEST['to'] : null;
		$pageSize = 99999999999;
		$raw_data = (new PatientDiagnosisDAO())->reportAll($page = 0, $pageSize, $from, $to, $diagnosis)->data;
		$data = [];
		
		foreach ($raw_data as $report) {
			$row = [];
			$row['Date'] = $report->Date;
			$row['Patient'] = $report->Patient;
			$row['EMR ID'] = $report->patient_ID;
			$row['Sex'] = $report->sex;
			$row['Age'] = getAge($report->date_of_birth);
			$row['Diagnosis'] = $report->Diagnosis;
			$row['Code'] = strtoupper($report->DCode);
			$row['Type'] = strtoupper($report->DType);
			$row['Status'] = ucfirst($report->Status);
			$row['Coverage'] = $report->coverage;
			$row['Diagnosed By'] = $report->DiagnosedBy;
			
			$data[] = $row;
		}
		break;

	case 'admissions':
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
		$dates = [date('Y-m-d'), date('Y-m-d')];
		if (isset($_REQUEST['from'], $_REQUEST['to'])) {
			$dates = [$_REQUEST['from'], $_REQUEST['to']];
		} else {
			$_REQUEST['from'] = $_REQUEST['to'] = date('Y-m-d');
		}
		$ward = (isset($_REQUEST['ward_id']) && !is_blank($_REQUEST['ward_id'])) ? $_REQUEST['ward_id'] : null;
		$pageSize = 9999999999;
		$page = 0;
		$inPatients = [];
		if (isset($_REQUEST['view']) && $_REQUEST['view'] == "current") {
			$inPatients = (new InPatientDAO())->getInPatientReport($filter = null, $ward, $dates, $page, $pageSize)->data;
		} else if (isset($_REQUEST['view']) && $_REQUEST['view'] == "discharged") {
			$inPatients = (new InPatientDAO())->getInPatientReport($filter = "discharged", $ward, $dates, $page, $pageSize)->data;
		} else if (isset($_REQUEST['view']) && $_REQUEST['view'] == "admissions") {
			$inPatients = (new InPatientDAO())->getInPatientReport($filter = "admissions", $ward, $dates, $page, $pageSize)->data;
		}
		
		$data = [];
		foreach ($inPatients as $report) {
			$patient = (new PatientDemographDAO())->getPatient($report->patient_id, FALSE);
			$row = [];
			$row['Date'] = $report->date_admitted;
			$row['Patient'] = $report->patientName;
			$row['EMR ID'] = $report->patient_id;
			$row['Phone Number'] = $patient->getPhoneNumber();
			$row['Email'] = $patient->getEmail();
			$row['Ward'] = $report->wardName;
			$row['Reason'] = $report->reason;
			$row['Anticipated Discharge Date'] = date(MainConfig::$dateFormat, strtotime($report->anticipated_discharge_date));
			$row['Discharged Date'] = date(MainConfig::$dateTimeFormat, strtotime($report->date_discharged));
			$row['Coverage'] = $report->schemeName;
			$row['Status'] = ucfirst($report->status);
			$row['Admitted By'] = $report->staffName;
			$data[] = $row;
		}
		break;
	case 'credit_limits':
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
		$dates = [date('Y-m-d'), date('Y-m-d')];
		if (isset($_REQUEST['from'], $_REQUEST['to'])) {
			$dates = [$_REQUEST['from'], $_REQUEST['to']];
		} else {
			$_REQUEST['from'] = $_REQUEST['to'] = date('Y-m-d');
		}
		$pageSize = 9999999999;
		$page = 0;
		$staff = !is_blank(@$_REQUEST['staff_id']) ? @$_REQUEST['staff_id'] : null;
		$data = [];
		$report = (new CreditLimitDAO())->allAudit($page, $pageSize, $dates, $staff);
		foreach ($report->data as $cr) {//$cr=new CreditLimit();
			$row = [];
			$row['Patient'] = $cr->getPatient()->getId();
			$row['Fullname'] = $cr->getPatient()->getFullname();
			$row['Amount'] = $cr->getAmount();
			$row['Date Set'] = date(MainConfig::$dateTimeFormat, strtotime($cr->getDate()));
			$row['Expires on'] = date(MainConfig::$dateFormat, strtotime($cr->getExpiration()));
			$row['Reason'] = $cr->getReason();
			$row['Set By'] = $cr->getSetBy() ? $cr->getSetBy()->getFullname() : '--';
			$data[] = $row;
		}
		break;
	case 'dispensed_drugs':
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DispensedDrugsDAO.php';
		$from = !is_blank(@$_GET['from']) ? @$_GET['from']: null;
		$to = !is_blank(@$_GET['to']) ? @$_GET['to']: null;
		$data_row = (new DispensedDrugsDAO())->all(0, 9999999999, $from, $to);
		foreach ($data_row->data as $datum) {
			$row = [];
			$row['Drug Name'] = $datum['drug']->getName();
			$row['Generic Name'] = trim($datum['drug']->getGeneric()->getName());
			$row['Quantity Dispensed'] = $datum['quantity'];
			$row['Stock Unit'] = ucwords($datum['drug']->getGeneric()->getForm());
			$row['Service Center'] = ' - - ';
			//$datum->getBatch()->getServiceCentre()->getName();
			$data[] = $row;
		}
		break;
	case 'refill_drugs':
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RefillsDAO.php';
		$from = !is_blank(@$_GET['from']) ? @$_GET['from']: null;
		$to = !is_blank(@$_GET['to']) ? @$_GET['to']: null;
		$data_row = (new RefillsDAO())->all(0, 9999999999, $from, $to);
		foreach ($data_row->data as $datum){
			$row['Drug Brand'] = $datum['drug'];
			$row['Generic'] = $datum['generic']->getName() . " " . $datum['generic']->getWeight();
			$row['Patient'] = $datum['patient']->getFullname();
			$row['EMR ID'] = $datum['patient']->getId();
			$row['Refills Left'] = $datum['refill_number'];
			$row['Due Date'] = date(MainConfig::$dateFormat, strtotime($datum['refill_date']));
			$data[] = $row;
		}
		break;
	case 'schemeItems':
		$id = $_GET['id'];
		$source = !is_blank(@$_GET['source_id']) ? $_GET['source_id'] : NULL;
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceItemsCostDAO.php";
		$items = (new InsuranceItemsCostDAO())->getItemCosts($id, $source);
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		if (Clinic::$editStyleByAdd) {
			$desc = ["Surgical Fee", "Anaesthetist Fee", "Anaesthesia", "Theatre Cost"];
		} else {
			$desc = ["Base Price", "Surgeon Price", "Anaesthesia", "Theatre Cost"];
		}
		foreach ($items as $item) {
			$row['Item Code'] = $item->item_code;
			$row['Insurance Code'] = $item->insurance_code;
			$row['Category'] = ucwords(str_replace('_', ' ', $item->item_category));
			$row['Description'] = escape($item->item_description);
			$row['Price'] = $item->selling_price;
			$row['Follow up'] = $item->followUpPrice;
			$row[ $desc[1] ] = $item->surgeonPrice;
			$row[ $desc[2] ] = $item->anaesthesiaPrice;
			$row[ $desc[3] ] = $item->theatrePrice;
			$row['Type'] = ucwords($item->type);
			$row['Capitation?'] = ((bool)$item->capitated) ? 'Capitated' : '--';
			$data[] = $row;
		}
		break;

	case 'insurancePatientsList':
		$id = $_GET['scheme'];
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceSchemeDAO.php";
		$items = (new InsuranceSchemeDAO())->getSchemePatients($id);
		foreach ($items as $item) {
			$row['Patient Name'] = $item->PatientName;
			$row['EMR #']=$item->PatientId;
			$row['Active?']= $item->active ? 'Yes' : 'No';
			$row['Phone Number'] = $item->Phone;
			$row['Enrollee Id'] = $item->EnrolleeNumber;
			$data[] = $row;
		}
		break;
	
	case 'staffRoles':
		require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/StaffDirectoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/StaffRolesDAO.php';
		$roles = (new StaffRolesDAO())->getRoles();
		$staffs = (new StaffDirectoryDAO())->getActiveUsers();
		$data = [];
		foreach ($staffs as $staff) {
			//$staff = new StaffDirectory();
			$row = [];
			
			foreach ($roles as $role) {
				//$role = new StaffRole();
				$row['User'] = $staff->getFullname();
				$row['Profession'] = $staff->getProfession();
				$row[$role->getCode()] = $staff->hasRole($role) ? 'Yes' : 'No';
			}
			
			$data[] = $row;
		}
		break;
}

ob_end_clean();
$filename = $_GET['filename'];
$JSON2CSV = new JSON2CSVutil;
$JSON2CSV->readJSON(json_encode($data));
$JSON2CSV->flattenDL($filename . ".csv");
exit;