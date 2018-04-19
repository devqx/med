<?php
/**
 * Created by PhpStorm.
 * User: emnity3
 * Date: 2/2/15
 * Time: 1:54 PM
 */

@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$bills = new Bills();
$staff_id = $_SESSION ['staffID'];
$access = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($staff_id, false);

$pScans = (new PatientScanDAO())->getScan($_GET['id']);
$approvedBy = ($pScans->getApprovedBy() == null) ? '' : $pScans->getApprovedBy()->getId();
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pScans->getPatient()->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pScans->getPatient()->getId()) + $bills->_getPatientCreditTotals($pScans->getPatient()->getId());
$selfOwe = $_ > 0 ? $_ : 0;
?>
<div style="width: 850px">
	<table class="table table-borderless table-condensed">
		<tbody>
		<tr>
			<td rowspan="3" style="width:85px">
				<img style="height:70px" src="<?= $pScans->getPatient()->getPassportPath() ?>"></td>
			<td class="fadedText "><span>ID:</span></td>
			<td>
				<a href="/patient_profile.php?id=<?= $pScans->getPatient()->getId() ?>" target="_blank"><?= $pScans->getPatient()->getId() ?></a>
			</td>
			<td class="fadedText ">
				<span>Date of Birth:</span></td>
			<td><?= date("jS M, Y", strtotime($pScans->getPatient()->getDateOfBirth())) ?>
			</td>
		</tr>
		<tr>
			<td class="fadedText "><span>Name:</span></td>
			<td> <?= $pScans->getPatient()->getFullName() ?></td>
			<td class="fadedText ">
				<span>Insurance:</span></td>
			<td><?= (new PatientDemographDAO())->getPatient($pScans->getPatient()->getId(), false)->getScheme()->getName() ?>
			</td>
		</tr>
		<tr>
			<td class="fadedText "><span>Sex:</span></td>
			<td><?= ucwords($pScans->getPatient()->getSex()) ?></td>
		</tr>
		<tr>
			<td colspan="6">
				<div class="alert-box <?= ($selfOwe <= 0) ? "notice" : "warning" ?>"><em class="fadedText">Outstanding Balance</em> &#8358;<em><?= number_format($selfOwe, 2); ?></em>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
	<table class="table table-striped">
		<tr>
			<td colspan="4"><strong>Investigations requested</strong></td>
		</tr>
		<tr>
			<td colspan="4">
				<ul class="list-blocks">
					<li><?= $pScans->getScan()->getName() ?></li>
				</ul>
			</td>
		</tr>
		<tr>
			<td colspan="4"><strong>Request Note/Reason</strong></td>
		</tr>
		<tr>
			<td colspan="4"><?= ($pScans->getRequestNote() != null) ? $pScans->getRequestNote() : "N/A" ?></td>
		</tr>
		<tr>
			<td colspan="4">
				<strong>Attachments</strong>
				<?php if (!$pScans->getApproved() && !$pScans->getCancelled()) { ?><span class="">
					<i class="icon-paper-clip"></i>
                <a href="javascript:;" data-title="New Attachment: <?= $pScans->getRequestCode() ?> (<?= escape($pScans->getScan()->getName()) ?>)" class="_newDialog_" data-id="<?= $pScans->getId() ?>" data-reload="true" data-href="/imaging/add_attachment.php?scan_id=<?= $pScans->getId() ?>">Add</a>
					</span><?php } ?>
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
				<td class="nowrap"><?= date("d M, Y h:iA", strtotime($at->getDateAdded())) ?></td>
				<td colspan="2"><a href="<?= $at->getAttachmentURL() ?>" target="_blank">Download</a> <?= $at->getNote() ?>
					<i class="icon-download-alt"></i></td>
				<td class="fadedText nowrap"><?= $at->getCreator()->getFullname() ?></td></tr><?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="4">
					<div class="alert-box notice">No attachments available
					</div>
				</td>
			</tr> <?php } ?>
		<tr>
			<td colspan="4"><strong>Notes</strong> <?php if ((!$pScans->getApproved() && !$pScans->getCancelled()) || $approvedBy == $staff_id) { ?>
					<span class=""><i class="icon-comments-alt"></i>
                <a data-title="New Note: <?= $pScans->getRequestCode() ?> (<?= escape($pScans->getScan()->getName()) ?>)" href="javascript:;" class="_newDialog_" data-reload="true"
                   data-href="/imaging/add_note.php?scan_id=<?= $pScans->getId() ?>" data-id="<?= $pScans->getId() ?>">Add</a>
					</span><?php } ?>
				<?php if (!$pScans->getApproved() && $pScans->getStatus() && !$pScans->getCancelled() && count($pScans->getNotes()['reports']) > 0 && $this_user->hasRole($access->radiologyApproval)) { ?>| <a href="javascript:" class="approve-link-in" data-id="<?= $pScans->getId() ?>">
						Approve</a><?php } ?>
				<?php if ($pScans->getApproved()) { ?>
					<div class="alert-box success">Report is Approved
						<span class="pull-right"><i class="icon-print"></i> <a href="/imaging/printNotes.php?id=<?= $pScans->getId() ?>" target="_blank">Print</a></span>
					</div>
				<?php } ?>
			</td>
		</tr>
		<?php if (count($pScans->getNotes()['reports']) > 0) { ?>
			<tr class="fadedText">
				<td class="nowrap"><strong>Date</strong></td>

				<td colspan="3"><strong>Note</strong></td>
			</tr>
			<?php foreach ($pScans->getNotes()['reports'] as $note) { ?>
				<tr>
				<td class="nowrap"><?= date("d M, Y h:iA", strtotime($note->getDateAdded())) ?></td>

				<td colspan="3">
					<div><?php if (!$pScans->getCancelled()) {
							if (!$pScans->getApproved() || ($approvedBy == $staff_id)) { ?>
								<a data-title="Edit Note: <?= $pScans->getRequestCode() ?> (<?= escape($pScans->getScan()->getName()) ?>)" href="javascript:;" class="_editDialog_" data-reload="true" data-href="/imaging/boxy.edit_note.php?scan_id=<?= $pScans->getId() ?>&id=<?= $note->getId() ?>" data-id="<?= $pScans->getId() ?>">Edit</a>
							<?php }
						} ?>
					</div>
					<div><?= $note->getNote() ?></div>
				</td>
				</tr><?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="4">
					<div class="alert-box notice"> No notes available</div>
				</td>
			</tr><?php } ?>
		<tr>
			<td colspan="4"><strong>Comments</strong> <?php if ((!$pScans->getApproved() && !$pScans->getCancelled()) || $approvedBy == $staff_id) { ?>
					<span class=""><i class="icon-comments-alt"></i>
                <a data-title="New Comment: <?= $pScans->getRequestCode() ?> (<?= escape($pScans->getScan()->getName()) ?>)" href="javascript:;" class="_newDialog_" data-reload="true"
                   data-href="/imaging/add_note.php?comment=true&scan_id=<?= $pScans->getId() ?>" data-id="<?= $pScans->getId() ?>">Add</a>
					</span><?php } ?>
			</td>
		</tr>
		<?php if (count($pScans->getNotes()['comments']) > 0) { ?>
			<tr class="fadedText">
				<td class="nowrap"><strong>Date</strong></td>

				<td colspan="3"><strong>Comment</strong></td>
			</tr>
			<?php foreach ($pScans->getNotes()['comments'] as $note) { ?>
				<tr>
				<td class="nowrap"><?= date("d M, Y h:iA", strtotime($note->getDateAdded())) ?></td>

				<td colspan="3">
					<div></div>
					<div><?= $note->getNote() ?></div>
				</td>
				</tr><?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="4">
					<div class="alert-box notice"> No comments available</div>
				</td>
			</tr><?php } ?>
	</table>
</div>

<script type="text/javascript">
	$('a.approve-link-in').live('click', function (e) {
		var $id = $(this).data("id");
		if (!e.handled) {
			Boxy.ask('Are you sure to approve the notes?', ['Yes', 'No'], function (answer) {
				if (answer === 'Yes') {
					$.ajax({
						url: "/imaging/ajax.approve.php",
						data: {id: $id},
						type: "post",
						beforeSend: function () {
						},
						success: function (s) {
							try {
								showTabs(11);
							} catch (e) {
							}
							Boxy.get($('.close')).hideAndUnload(function () {
								Boxy.load('/imaging/scan.details.php?id=' + $id, {
									afterShow: function () {
										Boxy.ask('Print this report now?', ['Yes', 'No'], function (answer) {
											if (answer === 'Yes') {
												var link = document.createElement('a');
												link.href = "/imaging/printNotes.php?id=" + $id;
												link.target = '_blank';
												document.body.appendChild(link);
												link.click();
											}
										});
									}
								});
							});
						}, error: function () {
							Boxy.alert("Approval failed");
						}
					});
				}
			});
			e.handled = true;
		}
	});
</script>