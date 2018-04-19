<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 6/15/15
 * Time: 5:15 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NurseReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';

$date = @$_REQUEST['date'];
$scheme = @$_REQUEST['scheme'];
$type = @$_REQUEST['type'];
$view = array();
$meta = @$_REQUEST['meta'];
if ($type == 'visits') {
	$view = (new NurseReportDAO())->getVisitsByMeta($meta);
	//    $view = (new NurseReportDAO())->getVisitsByDate($date, $scheme);
}
if ($type == 'enrollments') {
	$view = (new NurseReportDAO())->getEnrollmentsByMeta($meta);
	//    $view = (new NurseReportDAO())->getEnrollmentsByDate($date, $scheme);
}
?>
<div style="width:800px;">
	<h4><?= ($type == 'visits') ? 'Visits' : '' ?><?= ($type == 'enrollments') ? 'Enrollments' : '' ?> Report for <?= date("D, d M, Y", strtotime($date)) ?></h4>
	<?php if ($type == 'visits') { ?>
		<h5><?= sizeof($view) ?> records</h5>
		<table class="rpt_table table table-bordered table-hover" id="visits_table">
			<thead>
			<tr>
				<th>Insurance Program</th>
				<th>Patient</th>
				<th>EMR</th>
				<th>Age</th>
				<th>Phone</th>
			</tr>
			</thead>
			<?php if (sizeof($view) > 0) {
				foreach ($view as $d) {
					?>
					<tr>
						<td nowrap><?= $d->getScheme()->getName() ?></td>
						<td nowrap><?= $d->getPatient()->getFullname() ?></td>
						<td nowrap><?= $d->getPatient()->getId() ?></td>
						<td nowrap><?= $d->getPatient()->getAge() ?></td>
						<td nowrap><?= $d->getPatient()->getPhoneNumber() ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="5"><span class="warning-bar">No <?= $scheme ?> patient were seen on this day</span></td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>
	<?php if ($type == 'enrollments') { ?>
		<h5><?= sizeof($view) ?> records</h5>
		<table class="rpt_table table table-bordered table-hover" id="enroll__">
			<thead>
			<tr>
				<th>Insurance Program</th>
				<th>Patient</th>
				<th>EMR</th>
				<th>Age</th>
				<th>Phone</th>
			</tr>
			</thead>
			<?php if (sizeof($view) > 0) {
				foreach ($view as $d) {
					?>
					<tr>
						<td nowrap><?= $d->getScheme()->getName() ?></td>
						<td nowrap><?= $d->getPatient()->getFullname() ?></td>
						<td nowrap><?= $d->getPatient()->getId() ?></td>
						<td nowrap><?= $d->getPatient()->getAge() ?></td>
						<td nowrap><?= $d->getPatient()->getPhoneNumber() ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="5"><span class="warning-bar">No <?= $scheme ?> patients were seen today</span></td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>
</div>
<script>
	$(document).ready(function () {
		$(".rpt_table").dataTable();
	});
</script>