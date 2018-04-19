<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 6:09 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$_centres = (new ServiceCenterDAO())->all('Ophthalmology');
?>
<div>
	<div class="menu-head">
		<div class="row-fluid">
			<h5 class="span7">Items Requests</h5>
			<label class="span5">
				Business Unit/Service Center<select name="ophthalmology_centre_id" data-placeholder="-- Select processing centre --">
					<option></option><?php foreach ($_centres as $l) { ?>
						<option value="<?= $l->getId() ?>" <?= (isset($_POST['ophthalmology_centre_id']) && $_POST['ophthalmology_centre_id'] === $l->getId()) ? ' selected="selected"' : '' ?>><?= $l->getName() ?></option><?php } ?></select>
			</label>
		</div>
	</div>

	<div class="mini-tab">
		<a class="tab on list_open items" href="javascript:" data-url="items/requests_list_open.php">Open Requests</a>
		<a class="tab list_fulfilled items" href="javascript:" data-url="items/requests_list_received.php">Fulfilled Requests</a>
		<a class="tab list_search items" href="javascript:" data-url="items/requests_list_search.php">Search Requests</a>
		<a class="pull-right tab new_item items" href="javascript:" data-url="items/new_request.php">New Request</a>
	</div>
	<div id="requests_container">
		<?php include_once "items/requests_list_open.php" ?>
	</div>
</div>
<script>
	$(document).ready(function () {
		$('select[name="ophthalmology_centre_id"]').select2({width: '100%', allowClear: true}).change(function () {
			url = $(".mini-tab a.on.items").data("url");
			$.get(url, {'page': 0, 'ophthalmology_centre_id': $('select[name="ophthalmology_centre_id"]').val()}, function (s) {
				$('#requests_container').html(s);
			});
		});
		$(".mini-tab a.items").bind('click', function () {
			$(".mini-tab a.items").removeClass('on');
			$(this).addClass('on');
			var URL = $(this).data("url");
			$.ajax({
				url: URL,
				complete: function (xhr, status) {
					if (status !== "error") {
						$("#requests_container").html(xhr.responseText)
					}
				},
				error: function (xhr, status) {
					$("#requests_container").html("<span class='warning-bar'>Failed to load requested page</span>");
				}
			});
		});

		$('a.receiveItem, a.deliverItem').live('click', function (e) {
			if (!e.handled) {
				if ($(this).hasClass('receiveItem')) {
					Boxy.load("items/receive.php?req-id=" + $(this).data("id"));
				} else if ($(this).hasClass('deliverItem')) {
					Boxy.load("items/deliver.php?req-id=" + $(this).data("id"));
				}
				e.handled = true;
			}
		})
	})
</script>


