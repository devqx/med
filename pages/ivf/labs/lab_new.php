<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/16
 * Time: 1:45 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticSpecimenDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$referrals = (new ReferralDAO())->all();
$specimens = (new GeneticSpecimenDAO())->all();
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/GeneticRequest.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/GeneticSpecimen.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticLabDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';

	if(is_blank($_POST['female_patient_id']) && is_blank($_POST['male_patient_id']) ){exit("error:One of Male or Female Patient is required");}
	if(is_blank($_POST['genetic_lab_id'])){exit("error:Lab to request is required");}
	if(is_blank($_POST['specimen_type_id'])){exit("error:Specimen is required");}
	if(is_blank($_POST['reason'])){exit("error:Request note is required");}

	$fp = $mp = null;
	if(!is_blank($_POST['female_patient_id'])){$fp = new PatientDemograph($_POST['female_patient_id']);}
	if(!is_blank($_POST['male_patient_id'])){$mp = new PatientDemograph($_POST['male_patient_id']);}

  $request = (new GeneticRequest())
	  ->setFemalePatient($fp)
	  ->setMalePatient($mp)
	  ->setLab((new GeneticLabDAO())->get($_POST['genetic_lab_id']))
	  ->setSpecimenType(new GeneticSpecimen($_POST['specimen_type_id']))
	  ->setReason($_POST['reason'])->add();

	if($request != null){
		exit("success:Request successful");
	}
	exit("error:Failed to request");
}
?>
<section>
    <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: __wrap8e3dd93a60__})">
        <label class="output"></label>
        <label>Patient (Female) <span class="pull-right">*ignore accordingly</span><input required type="hidden" name="female_patient_id" id="female_patient_id"> </label>
        <label>Patient (Male) <span class="pull-right">*ignore accordingly</span><input type="hidden" name="male_patient_id" id="male_patient_id"> </label>
        <label>Genetic Test <input type="hidden" name="genetic_lab_id" id="genetic_lab_id"> </label>
        <label>Referral <select name="referral_id" placeholder="Select referring entity where applicable">
		        <option></option>
		        <?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
			        <option value="<?= $ref->getId() ?>"><?= $ref->getName() ?> (<?= $ref->getCompany()->getName() ?>
			        )</option><?php } ?></select> </label>
        <label>Specimen Type <select name="specimen_type_id" data-placeholder="Specimen Type">
		        <option></option>
		        <?php foreach ($specimens as $specimen) {?>
			        <option value="<?= $specimen->getId() ?>"><?= $specimen->getName() ?></option><?php } ?>
	        </select></label>
        <label>Reason/Request Note <textarea name="reason"></textarea> </label>
        <button type="submit" class="btn">Request</button>
        <button type="button" class="btn-link" reset>Cancel</button>
        <span name="messageBox" class="clear"></span>
    </form>
</section>
<script type="text/javascript">
	function __wrap8e3dd93a60__(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			$('span[name="messageBox"]').html('<div class="error alert-box">' + data[1] + '</div>');
		} else {
			$('span[name="messageBox"]').html('<div class="notice alert-box">' + data[1] + '</div>');
			$('button[reset]').click();
			$('.mini-tab > .tab:first').get(0).click();
		}
	}

    $(document).ready(function () {
	    $('#female_patient_id').select2({
		    placeholder: "Search and select patient",
		    minimumInputLength: 3,
		    width: '100%',
		    allowClear: true,
		    ajax: {
			    url: "/api/search_patients.php",
			    dataType: 'json',
			    data: function (term, page) {
				    return {
					    q: term,
					    sex: 'female'
				    };
			    },
			    results: function (data, page) {
				    return {results: data};
			    }
		    },
		    formatResult: function (data) {
			    var details = [];
			    details.push(data.patientId ? "EMR ID:"+data.patientId : null);
			    details.push(data.fname ? data.fname : null);
			    details.push(data.mname ? data.mname : null);
			    details.push(data.lname ? data.lname : null);
			    return implode(" ", details);
			    //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
		    },
		    formatSelection: function (data) {
			    var details = [];
			    details.push(data.patientId ? "EMR ID:"+data.patientId : null);
			    details.push(data.fname ? data.fname : null);
			    details.push(data.mname ? data.mname : null);
			    details.push(data.lname ? data.lname : null);
			    return implode(" ", details);
			    //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
		    },
		    id: function (data) {
			    return data.patientId;
		    }
	    });

	    $('#male_patient_id').select2({
		    placeholder: "Search and select patient",
		    minimumInputLength: 3,
		    width: '100%',
		    allowClear: true,
		    ajax: {
			    url: "/api/search_patients.php",
			    dataType: 'json',
			    data: function (term, page) {
				    return {
					    q: term,
					    sex: 'male'
				    };
			    },
			    results: function (data, page) {
				    return {results: data};
			    }
		    },
		    formatResult: function (data) {
			    var details = [];
			    details.push(data.patientId ? "EMR ID:"+data.patientId : null);
			    details.push(data.fname ? data.fname : null);
			    details.push(data.mname ? data.mname : null);
			    details.push(data.lname ? data.lname : null);
			    return implode(" ", details);
			    //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
		    },
		    formatSelection: function (data) {
			    var details = [];
			    details.push(data.patientId ? "EMR ID:"+data.patientId : null);
			    details.push(data.fname ? data.fname : null);
			    details.push(data.mname ? data.mname : null);
			    details.push(data.lname ? data.lname : null);
			    return implode(" ", details);
			    //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
		    },
		    id: function (data) {
			    return data.patientId;
		    }
	    });
	    $('#genetic_lab_id').select2({
		    placeholder: "Search and Select Genetic Lab Test",
		    minimumInputLength: 0,
		    multiple: false,
		    width: '100%',
		    allowClear: true,
		    ajax: {
			    url: "/ivf/api/get_pgd_labs.php",
			    dataType: 'json',
			    data: function (term, page) {
				    return {
					    search: term
				    };
			    },
			    results: function (data, page) {
				    return {results: data};
			    }
		    },
		    formatResult: function (data) {
			    return data.name;
		    },
		    formatSelection: function (data) {
			    return data.name;
		    }
	    }).change(function(evt) {
		    var total = 0;
		    var pid = $('#female_patient_id').val();
		    //abi the male guy?
		    if (evt.added != undefined) {
			    showInsuranceNotice(pid, evt);
		    }
		    var ele = $('#genetic_lab_id').select2("data");

		    total = ele ? parseFloat ( ele.basePrice ) : 0;

		    $("form label.output").html("Estimated Test cost: <?= $currency->getSymbolLeft() ?>" + parseFloat(total).toFixed(2)+"<?= $currency->getSymbolRight() ?>");
	    });
	    $('select[name="referral_id"]').select2({width:'100%'});
	    $('select[name="specimen_type_id"]').select2({width:'100%'});
    })
</script>