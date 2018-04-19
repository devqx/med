<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProgressNote.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProgressNoteDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
//$pid = $_REQUEST['pid'];
//$pat = (new ProgressNoteDAO())->getProgressNote($pid);
$specs = (new StaffSpecializationDAO())->getIpSpecializations();

/*usort($specs, function ($item1, $item2){
	$ipConsultationId = 18;
	if($item1->getId() != $ipConsultationId && $item2->getId() == $ipConsultationId){
		return 1;
	} elseif ($item1->getId() == $ipConsultationId && $item2->getId() != $ipConsultationId){
		return -1;
	} else {
		return $item1->getId() -$item2->getId();
	}
});*/
if (isset($_POST['progress_note'])) {
	if (isset($_POST['progress_note']) && strlen(trim($_POST['progress_note'])) < 2) {
		exit("error:Note content is blank?");
	}
	$_GET['suppress'] = TRUE;
	require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_staff.php';
	// $staff is got from the above file
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
	$pNote = new ProgressNote();
	$instance = (new InPatientDAO())->getInPatient($_POST['aid'], FALSE, $pdo);

	$pNote->setInPatient($instance);
	$pNote->setValue(NULL);
	$pNote->setNote($_POST['progress_note']);
	$pNote->setNotedBy($staff);
	$pNote->setNoteType('pr_note');

	$pNote = (new ProgressNoteDAO())->add($pNote, $pdo);
	if ($pNote == NULL) {
		$pdo->rollBack();
		ob_end_clean();
		exit("error:Sorry something went wrong");
	}

	if(isset($_POST['specialization_id']) && !is_blank($_POST['specialization_id'])){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
		$specialty = (new StaffSpecializationDAO())->get($_POST['specialization_id'], $pdo);
		if(isset($_POST['follow_up'])){
			$price = (new InsuranceItemsCostDAO())->getItemFollowUpPriceByCode($specialty->getCode(), $_POST['pid'], TRUE, $pdo);
		} else {
			$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialty->getCode(), $_POST['pid'], TRUE, $pdo);
		}
		
		$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialty->getCode(), $instance->getPatient()->getId(), TRUE, $pdo);
		$pat = (new PatientDemographDAO())->getPatient($instance->getPatient()->getId(), FALSE, $pdo, NULL);
		$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE, $pdo);
		$bil=new Bill();
		$bil->setPatient($pat);
		$bil->setDescription("Consultancy: ".$specialty->getName());
		$bil->setItem($specialty);
		$bil->setSource( (new BillSourceDAO())->findSourceById(5, $pdo) );
		$bil->setSubSource( (new BillSourceDAO())->findSourceById(3, $pdo) );
		$bil->setTransactionType("credit");
		$bil->setAmount($price);
		$bil->setDiscounted(NULL);
		$bil->setDiscountedBy(NULL);
		$bil->setClinic($staff->getClinic());
		$bil->setBilledTo($pat->getScheme());
		// $bil->setCostCentre((new DepartmentDAO())->get($_POST['did'], $pdo)->getCostCentre());
		if((new BillDAO())->addBill($bil, 1, $pdo, $_POST['aid']) == NULL){
			$pdo->rollBack();
			exit("error:Failed to add a charge [in-patient consultancy]");
		}
	}
	$pdo->commit();
	ob_end_clean();
	exit("success:InPatient Note saved successfully");
}
?>
<section style="width: 750px;">
	<form method="post" id="pNForm">
		<label>Note
			<textarea class="form-control" name="progress_note" id="progress_note" rows="5"></textarea>
		</label>

		<div class="row-fluid">
			<label class="span9">
				Ward Round Consulting Charge: <span class="pull-right fadedText">Select appropriate specialty [if any]</span>
				<select name="specialization_id" data-placeholder="Select Doctor's Specialization" required="required">
					<option value=""></option>
					<?php foreach ($specs as $spec) {//$spec=new StaffSpecialization();?>
						<option value="<?= $spec->getId() ?>"><?= $spec->getName() ?></option>
					<?php } ?>
				</select>
			</label>
			<label class="span2 no-label pull-right">
				<input type="checkbox" name="follow_up" class=""> Follow-Up
			</label>
		</div>
		<div class="clear"></div>
		<div class="clear"></div>
		<div class="btn-block">
			<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>"/>
			<button type="button" onclick="save()" class="btn">Save &raquo;</button>
			<button type="button" onclick="Boxy.get(this).hideAndUnload();" class="btn-link">Cancel</button>
		</div>
	</form>

</section>
<?php unset($spec);?>
<script type="text/javascript">
	$('#progress_note').summernote(SUMMERNOTE_CONFIG);
	var data;
	var callback = function () {
		data =($("#pNForm").serializeObject());
		data.progress_note = $('#progress_note').code();
		$.ajax({
			url: '<?= $_SERVER["PHP_SELF"] ?>',
			type: 'post',
			data: data,
			success: function (data) {
				var ret = data.split(":");
				if (ret[0] === "error") {
					Boxy.alert(ret[1]);
				} else {
					Boxy.get($('.close')).hideAndUnload();
				}
			},
			error: function (data) {
				console.error(data);
				Boxy.alert("Oops! Something went wrong");
			}
		});
	};
	function save() {
		showPinBox(callback);
	}
</script>