<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/21/16
 * Time: 8:52 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$insurance_id = (new InsuranceSchemeDAO())->getInsuranceSchemes();
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 50;
$totalSearch = 0;
$owers = [];
if (isset($_REQUEST['page'], $_REQUEST['sort'])) {
	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "HAVING SUM(amount)>0";
	$admitted = isset($_REQUEST['admitted']) && $_REQUEST['admitted']=='admitted' ? 'admitted' : null;
	$insScheme = isset($_REQUEST['insurance_scheme_id']) && !is_blank($_REQUEST['insurance_scheme_id']) ? $_REQUEST['insurance_scheme_id'] : null;
	$data = (new BillDAO())->outstandingBills(null, $page, $pageSize, $insScheme, $sort, $admitted);

	$totalSearch = $data->total;
	$owers = $data->data;
}

?>
<form id="params">
	<div class="row-fluid">
		<label class="span5">
			Filter by Insurance Scheme
			<select id="insurance_scheme_id" name="insurance_scheme_id" data-placeholder="Select insurance scheme">
				<option></option>
				<?php foreach ($insurance_id as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['insurance_scheme_id']) && $_REQUEST['insurance_scheme_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label class="span2"><span style="visibility: hidden;">...</span>
			<select name="sort" required>
				<option value="HAVING SUM(amount)>0">Debtors</option>
				<option value="HAVING SUM(amount)<0">Creditors</option>
			</select>
		</label>
		<label class="span3"><span style="visibility: hidden;">...</span>
			<select name="admitted">
				<option value="not_admitted">All patients</option>
				<option value="admitted">Currently on admission</option>
			</select>
		</label>
		<label class="span1"><span style="visibility: hidden;">...</span>
			<button class="wide show btn" type="button">Show</button>
		</label>
		<label class="span1"><span style="visibility: hidden;">...</span>
			<button class="wide export btn" type="button">Export</button>
		</label>
	</div>
</form>
<div class="document" id="bill_report_container">
	<h6>Outstanding Bills: <?= $totalSearch ?></h6>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Patient</th>
			<th>Scheme</th>
			<th class="amount">Amount <span class="naira"></span></th>
		</tr>
		</thead>
		<?php foreach ($owers as $o) { //o is custom object ?>
			<tr>
				<td><?php if($o->isAdmitted){?><i title="Patient is admitted" class="fa fa-hospital-o required"></i><?php }?> <a href="/patient_profile.php?id=<?= $o->PatientID ?>" target="_blank"><?= $o->Patient ?></a></td>
				<td><?= $o->Scheme ?></td>
				<td class="amount"><?= $o->Outstanding ?></td>
			</tr>
		<?php } ?>
	</table>

	<div class="list1 dataTables_wrapper no-footer">
		<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
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
			   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
			<a id="DataTables_Table_1_next"
			   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
			   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$("#insurance_scheme_id").select2({allowClear: true, width: '100%'});
		$('select[name="sort"]').select2({width: '100%'});
		$('select[name="admitted"]').select2({width: '100%'});
	});
	$(document).on('click', 'button[class="wide export btn"][type="button"]', function () {
		window.open('/excel.php?dataSource=outstanding_bill&filename=Outstanding_Bills&insurance_scheme_id=' + $("#insurance_scheme_id").val() + '&sort=' + $('select[name="sort"]').val()+'&admitted='+$('select[name="admitted"]').val());
	});
	$(document).on('click', 'button[class="wide show btn"][type="button"]', function () {
		goTo(0);
	});
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				goTo(page);
			}
			e.clicked = true;
		}
	});

	function goTo(page) {
		var params = $('#params').serialize();
		if (page !== undefined) {
			params += "&page=" + page;
		}
		$('#bill_report_container').load('/pages/pm/reporting/outstanding_bills.php?' + params + " #bill_report_container>*", "");
	}
</script>
