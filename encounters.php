<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/3/16
 * Time: 11:49 PM
 */
$time1 = microtime(true);
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.assessments.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
//if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse))exit($protect->ACCESS_DENIED);
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$pid = $_GET['id'];
$ip = (new InPatientDAO())->getActiveInPatient($_GET['id'], FALSE);
$patient = (new PatientDemographDAO())->getPatient($pid, FALSE);
$owing = $patient->getOutstanding() - (new CreditLimitDAO())->getPatientLimit($pid)->getAmount();
?>
<div class="menu-head">
	<?php if ($this_user->hasRole($protect->doctor_role)) {
		if (is_dir("admissions")) { ?>
			<div style="width:50%;float:left">
				<a href="javascript:void(0)">
					<label class="no-margin" style="cursor:pointer;display:inline-block">
						<?php if ($ip === null) { ?>
							<input type="checkbox" id="toAdmit" name="ger89" value="<?= $pid ?>" onClick="askAdmit($(this),'<?= $pid ?>')"> Request Admission<?php } else { ?>
							<input type="checkbox" name="ger89" value="<?= $pid ?>" onClick="askDischarge($(this), '<?= $pid ?>')">Discharge<?php } ?>
					</label>
				</a>
			</div>
		<?php } ?>
	<?php } ?>
	<?php /*if ($this_user->hasRole($protect->doctor_role)) { ?> |
								<label class="no-margin" style="display: inline-block">
										<i class="icon-book"></i>
										<a href="javascript:void(0)" onclick="showNewDocNote()">Doc. Note</a>
								</label> <?php }*/ ?>
	<?php if ($this_user->hasRole($protect->doctor_role)) { ?>
		<div style="width:auto;float:right">
		<a href="javascript:void(0)" id="referralsTrg">View Referrals</a> |
		<a href="javascript:void(0)"
		   onClick="Boxy.load('/boxy.transferPatient.php?id=<?= $_GET['id'] ?>',{title:'Transfer Patient'})"
		   title="Transfer Patient to another Doctor">Refer Patient</a>
		</div><?php } ?>

</div>
<!--    end if open encounter-->
<?php // }?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
$encounters = (new EncounterDAO())->forPatient($_GET['id'], false, $page, $pageSize);
$totalSearch = $encounters->total;
?>
<?php if ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->records)) { ?>
	<?php if (($totalSearch) > 0) { ?>
		<div id="encountersList">
			<table class="table table-striped" style="max-width:100%">
				<thead>
				<tr>
					<th>*</th>
					<th width="" nowrap>Date</th>
					<th>Specialization</th>
					<th width="25%">Presenting Complaints</th>
					<th width="25%">Diagnoses</th>
					<th width="25%">Plan</th>
					<th>*</th>
				</tr>
				</thead>
				<?php foreach ($encounters->data as $e) {
					//$e=new Encounter();
					$complaints = $diagnoses = $plans = [];
					foreach ($e->getPresentingComplaints() as $pc) {
						$complaints[] = $pc->description;
					}
					unset($pc);
					foreach ($e->getDiagnoses() as $pc) {
						$diagnoses[] = $pc->description;
					}
					unset($pc);
					foreach ($e->getPlan() as $pc) {
						$plans[] = $pc->description;
					}
					unset($pc);
					?>
					<tr>
						<td nowrap="">
							<?php if ($this_user->hasRole($protect->doctor_role) && $e->getOpen() == true) { ?>
								<a <?= $owing > 0 ? ' class="muted" title="Patient has outstanding balance"' : '' ?> href="javascript:void(0)" <?php if ($owing <= 0) { ?>onclick="Boxy.load('boxy.soap.php?pid=<?= $_GET['id'] ?>&encounter_id=<?= $e->getId() ?>',{title:'Add Note / Diagnosis Report', beforeHide: beforeHide, canClose: false })"<?php } ?>>
									Chart</a>
							<?php } ?>
							<?php if ($e->getCanceled() == false && count($plans)==0 && count($complaints)==0 && count($diagnoses)==0) { ?>
								| <a href="javascript:" class="cancelEncounter" data-id="<?= $e->getId() ?>">Cancel</a>
							<?php } ?>
						</td>
						<td nowrap><?= date("d/m/y <\\b\\r\\> g:ia", strtotime($e->getStartDate())) ?></td>
						<td><?= $e->getSpecialization() ? $e->getSpecialization()->getName() : 'N/A' ?></td>
						<td width="25%"><?php if ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) { ?>
								<ul>
								<li><?= (count($complaints) > 0) ? implode("</li><li>",  $complaints) : 'N/A' ?></li>
								</ul><?php } else { ?>- -<?php } ?></td>
						<td width="25%"><?php if ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) { ?>
								<ul>
								<li><?= (count($diagnoses) > 0) ? implode("</li><li>", $diagnoses) : 'N/A' ?></li>
								</ul><?php } else { ?>- -<?php } ?></td>
						<td width="25%"><?php if ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) { ?>
								<ul>
								<li><?= (count($plans) > 0) ? implode("</li><li>", $plans) : 'N/A' ?></li>
								</ul><?php } else { ?>- -<?php } ?></td>
						<td>
							<a href="javascript:;" data-link-type="encounterDetails" data-id="<?= $e->getId() ?>" data-href="encounter.details.php?encounter_id=<?= $e->getId() ?>">Details</a>
							| <a target="_blank" href="encounter.print.php?id=<?= $e->getId() ?>">Print</a>
						</td>
					</tr>
				<?php } ?>
			</table>
			<div class="encounterResultsPager dataTables_wrapper no-footer">
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

	<?php } else { ?>
		<div class="notify-bar">No encounters available</div>
	<?php } ?>
<?php } else { ?>
	<div class="warning-bar"><?= $protect->ACCESS_DENIED ?></div>
<?php } ?>
<script type="text/javascript">
	var beforeHide = function () {
		var boxy = Boxy.get($(".close"));
		if (!boxy.options.canClose) {
			if (window.confirm("Are you sure you want to close this dialog? Unsaved data is discarded.")) {
				boxy.options.canClose = true;
			}
		}

		//the above is the best because it's `blocking`
		//the other plugins are not `blocking`

		/*vex.dialog.confirm({
		 message: "Are you sure you want to close this dialog? Unsaved data is discarded.",
		 buttons: [{
		 text: "Close anyway",
		 type: 'submit',
		 className: 'vex-dialog-button-primary'
		 }, {
		 text: "Leave open",
		 type: 'submit',
		 className: 'vex-dialog-button-secondary',
		 click: function($vexContent, event) {
		 $vexContent.data().vex.value = false;
		 return vex.close($vexContent.data().vex.id);
		 }
		 }],
		 callback: function(e){
		 boxy.options.canClose = e;
		 }
		 });
		 return true;*/
	};

	$(document).on('click', 'a[data-link-type="encounterDetails"]', function (e) {
		if (!e.handled) {
			Boxy.load($(e.target).data('href'), {title: "Encounter Details"});
			e.handled = true;
		}
	}).on('click', '#referralsTrg', function (e) {
		if (!e.handled) {
			$('#contentPane').load('/patient_referrals.php?patient_id=<?=$_GET['id']?>');
			e.handled = true;
		}
	}).on('click', '.cancelEncounter', function (e) {
		var $id = $(this).data("id");
		if (!e.handled) {
			Boxy.ask("Are you sure you want to cancel this encounter?", ["Yes", "No"], function (answer) {
				if (answer === "Yes") {
					$.post("/api/cancel_encounter.php", {id: $id}, function (response) {
						if (response.success) {
							showTabs(1);
							Boxy.info(response.message);
						} else {
							Boxy.warn(response.message);
						}
					}, 'json').fail(function () {
						Boxy.alert("A server error occurred.");
					});
				}
			});
			e.handled = true;
		}
	}).on('click', '.encounterResultsPager a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.get('encounters.php', {
					'page': page,
					'id': '<?= $_GET['id'] ?>'
				}, function (s) {
					$('#encountersList').html( $(s).filter('#encountersList').html());
				});
			}
			e.clicked = true;
		}
	});
</script>

