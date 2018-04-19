<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/15/16
 * Time: 5:23 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioBookingDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$bookingId = isset($_REQUEST['booking_id']) ? $_REQUEST['booking_id'] : null;
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$temp = (new PhysioBookingDAO())->forPatient($_GET['pid'], $bookingId, $page, $pageSize);
$bookings = $temp->data;
$totalSearch = $temp->total;
?>
<div class="menu-head">
	<a href="/patient_profile.php?id=<?= $_GET['pid'] ?>&type=physio">All Bookings</a> |
	<a onclick="load(this)" href="javascript:;" data-href="/physiotherapy/tabs/new_request.php?patient_id=<?= $_GET['pid'] ?>">New Booking</a>
</div>
<p></p>
<div class="dataTables_wrapper">
	<?php if ($totalSearch == 0){ ?>
		<div class="notify-bar">No data available</div>
	<?php } else { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Date</th>
			<th>Reg ID #</th>
			<th>Requested By</th>
			<th>Specialization</th>
			<th>Sessions Booked</th>
			<th>Available</th>
			<th>Last Encounter</th>
			<td></td>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($bookings as $b) {//$b=new PhysioBooking();?>
			<tr>
				<td><?= date(MainConfig::$dateTimeFormat, strtotime($b->getBookingDate()))?></td>
				<td><a href="javascript:" onclick="load(this)" data-href="/physiotherapy/tabs/view_sessions.php?booking_id=<?= $b->getId() ?>" title="View Session Data"><?= $b->getRequestCode() ?></a></td>
				<td><a target="_blank" href="/staff_profile.php?id=<?= $b->getBookedBy()->getId() ?>"><?= $b->getBookedBy()->getUsername() ?></a></td>
				<td><?= $b->getSpecialization()->getName() ?></td>
				<td><?= $b->getCount() ?></td>
				<td><?= $b->getAvailable() ?></td>
				<td><?= (isset($b->getSessions()[0])) ? date(MainConfig::$dateTimeFormat, strtotime($b->getSessions()[0]->getDate())) : 'N/A' ?></td>
				<td nowrap>
					<?php if ($b->getCount() == $b->getAvailable() && $b->getActive()) { ?><a href="javascript:" data-href="/api/cancel_booking.php?booking_id=<?= $b->getId() ?>" class="btn btn-mini cancelBooking" title="Cancel Booking"><i class="icon-trash"></i></a><?php } ?>
					<?php if ($b->getAvailable() > 0 && $b->getActive()) { ?><a href="javascript:" onclick="load(this)" data-href="/physiotherapy/tabs/new_session.php?booking_id=<?= $b->getId() ?>" class="btn btn-mini" title="New Session Note"><i class="icon-edit"></i></a><?php } ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>
	<div class="resultsPatientBookings no-footer dataTables_paginate">
		<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
			<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
			<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>
			<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
			<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
		</div>
	</div>
</div>
<?php } ?>

<script type="text/javascript">
	var load = function (element) {
		Boxy.load($(element).data("href"));
	};

	$(document).on('click', '.resultsPatientBookings.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		if (!$(this).hasClass("disabled") && !e.handled) {
			var url = "/physiotherapy/patient_bookings.php?pid=<?=$_GET['id']?>&page=" + page;
			$('#contentPane').load(url, function (responseText, textStatus, req) {
			});
			e.handled = true;
		}
	});

	$(document).ready(function () {
		$('a.cancelBooking').live('click', function (e) {
			var href = $(this).data("href");
			if (e.handled != true) {
				Boxy.ask("Are you sure you want to cancel this session?", ["Yes", "No"], function (choice) {
					if (choice == "Yes") {
						$.post(href, {action: "cancel"}, function (s) {
							if (s) {
								showTabs(18);
							} else {
								Boxy.alert("An error occurred");
							}
						}, 'json');
					}
				});
				e.handled = true;
			}
		});
	});
</script>
