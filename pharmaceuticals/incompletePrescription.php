<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
if ($_POST) {
	if ($_POST['action'] === "externalize") {
		$DAO = new PrescriptionDAO();
		$regimen = $DAO->getPrescription($_POST['id'], FALSE);
		$regimen->setExternal(TRUE);
		exit(json_encode($DAO->markExternal($regimen)));
	}
	exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');
$pharmacy = (isset($_REQUEST['pharmacy_id'])) ? $_REQUEST['pharmacy_id'] : null;
$under_admission = (isset($_REQUEST['under_admission'])) ? $_REQUEST['under_admission'] : null;
@session_start();
$patient = (isset($_SESSION['pid']) ? $_SESSION['pid'] : ( !is_blank(@$_REQUEST['patient_id']) ? @$_REQUEST['patient_id']: null  ));

$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$data = (new PrescriptionDAO())->getOpenPrescriptions($page, $pageSize, $pharmacy, TRUE, $patient, $under_admission);
$totalSearch = $data->total;
?>
<div class="row-fluid">
	<label class="span4"> Business Unit/Service Center <select name="pharmacy_id" data-placeholder="-- Select processing pharmacy --">
			<option></option><?php foreach ($pharmacies as $l) { ?>
				<option value="<?= $l->getId() ?>" <?= (isset($_REQUEST['pharmacy_id']) && $_REQUEST['pharmacy_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?>
		</select></label>
	<label class="span4">Filter By patient
		<input type="hidden" name="patient_id" value="<?=$patient ?>">
	</label>
	<label class="span4">Patients on admission
		<select name="under_admission" data-placeholder="-- Filter List by Admission Status --">
			<option></option>
			<option value="1"<?= isset($_REQUEST['under_admission']) && $_REQUEST['under_admission'] == 1 ? 'selected="selected"': '' ?> >Patient On Admission</option>
		</select>
	</label>
</div>


<div id="open_prescriptions"><a href="javascript:;" onclick="aTab(1)" data-href="?incomplete">Refresh List</a></div>
<?= (($totalSearch > 0) ? '' : '<div class="notify-bar">There are no open prescriptions</div>') ?>
<?php if ($totalSearch > 0) { ?>
	<div class="dataTables_wrapper">
		<table class="table outer table-hover table-striped">
			<thead>
			<tr>
				<th>Date</th>
				<th>ID</th>
				<th>By</th>
				<th>Patient</th>
				<th>Coverage</th>
				<th>*</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($data->data as $p) {
//        $p = new Prescription();
				if ($p->getPatient() != null) { ?>
					<tr>
						<td nowrap class="pres_details"
						    data-href="/pharmaceuticals/boxy_fillBatch.php?pCode=<?= $p->getCode() ?>&mode=pharmacy&pid=<?= $p->getPatient()->getId() ?>"><?= date("d M, Y", strtotime($p->getWhen())) ?></td>
						<td class="color pres_details"
						    data-href="/pharmaceuticals/boxy_fillBatch.php?pCode=<?= $p->getCode() ?>&mode=pharmacy&pid=<?= $p->getPatient()->getId() ?>"><?= $p->getCode() ?></td>
						<td class="pres_details"
						    data-href="/pharmaceuticals/boxy_fillBatch.php?pCode=<?= $p->getCode() ?>&mode=pharmacy&pid=<?= $p->getPatient()->getId() ?>"><?= $p->getRequestedBy()->getFullname() ?></td>
						<td class="pres_details" data-href="/pharmaceuticals/boxy_fillBatch.php?pCode=<?= $p->getCode() ?>&mode=pharmacy&pid=<?= $p->getPatient()->getId() ?>">
							<?= $p->getPatient()->isAdmitted() ? '<i title="Patient is currently admitted" class="required fa fa-hospital-o"></i>':'' ?>
							<span class="profile" data-pid="<?= $p->getPatient()->getId() ?>"><?= $p->getPatient()->getFullName()?></span></td>
						<td class="pres_details" data-href="/pharmaceuticals/boxy_fillBatch.php?pCode=<?= $p->getCode() ?>&mode=pharmacy&pid=<?= $p->getPatient()->getId() ?>">
							<?= $p->getPatient()->getScheme()->getName() ?>
						</td>
						<td><?php if ($p->getServiceCentre() != null) { ?>
								<a class="transfer" href="javascript:;" data-href="/pharmaceuticals/boxy_transferPrescription.php?pCode=<?= $p->getCode() ?>">Transfer</a> | <?php } ?>
							<?php if (!$p->getExternal()) { ?>
							<a href="" class="mark_request_external" title="This request is to be fulfilled externally" data-id="<?= $p->getId() ?>">
									Mark External</a><?php } ?></td>
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
			$('select[name="pharmacy_id"]').select2({width: '100%', allowClear: true}).change(function (e) {
				if(!e.handled){
					goto(0);
					e.handled = true;
				}
			});
			$('select[name="under_admission"], select[name="under_admission"]').select2({
				width: '100%',
				allowClear: true
			}).change(function (e) {
				goto(0);
				e.handled = true;
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

			$(document).on('click', '.mark_request_external', function (e) {
				if (!e.clicked) {
					$this = $(this);
					$.post('<?= $_SERVER['REQUEST_URI']?>', {'id': $this.data("id"), 'action': 'externalize'}, function (data) {
						console.log(data);
						if (data === 'true') {
							$this.remove();
						} else {
							Boxy.alert("Request failed");
						}
					});
					e.clicked = true;
				}
				e.preventDefault();
				return false;
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
				if (!e.handled) {
					goto(0);
					e.handled = true;
				}
			});

			function goto(page) {
				$('#prescription_container').load('incompletePrescription.php?page=' + page + '&pharmacy_id=' + $('select[name="pharmacy_id"]').val()+'&patient_id='+$('[name="patient_id"]').val()+'&under_admission='+$('select[name="under_admission"]').val());
			}
		</script>
