<?php
$_GET['suppress'] = 0;
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/state_lga.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';

$states = (new StateDAO())->getStates(true);
$clinic = (new ClinicDAO())->getClinic(1, true);
if ($_POST) {
	if ($_POST['clinic'] == '') {
		exit("error:Enter clinic name");
	}
	if ($_POST['state'] == '0') {
		exit("error:Select state");
	}
	if ($_POST['lga'] == '0') {
		exit("error:Select local government area");
	}
	if ($_POST['clinic_address'] == '') {
		exit("error:Please enter your address");
	}
	if ($_POST['class'] == '') {
		exit("error:Select class");
	}
	
	$clinic_ = new Clinic();
	$clinic_->setCode($_POST['code']);
	$clinic_->setFolioPrefix($_POST['folio_prefix']);
	$clinic_->setName($_POST['clinic']);
	$clinic_->setAddress(escape($_POST['clinic_address']));
	$clinic_->setKlass($_POST['class']);
	$clinic_->setLga($_POST['lga']);
	$clinic_->setPhoneNo($_POST['phone_number']);
	$clinic_->setLocationLat($_POST['lat']);
	$clinic_->setLocationLong($_POST['long']);
	$clinic_->setId(1);
	$save = (new ClinicDAO())->updateClinic($clinic_);
	if ($save != null) {
		exit("success:saved");
	}
	exit("error:Save failed");
}
?>
<script type="text/javascript">
	var states =<?php echo json_encode($states, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	$(document).ready(function () {
		$("#state").change(function () {
			var html = "<option value='0'>-- select LGA --</option>", lgas, lga;
			if ($("#state").val() != 0) {
				lgas = states[$("#state").val() - 1].lgas;
				for (var i = 0; i < lgas.length; i++) {
					html = html + "<option value='" + lgas[i].id + "'" + ((lga == lgas[i].id) ? " selected" : "") + ">" + lgas[i].name + "</option>";
				}
			} else {
				html = '<option value="0">-- select state of origin --</option>';
			}
			$("#lga").html(html);
			$("#lga").select2("val", "0");
		});
		$("#creator select").select2({
			width: '100%'
		});
		$("#klass").select2("val", "<?= $clinic->getKlass() ?>");
	});
</script>
<form action="/pages/pm/editHospital.php" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
	<label for="clinic">Hospital/Clinic name
		<input name="clinic" type="text" id="clinic" value="<?= $clinic->getName() ?>"/></label>
	<div class="row-fluid">
		<label class="span6">Code
			<input name="code" type="text" value="<?= $clinic->getCode() ?>"/></label>
		<label class="span6">Folio Number Prefix<span class="pull-right fadedText"><a href="javascript:" title="(Parsed as PHP date*)">info:</a> Used in generation of patient folio number</span>
			<input name="folio_prefix" type="text"  value="<?= $clinic->getFolioPrefix() ?>"/></label>
	</div>
	
	<div class="row-fluid">
		<label class="sub span6">State<select name="state" id="state">
				<option value="0">-- select state of origin --</option>
				<?php
				foreach ($states as $st) {
					$selected = (isset($_POST['state']) && ($_POST['state'] == $st->getId()) || ($clinic->getLga() != null) && ($st->getId() == $clinic->getLga()->getState()->getId())) ? " selected" : "";
					echo("<option value='" . $st->getId() . "'" . $selected . ">" . $st->getName() . "</option>");
				}
				?></select></label>

		<label class="sub span6">Local Government Area of Origin<select name="lga" id="lga">
				<option value="0">-- select LGA --</option>
				<?php
				if (isset($_POST['lga'])) {
					$lgas = getLgas($states, $_POST['lga']);
				} elseif ($clinic->getLga() != null) {
					$lgas = getLgas($states, $clinic->getLga()->getId());
				}
				if (isset($lgas)) {
					foreach ($lgas as $lg) {
						$selected = (isset($_POST['lga']) && ($_POST['lga'] == $lg->getId()) || ($lg->getId() == $clinic->getLga()->getId())) ? " selected" : "";
						echo("<option value='" . $lg->getId() . "'" . $selected . ">" . $lg->getName() . "</option>");
					}
				}
				?></select></label>
	</div>
	
	<label>Address
		<input name="clinic_address" id="clinic_address" type="text" value="<?= $clinic->getAddress() ?>"/></label>
	<div class="row-fluid">
		<label class="span6">Phone number
			<input name="phone_number" id="phone_number" type="text" value="<?= $clinic->getPhoneNo() ?>" placeholder="Phone number"/></label>
		<label class="span3">Location: Longitude
			<input name="long" id="long" type="text" value="<?= $clinic->getLocationLong() ?>"/></label>
		<label class="span3">Location: Latitude
			<input name="lat" id="long" type="text" value="<?= $clinic->getLocationLat() ?>"/></label>
	</div>

	<label>Class
		<select id="klass" name="class">
			<option value="">Select Class</option>
			<option value="Hosp">Hospital</option>
			<option value="PHC">Primary Health Care Center</option>
		</select></label>
	<div align="left">
		<button class="btn" name="editclinic" type="submit">Save</button>
		<button class="btn-link" type="button" onclick="$('#creator').attr('data', 'none').html('');$('span.error').html('');">
			Cancel
		</button>
	</div>
</form>
<script>
	function start() {
		$(".boxy-content").animate({scrollTop: 0}, "slow");
		$('span.error').html('<img src="/img/loading.gif"> please wait');
	}
	function done(s) {
		console.log(s);
		$('span.error').html('');
		if (s.indexOf("success") != -1) {
			Boxy.info("Hospital information saved");
			$('#creator').attr('data', 'none').html('');
		} else {
			var s1 = s.split(":");
			if (s1[0] == "error") {
				$('span.error').html('<img src="/img/warning.png" /><span style="color:#C00">' + s1[1] + '</span>');
			}
		}
	}
</script>