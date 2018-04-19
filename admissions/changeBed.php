<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/23/15
 * Time: 4:52 PM
 */


$_GET['suppress'] = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bed.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AdmissionConfiguration.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Ward.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BedDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AdmissionConfigurationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BedDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';

if (!isset($_SESSION)) {
	@session_start();
}
$adm = (new InPatientDAO())->getInPatient($_REQUEST['aid'], true);

//$beds = (new BedDAO())->getFreeBeds(TRUE);
if (isset($_POST['assignBed'])) {
	if (isset($_POST['bed_id']) && trim($_POST['bed_id']) !== "") {
		$pat = (new PatientDemographDAO())->getPatient($adm->getPatient()->getId(), false, null, null);
		$scheme_id = $pat->getScheme()->getId();
		$pdo = (new MyDBConnector())->getPDO();
		$pdo->beginTransaction();
		
		$ipInstance = (new InPatientDAO())->getInPatient($_POST['aid'], true, $pdo);
		
		if ($ipInstance->getWard()) {
			if ($ipInstance->getWard()->getId() != $_POST['ward_id']) {
				$changeWard = (new InPatientDAO())->changeWard($ipInstance, new Ward($_POST['ward_id']), $pdo);
				$wardItem = (new WardDAO())->getWard($_POST['ward_id'], false, $pdo);
				$wardCharge = new Bill();
				$wardCharge->setPatient($pat);
				$wardCharge->setDescription("Ward Fee: " . $wardItem->getName());
				
				$wardCharge->setItem($wardItem);
				$wardCharge->setSource((new BillSourceDAO())->findSourceById(17, $pdo));
				
				$wardPrice = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($wardItem->getCode(), $scheme_id, true, false, $pdo)->selling_price;
				$wardCharge->setAmount($wardPrice);
				$wardCharge->setClinic(new Clinic(1));
				$wardCharge->setBilledTo($pat->getScheme());
				$wardCharge->setCostCentre($adm->getWard() ? $adm->getWard()->getCostCentre() : null);
				$bill2_ = (new BillDAO())->addBill($wardCharge, 1, $pdo, $adm->getId());
			}
		}
		//$changeWard = (new InPatientDAO())->changeWard($ipInstance, new Ward($_POST['ward_id']), $pdo);
		
		$occupiedBedId = (new InPatientDAO())->getInPatient($_POST['aid'], true, $pdo)->getBed()->getId();
		$assignBed = (new InPatientDAO())->assignBed($_POST['aid'], $_POST['bed_id'], $pdo);
		$freeTheBed = (new BedDAO())->unAssignBed($occupiedBedId, $pdo);
		
		if ($occupiedBedId != null && $assignBed == true && $freeTheBed == true) {
			$emptyCharge = new Bill();
			$emptyCharge->setPatient($pat);
			$emptyCharge->setDescription("Bed change");
			
			$bedType = (new BedDAO())->getBed($_POST['bed_id'], true, $pdo)->getRoom()->getRoomType();
			$emptyCharge->setItem($bedType);
			$emptyCharge->setSource((new BillSourceDAO())->findSourceById(5, $pdo));
			$emptyCharge->setCostCentre($ipInstance->getWard() ? $ipInstance->getWard()->getCostCentre() : null);
			
			// the price of a bed is in the type
			// $roomTypePrice = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($bedType->getCode(), $scheme_id, TRUE, FALSE, $pdo)->getSellingPrice();
			$emptyCharge->setAmount(0);
			$emptyCharge->setClinic(new Clinic(1));
			$emptyCharge->setBilledTo($pat->getScheme());
			$bill2_ = (new BillDAO())->addBill($emptyCharge, 1, $pdo, $adm->getId());
			
			$pdo->commit();
			exit("success:Bed changed successfully");
		}
		$pdo->rollBack();
		exit("error:Failed to Transfer bed");
	} else {
		exit("error:Please select the bed to change to");
	}
}

?>
<div style="width:600px">
	<div class="line" style="z-index: 1000;">
		<div class="pull-left">
			<a href="javascript:void(0);"><img class="passport" src="<?= $adm->getPatient()->getPassportPath() ?>" width="53"/></a>
		</div>
		<div>

			<h4 class="uppercase"><?= $adm->getPatient()->getFullname() ?></h4>
			<span class="fadedText" id="pid_"><i class="icon icon-user"></i><a href="/patient_profile.php?id=<?= $adm->getPatient()->getId() ?>"> <?= $adm->getPatient()->getId() ?> </a></span>
			<div class="pull-right"><span class="fadedText">Admitted: <time class="fadedText" id="since" datetime="<?= $adm->getDateAdmitted() ?>" title="<?= $adm->getDateAdmitted() ?>"><?= $adm->getDateAdmitted() ?></time></span></div>
		</div>
		<div></div>
	</div>
	<br>
	<div id="bed" style="display:none"></div>
	<form id="assignBedForm" method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
		<label>Ward <select name="ward_id" required>
				<option value="">-- Select Ward --</option>
				<?php foreach ((new WardDAO())->getWards() as $ward) { ?>
					<option value="<?= $ward->getId() ?>" <?= ($adm->getWard() != null && $adm->getWard()->getId() === $ward->getId()) ? 'selected="selected"' : '' ?>><?= $ward->getName() ?></option>
				<?php } ?>
			</select></label>
		<label>Bed Label/Name
			<input type="hidden" name="bed_id" placeholder="--- select Bed ---">
		</label>

		<div class="btn-block">
			<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
			<button class="btn" type="submit" name="assignBed" value="true">Change Bed</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>

		</div>
		<div id="mgniu_"></div>
	</form>
</div>


<script type="text/javascript">
	$("document").ready(function () {
		$("#since").text(moment($("#since").text()).fromNow());
		setTimeout(function () {
			$('select[name="ward_id"]').trigger("change");
		}, 10);
	});
	function start_() {
		$('#mgniu_').html('<img src="/img/loading.gif">');
	}
	function done_(s) {
		var status_ = s.split(":");
		if (status_[0] == 'success') {
			location.reload();
			Boxy.get($(".close")).hideAndUnload();
		} else {
			$('#mgniu_').html('<span class="warning-bar">' + status_[1] + '</span>');
		}
	}
	var beds = [];

	$('input[name="bed_id"]').select2({
		data: function () {
			return {results: beds, text: 'name'};
		},
		width: '100%',
		placeholder: '--- Select Bed ---',
		formatResult: function (source) {
			return source.name + ' (' + source.room.name + '/' + source.room.ward.name + ')';
		},
		formatSelection: function (source) {
			return source.name + ' (' + source.room.name + '/' + source.room.ward.name + ')';
		}
	});

	$('select[name="ward_id"]').select2().change(function (evt) {
		if (typeof evt.added != "undefined" || $('select[name="ward_id"]').select2("val")) {
			$.post('/api/get_beds.php', {ward_id: $('select[name="ward_id"]').select2("val")}, function (data) {
				beds = data;
				$('input[name="bed_id"]').select2('destroy');
				$('input[name="bed_id"]').select2({
					data: function () {
						return {results: beds, text: 'name'};
					},
					width: '100%',
					placeholder: '--- Select Bed ---',
					formatResult: function (source) {
						return source.name + ' (' + source.room.name + '/' + source.room.ward.name + ')';
					},
					formatSelection: function (source) {
						return source.name + ' (' + source.room.name + '/' + source.room.ward.name + ')';
					}
				});

			}, 'json');
		}
	});
</script>
