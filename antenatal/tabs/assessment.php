<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/6/15
 * Time: 5:51 PM
 */

$instanceId = $_GET['aid'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalAssessmentDAO.php';
$data = (new AntenatalAssessmentDAO())->getAntenatalInstanceAssessments($instanceId);

$instance = (new AntenatalEnrollmentDAO())->get($instanceId);
$lmp = $instance->getLmpDate();
?>
<?php if ($instance->getActive()) { ?>
	<div class="menu-head">
		<div style="width:50%;float:left">
			<i class="icon-edit"></i>
			<a href="javascript:void(0)" onClick="Boxy.load('/antenatal/tabs/assessment.boxy.wizard.php?instance=<?= $_GET['aid'] ?>',{title:'New Assessment'})">New Assessment</a>
		</div>
	</div>
<?php } ?>
<p></p>
<?php if (count($data) == 0) { ?>
	<div class="notify-bar">No assessments available</div>
<?php } else { ?>
	<p></p>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Date</th>
			<th title="Gestational Age">G.A.</th>
			<th>Measurements</th>
			<th>FetalLie</th>
			<th>Presentation</th>
			<th>Comments</th>
			<th>By</th>
		</tr>
		</thead>
		<?php foreach ($data as $a) {//$a=new AntenatalAssessment();?>
			<tr>
				<td><a class="assess" data-id="<?= $a->getId() ?>" href="javascript:" title="View Assessment Data"><?= date(MainConfig::$dateTimeFormat, strtotime($a->getDate())) ?></a></td>
				<td> <?= weeksAndDays($lmp, $a->getDate())?> </td>
				<td><?php if($a->getFhr()){?>FHR: <?= $a->getFhr() ?><br><?php }?><?php if($a->getFundusHeight()){?>Fundus Height: <?= $a->getFundusHeight()?>cm<?php }?>
				<?= (!$a->getFhr() && !$a->getFundusHeight()) ? '--':''?>
				</td>
				<td><?= $a->getFetalLie() ? $a->getFetalLie(): '--'?></td>
				<td><?=$a->getFetalPresentation()?$a->getFetalPresentation()->getName():'--'?> <span class="tag"><?= $a->getFetalBrainRelationship() ? $a->getFetalBrainRelationship()->getName():'--'?></span> </td>
				<td><?= $a->getComments()?></td>
				<td><?= $a->getUser()->getFullname() ?></td>
			</tr>
		<?php } ?>
	</table>

<?php } ?>

<script>
	$(document).on('click', 'a.assess[data-id]', function (e) {
		if (!e.handled) {
			Boxy.load('/antenatal/tabs/assessmentDetails.php?id=' + $(this).data("id"), {title: 'Assessment Details'});
			e.handled = true;
		}
	});
</script>
