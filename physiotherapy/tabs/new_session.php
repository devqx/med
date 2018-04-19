<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/17/16
 * Time: 1:18 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioBookingDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioSessionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysioSession.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
$booking = (new PhysioBookingDAO())->get($_REQUEST['booking_id']);
$templates = (new ExamTemplateDAO())->all();
if ($_POST) {
	$session = (new PhysioSession())->setBooking($booking)->setNotedBy(new StaffDirectory($_SESSION['staffID']))->setNote($_POST['note']);
	
	$new = (new PhysioSessionDAO())->add($session);
	if ($new !== null) {
		exit("success:Session Summary saved");
	}
	exit("error:Failed to save");
}

?>
<section style="width:900px">
	<table class="table table-striped">
		<tr>
			<th width="10%">BOOKING #</th>
			<td><?= $booking->getRequestCode() ?></td>
		</tr>
		<tr>
			<th>SPECIALIZATION:</th>
			<td><?= $booking->getSpecialization()->getName() ?></td>
		</tr>
		<tr>
			<th>SESSIONS:</th>
			<td><?= $booking->getAvailable() ?> of <?= $booking->getCount() ?> Available</td>
		</tr>
	</table>

	<label>Template
		<span class="pull-right"><i class="icon-question-sign"></i><a href="javascript:;" class="exam_template_link" data-href="template_help.php">help</a>
			<!--| <i class="icon-star-empty"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_add.php">add selected to favorites</a> | <i class="icon-star"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_delete.php">remove selected from favorites</a>-->
			| <i class="icon-plus-sign"></i><a href="javascript:;" class="exam_template_link" data-href="template_new.php">add to list</a></span>
		<select name="template_id" id="template_id" data-placeholder="Select Custom Text Templates">
			<option></option>
			<?php foreach ($templates as $t) { ?>
				<option value="<?= $t->getId() ?>" data-text="<?= ($t->getContent()) ?>"><?= $t->getCategory()->getName() ?>: <?= $t->getTitle() ?></option><?php } ?>
		</select>
	</label>

	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: complet_})">
		<label>Session Summary Note<textarea name="note"></textarea></label>
		<div class="btn-block">
			<?php if ($booking->getAvailable() > 0) { ?>
				<button class="btn" type="submit">Save</button><?php } ?>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="booking_id" value="<?= $_REQUEST['booking_id'] ?>">
	</form>
</section>
<script type="text/javascript">
	var complet_ = function (s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1])
		} else if (data[0] === "success") {
			Boxy.info(data[1]);
			Boxy.get($(".close")).hideAndUnload();
			showTabs(18);
		}
	};
	$(document).ready(function () {
		$('a.exam_template_link').click(function () {
			Boxy.load("/consulting/" + $(this).data("href"));
		});
		$('#template_id').select2().change(function (data) {
			if (data.added !== undefined) {
				var content = $(data.added.element).data("text");
				$('textarea[name="note"]').code(content).focus();
			} else {
				$('textarea[name="note"]').code('').focus();
			}
		}).trigger('change');
		$('textarea[name="note"]').summernote(SUMMERNOTE_CONFIG);
	})
</script>
