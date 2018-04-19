<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
sessionExpired();
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$pid = (isset($_POST['patient_id'])) ? $_POST['patient_id'] : null;
$data = (new BillDAO())->outstandingBills($pid, $page, $pageSize, null);
$totalSearch = $data->total;
$owers = $data->data;
?>
<form id="params">
	<div class="row-fluid">
		<label class="span12">
			Filter by patient EMR
			<input type="hidden" name="patient_id" control>
		</label>
	</div>
</form>
<div id="bill_report_container">
	<h6>Outstanding Bills: <?= $totalSearch ?></h6>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Patient</th>
			<th>Scheme</th>
			<th class="amount">Outstanding Amount</th>
		</tr>
		</thead>
		<?php foreach ($owers as $o) { //o is custom object ?>
			<tr>
				<td><a href="/patient_profile.php?id=<?= $o->PatientID ?>" target="_blank"><?= $o->Patient ?></a></td>
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
		$('[name="patient_id"][control]').select2({
			placeholder: "Patient Name (Patient ID [Patient Legacy ID])",
			allowClear: true,
			minimumInputLength: 3,
			width: '100%',
			formatResult: function (data) {
				return data.fullname + " -" + data.id + (data.lid.trim() !== "" ? "[" + data.lid + "]" : "") + ", Phone: " + data.phone;
			},
			formatSelection: function (data) {
				return data.fullname + " -" + data.id + ", " + data.sex + ", " + moment(data.dob).fromNow(true) + " old " + (typeof data.vitalSigns !== "undefined" && typeof data.vitalSigns.weight !== "undefined" ? ", " + data.vitalSigns.weight.value + "kg" : "");
			},
			formatNoMatches: function (term) {
				return "Sorry no record found for '" + term + "'";
			},
			formatInputTooShort: function (term, minLength) {
				return "Please enter the patient name or ID";
			},
			ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
				url: '/api/search_patients.php',
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term, // search term
						limit: 100,
						asArray: true,
						medical: true
					};
				},
				results: function (data, page) { // parse the results into the format expected by Select2.
					// since we are using custom formatting functions we do not need to alter remote JSON data
					return {results: data};
				}
			}
		}).change(function (evt) {
			if (evt.added !== undefined) {
				$.post("/billing/all_outstandingbills.php", {patient_id: evt.added.id, page: 0}, function (s) {
					$("#bill_report_container").html($(s).filter("#bill_report_container").html());
				});
			} else if (evt.removed !== undefined && evt.added == undefined) {
				$.post("/billing/all_outstandingbills.php", {page: 0}, function (s) {
					$("#bill_report_container").html($(s).filter("#bill_report_container").html());
				});
			}
		});
	});
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post("/billing/all_outstandingbills.php", {page: page}, function (s) {
					$("#bill_report_container").html($(s).filter("#bill_report_container").html());
				});
			}
			e.clicked = true;
		}
	});
</script>
