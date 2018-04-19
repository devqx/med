<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/31/17
 * Time: 12:32 PM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Resource.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$aptClinics = (new AptClinicDAO())->all();
$resources = (new ResourceDAO())->getResources();
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	if(!isset($_SESSION['staffID'])){exit('error:Session has expired');}
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	if(is_blank($_POST['resource_ids'])){exit('error:Resource is required');}
	if(is_blank($_POST['start_date'])){exit('error:Schedule start date is required');}
	if(is_blank($_POST['stop_date'])){exit('error:Schedule end date is required');}
	if(is_blank($_POST['request_id'])){exit("error:General error which shouldn't happen");}
	if(is_blank($_POST['clinic_id'])){exit("error:Clinic/Unit is required");}
	$scheduledResources = array_filter($_POST['resource_ids']);
	
	$time1 = strtotime($_POST['start_date']);
	$time2 = strtotime($_POST['stop_date']);
	
	$request = (new PatientProcedureDAO())->get($_POST['request_id'], $pdo)->setScheduledResources( $scheduledResources )->setScheduledBy( new StaffDirectory($_SESSION['staffID']) )->setScheduledOn(date(MainConfig::$mysqlDateTimeFormat))->setTimeStop(date("Y-m-d H:i:s", max($time1, $time2)))->setTimeStart(date("Y-m-d H:i:s", min($time1, $time2)))->schedule($pdo);
	if($request != null){
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Resource.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AptClinic.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Appointment.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentInvitee.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentGroup.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentResource.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		if(isset($_SESSION['staffID'])){
			$staff = (new StaffDirectoryDAO())->getStaff( $_SESSION['staffID'], TRUE, $pdo);
		} else if(isset($_POST['staff_id'])){
			$staff = (new StaffDirectoryDAO())->getStaff( $_POST['staff_id'], TRUE, $pdo);
		} else {
			exit('warn:Sorry, access is denied');
		}
		$appointment = new stdClass();
		$appointment->sdates = [$request->getTimeStart()];
		$appointment->edates = [$request->getTimeStop()];
		$appointment->freq = "";
		$appointment->clinic = $_POST['clinic_id'];
		$appointment->patient = $request->getPatient()->getId();
		$appointment->staffs = [];
		$appointment->resource = $scheduledResources;
		$appointment->description = "Procedure Schedule (".$request->getProcedure()->getName().") for ".$request->getPatient()->getFullname();
		$appointment->allDay = false;
		$appointment->forced = false;
		
		$_REQUEST['createAppointment'] = json_encode($appointment);
		
		$p = json_decode($_REQUEST['createAppointment']);
		$resources = [];
		
		foreach ($p->resource as $r){
			$resources[] =(new AppointmentResource())->setResource(new Resource($r));
		}
		$ag = (new AppointmentGroup())
			->setCreator($staff)
			->setDepartment($staff->getDepartment())
			->setClinic($p->clinic ? new AptClinic($p->clinic) : null)
			->setIsAllDay($p->allDay)
			->setResource($resources)
			->setDescription($p->description)
			->setPatient(new PatientDemograph($p->patient));
		$apps = $appInvs = [];
		foreach ($p->sdates as $key => $d) {
			$app = new Appointment();
			$app->setEditor($staff);
			$app->setStartTime($d);
			$app->setEndTime(($p->edates[$key] === null || trim($p->edates[$key]) === "") ? null : $p->edates[$key]);
			$unlimited = (new AppointmentGroupDAO())->checkAppointByClinic($d, $p->clinic, $pdo);
			if(!$unlimited && $p->forced !== true){
				$pdo->rollBack();
				exit('warn:Sorry, Clinic has reached the daily appointment limit');
			}
			
			$apps[] = $app;
		}
		$staffs = count($p->staffs) > 0 ? $p->staffs : [];
		
		if (count($staffs) > 0 && is_array($staffs)) {
			foreach ($staffs as $s) {
				$ai = new AppointmentInvitee();
				$ai->setStaff(new StaffDirectory($s->id));
				$appInvs[] = $ai;
			}
		}
		
		$ag->setAppointments($apps);
		$ag->setInvitees($appInvs);
		
		$appGroup = (new AppointmentGroupDAO())->add($ag, $pdo);
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AppointmentExist.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ResourceUnavailable.php';
		if($appGroup instanceof AppointmentExist){
			$pdo->rollBack();
			exit("error:There's an appointment that is already set for the requested time(s) for one or more of the selected staffs");
		} else if($appGroup instanceof ResourceUnavailable){
			$res = escape($appGroup->getMessage());
			$pdo->rollBack();
			exit("error:$res is not available for the requested time(s)");
		} else if ($appGroup == null) {
			$pdo->rollBack();
			exit('error:Unable to book appointment (Maybe another active appointment exists)');
		} else {
			if (!$appGroup == null){
				// update the procedure appointment appoint Id
				$proce = (new PatientProcedureDAO())->get($_POST['request_id'], $pdo)->setAppointmentId($appGroup->getId())->update_($pdo);
			}
			/*if ($request->getBilled()) {
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
				$bil = new Bill();
				$bil->setPatient($request->getPatient());
				$bil->setDescription("Procedure theatre charge: " . $request->getProcedure()->getName());
				
				$bil->setItem($request->getProcedure());
				$bil->setSource((new BillSourceDAO())->findSourceByName("procedure", $pdo));
				$bil->setTransactionType("credit");
				
				$amount = (new InsuranceItemsCostDAO())->getItemPricesByCode($request->getProcedure()->getCode(), $request->getPatient()->getId(), true, $pdo);
				$bil->setAmount($amount->theatrePrice);
				$bil->setPriceType('theatrePrice');
				$bil->setDiscounted(null);
				$bil->setDiscountedBy(null);
				
				$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
				$bil->setClinic($staff->getClinic());
				$bil->setBilledTo($request->getPatient()->getScheme());
				
				$bill = (new BillDAO())->addBill($bil, 1, $pdo);
			}
			ob_end_clean();*/
			$pdo->commit();
			exit ("success:Schedule success!");
		}
	}
	$pdo->rollBack();
	exit('error:Schedule request failed');
}
?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, schedulerHandler)">
		<label>Unit/Clinic <input name="clinic_id" type="hidden" id="clinic_id" required placeholder="Select Unit/Clinic"></label>
		<label>Resource <select name="resource_ids[]" multiple="multiple" data-placeholder="- Select machine resources">
				<option></option>
				<?php foreach ($resources as $resource){?><option value="<?=$resource->getId()?>"><?=$resource->getName()?></option><?php }?>
			</select> </label>
		<label>Schedule Dates </label>
		<div class="row-fluid">
			<label class="span6">From<input type="text" name="start_date"> </label>
			<label class="span6">To<input type="text" name="stop_date"> </label>
		</div>
		<p class="clearfix"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Schedule</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="request_id" value="<?=$_GET['id']?>">
	</form>
</section>
<script type="text/javascript">
	var units = <?= json_encode( $aptClinics, JSON_PARTIAL_OUTPUT_ON_ERROR ); ?>;
	var schedulerHandler = {
		onStart: function(){
			$(document).trigger('ajaxSend');
		},
		onComplete: function(s){
			$(document).trigger('ajaxStop');
			var data = s.split(':');
			if(data[0]==='error'){
				Boxy.warn(data[1]);
			} else if(data[0]==='success'){
				Boxy.info(data[1], function () {
					Boxy.get($('.close')).hideAndUnload();
					location.reload();
				})
			}
		}
	};
	$('input[name="start_date"]').datetimepicker({format: 'Y-m-d H:i', step: 60});
	$('input[name="stop_date"]').datetimepicker({format: 'Y-m-d H:i', step: 60});
	$("#clinic_id").select2({
		width: '100%',
		allowClear: true,
		data: function () {
			return {results: units, text: 'name'};
		},
		formatResult: function (source) {
			return source.name;
		},
		formatSelection: function (source) {
			return source.name;
		}
	})
</script>