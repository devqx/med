<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/7/14
 * Time: 12:19 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureResourceTypeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureResourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedureResource.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProcedureResourceType.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
$resources = (new StaffDirectoryDAO())->getStaffs(true);
$resourceTypes = (new ProcedureResourceTypeDAO)->all();
$bills = new Bills();
$procedure = (new PatientProcedureDAO())->get($_GET['id']);
$pat = (new PatientDemographDAO())->getPatient($procedure->getPatient()->getId(), false, null, null);
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
if ($_POST) {
	if (!isset($_SESSION)) {
		session_start();
	}
	if(!isset($_SESSION['staffID'])){exit('error:Your session has expired.');}
	
	$pRes = new PatientProcedureResource();
	$pRes->setPatientProcedure(new PatientProcedure($_GET['id']));
	
	if ($selfOwe - $creditLimit > 0) {
		//exit("error:Patient has outstanding credit");
	}
	
	if (!is_blank($_POST['staff_id'])) {
		$pRes->setResource(new StaffDirectory($_POST['staff_id']));
	} else {
		exit("error:Staff Resource is required");
	}
	
	if(!is_blank($_POST['resource_type_id'])){
		$pRes->setResourceType( new ProcedureResourceType($_POST['resource_type_id']) );
	} else {
		exit('error:Resource Type is required');
	}
	
	$pRes->setCreator(new StaffDirectory($_SESSION['staffID']));
	
	$newPres = (new PatientProcedureResourceDAO())->addResource($pRes);
	
	if ($newPres !== null) {
		exit("success:Resource added successfully");
	}
	exit("error:Failed to add resource");
}
?>
<section>
	<div class="well">
		Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>
	</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:__start,onComplete:__done})">
		<label>Resource:
			<select name="staff_id" data-placeholder=" -- select a doctor / resource --">
				<option></option>
				<?php foreach ($resources as $r) {//$r=new StaffDirectory();?>
					<option value="<?= $r->getId() ?>"><?= $r->getFullname() ?>
						--<?= (!is_null($r->getSpecialization()) ? $r->getSpecialization()->getName() : "") ?></option>
				<?php } ?>

			</select></label>

		<label>Resource Type
			<select name="resource_type_id" data-placeholder="- - Select - -">
				<option></option>
				<?php foreach ($resourceTypes as $resourceType) { ?>
					<option value="<?= $resourceType->getId() ?>"><?= $resourceType->getName() ?></option>
				<?php } ?>
			</select>
		</label>

		<div class="btn-block">
			<button type="submit" class="btn"<?php //= ($selfOwe - $creditLimit > 0 ? ' disabled="disabled"' : '') ?>>
				Add Resource
			</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Cancel
			</button>
		</div>

	</form>
</section>
<script type="text/javascript">
	function __start() {
	}
	function __done(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1]);
		} else if (data[0] === "success") {
			Boxy.info(data[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			});
		}
	}
</script>