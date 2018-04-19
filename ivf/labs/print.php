<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/17/16
 * Time: 12:17 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$req = (new GeneticRequestDAO())->get($_REQUEST['id']);
$orientation = $req->getLab()->getPrintLayout();
$width = $orientation == "portrait" ? "900px" : "100%";
//$result=new GeneticLabResult();
$result=$req->getResult();
?>
<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
	<meta charset="UTF-8">
	
	<script src="/js/jquery-2.1.1.min.js"></script>
	<script src="/js/jquery-migrate-1.2.1.min.js"></script>
	<script src="/assets/jquery-print/jQuery.print.js" type="text/javascript"></script>
	<link href="/style/def.css" rel="stylesheet" type="text/css"/>
	<link href="/style/bootstrap.css" rel="stylesheet" type="text/css"/>
	<link href="/style/font-awesome.css" rel="stylesheet" type="text/css"/>
	<meta name="viewport" content="width=device-width">
	<style>
		.table-condensed > thead > tr > th, .table-condensed > tbody > tr > th, .table-condensed > tfoot > tr > th, .table-condensed > thead > tr > td, .table-condensed > tbody > tr > td, .table-condensed > tfoot > tr > td {
			padding: 2px !important;
		}
		
		.table {
			color: #000;
		}
		
		table, tr, td, th, tbody, thead, tfoot {
			page-break-inside: avoid !important;
		}
	</style>
</head>
<body>
<section style="width: <?=$width?>;margin: 0 auto">
	<table class="table table-striped">
		<tr><td>Request Code</td><td><?= $req->getRequestCode() ?></td> </tr>
		<tr><td>PGD Lab</td><td><?= $req->getLab()->getName() ?></td> </tr>
<tr><td>Female Patient</td><td><?= $req->getFemalePatient()->getFullname()?></td></tr>
<tr><td>Male Patient</td><td><?php if($req->getMalePatient()){?> <?= $req->getMalePatient()->getFullname()?><?php } else {?>N/A<?php }?></td></tr>

<tr><td colspan="2">Result</td></tr>
<tr><td colspan="2">
		<div class="clear"></div>
		<?php if($result!=null) {?>
			<div class="document">
				<?= $result->getNote()?>
			</div>
			<div class="clear fadedText pull-right">Entered by <strong><?= $result->getUser()->getUsername()?></strong> on <?= date(MainConfig::$dateTimeFormat, strtotime($result->getTimeEntered()))?></div>
			
			<?php if($req->getStatus()==='result_approved'){?><span class="alert-box notice">Report Approved </span><?php }?>
		<?php }?>
		<div class="clear"></div>
		<table class="table table-striped table-bordered_">
			<tr><th colspan="2">Reagent Lots Used</th><th></th></tr>
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
		<table class="table table-striped table-bordered_">
			<tr><th colspan="3">Quality Controls</th></tr>
			<tr><th width="60%">Control</th><th>By</th><th>Date</th></tr>
			<?php foreach ($req->getQualityControls() as $item) {//$item=new QualityControl()?>
				<tr><td><?= $item->getType()->getName() ?></td><td><?php if($item->getUser()){?><?= $item->getUser()->getUsername()?><?php } else {?><a href="javascript:" class="quality_control_action" data-id="<?=$item->getId()?>" data-action="<?= $item->getType()->getName()?>">Perform</a><?php }?></td><td><?= $item->getActionDate() ? date(MainConfig::$dateTimeFormat, strtotime($item->getActionDate())) : "N/A" ?></td></tr>
			<?php }?>
		</table>
	</td></tr>
<tr class="no-print"><td colspan="2"><?php if($req->getStatus()=="result_approved"){?><a href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&orientation=<?=$orientation?>" class="">Print</a><?php } else {?>Not Yet Approved<?php }?></td></tr>

</table>
</section>
</body>
</html>