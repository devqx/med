<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/24/18
 * Time: 8:40 PM
 */

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

$currency = (new CurrencyDAO())->getDefault();

$insurance_id = (new InsuranceSchemeDAO())->getInsuranceSchemes();
$provider = (new InsurerDAO())->getInsurers(FALSE);

$date = ((isset($_POST['from']) && $_POST['from'] != '' && isset($_POST['to']) && $_POST['to'] != '') ? TRUE : FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize =  (isset($_POST['pageSize'])) ? $_POST['pageSize'] : 100;
$totalSearch = 0;


$claimsReport = array();

if ($date === TRUE) {
	$data = (new ClaimDAO())->getClaimsReport($_POST['from'], $_POST['to'], $_POST['schemeId'], $_POST['provider_id'], $page, $pageSize);
	$totalSearch = $data->total;
	$claimsReport = $data->data;
}

$encounterDate = date(MainConfig::$mysqlDateTimeFormat);
$encounter = null;
$encounter1 = null;
$return = [];
foreach ($claimsReport as $lines) {
	
	$diagCode = "";
	$pnotes_ = "";
	$line_ids = array_filter(explode(',', $lines->line_ids));
	
	if( $lines->type == 'op'){
		$opnotes = (new VisitNotesDAO())->getEncounterNotes($lines->encounter_id, 'a');
		foreach ($opnotes as $note){
			$pnotes_  .= "<li>$note->description</li>";
			$dCode = preg_match("^\[(.*)\]^","$note->description", $matches);
			$diagCode .= $matches[0];
			$diagCode .= ', ';
		}
		
	}else { // use progress note table and pass in_patient_id
		$ipnotes = (new ProgressNoteDAO())->getProgressNotes($lines->encounter_id, True, 'g');
		foreach ($ipnotes as $note){
			$pnotes_ .= "<li>$note->note</li>";
			$dCode = preg_match("^\[(.*)\]^", "$note->note", $matches );
			$diagCode .= $matches[0];
			$diagCode .= ', ';
			
			
			
		}
	}
	
	$foloioNumber = (new EncounterDAO())->getClaimed($lines->encounter_id);
	if ($lines->type && $lines->type == "op") {
		$encounter = (new EncounterDAO())->get($lines->encounter_id, true);
	} else if ($lines->type && $lines->type == "ip") {
		$encounter1 = (new InPatientDAO())->getInPatient($lines->encounter_id, true);
	}
	
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
			$re->EncounterDate1 = $line->getDueDate();
			$re->EncounterDate2 = $line->getDueDate();
		}else if ($lines->type == 'ip'){
			$re->EncounterDate1 = $encounter1->getDateAdmitted();
			$re->EncounterDate2 = $encounter1->getDateDischarged();
		}
		
		
		if($line){
			$re->DrFolio = ($foloioNumber && $foloioNumber->getSignedBy()) ? $foloioNumber->getSignedBy()->getFolioNUmber() : "";
			$re->Diagnosis = $pnotes_;
			$re->DiagnosisCode = $diagCode;
			$re->item_code = $line->getItemCode();
			$re->transaction_date = $line->getTransactionDate();
			$re->Description = $line->getDescription();
			$re->BillSource = $line->getSource()->getName();
			$re->Amount = $line->getAmount();
			$re->Code = $line->getAuthCode();
			$re->quantity = $line->getQuantity();
			$re->unitCharge = floatval($line->getAmount()/$line->getQuantity());
			$re->insurance = $line->getBilledTo()->getName();
			$re->Patient = $line->getPatient()->getFullName();
			$re->Phone = $line->getPatient()->getPhoneNumber();
			$re->cliniId = $line->getPatient()->getId();
			$re->clincLocation = $line->getClinic()->getName();
			$re->errolleeId = (new PatientDemographDAO())->getPatient($line->getPatient()->getId(), TRUE)->getInsurance()->getEnrolleeId();
			$return[] = $re;
		}
		
	}
}

?>

<div class="document" style="overflow: scroll;">
	<?php if (isset($_POST['from']) && isset($_POST['to']) && $_POST['from'] != '') { ?>
		<h3 style="text-align: center">Claims Report for
			<br>PERIOD:
			<span> [<?php echo date("Y M d", strtotime($_POST['from'])) . ' - ' . (($_POST['to'] == '') ? date('Y M d') : date("Y M d", strtotime($_POST['to']))) ?>
				]</span></h3>
	<?php } ?>
	<div id="claim_report_container">
		<?php if ($totalSearch < 1) {
			echo '<div class="notify-bar">There are no claims reports</div>';
		} else { ?>
			<table class="table table-striped table-hover no-footer table-bordered">
				<thead>
				<tr>
					<th>Claim ID</th>
					<th>Claim Date</th>
					<th>Transaction Date</th>
					<th>Hospital ID</th>
					<th>Patient</th>
					<th>Phone Number</th>
					<th>Insurance Type</th>
					<th>Scheme Name</th>
					<th>Enrollee #</th>
					<th>Coverage Type</th>
					<th>Type</th>
					<th>Encounter Date From</th>
					<th>Encounter Date To</th>
					<th>Service</th>
					<th>Description</th>
					<th>Item Code</th>
					<th>INSURANCE (HMIS) CODE</th>
				   <th>Diagnosis Code</th>
					<th>Diagnosis</th>
					<th>PA Code</th>
				  <th>Physician Folio #</th>
					<th>Quantity</th>
				  <th>Unit Charge</th>
					<th>Amount (<?= $currency ?>)</th>
				  <th>Location</th>
				</tr>
				</thead>
				<?php if (isset($return) && sizeof($return) > 0) {
					foreach ($return as $report) {?>
						<tr>
							<td><?= $report->claimId ?></td>
							<td nowrap><?= date('M jS, Y', strtotime($report->claimDate)) ?></td>
							<td nowrap><?= date('M jS, Y', strtotime($report->transaction_date)) ?></td>
							<td nowrap><?= $report->cliniId ?></td>
							<td nowrap><?= $report->Patient ?></td>
							<td nowrap><?= $report->Phone ?></td>
							<td nowrap><?= $report->insurance_type ?></td>
							<td  nowrap><?= $report->insurance ?></td>
							<td nowrap><?= $report->errolleeId ?></td>
							<td nowrap><?= $report->coverage_type ?></td>
							<td nowrap><?= $report->type_ ?></td>
							<td nowrap><?= $report->EncounterDate1 ? date('M jS, Y', strtotime($report->EncounterDate1 )) : "" ?></td>
							<td nowrap><?= $report->EncounterDate2 ? date('M jS, Y', strtotime($report->EncounterDate2 )) : "" ?></td>
							<td  nowrap><?= ucfirst(str_replace('_', ' ', $report->BillSource) ) ?></td>
							<td nowrap><?= ucwords($report->Description) ?></td>
							<td nowrap> <?= $report->item_code ?> </td>
							<td nowrap><?= "--" ?></td>
							<td nowrap><?= $report->DiagnosisCode ?></td>
							
							<td nowrap><ul>
									<?= $report->Diagnosis ?>
								</ul>
							</td>
							<td nowrap><?= $report->Code ?></td>
							<td nowrap><?=  $report->DrFolio ?></td>
							<td nowrap><?= $report->quantity ?></td>
							<td  class="amount" nowrap><?= number_format(abs($report->unitCharge), 2) ?></td>
							<td class="amount" nowrap><?= number_format(abs($report->Amount), 2) ?></td>
							<td><?= $report->clincLocation ?></td>
						</tr>
					<?php }
				} ?>
			</table>
			<div class="list1 dataTables_wrapper no-footer">
				<div class="dataTables_info" id="DataTables_Table_0_info" role="status"
				     aria-live="polite"> <?= $totalSearch ?>
					results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
				</div>
				<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
					<a id="DataTables_Table_1_first" data-page="0"
					   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
						records</a>
					<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
					   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
						records</a>
					<a id="DataTables_Table_1_last"
					   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
					   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
						records</a>
					<a id="DataTables_Table_1_next"
					   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
					   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
						records</a>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post('/api/find_claims.php', {
					from:'<?=@$_REQUEST['from']?>',
					to:'<?=@$_REQUEST['to']?>',
					insurance_scheme_id:'<?=@$_REQUEST['schemeId']?>',
					provider:'<?=@$_REQUEST['provider_id']?>',
					page: page
				}, function (s) {
					$('#claim_report_container').html(s);
				});
			}
			e.clicked = true;
		}
	});
	$(document).ready(function () {

	
	});

</script>