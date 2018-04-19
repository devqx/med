<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/17/16
 * Time: 3:17 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$req = (new GeneticRequestDAO())->get($_REQUEST['id']);
//$result=new GeneticLabResult();
$result=$req->getResult();
?>
<section style="width:1000px">
	<table class="table table-striped">
		<tr><td>Request Code</td><td><?= $req->getRequestCode() ?></td> </tr>
		<tr><td>PGD Lab</td><td><?= $req->getLab()->getName() ?></td> </tr>
		<tr><td>Female Patient</td><td><a href="/patient_profile.php?id=<?= $req->getFemalePatient()->getId() ?>" target="_blank"><?= $req->getFemalePatient()->getFullname()?></a></td></tr>
		<tr><td>Male Patient</td><td><?php if($req->getMalePatient()){?> <a href="/patient_profile.php?id=<?= $req->getMalePatient()->getId() ?>" target="_blank"><?= $req->getMalePatient()->getFullname()?></a> <?php } else {?>N/A<?php }?></td></tr>
		
		<tr><td colspan="2">Result</td></tr>
		<tr><td colspan="2">
				<?php if($result==null) {?> <a href="javascript:" data-href="/ivf/labs/lab_result_add.php?request_id=<?= $req->getId() ?>" class="add_genetic_lab_result">Add Result</a><?php }?>
				<?php if($result!=null) {?> <a href="javascript:" data-href="/ivf/labs/lab_result_edit.php?request_id=<?= $req->getId() ?>" class="edit_genetic_lab_result">Edit Result</a><?php }?>
				<div class="clear"></div>
				<?php if($result!=null) {?>
					<div class="document">
						<?= $result->getNote()?>
					</div>
					<div class="clear fadedText pull-right">Entered by <strong><?= $result->getUser()->getUsername()?></strong> on <?= date(MainConfig::$dateTimeFormat, strtotime($result->getTimeEntered()))?></div>

					<?php if($req->getStatus()==='awaiting_review'){?><a href="javascript:" class="approveLink" data-id="<?= $req->getId()?>">Approve</a><?php }?>
					<?php if($req->getStatus()==='result_approved'){?><span class="alert-box notice">Approved</span><?php }?>
				<?php }?>
				<div class="clear"></div>
				<table class="table table-striped table-bordered">
					<tr><th colspan="2">Reagent Lots Used</th><th><a class="add_reagent" href="javascript:" data-id="<?= $req->getId() ?>">Add</a></th></tr>
					<?php if(count($req->getReagents()) == 0){?>
						<tr><td colspan="3"><div class="alert-box notice">No Reagents Used</div> </td></tr>
					<?php } else {?>
					<tr><th width="60%">Lot #</th><th>By</th><th>Date</th></tr>
					<?php foreach ($req->getReagents() as $item) {//$item=new ReagentUsed()?>
						<tr><td><?= $item->getReagent()->getName() ?> [<?= $item->getLotNumber() ?>]</td><td><?= $item->getUser()->getUsername()?></td><td><?= date(MainConfig::$dateTimeFormat, strtotime($item->getDate()))?></td></tr>
					<?php }?>
					<?php }?>
				</table>
				<div class="clear"></div>
				<table class="table table-striped table-bordered">
					<tr><th colspan="3">Quality Controls</th></tr>
					<tr><th width="60%">Control</th><th>By</th><th>Date</th></tr>
					<?php foreach ($req->getQualityControls() as $item) {//$item=new QualityControl()?>
						<tr><td><?= $item->getType()->getName() ?></td><td><?php if($item->getUser()){?><?= $item->getUser()->getUsername()?><?php } else {?><a href="javascript:" class="quality_control_action" data-id="<?=$item->getId()?>" data-action="<?= $item->getType()->getName()?>">Perform</a><?php }?></td><td><?= $item->getActionDate() ? date(MainConfig::$dateTimeFormat, strtotime($item->getActionDate())) : "N/A" ?></td></tr>
					<?php }?>
				</table>
			</td></tr>
		<tr><td colspan="2"><?php if($req->getStatus()=="result_approved"){?><a target="_blank" href="print.php?id=<?= $req->getId() ?>" class="">Print</a><?php } else {?>Not Yet Approved<?php }?></td></tr>
		
	</table>
</section>
<script type="text/javascript">
	$(document).on('click', '.add_genetic_lab_result', function (e) {
		var url = $(this).data("href");
		var title = $(this).data("heading");
		if(!e.handled){
			Boxy.load(url, {title: title});
			e.handled = true;
		}
	}).on('click', '.edit_genetic_lab_result', function (e) {
		var url = $(this).data("href");
		var title = $(this).data("heading");
		if(!e.handled){
			Boxy.load(url, {title: title});
			e.handled = true;
		}
	}).on('click', '.quality_control_action', function (e) {
		var id = $(this).data("id");
		var actionName = $(this).data("action");
		if(!e.handled){
			if(window.confirm("Are you sure to perform "+actionName+" on the request?")){
				$.post('/api/alter_request_quality_control.php', {id: id}, function (data) {
					if(data=="success"){
						Boxy.info(actionName + " action noted", function () {
							Boxy.get($(".close")).hideAndUnload(function () {
								setTimeout(function () {
									$('.openLink[data-id="'+id+'"]').get(0).click();
								}, 1000);
							});
						})
					} else if(data==="error"){
						Boxy.alert("Failed to acknowledge action");
					}
				})
			}
			e.handled = true;
		}
	}).on('click', '.add_reagent', function (e) {
		var id = $(this).data("id");
		if(!e.handled){
			Boxy.load("/ivf/labs/add_reagent.php?request_id="+id, {title: "Add Reagent Used", afterHide: function () {
				Boxy.get($(".close")).hideAndUnload(function () {
					/*setTimeout(function () {
						$('.openLink[data-id="'+id+'"]').get(0).click();
					}, 1000);*/
				});
			}});
			e.handled = true;
		}
	})
</script>
