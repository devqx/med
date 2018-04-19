	<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/14/15
 * Time: 12:42 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/StaffDirectoryDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PrescriptionDataDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$protect = new Protect();
$pps = (new PrescriptionDataDAO())->getPrescriptionDataByCode($_GET['id'], TRUE);

?>
<div class="display" style="width: 960px;">
	<h3>Prescription ID: <?= $_GET['id']; ?></h3>
	<h5 class="fadedText">Notes: <?= urldecode($_GET['note']) ?></h5>
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th>*</th>
			<th>Drug/Generic</th>
			<th>Dose/Quantity</th>
			<th>Refillable?</th>
			<th>Comment</th>
			<th>Status</th>
			<th nowrap>Body part</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($pps as $j => $d) { ?>
			<tr>
				<td >
					<div class="dropdown">
						<button class="drop-btn dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
						<ul class="img dropdown-menu" role="menu" aria-labelledby="dLabel">
							<?php if(!in_array($d->getStatus(), ["cancelled", "open"])) { ?>
								<li><a href="/pharmaceuticals/print_prescription2.php?grouped=false&pcode=<?= $d->getCode() ?>&single=<?= $d->getId() ?>" target="_blank" class="btn btn-small1">Print</a></li>
							<?php } ?>
							
							<?php if (!in_array($d->getStatus(), ["cancelled", "history"])  && ($this_user->hasRole($protect->pharmacy_super) || $this_user->hasRole($protect->doctor_role))) { ?>
								<li>
									<a
										class="_p_action btn btn-mini" href="javascript:void(0)" data-action="cancel" data-id="<?= $d->getId() ?>"
										title="Cancel this prescription">Cancel</a>
								</li>
							<?php } ?>
						</ul>
					</div>
				</td>
				<td>
					<?= (($d->getDrug() === null) ? $d->getGeneric()->getName().' ('.$d->getGeneric()->getWeight().' ' .$d->getGeneric()->getForm() .')' : $d->getGeneric()->getName().' ('.$d->getGeneric()->getWeight(). ' ' .$d->getGeneric()->getForm() .')' . " [" . $d->getDrug()->getName() . "]") ?>
				</td>
				
				<td><?= $d->getDose() ?> <?= $d->getGeneric()->getForm() ?><?= ($d->getDose() != 1) ? 's' : '' ?>
					[<?= $d->getFrequency() ?>] for <?= $d->getDuration() ?>
					day<?= (((int)$d->getDuration()) > 1 ? "s" : "") ?>
				</td>
				<td>
					<?= $d->isRefillable() ? "Yes" : "No" ?>
				</td>
				<td class="fadedText">
					<?= $d->getComment() ?>
				</td>
				<td style="padding-right: 5px"><?= ucwords( $d->getStatus()) ?></td>
				
				<td class="fadedText"><?= $d->getBodypart() ? $d->getBodypart()->getName() : '- - ' ?></td> <!-- REMOVE FOR GARKI -->
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('a._p_action').click(function (e) {
			var action = $(this).data("action");
			var item_id = $(this).data("id");
			if (action === "cancel") {
				cancelPrescription(item_id);
			}
			e.preventDefault();
		});
	});

	function cancelPrescription(id) {
		if (confirm("Are you sure you want to cancel this prescription?")) {
			vex.dialog.prompt({
				message: 'Please enter your reason for cancellation',
				placeholder: 'Regimen Cancellation note',
				value: null,
				overlayClosesOnClick: false,
				beforeClose: function (e) {
					e.preventDefault();
				},
				callback: function (value) {
					if (value !== false && value !== '') {
						$.ajax({
							url: '/api/regimens.php',
							data: {action:'cancel',id:id,reason:value},
							type: 'POST',
							complete: function (xhr, status) {
								if (status === "success" && xhr.responseText === "true") {
									$('[data-id="' + id + '"]').prev().html("cancelled");
									$('[data-id="' + id + '"]').parents('tr').find('td input').prop('disabled', true);
									$('[data-id="' + id + '"]').parents('tr').find('td select').prop('disabled', true);
									$('[data-id="' + id + '"]').remove();
								}
							}
						});

					} else {

					}
				}, afterOpen: function ($vexContent) {
					$('.vex-dialog-prompt-input').attr('autocomplete', 'off');
					$submit = $($vexContent).find('[type="submit"]');
					$submit.attr('disabled', true);
					$vexContent.find('input').on('input', function () {
						if ($(this).val()) {
							$submit.removeAttr('disabled');
						} else {
							$submit.attr('disabled', true);
						}
					});
				}
			});
		}
	}

	$('a.printReceipt').live('click', function(e){
		Boxy.load($(this).data('href'), {title:'Print Filled Prescription Packing Slip'});
	});
	
</script>