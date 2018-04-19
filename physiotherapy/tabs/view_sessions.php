<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/17/16
 * Time: 2:49 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioBookingDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioSessionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysioSession.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$booking = (new PhysioBookingDAO())->get($_REQUEST['booking_id']);
$sessions = $booking->getSessions();
?>

<section style="width:900px">
	<table class="table table-striped table-bordered">
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
	<?php if (count($sessions) > 0) { ?>
		<table class="table table-striped">
			<tr>
				<th width="15%">Date</th>
				<th>Note</th>
				<th width="15%">By</th>
			</tr>
			
			<?php foreach ($sessions as $s) {//$s=new PhysioSession();?>
				<tr>
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($s->getDate()))  ?></td>
					<td><?= $s->getNote() ?></td>
					<td><a target="_blank" href="/staff_profile.php?id=<?= $s->getNotedBy()->getId() ?>"><?= $s->getNotedBy()->getUsername() ?></a></td>
				</tr>
			<?php } ?>
		</table>
	<?php } else { ?>
		<div class="notify-bar">No sessions exist for this booking yet</div>
	<?php } ?>
</section>
