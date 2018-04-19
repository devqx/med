<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/15/16
 * Time: 1:44 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';

if ($_GET['type'] == "lab") {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabGroupDAO.php';
	$lg = (new LabGroupDAO())->getLabGroup($_GET['item'], $_GET['id'], TRUE);
	?>
	<table class="table ">
		<tr class="head-link" id="lab<?= $lg->getGroupName() ?>" data-group="<?= $lg->getGroupName() ?>">
			<td>Request Date <br> <?= date(MainConfig::$dateTimeFormat, strtotime($lg->getRequestTime())) ?></td>
			<td>Request # <br><?= $lg->getGroupName() ?></td>
			<td>Requested By <br><?= $lg->getRequestedBy()->getUsername() ?></td>
			<td style="float: right;">
				<a href="/labs/printLab.php?gid=<?= $lg->getGroupName() ?>&id=<?= $_GET['id'] ?>" class="btn btn-small1 _p_action" title="Print this result" target="_blank" data-action="print">Print</a>
			</td>
		</tr>

		<tr>
			<td colspan="4">
				<table class="table table-striped">
					<tr class="">
						<th>Test Type</th>
						<th>Test Result</th>
						<th>Reference/Range</th>
						<th>*</th>
					</tr>
					<?php foreach ($lg->getRequestData() as $pl) { ?>
						<tr>
							<td><?= $pl->getTest()->getName() ?></td>
							<td><?php if ($pl->getLabResult() !== null && ($pl->getLabResult()->isApproved())) { ?>
									<a href='javascript:;' onclick="new Boxy('<div><table class=\'table table-striped table-bordered table-hover\'>'
									+ '<tr><td>Field</td><td>Value</td></tr>'
									<?php foreach ($pl->getLabResult()->getData() as $data) { ?>
										+'<tr><td><?= htmlspecialchars($data->getLabTemplateData()->getLabel()) ?><?php if($data->getLabTemplateData()->getReference()!=""){?><br>(Reference: <?= htmlspecialchars($data->getLabTemplateData()->getReference()) ?>)<?php }?></td><td><?= htmlspecialchars($data->getValue()) ?></td></tr>'
									<?php }
									if ($pl->getLabResult()->getAbnormalValue()) { ?>
										+ '<tr><td colspan=\'2\' class=\'alert-error\'><span><i class=\'icon-exclamation-sign\'></i> Attention Required</span></td></tr></table></div>', {title: '<?= $pl->getTest()->getName() ?> Result'}
										)" title="View this result">Result</a>
										<?php
									} else if ($pl->getLabResult() !== null && !($pl->getLabResult()->isApproved())) {
										echo 'Waiting Approval';
									} else {
										echo 'Not Ready';
									}
								}
								?>
							</td>

							<td><?= $pl->getTest()->getReference() ?></td>
							<td>
								<a href="javascript:void(0)" onClick="Boxy.load('/labs/lab.notes.php?id=<?= $pl->getId() ?>')">
									View Notes</a></td>
						</tr>

					<?php } ?>
				</table>
			</td>
		</tr>
	</table>
	<?php
} else if ($_GET['type'] == "scan") {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
	$pScans = (new PatientScanDAO())->getScan($_GET['id']);
	?>
	<table class="table table-striped">
		<tr>
			<td colspan="4"><strong>Investigations requested</strong></td>
		</tr>
		<tr>
			<td colspan="4"> <ul class="list-blocks"><?php foreach ($pScans->getScan() as $scan) { ?><li><?= $scan->getName() ?></li><?php } ?></ul> </td>
		</tr>
		<tr>
			<td colspan="4"><strong>Request Note/Reason</strong></td>
		</tr>
		<tr>
			<td colspan="4"><?=($pScans->getRequestNote()!=null)?$pScans->getRequestNote():"N/A"?></td>
		</tr>
		<tr>
			<td colspan="4">
				<strong>Attachments</strong>
			</td>
		</tr>
		<?php if (count($pScans->getAttachments()) > 0) { ?>
			<tr class="fadedText">
				<td><strong>Date</strong></td>
				<td colspan="2"><strong>Attachment</strong></td>
				<td><strong>By</strong></td>
			</tr>
			<?php foreach ($pScans->getAttachments() as $at) { ?>
				<tr>
				<td class="nowrap"><?= date(MainConfig::$dateTimeFormat, strtotime($at->getDateAdded())) ?></td>
				<td colspan="2"><a href="<?= $at->getAttachmentURL() ?>" target="_blank">Download</a> <?= $at->getNote() ?> <i class="icon-download-alt"></i></td>
				<td class="fadedText nowrap"><?= $at->getCreator()->getFullname() ?></td></tr><?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="4">
					<div class="notify-bar"><i class="icon-info-sign"></i> No attachments available
					</div>
				</td>
			</tr> <?php } ?><tr>
			<td colspan="4"><strong>Notes</strong>
			</td>
		</tr>
		<?php if (count($pScans->getNotes()['reports']) > 0) { ?>
			<tr class="fadedText">
				<td class="nowrap"><strong>Date</strong></td>

				<td colspan="3"><strong>Note</strong></td>
			</tr>
			<?php foreach ($pScans->getNotes()['reports'] as $note) { ?>
				<tr>
				<td class="nowrap"><?= date(MainConfig::$dateTimeFormat, strtotime($note->getDateAdded())) ?></td>

				<td colspan="3">
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
<?php } else if($_GET['type']=="procedure"){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
	@session_start();
	$p_p = (new PatientProcedureDAO())->get($_GET['id']);

	$insurance = (new InsuranceDAO())->getInsurance($p_p->getPatient()->getId(), TRUE);
	$protect = new Protect();
	$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

	$bills = new Bills();
	$pat = (new PatientDemographDAO())->getPatient($p_p->getPatient()->getId(), false, null, null);

	$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId()) - (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
	$selfOwe = $_ > 0 ? $_ : 0;


	$status = $selfOwe > 0 ? "disabled" : "enabled";
	?>

	<div class="patient_profile_container_">
		<div id="profile_container">
			<?php $ARR['patient_ID'] = $p_p->getPatient()->getId();
			?>
			<!--<div class="line">
				<div class="pull-left">
					<a title="Change Photo" href="javascript:void(0);">
						<img class="passport" src="<?= $p_p->getPatient()->getPassportPath(); ?>" width="53"/>
					</a>
				</div>
				<div>
					<h4 class="uppercase"><?= $p_p->getPatient()->getFullname() ?></h4>
                <span class="fadedText" id="pid_"><i class="icon icon-user"></i>
                    <a href="/patient_profile.php?id=<?= $p_p->getPatient()->getId() ?>"><?= $p_p->getPatient()->getId() ?> <?= (trim($p_p->getPatient()->getLegacyId()) != "") ? ' (' . $p_p->getPatient()->getLegacyId() . ')' : '' ?></a></span>
				</div>
				<div></div>

				<button class="btn pull-right" disabled><i class="icon-adjust"></i>
					STATUS: <?= strtoupper($p_p->getStatus()) ?></button>
				<div class="pull-right">&nbsp;</div>
				<button class="btn pull-right" disabled title="modify this request"><i class="icon-edit"></i></button>

			</div>
			<div class="line">
				<div class="item_block">
					<span>DOB</span>
					<span><?= date("d M, Y", strtotime($p_p->getPatient()->getDateOfBirth())) ?></span>
				</div>
				<div class="item_block">
					<span>Insurance Status</span>
					<span <?=$insurance->getActive() ? 'class="abnormal" title="Insurance is not active"' : ''?>><?= strtoupper($insurance->getScheme()->getName()) . " (<em>" . strtoupper($insurance->getScheme()->getType()) . "</em>)" ?></span>
				</div>
			</div> -->
			<div class="line">
				<div class="item_block">
					<span>Request #</span>
					<span><?= $p_p->getRequestCode() ?></span>
				</div>
				<div class="item_block">
					<span>Procedure</span>
					<span><?= $p_p->getProcedure()->getName() ?></span>
				</div>
				<div class="item_block">
					<span>Diagnoses</span> <?php if (count($p_p->getConditions()) == 0) {
						echo 'N/A';
					} ?><?php foreach ($p_p->getConditions() as $con) { ?>
						<span class="tag"><?= $con->getName() ?></span>
					<?php } ?>
				</div>
				<div class="item_block">
					<span>Request Date</span><span><?= date("d M, Y h:i A", strtotime($p_p->getRequestDate())) ?></span></div>
				<div class="item_block"><span>Request By</span> <span><?= $p_p->getRequestedBy()->getFullname() ?></span></div>

			</div>
			<div class="line">
				<div class="pull-"><?php if ($p_p->getHasAnesthesiologist()) {if (is_null($p_p->getAnesthesiologist())) {?>Anesthesiologist Not set <?php } else {?>Anesthesiologist Set <i class="icon-stethoscope"></i><?php }} ?>
				</div>
				<div class="pull-"><?php if ($p_p->getHasSurgeon()) {if (is_null($p_p->getSurgeon())) {?>Surgeon Not Set<?php} else {?>Surgeon Set <i class="icon-heart"></i><?php }} ?>
				</div>
				<div class="pull-">
					<?php if (is_null($p_p->getTheatre())) { ?>
					 Theatre not Set<?php } else { ?>
						<i class="icon-hospital"></i> Theatre: <?= $p_p->getTheatre()->getName() ?><?php } ?>
				</div>
			</div>
		</div>
		<div id="tab-container" class="tab-container">
			<ul>
				<li><a href="/procedures/tabs/notes.php?id=<?= $p_p->getId() ?>&add=false" data-target="#notes">Notes/Findings</a></li>
				<li><a href="/procedures/tabs/resources.php?id=<?= $p_p->getId() ?>&add=false" data-target="#resources">Resources</a></li>
				<li><a href="/procedures/tabs/materials.php?id=<?= $p_p->getId() ?>&add=false" data-target="#materials">Materials</a></li>
				<li><a href="/procedures/tabs/nursing_services.php?id=<?= $p_p->getId() ?>&add=false" data-target="#nursing_tasks">Nursing
						Services</a></li>
				<li><a href="/procedures/tabs/reports.php?id=<?= $p_p->getId() ?>&add=false" data-target="#reports">Medical Report</a></li>
				<li><a href="/procedures/tabs/regimens.php?id=<?= $p_p->getId() ?>&add=false" data-target="#regimens">Regimens</a></li>
				<li><a href="/procedures/tabs/pre-proc-list.php?id=<?= $p_p->getId() ?>&add=false" data-target="#pre-procs">Pre-Procedure
						List</a></li>
			</ul>
			<div id="notes"></div>
			<div id="resources"></div>
			<div id="materials"></div>
			<div id="nursing_tasks"></div>
			<div id="tasks"></div>
			<div id="reports"></div>
			<div id="regimens"></div>
			<div id="pre-procs"></div>
		</div>
	</div>
	<script>
		$("#tab-container").easytabs({
			animate: false,
			tabsClass: "nav nav-tabs",
			tabClass: "tabClass",
			updateHash: false,
			cache: false
		});
	</script>
<?php } else {?>
<div class="alert-box error">Invalid request</div>
<?php }?>
