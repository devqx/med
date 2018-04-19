<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/11/14
 * Time: 12:43 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';

$p = (new PatientProcedureDAO())->get($_GET['id']);

?>
<?php if (in_array($p->getStatus(), ["open", "started"]) && (@$_GET['add'] != "false")) { ?>
	<div class="menu-head">
		<a href="javascript:;" onclick="addRegimen(<?= $_GET['id'] ?>)">Add a Prescription</a>
	</div>
<?php } ?>

<p></p>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Date</th>
		<th>Generic</th>
		<th>Quantity</th>
		<th>By</th>
		<th>Status</th>
		<th>*</th>
	</tr>
	</thead>
	<?php foreach ($p->getRegimens() as $regimens) { ?>
		<tr>
			<td title=""><?= date("d M, Y h:i A", strtotime($regimens->getRequestTime())) ?></td>
			<td><strong><?= $regimens->getDrugGeneric()->getName() ?></strong></td>
			<td><?= $regimens->getQuantity() ?>&nbsp;(<?= $regimens->getDrugGeneric()->getWeight() ?>)</td>
			<td><em><?= $regimens->getRequestingUser()->getFullname() ?></td>
			<td>
				<?= $regimens->getStatus() ? $regimens->getStatus() : '..' ?>
			</td>
			<td>
       <?php if($regimens->getStatus() && $regimens->getBillLine() && $regimens->getStatus() !== 'cancelled' && !in_array($p->getStatus(), ['cancelled'])) { ?>
				<div class="dropdown pull-right">
					<button class="drop-btn large dropdown-toggle" data-toggle="dropdown" style="padding:10px">
						Action
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
						<?php if ($regimens->getStatus() !== 'cancelled' && !in_array($p->getStatus(), ['cancelled']) && $regimens->getBillLine()) { ?>
							<li><a href="javascript:;" onclick="cancelMedicationLine(<?= $regimens->getBillLine() ?>,<?= $regimens->getId() ?>)">Cancel</a></li>
						<?php } ?>
					</ul>
				</div>
       <?php } ?>
			</td>
		</tr>
	<?php } ?>
</table>


<script type="text/javascript">
	function addRegimen(key) {
		Boxy.load('/procedures/dialogs/new-regimen.php?id=' + key, {
			afterHide: function () {
				// refresh this tab?
				$("#tab-container").easytabs('select', '#regimens');
			}
		});
	}

	function processInvt(id, gid, ppr_id, dose) {
		Boxy.load('/procedures/dialogs/process-inventory-bill.php?id=' + id + '&gid=' + gid + '&ppr_id=' + ppr_id + '&dose=' + dose);

	}

	function cancelMedicationLine(bid, pregid) {
		Boxy.ask('Are you sure to cancel this bill line?', ['Yes', 'No'], function(answer){
			if(answer==='Yes'){
				$.post('/api/cancel_procedure_medication.php', {id: bid, ppid:pregid, location_:'procedure'}, function (response) {
					if(response==='success') {
						// refresh this tab?
						$("#tab-container").easytabs('select', '#regimens');
					}else if(response==='error1'){
						Boxy.alert('Bill is already cancelled');
					} else {
						Boxy.alert('An error occurred/bill has been reversed before');
					}

				});
			}
		});
	}


</script>