<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/15/16
 * Time: 5:39 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = (new Protect());
@session_start();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false);
?>
<div id="report_container">
	<?php if($totalSearch > 0){?>
		<table class="table table-striped ">
			<thead>
			<tr>
				<th>Date</th>
				<th>Request #</th>
				<th>*</th>
				<?php if(!isset($_POST['pid']) && !isset($_GET['pid'])){?><th>Patient</th><?php }?>
				<th>Request By</th>
				<th>Status</th>
				<th>*</th>
			</tr>
			</thead>
			<?php foreach ($data->data as $request){//$request=new PatientMedicalReport();?>
				<tr>
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($request->getRequestDate())) ?></td>
					<td><a href="javascript:;" class="request_link_open" data-heading="<?=$request->getRequestCode()?>" data-id="<?= $request->getId() ?>"><?= $request->getRequestCode()?></a></td>
					<td><?= $request->getExam()->getName()?></td>
					<?php if(!isset($_POST['pid']) && !isset($_GET['pid'])){?><td><a href="javascript:;" class="profile" data-pid="<?=$request->getPatient()->getId()?>"><?=$request->getPatient()->getFullname()?></a></td><?php }?>
					<td><?= $request->getRequestBy()->getUsername()?></td>
					<td>
						<?php if((bool)$request->getCancelled()) { ?>
						<span>Cancelled</span>
						<?php } ?>
						<?php if((bool)$request->getApproved() && $request->getNotesCount() > 0){ ?>
							<span >Approved</span>
						<?php } ?>
						<?php if(!(bool)$request->getApproved()  && !(bool)$request->getCancelled()){ ?>
							<span >Open</span>
						<?php } ?>
					</td>
					
					<td>
						<div class="dropdown pull-right">
							<button class="drop-btn large dropdown-toggle" data-toggle="dropdown" style="padding:10px">
								Action
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
						<?php if(!(bool)$request->getCancelled() && $request->getNotesCount() == 0){ ?>
							<li><a href="javascript:" class="cancelM_RLink" data-id="<?= $request->getId() ?>">Cancel</a></li>
						<?php } ?>
						<?php if(!(bool)$request->getApproved() && $request->getNotesCount() > 0 && ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->medical_report_approver))){?>
							<li><a href="javascript:" class="approveM_RLink" data-id="<?= $request->getId() ?>">Approve</a><li/>  <?php }?>
					<?php if((bool)$request->getApproved() && $request->getNotesCount() > 0){?>
						<li><a href="javascript:" data-id="<?= $request->getId() ?>" class="printLink">Print</a></li>
					<?php }?>
							</ul>
						</div>
					</td>
				</tr>
			<?php }?>
		</table>
		<div class="<?= isset($pager) ? $pager :"list2"?> dataTables_wrapper no-footer">
			<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
			</div>
		</div>
	<?php } else {?>
		<div class="notify-bar">Nothing found</div>
	<?php }?>
</div>
<script type="text/javascript">
	$(document).on('click', '.approveM_RLink', function (evt) {
		if(!evt.handled){
			var id = $(evt.target).data("id");
			Boxy.ask("Approve this report?", ["Yes", "No"], function (choice) {
				if (choice === "Yes") {
					$.post('/medical_exam/ajax.approve.php', {id: id}, function (s) {
						if (s.trim() === "ok") {
							$('.mini-tab > a.tab.on').get(0); //.click();
						} else {
							Boxy.alert("An error occurred");
						}
					});
				}
			});
			evt.handled = true;
		}
	}).on('click', '.cancelM_RLink', function (evt) {
		if(!evt.handled){
			var id = $(evt.target).data("id");
			Boxy.ask("Cancel this report?", ["Yes", "No"], function (choice) {
				if (choice === "Yes") {
					$.post('/medical_exam/ajax.cancel.php', {id: id}, function (s) {
						if (s.trim() === "ok") {
							if($('.mini-tab > a.tab.on').length !== 0){
								$('.mini-tab > a.tab.on').get(0).click();
							} else {
								showTabs(19);
							}
						} else {
							Boxy.alert(s);
						}
					});
				}
			});
			evt.handled = true;
		}
	}).on('click', '.printLink', function (evt) {
		var id = $(evt.target).data("id");
		if(!evt.handled){
			window.open("/medical_exam/printNotes.php?id="+id);
			evt.handled = true;
		}
	});
	
	function reloadMedicalReportPage(page) {
		$.post('/medical_exam/requests_open.php', {'page':page}, function(s){
			$('#report_container').html(s);
		});
	}
</script>
