<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/12/16
 * Time: 9:20 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalAssessmentDAO.php';
$a = (new AntenatalAssessmentDAO())->get($_GET['id']);
?>
<section style="width: 580px">
	<table class="table table-striped">
		<tr>
			<td colspan="2">Assessment By: <?= $a->getUser()->getUsername() ?> <br>
				on <?= date("y/m/d h:ia", strtotime($a->getDate())) ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th>Height of Fundus</th>
			<td><?= $a->getFundusHeight() ? $a->getFundusHeight() . 'cm' : '--' ?></td>
		</tr>
		<tr>
			<th>Fetal Heart Rate</th>
			<td><?= $a->getFhr() ? $a->getFhr() . 'bpm' : '--' ?></td>
		</tr>
		<tr>
			<th>Fetal Lie</th>
			<td><?= $a->getFetalLie() ? $a->getFetalLie() : '--' ?></td>
		</tr>
		<tr>
			<th>Presentation and Position of Foetus</th>
			<td><?= $a->getFetalPresentation() ? $a->getFetalPresentation()->getName() : '--' ?></td>
		</tr>
		<tr>
			<th>Relationship to Brim</th>
			<td><?= $a->getFetalBrainRelationship() ? $a->getFetalBrainRelationship()->getName() : '--' ?></td>
		</tr>
		<tr>
			<th>Lab</th>
			<td><?php if ($a->getLab()) { ?><a target="_blank" href="/labs/printLab.php?gid=<?= $a->getLab() ?>"><?= $a->getLab() ?></a><?php } else { ?>--<?php } ?></td>
		</tr>
		<tr>
			<th>Scan</th>
			<td>
				<?php if ($a->getScan()) {
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
					$scan = (new PatientScanDAO())->getScan($a->getScan()) ?>

					<a data-title="<?= $scan->getRequestCode() ?>: <?= $scan->getScan()->getName() ?>" class="boxy" href="javascript:;" data-id="<?= $a->getScan() ?>" data-href="/imaging/scan.details.php?id=<?= $a->getScan() ?>"><?= $scan->getRequestCode() ?></a>
				<?php } else { ?>
					--
				<?php } ?></td>
		</tr>
		<tr>
			<th valign="top">General Comments</th>
			<td><?= $a->getComments() ?></td>
		</tr>
		<tr>
			<th>Next Appointment</th>
			<td><?= date("Y M, d", strtotime($a->getNextAppointmentDate())) ?></td>
		</tr>
	</table>
</section>
<script>
	$(document).on('click', 'tr td a[data-title]', function (e) {
		if (!e.handled) {
			Boxy.load($(this).data("href"), {title: $(this).data("title")});
			e.handled = true;
			e.preventDefault();
		}
	});
</script>
