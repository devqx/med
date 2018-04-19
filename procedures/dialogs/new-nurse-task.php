<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/18/16
 * Time: 2:53 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/NursingServiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientProcedureNursingTaskDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/PatientProcedureNursingTask.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/NursingService.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/PatientProcedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$ServiceCentres = (new ServiceCenterDAO())->all('nursing');

if($_POST){
	
	if (is_blank($_POST['service_centre_id'])) {
		exit("error:Please select service centre");
	}
    if(is_blank($_POST['nursing_service_id'])){
        exit("error:Please select a service");
    }
    $pdo = (new MyDBConnector())->getPDO();
    $pdo->beginTransaction();
    $nService = (new PatientProcedureNursingTask())
        ->setTask( new NursingService($_POST['nursing_service_id']) )
	      ->setServiceCentre(new ServiceCenter($_POST['service_centre_id']))
	      ->setPatientProcedure( new PatientProcedure($_POST['patient_procedure_id']) );

    if((new PatientProcedureNursingTaskDAO())->addTask($nService, $pdo) != null){

        $staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE, $pdo);
        $pp = (new PatientProcedureDAO())->get($_POST['patient_procedure_id'], $pdo);
        $patient = (new PatientDemographDAO())->getPatient($pp->getPatient()->getId(), false, $pdo);
        $service1 = (new NursingServiceDAO())->get($_POST['nursing_service_id'], $pdo);
        $price = (new InsuranceItemsCostDAO())->getItemPriceByCode($service1->getCode(), $_POST['pid'], TRUE, $pdo);

        $bil=new Bill();
        $bil->setPatient($patient);
        $bil->setDescription($service1->getName(). " [Used in Procedure]");
        $bil->setItem($service1);
        $bil->setSource( (new BillSourceDAO())->findSourceById(16, $pdo) );
        $bil->setTransactionType("credit");
        $bil->setAmount($price);
        $bil->setDiscounted(NULL);
        $bil->setDiscountedBy(NULL);
        $bil->setClinic($staff->getClinic());
        $bil->setBilledTo($patient->getScheme());
        $bill=(new BillDAO())->addBill($bil, 1, $pdo, (isset($_POST['aid']) && trim($_POST['aid']) !== "") ? ($_POST['aid']) : NULL);

        if($bill != null){
            $pdo->commit();
            exit("success:Successfully done");
        }
    }
    $pdo->rollBack();
    exit("error:Operation failed");
}
?>
<section style="width: 600px;">
    <form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart:__start__,onComplete:__done__})">

	    <label>Business Unit/Service Centre <select name="service_centre_id" data-placeholder="-- Select --">
			    <option></option>
			    <?php foreach ($ServiceCentres as $center) { ?>
				    <option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
			    <?php } ?>
		    </select> </label>
	    <label>
            Nursing Service <select name="nursing_service_id" data-placeholder=" - - Nursing Service applicable - -">
                <option value=""></option>
                <?php foreach ( (new NursingServiceDAO())->all()  as $service) {?>
                    <option value="<?= $service->getId()?>"><?= $service->getName()?></option>
                <?php }?>
            </select>
        </label>
        <input type="hidden" name="patient_procedure_id" value="<?= $_GET['id']?>">

        <div class="btn-block">
            <button type="submit" class="btn">Add</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>

    </form>
</section>
<script type="text/javascript">
    function __start__(){}
    function __done__(s){
        var data = s.split(":");
        if(data[0]==="error"){
            Boxy.alert(data[1]);
        }else if(data[0]==="success"){
            Boxy.info(data[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
        }
    }

</script>
