<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 4:49 PM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientMedicalReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$staff_id = $_SESSION ['staffID'];

$request = (new PatientMedicalReportDAO())->get($_GET['id']);
$patientId = $request->getPatient()->getId();

$approvedBy = ($request->getApprovedBy() == null) ? '' : $request->getApprovedBy()->getId();
$protect = (new Protect());
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false);
?>
<div style="width: 850px">
	<table class="table table-striped">
		<tr>
			<td colspan="4"><strong>Request Note/Reason</strong></td>
		</tr>
		<tr>
			<td colspan="4"><?= ($request->getRequestNote() != null) ? $request->getRequestNote() : "N/A" ?></td>
		</tr>
		<tr>
			<td colspan="4"><strong>Request Items</strong></td>
		</tr>
		<tr>
			<td colspan="4"><div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h5 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Lab Requests</a></h5>
						</div>
						<div id="collapseOne" class="panel-collapse collapse">
							<div class="panel-body">
								<p><?php foreach ($request->getLabs() as $lab){//$lab=new PatientLab();$lab=new LabGroup();?><a class="tag" href="javascript:" data-item="<?= $lab->getLabGroup()->getGroupName() ?>" data-id="<?= $patientId ?>" data-type="lab"><?= $lab->getLabGroup()->getGroupName() ?></a><?php } ?></p>
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h5 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">Imaging Requests</a></h5>
						</div>
						<div id="collapseTwo" class="panel-collapse collapse">
							<div class="panel-body">
								<p><?php foreach ($request->getImagings() as $scan){/*$scan=new PatientScan()*/?><a class="tag"  href="javascript:" data-item="<?=$scan->getRequestCode()?>" data-id="<?= $scan->getId()?>" data-type="scan"><?=$scan->getRequestCode()?></a><?php }?></p>
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h5 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">Procedure Requests</a></h5>
						</div>
						<div id="collapseThree" class="panel-collapse collapse">
							<div class="panel-body">
								<p><?php foreach ($request->getProcedures() as $procedure) {/*$procedure=new PatientProcedure()*/?><a href="javascript:;" data-item="<?=$procedure->getRequestCode()?>" data-patientId="<?=$patientId?>" data-id="<?=$procedure->getId()?>" data-type="procedure" class="tag"><?=$procedure->getRequestCode()?></a><?php }?></p>
							</div>
						</div>
					</div>
				</div>
			</td>
		</tr>

		<tr>
			<td colspan="4"><strong>Notes</strong> <?php if (!$request->getApproved() && !$request->getCancelled()) { ?>
					<span class="">
					<a data-heading="New Note: <?= $request->getRequestCode() ?> (<?= escape($request->getExam()->getName()) ?>)"
					   href="javascript:;" class="addMedicalReportNote pull-right" data-id="<?= $request->getId() ?>">Add</a>
					</span><?php } ?>
			</td>
		</tr>
		<?php if (count($request->getNotes()) > 0) { ?>
			<tr class="fadedText">
				<td class="nowrap"><strong>Date</strong></td>
				<td colspan="3"><strong>Note</strong></td>
			</tr>
			<?php foreach ($request->getNotes() as $note) { //$note = new PatientMedicalReportNote()?>
				<tr>
				<td class="nowrap"><?= date(MainConfig::$dateTimeFormat, strtotime($note->getCreateDate())) ?></td>

				<td colspan="3">
					<div><?php if (!(bool)$request->getCancelled()) {
							if (!(bool)$request->getApproved() || ($approvedBy == $staff_id)) { ?>
								<a data-heading="Edit Note: <?= $request->getRequestCode() ?> (<?= escape($request->getExam()->getName()) ?>)"
									href="javascript:;" class="_editDialog_"
									data-href="/medical_exam/boxy.edit_note.php?request_id=<?= $request->getId() ?>&id=<?= $note->getId() ?>"
									data-id="<?= $request->getId() ?>">Edit</a>
							<?php }
						} ?>|
					<?php if((bool)$request->getApproved()){?>
						<a href="javascript:" data-id="<?= $request->getId() ?>" class="printLink">Print</a>
					<?php } else if(!(bool)$request->getApproved() && !(bool)$request->getCancelled() && $this_user->hasRole($protect->medical_report_approver)){?>
						<a href="javascript:" class="approveM_RLink" data-id="<?= $request->getId() ?>">Approve</a>
					<?php }?>
					</div>
					<div><?= $note->getNote() ?></div>
				</td>
				</tr><?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="4">
					<div class="notify-bar"><i class="icon-info-sign"></i> No notes available</div>
				</td>
			</tr><?php } ?>

	</table>
</div>

<script type="text/javascript">
	$(document).on('click', '.addMedicalReportNote', function (evt) {
		var $id = $(this).data("id");
		var $title = $(this).data("heading");
		if (!evt.handled) {
			Boxy.load('/medical_exam/add_note.php?request_id=' + $id, {
				title: $title, afterHide: function () {
					Boxy.get($(".close")).hideAndUnload();
				}
			});
			evt.handled = true;
		}
	}).on('click', '._editDialog_', function (evt) {
		var $id = $(this).data("id");
		var $title = $(this).data("heading");
		$url = $(this).data("href");
		if (!evt.handled) {
			Boxy.load($url, {
				title: $title, afterHide: function () {
					Boxy.get($(".close")).hideAndUnload();
				}
			});
			evt.handled = true;
		}
	}).on('click', '.approveM_RLink', function (evt) {
		if(!evt.handled){
			<?php if($this_user->hasRole($protect->medical_report_approver)){?>
			var id = $(evt.target).data("id");
			Boxy.ask("Approve this report?", ["Yes", "No"], function (choice) {
				if (choice === "Yes") {
					$.post('/medical_exam/ajax.approve.php', {id: id}, function (s) {
						if (s.trim() === "ok") {
							$('.mini-tab > a.tab.on');//.get(0).click();
						} else {
							Boxy.alert("An error occurred");
						}
					});
				}
			});
			
			<?php }?>
			evt.handled = true;
		}

	}).on('click', '.printLink', function (evt) {
		var id = $(evt.target).data("id");
		if(!evt.handled){
			window.open("/medical_exam/printNotes.php?id="+id);
			evt.handled = true;
		}
	})
</script>