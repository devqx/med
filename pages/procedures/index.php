<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
@session_start();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->procedures)) {
	exit($protect->ACCESS_DENIED);
} ?>
<div class="mini-tab">
	<a class="tab on open" href="javascript:;" data-href="?open">Open Requests</a>
	<a class="tab scheduled" href="javascript:;" data-href="?scheduled">Scheduled</a>
	<a class="tab ongoing" href="javascript:;" data-href="?current">On-Going (Started)</a>
	<a class="tab my" href="javascript:;" data-href="?my">My Procedures</a>
	<a class="tab search" href="javascript:;" data-href="?search">Search ...</a>
	<a class="tab new pull-right" href="javascript:;" data-href="?new">New Request</a>
</div>
<div id="procedure_container"></div>

<script type="text/javascript">
	$(document).ready(function () {
		$('a.tab').live('click', function (e) {
			if (e.handled !== true) {
				$('a.tab').each(function () {
					$(this).removeClass('on');
				});
				$(this).addClass('on');
				//load the url specified
				$('#procedure_container').load($(this).data("href"), function (responseText, textStatus, req) {
				});
				e.handled = true;
			}
		})
	})
</script>