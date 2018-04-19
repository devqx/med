<?php
require_once $_SERVER['DOCUMENT_ROOT'] ."/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientOphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/OphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/OphthalmologyGroup.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ServiceCenter.php';

$currency = (new CurrencyDAO())->getDefault();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$referrals = (new ReferralDAO())->all();
$allCentres = (new ServiceCenterDAO())->all('Ophthalmology');

if(!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->lab) && !$this_user->hasRole($protect->lab_super))
    exit ($protect->ACCESS_DENIED);

if ($_POST) {
    if (isset($_POST['test-reqs']) && !empty($_POST['test-reqs'])) {
        $request = new OphthalmologyGroup();
        $request->setPatient( (new PatientDemographDAO())->getPatient($_POST['pid'], FALSE) );
        $request->setRequestedBy( $this_user );

        $lab_data = array();
        $tests = explode(",",$_POST['test-reqs']);

        foreach ($tests as $l) {
            $lab_data[] = (new OphthalmologyDAO())->get($l);
        }
        $request->setRequestData($lab_data);
        $request->setServiceCentre( (new ServiceCenterDAO())->get($_POST['service_centre_id']) );
        $request->setReferral((new ReferralDAO())->get($_POST['referral_id']) );
        $data = (new PatientOphthalmologyDAO())->newRequest($request);
        if($data!==NULL){
            exit(json_encode($data));
        }
        exit(json_encode("error:Failed to create the Optometry request(s)"));
    } else {
        exit(json_encode('error:No Optometry test(s) selected!'));
    }
}
?>
<div style="width:500px">
    <script type="text/javascript">
        $('.boxy-content #tests_to_request').select2({
            placeholder: "Search and select Optometry Service",
            minimumInputLength: 3,
            width: '100%',
            multiple: true,
            allowClear: true,
            ajax: {
                url: "/api/get_ophthalmology.php",
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
                return data.name + " (" + data.category.name + ")";
            },
            formatSelection: function (data) {
                return data.name + " (" + data.category.name + ")";
            }
        }).change(function(){
            var total = 0;
            $.each($(this).select2("data"), function(){
                total = parseFloat(this.basePrice) + total;
            });
            $("form label.output").html("Estimated Test cost: <?= $currency->getSymbolLeft() ?>" + parseFloat(total).toFixed(2)+"<?= $currency->getSymbolRight() ?>");
        });
        <?php if(!isset($_GET['id'])){?>
        $('.boxy-content [name="pid"]').select2({
            placeholder: "Search and select patient",
            minimumInputLength: 3,
            width: '100%',
            allowClear: true,
            ajax: {
                url: "/api/search_patients.php",
                dataType: 'json',
                data: function (term, page) {
                    return {
                        q: term
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
        <?php } ?>
        function start() {
        }
        function done(s) {
            s = JSON.parse(s);
            if ($.isPlainObject(s) && s.group_name != ""){
                Boxy.get($('.close')).hideAndUnload();
            }
            try {
                var returnData = s.split(":");
                if (returnData[0] == "error") {
                    Boxy.alert(returnData[1]);
                } else {
                    try {
                        showTabs(6);
                    } catch (exception) { }
                    Boxy.get($('.close')).hideAndUnload();
                }
            } catch(exception){
            }
            //Boxy.get($('.close')).hideAndUnload();
        }
    </script>
    <form method="post" action="/ophthalmology/new.php"
          onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
        <label class="output well well-small"></label>
        <label> Business Unit/Service Center <select name="service_centre_id" placeholder="Select a receiving Optometry center">
                <option></option>
                <?php foreach ($allCentres as $center) {?>
                    <option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
                <?php }?>
            </select> </label>

        <?php if (!isset($_GET['id'])) { ?><label>Patient </label><?php } ?>
        <label><input type="hidden" name="pid"
                      value="<?= (isset($_GET['id']) ? $_GET['id'] : '') ?>"></label>

        <label>Referred by
            <select name="referral_id" placeholder="Select referring entity where applicable">
                <option></option>
                <?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
                    <option value="<?= $ref->getId() ?>"><?= $ref->getName() ?> (<?= $ref->getCompany()->getName() ?>
                    )</option><?php } ?>
            </select>
        </label>
        <label>Optometry Services to request:</label>
        <label><input type="hidden" id="tests_to_request" name="test-reqs"></label>
        <div class="btn-block">
            <button class="btn" type="submit" name="btn">Request &raquo;</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>