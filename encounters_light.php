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
							<input type="checkbox" name="ger89" value="<?= $pid ?>" onClick="askDischarge($(this), '<?= $pid ?>')"> Discharge<?php } ?>
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
		<a href="javascript:void(0)" id="referralsTrg">View Referrals</a> <?php /**|
		<a href="javascript:void(0)"
		   onClick="Boxy.load('/boxy.transferPatient.php?id=<?= $_GET['id'] ?>',{title:'Transfer Patient'})"
		   title="Transfer Patient to another Doctor">Refer Patient</a> **/?>
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
					<th nowrap>Date</th>
					<th>Department</th>
					<th>Specialization</th>
					<th>Doctor</th>
					<th class="hide">*</th>
				</tr>
				</thead>
				<?php foreach ($encounters->data as $e) {
					//$e=new Encounter();
					?>
					<tr>
						<td nowrap="">
							<div class="dropdown">
								<button class="drop-btn large dropdown-toggle" data-toggle="dropdown">
									Action
									<span class="caret"></span>
								</button>
								<ul class="soap dropdown-menu" role="menu" aria-labelledby="dLabel">
									<?php if ($this_user->hasRole($protect->doctor_role) && $e->open == true && !isset($_GET['aid']) && $owing <= 0) { ?>
										<li><a href="javascript:" data-id="<?= $e->id ?>" data-action="chart">Chart</a></li>
										<li><a href="javascript:" data-id="<?= $e->id ?>" data-action="inv_chart">Order Entry</a></li>
										<li><a href="javascript:" data-id="<?= $e->id ?>" data-action="pre_encounter">Pre-encounter</a></li>
									<?php } ?>
									<li><a href="javascript:" data-id="<?= $e->id ?>" data-action="details">View Details</a></li>
									<li><a href="javascript:" data-id="<?= $e->id ?>" data-action="print">Print</a></li>
			<?php if (!($e->count_complaints == 0 && $e->count_diagnoses == 0 && $e->count_plans == 0)) { ?>
									<li><a href="javascript:" data-id="<?= $e->id ?>" data-action="refer">Refer</a></li>
				<?php }?>
									<?php if ($e->open == true && $e->count_complaints == 0 && $e->count_diagnoses == 0 && $e->count_plans == 0) { ?>
									<li><a href="javascript:" data-id="<?= $e->id ?>" data-action="cancel">Cancel</a></li><?php } ?>
								</ul>
							</div>
						</td>
						<td nowrap><?= date("d/m/y g:ia", strtotime($e->start_date)) ?></td>
						<td><?= $e->department ? $e->department->getName() : 'N/A' ?></td>
						<td><?= $e->specialization_ ? $e->specialization_->getName() : 'N/A' ?></td>

						<td><?= $e->signed_by_name ?  $e->signed_by_name : 'N/A' ?></td>

						<td class="hide">
							<a href="javascript:" data-link-type="encounterDetails" data-id="<?= $e->id ?>" data-href="/encounter.details.php?encounter_id=<?= $e->id ?><?php if(isset($_GET['aid'])){?>&aid=<?=$_GET['aid']?><?php }?>">Details</a>
						| <a target="_blank" href="/encounter.print.php?id=<?= $e->id ?>">Print</a>
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
	};

	function askAdmit(element, pid) {
		if ($(element).prop('checked')) {
			Boxy.ask('Are you sure you want to place this patient on admission?', ['Yes', 'No'],
				function (choice) {
					if (choice === 'Yes') {
						sendAdmission(pid);
					} else {
						$('#toAdmit').prop('checked', false).iCheck('update');
						$('input[name=ger89]').prop('checked', false).iCheck('update');
					}
				}, {title: "Confirm Admission"});
		}
	}

	function askDischarge(element, pid) {
		if ($(element).prop('checked')) {
			Boxy.ask('Are you sure you want to discharge this patient?', ['Yes', 'No'],
				function (choice) {
					if (choice === 'Yes') {
						sendDischarge('<?= $ip != null ? $ip->getId() : '' ?>');
					} else {
						$('input[name="ger89"]').attr('checked', false).iCheck('update');
					}
				}, {title: "Confirm Discharge"});
		}
	}
	$(document).on('click','.soap.dropdown-menu > li > a', function(e){
		if(!e.handled){
			if(e.target !== undefined){
				var dataGot = $(e.target).data();
				//$(this).select2('val','');//should we reset the selection immediately?
				switch (dataGot.action){
					case 'chart':
						Boxy.load('boxy.soap.php?pid=<?= $_GET['id'] ?>&encounter_id='+dataGot.id,{title:'Add Note / Diagnosis Report', beforeHide: beforeHide, canClose: false });
						break;
					case 'inv_chart':
						Boxy.load('boxy.soap.inv.php?pid=<?= $_GET['id'] ?>&encounter_id='+dataGot.id,{title:'Investigations only Encounter', beforeHide: beforeHide, canClose: false });
						break;
					case 'pre_encounter':
						Boxy.load("/pre_encounter_forms.php?pid=<?= $_GET['id'] ?>&encounter_id="+dataGot.id, {title: 'Pre-Encounter Forms'});
						break;
					case 'details':
							Boxy.load("/encounter.details.php?encounter_id="+dataGot.id+"<?php if(isset($_GET['aid'])){?>&aid=<?=$_GET['aid']?><?php }?>", {title: "Encounter Details"});
						break;
					case 'refer':
						Boxy.load("/boxy.transferPatient.php?id=<?= $_GET['id'] ?>&encounter_id="+dataGot.id+"<?php if(isset($_GET['aid'])){?>&aid=<?=$_GET['aid']?><?php }?>",{title:'Transfer Patient'});
						break;
					case 'print':
						window.open("/encounter.print.php?id="+dataGot.id);
						break;
					case 'cancel':
						var $id = dataGot.id;
						Boxy.ask("Are you sure you want to cancel this encounter?", ["Yes", "No"], function (answer) {
							if (answer === "Yes") {
								$.post("/api/cancel_encounter.php", {id: $id}, function (response) {
									if (response.success) {
										<?php if(isset($_GET['aid'])){?>showTabs(16);<?php } else {?>showTabs(1);<?php } ?>
										Boxy.info(response.message);
									} else {
										Boxy.warn(response.message);
									}
								}, 'json').fail(function () {
									Boxy.alert("A server error occurred.");
								});
							}
						});
						break;
					default:
						break;
				}
			}
			e.handled = true;
		}
	}).on('click', '#referralsTrg', function (e) {
		if (!e.handled) {
			$('#contentPane').load('/patient_referrals.php?patient_id=<?=$_GET['id']?>');
			e.handled = true;
		}
	}).on('click', '.encounterResultsPager a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.get('encounters_light.php', {
					'page': page,
					'id': '<?= $_GET['id'] ?>'
				}, function (s) {
					$('#encountersList').html( $(s).filter('#encountersList').html());
				});
			}
			e.clicked = true;
		}
	}).ready(function(){
		$('select.encounter_action').select2({width:'100%', allowClear: true});
	});
</script>
