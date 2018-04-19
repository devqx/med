<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/24/16
 * Time: 11:22 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
?>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Date</th>
		<th>Reg ID #</th>
		<th>Patient</th>
		<th>Requested By</th>
		<th>Specialization</th>
		<th class="amount">Sessions Booked</th>
		<th class="amount">Available</th>
		<th>Last Encounter</th>
		<th>*</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($bookings as $b) { //$b=new PhysioBooking();?>
		<tr>
			<td><?= date(MainConfig::$dateTimeFormat, strtotime($b->getBookingDate()))?></td>
			<td><?= $b->getRequestCode() ?></td>
			<td>
				<a href="/patient_profile.php?id=<?= $b->getPatient()->getId() ?>&type=physio&booking_id_=<?= $b->getId() ?>"><?= $b->getPatient()->getFullname() ?></a>
			</td>
			<td>
				<a target="_blank" href="/staff_profile.php?id=<?= $b->getBookedBy()->getId() ?>"><?= $b->getBookedBy()->getUsername() ?></a>
			</td>
			<td><?= $b->getSpecialization()->getName() ?></td>
			<td class="amount"><?= $b->getCount() ?></td>
			<td class="amount"><?= $b->getAvailable() ?></td>
			<td><?= isset($b->getSessions()[0]) ? date(MainConfig::$dateTimeFormat, strtotime($b->getSessions()[0]->getDate()))  : 'N/A' ?></td>
			<td><?php if ($b->getCount() == $b->getAvailable()) { ?>
					<a class="cancelSessions" href="javascript:" data-href="/api/cancel_booking.php?booking_id=<?= $b->getId() ?>" class="btn btn-mini" title="Cancel Session">
						Cancel</a><?php } ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
