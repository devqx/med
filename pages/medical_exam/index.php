<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 5:11 PM
 */
if(!isset($_SESSION))@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] .'/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
?>
<div id="" class="mini-tab">
	<a class="tab" href="javascript:;" data-href="requests_open.php">Open Requests</a>
	<a class="tab approve" href="javascript:;" data-href="requests_to_approve.php">Approval List</a>
	<a class="tab on" href="javascript:;" data-href="requests_search.php">Search</a>
	<a class="tab pull-right" href="javascript:;" data-title="New Scan Request" data-href="request_new.php">New Request</a>
</div>
<div class="document">
	<div class="ball"></div>
</div>
<script type="text/javascript">
	$(document).on('click','.mini-tab > .tab', function(e){
		if(!e.handled){
			$('.mini-tab > .tab').removeClass('on');
			$(e.target).addClass('on');
			$('.mini-tab + .document').load('/medical_exam/'+$(e.target).data('href'), function(response, status){
				if(status==="error"){
					$('.mini-tab + .document').html('<div class="warning-bar">Page failed to load</div>');
				}
			});
			e.handled = true;
		}
	}).ready(function () {
		$('.mini-tab > .tab:first').click();
	});


</script>
