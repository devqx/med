<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$staffTypes = (new StaffSpecializationDAO())->getSpecializations();

if ($_POST) {
	$update = array();
	foreach ($_POST['code'] as $index => $id) {
		$spe = (new StaffSpecializationDAO())->get($index);
		$spe->setName($_POST['name'][$index]);
		$spe->setInpatient(isset($_POST['inpatient'][$index])?TRUE:FALSE);
		$spe->setOutpatient(isset($_POST['outpatient'][$index])?TRUE:FALSE);
		$update[] = (new StaffSpecializationDAO())->updateSpecialization($spe, parseNumber($_POST['price'][$index]), parseNumber($_POST['follow_up_price'][$index]));
	}
	
	exit(json_encode($update));
}
?>
<div id="">
	<h4>Consultancy Charges</h4>
	<span class="error2 loader" style="display:block;"></span>

	<form action="<?= $_SERVER['PHP_SELF']; ?>" id="p09" method="post"
	      onSubmit="return AIM.submit(this, {'onStart' : startSaving, 'onComplete' : endSaving})">
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Specialization</th>
				<th style="width: 200px">Consultancy Price</th>
				<th style="width: 200px">FollowUp Consultancy Price</th>
				<th><label title="Available in OutPatient charges"><input type="checkbox" onchange="toggleSelect('outpatient', this)"> OutPatient?</label></th>
				<th><label title="Available in InPatient charges"><input type="checkbox" onchange="toggleSelect('inpatient', this)"> InPatient?</label></th>
			</tr>
			</thead>
			<?php
			if (count($staffTypes) == 0) { ?>
				<tr>
					<td colspan="3">
						<div class="warning-bar">There are no configured staff types for
							consultancy. Please configure
							them at the <a
								href="javascript:void(0)" onclick="loadCfgOprType()">Configure
								Operator Types</a> tab
						</div>
					</td>
				</tr>
			
			<?php }
			foreach ($staffTypes as $s) {//$s=new StaffSpecialization() ?>
				<tr>
					<td>
						<input type="hidden" name="id[]" value="<?= $s->getId() ?>">
						<input type="hidden" name="code[<?= $s->getId() ?>]" value="<?= $s->getCode() ?>">
						<input title="Name" class="wide" type="text" name="name[<?= $s->getId() ?>]" value="<?= $s->getName() ?>">
					</td>
					<td><input title="Amount" class="wide amount" type="number" name="price[<?= $s->getId() ?>]" min="0" value="<?= (new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($s->getCode()) ?>"></td>
					<td><input title="Follow up price" class="wide amount" type="number" name="follow_up_price[<?= $s->getId() ?>]" min="0" value="<?= (new InsuranceItemsCostDAO())->getItemDefaultFollowUpPriceByCode($s->getCode()) ?>"></td>
					<td><input title="Available for outpatient charges" type="checkbox" name="outpatient[<?= $s->getId() ?>]" <?= $s->getOutpatient() ? 'checked':'' ?> > </td>
					<td><input title="Available for inpatient charges" type="checkbox" name="inpatient[<?= $s->getId() ?>]" <?= $s->getInpatient() ? 'checked':'' ?>> </td>
				</tr>
			<?php } ?>
		</table>

		<div>
			<button class="btn" name="updatebtn" type="submit">Update</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function startSaving() {
		$('.loader').html('<img src="/img/ajax-loader.gif"> please wait ... ');
	}
	function endSaving(s) {
		var ret = JSON.parse(s);
		if ($.inArray(false, ret) !== -1) {
			Boxy.info("Some data could not be changed");
		} else {
			$('.loader').html('Saved').addClass('alert alert-info');
			$(".boxy-content").animate({scrollTop: 0}, "slow");
		}
		setTimeout(function () {
			$('.loader').html('').removeClass('alert alert-info');
		}, 4000);
	}
	
	var toggleSelect = function (name, element) {
		
		if($(element).is(':checked')){
			_.each($(':checkbox[name^="'+name+'"]'),  function (obj) {
				$(obj).prop('checked', true).iCheck('update');
			} )
		} else {
			_.each($(':checkbox[name^="'+name+'"]'),  function (obj) {
				$(obj).prop('checked', false).iCheck('update');
			} )
		}
	}
</script>