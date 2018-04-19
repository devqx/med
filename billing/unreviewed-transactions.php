<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/3/15
 * Time: 3:23 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$sources = (new BillSourceDAO())->getBillSources();
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$patientId = !is_blank(@$_POST['patient_id']) ? @$_POST['patient_id'] : null;
$staffId = !is_blank(@$_POST['staff_id']) ? @$_POST['staff_id'] : null;
$bill_source_ids = !is_blank(@$_POST['bill_source_ids']) ? @$_POST['bill_source_ids'] : null;
//$sources = !is_blank($_POST['bill_source_ids']) ? " AND bill_source_id IN (". implode(", ", $_POST['bill_source_ids']) .")" : "";

$date_from = !is_blank(@$_POST['date_from']) ? @$_POST['date_from'] : null;
$date_to = !is_blank(@$_POST['date_to']) ? @$_POST['date_to'] : null;

$data = (new BillDAO())->getUnReviewedBills(TRUE, $page, $pageSize, $patientId, $staffId, $bill_source_ids, $date_from, $date_to);
$totalSearch = $data->total;
?>

<?php if ($patientId == null) { ?>
<div class="row-fluid">
	<label class="span6">
		<input type="hidden" name="patient_id" placeholder="-- Filter by patient --">
	</label><label class="span6">
		<input type="hidden" name="staff_id" placeholder="-- Filter by Staff --">
	</label>
</div>

<div class="row-fluid">
	<div class="span6">
		<label><select data-placeholder="Bill Source:" name="bill_source_ids" id="bill_source_ids" class="bill_source_ids" multiple="multiple" class="wide">
				<?php foreach ($sources as $source) { ?>
					<option value="<?= $source->getId() ?>"><?= ucwords($source->getName()) ?></option><?php } ?>
			</select></label>
	</div>
	<div class="span6">
		<div class="input-prepend">
			<span class="add-on">From</span>
			<input class="span2" type="text" id="date_start" name="date_start" value="<?= isset($start) ? $start : '' ?>" placeholder="Start Date">
			<span class="add-on">To</span>
			<input class="span2" type="text" id="date_stop" name="date_stop" value="<?= isset($stop) ? $stop : '' ?>" placeholder="Stop Date">
			<button class="btn" type="button" id="date_filter">Apply</button>
		</div>
	</div>
</div>
<?php } ?>
<div id="toBeReviewedBills">
	<h6><?= $totalSearch ?> transactions to be reviewed</h6>
	<table id="unRevBills" class="table table-striped">
		<thead>
		<tr>
			<th>Date</th>
			<th>Bill Description</th>
			<th>Type</th>
			<th class="amount">Amount</th>
			<?php if($patientId == null) { ?>
				<th>Patient</th>
			<?php } ?>
			<th class="hide">EMR</th>
			<th>Staff</th>
			<th></th>
		</tr>
		</thead>
		<?php foreach ($data->data as $b) {//$b=new Bill;?>
			<tr>
				<td nowrap="nowrap"><?= date(MainConfig::$dateTimeFormat, strtotime($b->getTransactionDate())) ?></td>
				<td>
					<small><?= $b->getDescription() ?></small>
				</td>
				<td><?= explode("-", ucwords($b->getTransactionType()))[0]  ?></td>
				<td class="amount"><?= $b->getAmount() ?></td>
				<?php if ($patientId == null) { ?>
				<td><span class="profile" data-pid="<?= $b->getPatient()->getId() ?>"><?= $b->getPatient()->getFullname() ?></span></td>
				<td class="hide"><?= $b->getPatient()->getId() ?></td>
				<?php } ?>
				<td><?= $b->getReceiver()->getFullname() ?></td>
				<td><a href="javascript:;" class="authorize_transaction_link" data-id="<?= $b->getId() ?>">Authorize</a></td>
			</tr>
		<?php } ?>
	</table>
	<div class="list12 dataTables_wrapper no-footer">
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

<script>
	function post(e){
		if (!e.handled) {
			reload(0);
			e.handled = true;
		}
	}
	$(document).ready(function () {
		$('[name="patient_id"]').select2({
			placeholder: "Filter List by Patient EMR or Name",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: '/api/search_patients.php',
				dataType: 'json',
				data: function (term, page) {return {q: term}},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			id: function (data) {
				return data.patientId;
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/search_patients.php?pid=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		}).change(function (e) {
			post(e);
		});

		$('[name="staff_id"]').select2({
			placeholder: $(this).attr('placeholder'),
			allowClear: true,
			minimumInputLength: 3,
			width: '100%',
			formatResult: function (data) {
				return data.fullname + "; " + (data.specialization == null ? "" : data.specialization.name);
			},
			formatSelection: function (data) {
				return data.fullname + "; " + (data.specialization == null ? "" : data.specialization.name);
			},
			ajax: {
				url: '/api/search_staffs.php',
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term, // search term
						limit: 100,
						asArray: true
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			}
		}).change(function(e){
			post(e);
		});

		$('input[name="date_start"]').datetimepicker({format: 'Y-m-d', timepicker: false});
		$('input[name="date_stop"]').datetimepicker({format: 'Y-m-d', timepicker: false});

		//$('#bill_source_ids').select2();
		$('.bill_source_ids').select2({
			placeholder: "Filter bill sourecs",
			width: '100%',
			allowClear: true,
		});
	});
	$(document).on('click', '.authorize_transaction_link', function (e) {
		var tid = $(e.target).data("id");
		if (!e.handled) {
			Boxy.load("authorize_transaction.php?tid=" + tid, {
				title: "Authorize Transaction", afterHide: function () {
					reload(0);
				}
			});
			e.handled = true;
		}
	});
	$(document).on('click', '.list12.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				reload(page);
			}
			e.clicked = true;
		}
	});

	$('#date_filter').live('click', function (e) {
		if (!e.handled) {
			reload(0);
			e.handled = true;
		}
	});

	function reload(page) {
		data = {page: page, patient_id: $('[name="patient_id"]').val(), staff_id: $('[name="staff_id"]').val(), bill_source_ids: $('.bill_source_ids').val(), date_from: $('#date_start').val(), date_to: $('#date_stop').val()};
		$.post("/billing/unreviewed-transactions.php", data, function (s) {
			$("#toBeReviewedBills").html($(s).filter("#toBeReviewedBills").html());
		});
	}
</script>

