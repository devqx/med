<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/21/16
 * Time: 12:34 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimLinesDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Claim.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ClaimLines.php';


$claim = (new ClaimDAO())->get($_GET['id']);
$patientId = $claim->getPatient()->getId();

$encounters = (new EncounterDAO())->forPatient($patientId, false, 0, 5000)->data;
$ip_instances = (new InPatientDAO())->getInPatientsForClaim($patientId, false);

$originalLines = [];
$claimLine = (new ClaimLinesDAO())->getLines($_GET['id']);

foreach ($claimLine as $item) {
	$originalLines[] = $item->getBillLine();
}
unset($item);
if ($_POST) {
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	
	$lines = !is_blank(@$_POST['bills']) ? array_filter(@$_POST['bills']) : [];
	$originalLines = !is_blank(@$_POST['original_lines']) ? array_filter(explode(',', $_POST['original_lines'])) : [];
	
	//get the line ids that were removed from the claim lines
	$removed = array_values(array_diff($originalLines, $lines));
	//get the line ids that were added to the claim lines
	$added = array_values(array_diff($lines, $originalLines));
	//$claims = (new Claim());
	foreach ($removed as $item) {
		$b = (new BillDAO())->getBill($item, true, $pdo);
		if ($b) {
			$b->setClaimed(false)->update($pdo);
			if((new Claim($_GET['id']))->setTotalCharge($claim->getTotalCharge() - $b->getAmount())->setBalance($claim->getBalance() - $b->getAmount())->setStatus('rejected')->updateCharge($pdo) == null ){ // status to be verified later
				$pdo->rollBack();
				exit("error:could not remove from the existing claims");
			}
		}
		unset($item);
		unset($b);
	}
	$newCharge = 0;
	foreach ($added as $item) {
		$b = (new BillDAO())->getBill($item, true, $pdo);
		if ($b) {
			$newCharge += $b->getAmount();
			$b->setClaimed(true)->update($pdo);
			if((new ClaimLines())->setBillLine($b)->setStatus('draft')->setAmount($b->getAmount())->setClaim($claim)->add($pdo)  == null){
				$pdo->rollBack();
				exit("Error: Could not add claim lines");
			}
		}
		
		unset($item);
		unset($b);
	}
	
	if($claim->setTotalCharge($claim->getTotalCharge() + floatval($newCharge) )->setBalance($claim->getBalance() + floatval($newCharge))->update($pdo) == null){
		$pdo->rollBack();
		exit("Error: Failed to update claim charges");
		
	}
	
	$newEncounter = $_POST['encounter_type'] == 'op' ? (new EncounterDAO())->get($_POST['encounter_id'], false, $pdo) : (!is_blank($_POST['encounter_type']) ? (new InPatientDAO())->getInPatient($_POST['encounter_id'], false, $pdo) : null);
	//mark the encounter as claimed and release the `changed` one whether inpatient or outpatient
	//correctly, the two different objects have similar `claimed` property and an `update` method
	if ($newEncounter) {
		$newEncounter->setClaimed(true)->update($pdo);
	}
	$c = (new ClaimDAO())->get($_POST['claim_id'], $pdo)->setEncounter($newEncounter)->setType($_POST['encounter_type'])->update($pdo);
	
	if ($_POST['original_encounter'] != $_POST['encounter_id']) {
		//encounter has changed so we need to `claim=false` the one that was swapped to
		//get the encounter that was there before
		//and the type too, so that we can apply the property on the `accurate` object {Encounter|InPatient}
		//and the do the property mod and update
		if (isset($_POST['original_encounter_type']) && $_POST['original_encounter_type'] == 'op') {
			(new EncounterDAO())->get($_POST['original_encounter'], false, $pdo)->setClaimed(false)->update($pdo);
		} else if (isset($_POST['original_encounter_type']) && $_POST['original_encounter_type'] == 'ip') {
			(new InPatientDAO())->getInPatient($_POST['original_encounter'], false, $pdo)->setClaimed(false)->update($pdo);
		}
	}
	if ($c !== null) {
		$pdo->commit();
		exit("success:Updated claim");
	}
	$pdo->rollBack();
	exit("error:Failed to update claim");
}
?>

<section style="width: 900px">
	<div class="row-fluid">
		<label class="span10"><input name="bill_lines" type="text" placeholder="Search for bill line and click add"></label>
		<a class="btn span2" id="addbills" disabled="disabled" href="javascript:">Add Bill</a>
	</div>
	<hr class="border">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: s, onComplete: d2})">
		<label>Select Encounter
			<select name="encounter_id" data-placeholder="Select Encounter">
				<option></option>
				<?php foreach ($encounters as $e) {//$e=new Encounter;?>
					<option value="<?= $e->id ?>" data-type="op"<?= $claim->getEncounter()->getId() == $e->id ? ' selected' : '' ?>><?= date(MainConfig::$dateTimeFormat, strtotime($e->start_date)) . ": " . ($e->specialization_ ? $e->specialization_->getName() : 'No Specialty') ?>
					[Out-Patient]</option><?php } ?>
				<?php foreach ($ip_instances as $e) {//$e=new InPatient flat object;?>
					<option value="<?= $e->id ?>" data-type="ip"<?= $claim->getEncounter()->getId() == $e->id ? ' selected' : '' ?>><?= date(MainConfig::$dateTimeFormat, strtotime($e->date_admitted)) ?>: <?= $e->reason ?>[In-Patient]</option><?php } ?>
			</select>
		</label>
		
		<label class="span6 hide">
			 Action
			<select name="action_performed" data-placeholder="select action">
				<option></option>
			</select>
		</label>

		<table id="claim_lines" class="table table-striped">
			<thead>
			<tr>
				<th>*</th>
				<th>Bill Description</th>
				<th>Amount</th>
				<th>Transaction Date/Time</th>
				<th>*</th>
			</tr>
			</thead>
			<tbody>
			<?php if (count($claimLine) == 0) { ?>
				<tr>
					<td colspan="5">
						<div class="alert-box warning">No bill lines !</div>
					</td>
				</tr>
			<?php } else {
				foreach ($claimLine as $item) {
					$bill = (new BillDAO())->getBill($item->getBillLine(), true);
					if ($bill) {
						?>
						<tr class="claim_line" data-id="<?= $bill->getId() ? $bill->getId() : '' ?>">
							<td><input title="Select" type="checkbox" name="bills[]" value="<?= (int)@$bill->getId() ?>" checked="checked"></td>
							<td><?= $bill->getDescription() ?></td>
							<td class="amount"><?= $bill->getAmount() ?></td>
							<td><?= date(MainConfig::$dateTimeFormat, strtotime($bill->getTransactionDate())) ?></td>
							<td><a href="javascript:" class="remove_line" data-id="<?= @$bill->getId() ?>">Remove</a></td>
						</tr>
					<?php }
				}
				unset($item);
				unset($bill);
			}
			?>
			</tbody>
		</table>
		<input type="hidden" name="original_lines" value="<?= implode(',', $originalLines) ?>">
		<input type="hidden" name="original_encounter" value="<?= $claim->getEncounter()->getId() ?>">
		<input type="hidden" name="original_encounter_type" value="<?= $claim->getType() ?>">
		<input type="hidden" name="encounter_type" value="">
		<input type="hidden" name="claim_id" value="<?= $_GET['id'] ?>">
		<button type="submit" class="btn">Apply Changes</button>
		<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</form>

</section>
<script type="text/javascript">

	var existingLineIds = [];

	$(document).on('click', '.remove_line', function (e) {
		if (!e.handled) {
			Boxy.ask("Are sure you want dissociate this bill line from this claim?", ['Yes', 'No'], function (choice) {
				if (choice === 'Yes') {
					$('.claim_line[data-id="' + $(e.target).data("id") + '"]').remove();
				}
			});
			e.handled = true;
		}
	}).on('change', '[name="encounter_id"]', function (e) {
		if (!e.handled) {
			$('[name="encounter_type"]').val($(e.currentTarget).find('option:selected').data('type'));
			e.handled = true;
		}
	}).ready(function () {
		$('.claim_line > td > :checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});
		$('[name="encounter_id"]').trigger('change');
	});

	function s() {
		$(document).trigger('ajaxSend');
	}

	function d2(s) {
		$(document).trigger('ajaxStop');
		var data = s.split(":");
		if (data[0] === "success") {
			Boxy.info(data[1]);
			Boxy.get($(".close")).hideAndUnload();
		} else {
			Boxy.warn(data[1]);
		}
	}

	function addLine(added) {
		_.each($('tr.claim_line'), function (obj) {
			existingLineIds.push(parseInt($(obj).data('id')));
		});
		if (_.includes(existingLineIds, parseInt(added.bill_id))) {
			Boxy.alert('Bill Line is already in the list');
			return;
		}
		var str = '<tr class="claim_line" data-id="' + parseInt(added.bill_id) + '"><td><input type="checkbox" name="bills[]" value="' + parseInt(added.bill_id) + '" checked="checked"></td><td>' + added.description + '</td><td class="amount">' + added.amount + '</td><td>' + moment(added.transaction_date).format('DD/MM/YYYY h:mmA') + '</td><td><a href="javascript:" class="remove_line" data-id="' + added.bill_id + '">Remove</a></td></tr>';
		if ($('tr.claim_line').length > 0) {
			$('tr.claim_line:last-child').after(str);
		} else {
			$('#claim_lines > tbody').html(str);
		}

		$("#lineinput").select2('val', '');
		$('.claim_line:last-child > td > :checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});

		$('.claim_line:last-child').find('.amount').number(true, 2);
		$('.boxy-content [name="bill_lines"]').select2('val', '').trigger('change');
	}
	$('.boxy-content [name="bill_lines"]').select2({
		placeholder: $(this).attr('placeholder'),
		minimumInputLength: 3,
		width: '100%',
		allowClear: true,
		ajax: {
			url: "/api/find_unclaimed_bills.php",
			dataType: 'json',
			data: function (term, page) {
				return {
					pid: '<?= $patientId?>',
					search: term
				};
			},
			results: function (data, page) {
				console.log(data);
				return {results: data};
			}
		},
		formatResult: function (data) {
			return data.description + '[' + data.transaction_type + '] ' + '(' + moment(data.transaction_date).format('DD/MM/YYYY h:mmA') + ')' + '(' + data.amount + ')';
		},
		formatSelection: function (data) {
			return data.description + '[' + data.transaction_type + '] ' + '(' + moment(data.transaction_date).format('DD/MM/YYYY h:mmA') + ')' + '(' + data.amount + ')';
		},
		id: function (data) {
			return data.bill_id;
		}
	}).change(function (evt) {
		if ($(this).val()) {
			$("#addbills").removeAttr('disabled');
		} else {
			$("#addbills").attr('disabled', 'disabled');
		}
	});

	$("#addbills").click(function () {
		addLine($('.boxy-content [name="bill_lines"]').select2('data'));
	});
</script>
