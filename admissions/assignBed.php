<?php
$_GET['suppress'] = TRUE;
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
$adm = (new InPatientDAO())->getInPatient($_REQUEST['aid'], TRUE);
$ward = $adm->getWard() != null ? $adm->getWard()->getId() : null;
$beds = (new BedDAO())->getFreeBeds(TRUE, $ward);
if (isset($_POST['assignBed'])) {
	$pat = (new PatientDemographDAO())->getPatient($adm->getPatient()->getId(), FALSE, null, null);
	$scheme_id = $pat->getScheme()->getId();

	if (isset($_POST['bed']) && trim($_POST['bed']) !== "") {
		//apply the ward ?
		if(isset($_POST['ward_id']) && !is_blank($_POST['ward_id'])){
			$adm->setWard(new Ward($_POST['ward_id']))->updateWard();
		}

		$bedCharge = new Bill();
		$bedCharge->setPatient($pat);
		$bedCharge->setDescription("Bed charge");

		$bedType = (new BedDAO())->getBed($_POST['bed'], TRUE, null)->getRoom()->getRoomType();
		$bedCharge->setItem($bedType);
		$bedCharge->setSource((new BillSourceDAO())->findSourceById(5));
		$bedCharge->setCostCentre($adm->getWard() ? $adm->getWard()->getCostCentre() : null);

		// the price of a bed is in the type
		$roomTypePrice = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($bedType->getCode(), $scheme_id, TRUE, FALSE, null)->selling_price;
		$bedCharge->setAmount($roomTypePrice);
		$bedCharge->setClinic(new Clinic(1));
		$bedCharge->setBilledTo($pat->getScheme());

		$pdo = (new MyDBConnector())->getPDO();
		$pdo->beginTransaction();

		if ((new InPatientDAO())->assignBed($_POST['aid'], $_POST['bed'], $pdo)) {
			//bed charge
			$bill_ = (new BillDAO())->addBill($bedCharge, 1, $pdo, $adm->getId());

			//ward charge
			$wardItem = (new WardDAO())->getWard($adm->getWard()->getId(), FALSE, $pdo);
			$wardCharge = new Bill();
			$wardCharge->setPatient($pat);
			$wardCharge->setDescription("Ward Fee: " . $wardItem->getName());

			$wardCharge->setItem($wardItem);
			$wardCharge->setSource((new BillSourceDAO())->findSourceById(17, $pdo));

			$wardPrice = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($wardItem->getCode(), $scheme_id, TRUE, FALSE, $pdo) !== null ? (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($wardItem->getCode(), $scheme_id, TRUE, FALSE, $pdo)->selling_price : 0;
			$wardCharge->setAmount($wardPrice);
			$wardCharge->setClinic(new Clinic(1));
			$wardCharge->setBilledTo($pat->getScheme());
			$wardCharge->setCostCentre($adm->getWard() ? $adm->getWard()->getCostCentre() : null);
			$bill2_ = (new BillDAO())->addBill($wardCharge, 1, $pdo, $adm->getId());

			foreach ((new AdmissionConfigurationDAO())->getAdmissionConfigurations($pdo) as $other_charges) {
				//$other_charges = new AdmissionConfiguration();
				$otherBill = new Bill();
				$otherBill->setPatient($pat);
				$otherBill->setDescription($other_charges->getName());
				$otherBill->setItem($other_charges);
				$otherBill->setSource((new BillSourceDAO())->findSourceById(5, $pdo));
				$otherBill->setCostCentre($adm->getWard() ? $adm->getWard()->getCostCentre() : null);

				//the price of a `other_admission_fees`
				$price = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($other_charges->getCode(), $scheme_id, TRUE, FALSE, $pdo)->selling_price;
				$otherBill->setAmount($price);
				$otherBill->setClinic(new Clinic(1));
				$otherBill->setBilledTo($pat->getScheme());

				(new BillDAO())->addBill($otherBill, 1, $pdo, $adm->getId());
			}

			$pdo->commit();
			exit("success:Bed assigned successfully");
		} else {
			$pdo->rollBack();
		}
		exit("error:Sorry something went wrong");
	} else {
		exit("error:Please select the bed to assign");
	}
}
?>
<div style="width:600px">
    <div class="line" style="z-index: 1000;">
        <div class="pull-left">
            <a href="javascript:void(0);"><img class="passport" src="<?= $adm->getPatient()->getPassportPath() ?>" width="53" /></a>
        </div>
        <div>

            <h4 class="uppercase"><?= $adm->getPatient()->getFullname() ?></h4>
            <h5>Ward: <?= ($adm->getWard() != null ? $adm->getWard()->getName() : 'N/A') ?></h5>
            <span class="fadedText" id="pid_"><i class="icon icon-user"></i><a href="/patient_profile.php?id=<?= $adm->getPatient()->getId() ?>"> <?= $adm->getPatient()->getId() ?> </a></span>
            <div class="pull-right"><span class="fadedText">Admitted: <time class="fadedText" id="since" datetime="<?= $adm->getDateAdmitted() ?>" title="<?= $adm->getDateAdmitted() ?>"><?= $adm->getDateAdmitted() ?></time></span></div>
        </div>
        <div></div>
    </div>
    <br>
    <div id="bed" style="display:none"> </div>
    <form id="assignBedForm" method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
        <label>Ward <select name="ward_id" required>
                <option value="">-- Select Ward --</option>
                <?php foreach( (new WardDAO())->getWards() as $ward){?>
                    <option value="<?=$ward->getId()?>" <?=($adm->getWard() != NULL && $adm->getWard()->getId()===$ward->getId())? 'selected="selected"':''?>><?=$ward->getName()?></option>
                <?php }?>
            </select></label>
        <label>Bed Label/Name
	        <input type="hidden" name="bed" placeholder="--- select Bed ---">
        </label>

        <div class="btn-block">
            <button class="btn" type="submit" name="assignBed" value="true">Assign Bed</button>
            <button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
            <input type="hidden" name="aid" value="<?= $_GET['aid'] ?>" >
        </div>
        <div id="mgniu_"></div>
    </form>
</div>

<script type="text/javascript">
    $("document").ready(function () {
	    $("#since").text(moment($("#since").text()).fromNow());
	    setTimeout(function(){$('select[name="ward_id"]').trigger("change");},10);
    });
    function start_() {
        $('#mgniu_').html('<img src="/img/loading.gif">');
    }
    function done_(s) {
        var status_ = s.split(":");
        if (status_[0] == 'success') {
            loadList();
            Boxy.get($(".close")).hideAndUnload();
        } else {
            $('#mgniu_').html('<span class="warning-bar">' + status_[1] + '</span>');
        }
    }

    function loadList() {
        $("#beds").load("/pages/pm/bedspaces/listBeds.php");
    }

    function reloadRoom() {
        $.ajax({
            url: '/api/get_rooms.php',
            type: 'get',
            dataType: 'json',
            success: function (d) {
                var html = "";
                for (var i = 0; i < d.length; i++) {
                    html = html + "<option value='" + d[i].id + "' >" + d[i].name + "</option>";
                }
                $("#room").html(html);
            }
        });
    }
    var beds = [];

    $('input[name="bed"]').select2({
	    data: function() {
		    return {results: beds, text: 'name'};
	    },
	    width: '100%',
	    placeholder: '--- Select Bed ---',
	    formatResult: function(source){return source.name + ' ('+source.room.name +'/'+source.room.ward.name+')'; },
	    formatSelection: function(source){return source.name + ' ('+source.room.name +'/'+source.room.ward.name+')'; }
    });

    $('.boxy-content select[name="ward_id"]').select2().change(function (evt) {
	    if (typeof evt.added != "undefined" || $('.boxy-content select[name="ward_id"]').select2("val")) {
		    $.post('/api/get_beds.php', {ward_id: $('.boxy-content select[name="ward_id"]').select2("val")}, function (data) {
			    beds = data;
			    $('input[name="bed"]').select2('destroy');
			    $('input[name="bed"]').select2({
				    data: function() {
					    return {results: beds, text: 'name'};
				    },
				    width: '100%',
				    placeholder: '--- Select Bed ---',
				    formatResult: function(source){return source.name + ' ('+source.room.name +'/'+source.room.ward.name+')'; },
				    formatSelection: function(source){return source.name + ' ('+source.room.name +'/'+source.room.ward.name+')'; }
			    });

		    }, 'json');
	    }
    });
</script>