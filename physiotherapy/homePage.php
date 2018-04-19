<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/15/16
 * Time: 2:02 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
if (!isset($_SESSION)) {
	session_start();
}
if (!isset($_SESSION ['staffID'])) {
	exit('error:Your session has expired. Please login again');
}
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->physiotherapy)) {
	exit ($protect->ACCESS_DENIED);
}
?>
<div class="mini-tab">
	<a class="tab on" data-url="tabs/bookings.php" href="javascript:">Available Bookings/Sessions</a>
	<a class="tab" data-url="tabs/search_bookings.php" href="javascript:">Search Sessions</a>
	<a class="tab itemsrequests" data-url="items/items_requests.php" href="javascript:;">Items Requests</a>
	<a class="tab pull-right" data-url="tabs/new_request.php" href="javascript:">New Booking Request</a>

</div>

<div id="contentPane_" class="document"></div>
<script type="text/javascript">
	$(document).ready(function () {
		setTimeout(function () {
			$('a[data-url="tabs/bookings.php"]').click();
		}, 10);

		$(".mini-tab a.tab").bind('click', function () {
			$(".mini-tab a.tab").removeClass('on');
			$(this).addClass('on');
			var URL = $(this).data("url");
			$.ajax({
				url: URL,
				complete: function (xhr, status) {
					if (status !== "error") {
						$("#contentPane_").html(xhr.responseText);
						$('select').select2({width: '100%', allowClear: true});
					}
				},
				error: function (xhr, status) {
					$("#contentPane_").html("<span class='warning-bar'>Failed to load requested page</span>");
				}
			});
		});

	});
</script>
