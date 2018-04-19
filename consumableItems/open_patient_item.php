<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/6/17
 * Time: 12:31 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/class.config.main.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$service_centers = (new ServiceCenterDAO())->all('item');
$center = (isset($_REQUEST['service_center_id'])) ? $_REQUEST['service_center_id'] : null;
@session_start();
$patient = (isset($_SESSION['pid']) ? $_SESSION['pid'] : (!is_blank(@$_REQUEST['patient_id']) ? @$_REQUEST['patient_id'] : null));

$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$data = (new PatientItemRequestDAO())->getAllItems($page, $pageSize, $center, true, $patient, null, $statusFilter = "open");

$totalSearch = $data->total;

?>
<div class="row-fluid">
	<label class="span6"> Business Unit/Service Center <select name="service_center_id" data-placeholder="-- Select processing business center --">
			<option></option><?php foreach ($service_centers as $l) { ?>
				<option value="<?= $l->getId() ?>" <?= (isset($_REQUEST['service_center_id']) && $_REQUEST['service_center_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?>
		</select></label>
	<label class="span6">Filter By patient
		<input type="hidden" name="patient_id" value="<?= $patient ?>">
	</label>
</div>

<div id="open_request"><a href="javascript:;" onclick="aTab(1)" data-href="?open">Refresh List</a></div>
<?= (($totalSearch > 0) ? '' : '<div class="notify-bar">There are no open requests</div>') ?>
<?php if ($totalSearch > 0) { ?>
	<div class="dataTables_wrapper">
		<table class="table outer table-hover table-striped">
			<thead>
			<tr>
				<th>Date</th>
				<th>ID</th>
				<th>By</th>
				<th>Patient</th>
				<th>Item</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($data->data as $p) {
				if ($p->getPatient() != null) { ?>
					<tr>
						<td nowrap class="pres_details"
						    data-href="/consumableItems/boxy.fillbatch.php?pCode=<?= $p->getCode() ?>&pid=<?= $p->getPatient()->getId() ?>"><?= date(MainConfig::$dateTimeFormat, strtotime($p->getRequestDate())) ?></td>
						<td class="pres_details"
						    data-href="/consumableItems/boxy.fillbatch.php?pCode=<?= $p->getCode() ?>&pid=<?= $p->getPatient()->getId() ?>"><a data-href="/consumableItems/boxy.fillbatch.php?pCode=<?= $p->getCode() ?>&pid=<?= $p->getPatient()->getId() ?>"><?= $p->getCode() ?></a></td>
						<td class="pres_details"
						    data-href="/consumableItems/boxy.fillbatch.php?pCode=<?= $p->getCode() ?>&pid=<?= $p->getPatient()->getId() ?>"><?= $p->getRequestedBy()->getFullname() ?></td>
						<td class="pres_details" data-href="/consumableItems/boxy.fillbatch.php?pCode=<?= $p->getCode() ?>&pid=<?= $p->getPatient()->getId() ?>"><?= $p->getPatient()->getFullName() ?></td>
						<td class="pres_details" data-href="/consumableItems/boxy.fillbatch.php?pCode=<?= $p->getCode() ?>&pid=<?= $p->getPatient()->getId() ?>">
                            <?php foreach ($p->getData() as $item)  { ?>
                                <?= $item->getItem() ? '<span class="tag">'. $item->getItem()->getName().'</span>' : '--' ?>
                         <?php } ?>
						</td>


					</tr>
				<?php }
			} ?>
			</tbody>
		</table>
		<div class="list1 dataTables_wrapper no-footer">
			<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
				results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
			</div>

			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
					records</a>
			</div>
		</div>

	</div>
<?php } ?>
<script>
	$('select[name="service_center_id"]').select2({
		width: '100%', allowClear: true
	}).change(function (e) {
		if (!e.handled) {
			goto(0);
			e.handled = true;
		}
	});
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				goto(page);
			}
			e.clicked = true;
		}
	});


	$('[name="patient_id"]').css({'font-weight': 400}).select2({
		placeholder: "Filter list by patient",
		minimumInputLength: 3,
		width: '100%',
		allowClear: true,
		ajax: {
			url: "/api/search_patients.php",
			dataType: 'json',
			data: function (term, page) {
				return {
					q: term
				};
			},
			results: function (data, page) {
				return {results: data};
			}
		},
		formatResult: function (data) {
			var details = [];
			details.push(data.patientId ? "EMR ID:" + data.patientId : null);
			details.push(data.fname ? data.fname : null);
			details.push(data.mname ? data.mname : null);
			details.push(data.lname ? data.lname : null);
			return implode(" ", details);
			//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
		},
		formatSelection: function (data) {
			var details = [];
			details.push(data.patientId ? "EMR ID:" + data.patientId : null);
			details.push(data.fname ? data.fname : null);
			details.push(data.mname ? data.mname : null);
			details.push(data.lname ? data.lname : null);
			return implode(" ", details);
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
		if (!e.handled) {
			goto(0);
			e.handled = true;
		}
	});

	function goto(page) {
		$('#prescription_container').load('open_patient_item.php?page=' + page + '&service_center_id=' + $('select[name="service_center_id"]').val() + '&patient_id=' + $('[name="patient_id"]').val());
	}

</script>

