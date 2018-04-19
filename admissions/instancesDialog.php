<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/14/15
 * Time: 11:49 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
$admInstances = (new InPatientDAO())->getInPatientInstancesSlim($_GET['pid'], TRUE);
?>
<section style="width: 720px">
	<div class="well">
		Recorded Admission instances
	</div>
	<div>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Admission Date</th>
				<th>Reason</th>
				<th>Status</th>
				<th>*</th>
			</tr>
			</thead>
			<?php foreach ($admInstances as $adm) {
				//$adm = new InPatient();
				$link = '/admissions/inpatient_profile.php?pid=' . $adm->patient_id . '&aid=' . $adm->id;
				$summary = '/admissions/inpatient_summary.php?pid=' . $adm->patient_id . '&aid=' . $adm->id;
				?>
				<tr>
					<td nowrap><?= date("d M, Y", strtotime($adm->date_admitted)) ?></td>
					<td><?= truncate($adm->reason, 50, true) ?></td>
					<td><?= $adm->status ?><?= ($adm->status == 'Discharged' ? ' on ' . date("d M, Y", strtotime($adm->date_discharged)) : '') ?> </td>
					<td>
						<a href="<?= $link ?>">Open</a> |
						<a href="<?= $summary ?>" target="_blank">Print Summary</a>
					</td>
				</tr>
			<?php } ?>

		</table>
	</div>


	<div class="btn-block">
		<div class="pull-left"><a class="btn" href="javascript:;" onclick="Boxy.get(this).hideAndUnload()">Close</a></div>

	</div>
</section>
