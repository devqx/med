<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/15/16
 * Time: 3:01 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioBookingDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysioBooking.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$all_specialty = (new StaffSpecializationDAO())->getSpecializations(NULL);
if(!isset($_SESSION)){@session_start();}
if($_POST){
  if(is_blank($_POST['patient_id'])){exit("error:Service Recipient is required");}
  if(is_blank($_POST['count'])){exit("error:Session Total Count is required");}
  if(is_blank($_POST['specialization_id'])){exit("error:Specialization is required");}

  $booking = new PhysioBooking();
  $booking->setPatient( new PatientDemograph($_POST['patient_id']) );
  $booking->setSpecialization( new StaffSpecialization($_POST['specialization_id']) );
  $booking->setCount(parseNumber($_POST['count']));
  $booking->setBookedBy(new StaffDirectory($_SESSION['staffID']));

  $newBooking = (new PhysioBookingDAO())->add($booking);
  if($newBooking !== NULL){
    exit("success:Booking successful");
  }
  exit("error:Booking failed");
}
?>

<section <?php if (isset($_REQUEST['patient_id'])){ ?>style="width: 500px;" <?php } ?>>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: savedSes})">
		<?php if (isset($_REQUEST['patient_id'])){ ?>
		<div class="row-fluid">Patient<?php } ?>
			<input type="hidden" name="patient_id" <?php if (isset($_REQUEST['patient_id'])) { ?> readonly value="<?= $_REQUEST['patient_id'] ?>" <?php } ?>>
			<?php if (isset($_REQUEST['patient_id'])){ ?></div><?php } ?>
		<div class="row-fluid">
			<label class="span4">Session Count
				<input name="count" type="number" step="1" min="1" value="1" required>
			</label>
			<label class="span8">Specialization:
				<select name="specialization_id" placeholder="Select ---">
					<option value=""></option>
					<?php foreach ($all_specialty as $_) { ?>
						<option value="<?= $_->getId() ?>"><?= $_->getName() ?></option>
					<?php } ?>
				</select>
			</label>
		</div>


		<div class="btn-block">
			<button type="submit" class="btn">Save</button>
			<?php if (isset($_REQUEST['patient_id'])) { ?>
				<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
					Cancel
				</button><?php } ?>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('[name="patient_id"]').select2({
			placeholder: "Filter List by Patient EMR or Name",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					}
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				return ("EMR ID:" + data.patientId + " " + data.fullname);
			},
			formatSelection: function (data) {
				return ("EMR ID:" + data.patientId + " " + data.fullname);
			},
			id: function (data) {
				return data.patientId;
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/search_patients.php?pid=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		})
	});
	var savedSes = function (s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1])
		} else if (data[0] === "success") {
			Boxy.info(data[1]);
			<?php if(!isset($_REQUEST['patient_id'])){?>setTimeout(function () {
				$('a[data-url="tabs/bookings.php"]').click();
			}, 10);
			<?php }else {?>showTabs(18);
			Boxy.get($(".close")).hideAndUnload();<?php }?>
		}
	}
</script>
