<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/25/18
 * Time: 5:25 PM
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

$currency = (new CurrencyDAO())->getDefault();

$insurance_id = (new InsuranceSchemeDAO())->getInsuranceSchemes();
$provider = (new InsurerDAO())->getInsurers(FALSE);

$date = ((isset($_POST['from']) && $_POST['from'] != '' && isset($_POST['to']) && $_POST['to'] != '') ? TRUE : FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize =  (isset($_POST['pageSize'])) ? $_POST['pageSize'] : 100;
$totalSearch = 0;


$bills = array();
if ($date === TRUE) {
	$data = (new BillDAO())->getUnclaimedBills($_POST['from'], $_POST['to'], $_POST['schemeId'], $_POST['provider_id'], $page, $pageSize);
	$totalSearch = $data->total;
	$bills = $data->data;
}
$return = [];
foreach ($bills as $lines) {
	$pnotes_ = "";
	
	$type = "";
	$line = (new BillDAO())->getBill($lines->bill_id, true);
	$re = new stdClass();
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


?>

<div class="document" style="overflow: scroll;">
	<div id="claim_report_container">
		<?php if ($totalSearch < 1) {
			echo '<div class="notify-bar">There are no claims reports</div>';
		} else { ?>
			<table class="table table-striped table-hover no-footer table-bordered">
				<thead>
				<tr>
					<th>Claim ID</th>
					<th>Claim Date</th>
					<th>Hospital ID</th>
					<th>Patient</th>
					<th>Phone Number</th>
					<th>Scheme Name</th>
					<th>Enrolle ID</th>
					<th>Type</th>
					<th>Transaction Date</th>
					<th>Service</th>
					<th>Description</th>
					<th>Item Code</th>
					<th>Reason</th>
					<th>Diagnosis</th>
					<th>PA Code</th>
					<th>Quantity</th>
					<th>Amount (<?= $currency ?>)</th>
				
				</tr>
				</thead>
				<?php if (isset($return) && sizeof($return) > 0) {
					foreach ($return as $report) { ?>
						
						<tr>
							<td><?= $report->claimId ?></td>
							<td nowrap></td>
							<td nowrap><?= $report->cliniId ?></td>
							<td nowrap><?= $report->Patient ?></td>
							<td nowrap><?= $report->Phone ?></td>
							<td  nowrap><?= $report->insurance ?></td>
							<td nowrap><?= $report->errolleeId ?></td>
							<td nowrap><?= $report->type_ ?></td>
							<td nowrap><?= date('M jS, Y', strtotime($report->transaction_date)) ?></td>
							<td  nowrap><?= ucfirst(str_replace('_', ' ', $report->BillSource) ) ?></td>
							<td nowrap><?= ucwords($report->Description) ?></td>
							<td nowrap> <?= $report->item_code ?> </td>
							<td nowrap>  </td>
							<td nowrap><ul>
									<?= $report->Diagnosis ?>
								</ul>
							</td>
							<td nowrap><?= $report->Code ?></td>
							<td nowrap><?= $report->quantity ?></td>
							<td class="amount" nowrap><?= number_format(abs($report->Amount), 2) ?></td>
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
