<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/24/16
 * Time: 1:30 PM
 */
$nursingBilling = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NursingTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InpatientObservation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InpatientObservationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';

$n_tpls = (new NursingTemplateDAO())->all();
if($_POST){
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	if (!is_blank($_POST['nursing_service_id']) && $nursingBilling) {
		$instance = ($_POST['in_patient_id']) ? (new InPatientDAO())->getInPatient($_POST['in_patient_id'], TRUE, $pdo) : null;
		if($instance->getPatient()){
			$pid = $instance->getPatient()->getId();
		} else {
			$pdo->rollBack();
			exit('error:Failed to determine patient instance');
		}

		$patient = (new PatientDemographDAO())->getPatient($pid, FALSE, $pdo);
		$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE, $pdo);
		$service = (new NursingServiceDAO())->get($_POST['nursing_service_id'], $pdo);
		$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($service->getCode(), $pid, TRUE, $pdo);
		$bil = new Bill();
		$bil->setPatient($patient);
		$bil->setDescription($service->getName() . " [Used in Tasks-]");
		$bil->setItem($service);
		$bil->setSource((new BillSourceDAO())->findSourceById(16, $pdo));
		$bil->setTransactionType("credit");
		$bil->setAmount($_POST['nursing_service_quantity']*$price);
		$bil->setDiscounted(null);
		$bil->setDiscountedBy(null);
		$bil->setCostCentre($instance && $instance->getWard() ? $instance->getWard()->getCostCentre() : null);
		$bil->setClinic($staff->getClinic());
		$bil->setBilledTo($patient->getScheme());
		$bill = (new BillDAO())->addBill($bil, $_POST['nursing_service_quantity'], $pdo, (isset($_POST['in_patient_id']) && trim($_POST['in_patient_id']) !== "") ? ($_POST['in_patient_id']) : null);

		if($bill==null){
			$pdo->rollBack();
			exit('error:Failed to add nursing service charge');
		}
	}

	$ob = (new InpatientObservation())->setNote($_POST['noteContent'])->setInPatient( new InPatient($_POST['in_patient_id']) );

	if(is_null(  (new InpatientObservationDAO())->add($ob, $pdo)  )){
		exit("error:Something failed");
	} else {
		$pdo->commit();
		exit("success:Observation noted");
	}

}
?>
<section style="width: 700px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: savedObservation})">
		<input type="hidden" name="in_patient_id" value="<?= $_GET['aid']?>">
		<label>Note Template <span class="pull-right"><a href="javascript:;" id="new_ip_tpl">New Template</a></span>
			<select id="template_id" data-placeholder="Select Template">
				<option></option>
				<?php foreach($n_tpls as $tpl){?><option value="<?= $tpl->getId() ?>>" data-text="<?= $tpl->getContent()?>"><?= $tpl->getTitle()?></option><?php }?>
			</select> </label>
		<label>Note	<textarea name="noteContent"></textarea> </label>
		<?php if($nursingBilling){?>
		<div class="row-fluid">
			<label class="span8">
				Nursing Service <select name="nursing_service_id" data-placeholder=" - - Nursing Service applicable - -">
					<option value=""></option>
					<?php foreach ((new NursingServiceDAO())->all() as $service) { ?>
						<option value="<?= $service->getId() ?>"><?= $service->getName() ?></option>
					<?php } ?>
				</select>
			</label>
			<label class="span4">Quantity
				<input type="number" min="1" step="1" required value="1" name="nursing_service_quantity">
			</label>
		</div>
		<?php }?>
		<p class="clear"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>

<script type="text/javascript">
	function savedObservation(s) {
		var data = s.split(":");
		if (data[0]==="error"){
			Boxy.warn(data[1]);
		} else if(data[0]==="success") {
			Boxy.get($(".close")).hideAndUnload();
		}
	}

	function reloadTemplates() {
		$.getJSON('/api/get_nursing_templates.php', function (data) {
			var str = '<option></option>';
			for(var i=0;i< data.length;i++){
				str += '<option value="'+data[i].id+'" data-text="'+data[i].content+'">'+data[i].title+'</option>';
			}
			$('#template_id').html(str);
		});
	}
	
	$(document).ready(function () {
		$('textarea[name="noteContent"]').summernote(SUMMERNOTE_CONFIG);
	}).on('click', '#new_ip_tpl', function (evt) {
		if(!evt.handled){
			Boxy.load('/admissions/dialogs/ip_template_new.php', {title: 'Add New Template', afterHide: function(){
				reloadTemplates();
			}});
			evt.handled = true;
		}
	}).on('change', '#template_id', function (data) {
		if(data.added != null){
			var content = $(data.added.element).data("text");
			$('textarea[name="noteContent"]').code(content).focus();
		} else {
			$('textarea[name="noteContent"]').code('').focus();
		}
	});
</script>
