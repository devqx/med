<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';

$labs = (new PatientLabDAO())->getPatientSingleLabsByGroupCode($_GET['gid'], $_GET['id'], $_GET['lid'], TRUE);
$requestedBy = (new StaffDirectoryDAO())->getStaff($labs[0]->getLabGroup()->getRequestedBy()->getId());
$approvedlLabs = $approvedlBy = $referredBy = $approvedDate = array();
foreach ($labs as $l) {
	$referredBy[] = ($l->getLabGroup()->getReferral() != null) ? $l->getLabGroup()->getReferral()->getName() . " [" . $l->getLabGroup()->getReferral()->getCompany()->getName() . "]" : '-';
	if ($l->getLabResult() != null && $l->getLabResult()->isApproved()) {
		$approvedlLabs[] = $l->getLabResult()->isApproved();
		$approvedlBy[] = $l->getLabResult()->getApprovedBy()->getFullname();
		$approvedDate[] = $l->getLabResult()->getApprovedDate();
	}
}

$referredBy = array_unique($referredBy);
$approvedlLabs = array_unique($approvedlLabs);
$approvedlBy = array_unique($approvedlBy);

$clinic = (new ClinicDAO())->getClinic(1);


//group results by their categories
$sorted  = [];
foreach ($labs as $lab) {
	$name = $lab->getTest()->getCategory()->getName();
	
	if($lab->getTest()->getCategory()->getName()==$name){
		$sorted[$name][] = $lab;
	}
}

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
	
	<script src="/assets/blockUI/jquery.blockUI.js"></script>
	<script src="/assets/boxy/js/jquery.boxy.js"></script>
	<link rel="stylesheet" href="/assets/boxy/css/boxy.css">
	<script src="/assets/jquery-number-master/jquery.number.js"></script>
	<script type="text/javascript" src="/assets/select2_2/select2.min.js"></script>
	<link rel="stylesheet" href="/assets/select2_2/select2.css">
	<link href="/assets/blockUI/growl.ui.css" rel="stylesheet" type="text/css"/>
	
	<meta name="viewport" content="width=device-width">
	<style>
		.table-condensed > thead > tr > th, .table-condensed > tbody > tr > th, .table-condensed > tfoot > tr > th, .table-condensed > thead > tr > td, .table-condensed > tbody > tr > td, .table-condensed > tfoot > tr > td {
			padding: 2px !important;
		}
		
		.container {
			width: 900px;
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
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
$c = new Clinic(); ?>
<?= ($c::$useHeader) ? $clinic->getHeader() : '' ?>
<div class="container">
	<div style="text-align: center; font-size: 28px; margin-top: <?= ($c::$useHeader) ? 0 : 2 ?>30px">Laboratory Report
	</div>
	<br/>
	
	<table class="table table-bordered table-striped text-capitalize">
		<tr>
			<td>Patient's Name:</td>
			<td><?= $labs[0]->getPatient()->getFullname() ?></td>
			<td>Sex/Age:</td>
			<td><?= ucfirst($labs[0]->getPatient()->getSex()) ?>/<?= $labs[0]->getPatient()->getAge() ?></td>
		</tr>
		<tr>
			<td>Patient EMR:</td>
			<td><?= $labs[0]->getPatient()->getId() ?></td>
			<td>Nationality:</td>
			<td><?= ucfirst($labs[0]->getPatient()->getNationality()->country_name) ?></td>
		</tr>
		<tr>
			<td>Patient Phone:</td>
			<td><?= $labs[0]->getPatient()->getPhoneNumber() ?></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td>Coverage:</td>
			<td><?= $labs[0]->getPatient()->getScheme()->getType() == 'self' ? "Self Pay" : "Covered" ?>
				(<?= $labs[0]->getPatient()->getScheme()->getName() ?>)
			</td>
			<td>Request Date:</td>
			<td><?= date("d M, Y h:i A", strtotime($labs[0]->getLabGroup()->getRequestTime())) ?></td>
		</tr>
		<tr>
			<td>Request ID:</td>
			<td><?= $labs[0]->getLabGroup()->getGroupName() ?></td>
			<td>Approved By:</td>
			<td><?= ((count($approvedlLabs) > 0) ? @$approvedlBy[0] : '') ?></td>
		</tr>
		<tr>
			<td>Approved Date:</td>
			<td><?= date(MainConfig::$dateTimeFormat, strtotime(@$approvedDate[0])) ?></td>
			<td>Referred By:</td>
			<td><?= $referredBy[0] ?></td>
		</tr>
		<tr>
			<td>Tests Requested:</td>
			<td colspan="3"><?php
				$reqs = [];
				foreach ($labs as $lab) {
					$reqs[] = $lab->getTest()->getName();
				}
				echo implode(", ", $reqs);
				?></td>
		</tr>
	</table>
	
	<ul>
		<?php foreach ($sorted as $i=>$labs){?>
			<li><?= $i?></li>
		<?php }?>
	</ul>
	
	<?php if (isset($_GET['mode']) && $_GET['mode'] == 'single') { ?>
		<?php foreach ($labs as $lab) {
			if ( $lab->getId() == $_GET['lid']) { ?>
				<div class="box tight" style="background: rgba(221, 221, 221, 0.31);">
					<div class="row-fluid">
						<div class="span12">
							<h2><?=($lab->getTest()->getCategory()->getName())?></h2>
						</div>
					</div>
				</div>
				<div class="box tight">
					<div class="row-fluid" style="background: rgba(221, 221, 221, 0.31);">
						<div class="span4">
							<?= "<strong>" . $lab->getTest()->getName() . "</strong>" . (($lab->getLabResult() === null) ? " Result is Not Ready" : "") ?>
						</div>
						<div class="span4">Specimen:</div>
						<div class="span4"><?php
							$spc = [];
							foreach ($lab->getSpecimens() as $spe) {
								$spc[] = $spe->getName();
							}
							echo implode(", ", $spc);
							?></div>
					</div>
				</div>
				
				<div class="box tight">
					<div class="row-fluid">
						<?php if ($lab->getLabResult() !== null) { ?>
							<div class="span4"><strong>- - -</strong></div>
							<div class="span4"><strong><u>Result</u></strong></div>
							<div class="span4"><strong><u>Reference</u></strong></div>
						<?php } ?>
					</div>
				</div>
				<?php
				if ($lab->getLabResult() !== null && $lab->getLabResult()->isApproved()) {
					foreach ($lab->getLabResult()->getData() as $result) {
						?>
						<div class="box tight">
							<div class="row-fluid">
								<div class="span4"><?= $result->getLabTemplateData()->getMethod()->getName() ?></div>
								<div class="span4"><?= $result->getValue() ?></div>
								<div class="span4"><?= $result->getLabTemplateData()->getReference() ?></div>
							</div>
						</div>
						<?php
					}
				}
				?>
				<!--</div>-->
				<div class="clear"></div>
			<?php } ?>
		<?php } ?>
	<?php }
	else { ?>
		<?php foreach ($sorted as $i=>$labs){?>
			<div class="box tight" style="background: rgba(221, 221, 221, 0.31);">
				<div class="row-fluid">
					<div class="span12 underline_">
						<h2 class=""><?= strtoupper($i)?></h2>
					</div>
				</div>
			</div>
			<?php foreach ($labs as $lab) { ?>
				<div class="box tight" style="background: rgba(221, 221, 221, 0.31);">
					<div class="row-fluid">
						<div class="span4 underline_">
							<?= "<strong>" . strtoupper($lab->getTest()->getName()) . "</strong>" . (($lab->getLabResult() === null) ? " <br class='clear'>Result is Not Ready" : "") ?>
						</div>
						<div class="span4">Specimen:</div>
						<div class="span4"><?php
							$spc = [];foreach ($lab->getSpecimens() as $spe) {
								$spc[] = $spe->getName();
							}
							echo implode(", ", $spc); ?>
						</div>
					</div>
				</div>
				
				<div class="box tight">
					<div class="row-fluid">
						<?php if ($lab->getLabResult() !== null) { ?>
							<div class="span4"><strong>- - -</strong></div>
							<div class="span4"><strong><u>Result</u></strong></div>
							<div class="span4"><strong><u>Reference</u></strong></div>
						<?php } ?>
					</div>
				</div>
				<?php if ($lab->getLabResult() !== null && $lab->getLabResult()->isApproved()) {
					foreach ($lab->getLabResult()->getData() as $result) {
						?>
						<div class="box tight">
							<div class="row-fluid">
								<div class="span4"><?= $result->getLabTemplateData()->getMethod()->getName() ?></div>
								<div class="span4"><?= $result->getValue() ?></div>
								<div class="span4"><?= $result->getLabTemplateData()->getReference() ?></div>
							</div>
						</div>
						<?php
					}
				} ?>
				<!--        </div>-->
			<?php } ?>
			<div class="clear"></div>
		<?php } ?>
	<?php } ?>
	
	<?php if (count($approvedlLabs) > 0) { ?>
		<div class="block clearfix" style="margin-top:100px;">
			<span class="pull-right">VERIFIED BY: <?= $approvedlBy[0] ?></span>
		</div>
	
	<?php } ?>
	
	<div class="pull-right no-print" style="margin-bottom: 20px">
		<!--<a href="javascript:Print();" class="action" title="Print this Lab Result">
				<i class="icon-print"></i> Print</a>-->
		<a
			href="javascript:"
			onclick="printSettings(this)"
			data-href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($labs[0]->getLabGroup()->getGroupName()) ?>" class="action"><i class="icon-book"></i>
			PDF</a>
	</div>
</div>
</body>
<script>
	var printSettings = function(e){
		Boxy.load('/print.dialog.php?url='+ encodeURIComponent($(e).data('href')),{});
		//console.log(e);
	};

	$(document).on('keydown', function (e) {
		if (e.ctrlKey && (e.key === "p" || e.charCode === 16 || e.charCode === 112 || e.keyCode === 80)) {
			alert("Please use the Print PDF button below for a better rendering on the document");
			e.cancelBubble = true;
			e.preventDefault();

			e.stopImmediatePropagation();
		}

	});
</script>
</html>