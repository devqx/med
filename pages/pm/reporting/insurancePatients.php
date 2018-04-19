<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/11/14
 * Time: 6:09 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
$schemes = (new InsuranceSchemeDAO())->getInsuranceSchemes(false);

//$patients = (new InsuranceSchemeDAO())->getSchemePatients()
?>
<?php if (!isset($_GET['s'])) { ?>
	<div class="document">
		<h6><?= count($schemes) ?> schemes available</h6>
		<table id="list" class="table table-striped">
			<thead>
			<tr>
				<th>Scheme</th>
				<th>Type</th>
				<th>Patients covered</th>
			</tr>
			</thead>
			<?php foreach ($schemes as $s) {
				$patientsCount = (new InsuranceSchemeDAO())->getSchemePatientsCount($s->getId()) ?>
				<tr>
					<td>
						<i class="icon-group"></i>
						<a href="?s=<?= $s->getId() ?>"><?= $s->getName() ?></a>
					</td>
					<td><?= ucwords($s->getType()) ?></td>
					<td><?= ($patientsCount) ?>
						<span class="pull-right"><a href="/excel.php?dataSource=insurancePatientsList&scheme=<?=$s->getId()?>&filename=<?= urlencode($s->getName(). ' Patients')?>">Export</a></span>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
<?php } else { ?>
	<div class="document">
		<p><i class="icon-chevron-left"></i><a href="<?= $_SERVER['SCRIPT_NAME'] ?>">Back</a></p>
		<?php $s = (new InsuranceSchemeDAO())->get($_GET['s'], false); ?>
		<?php $patients = (new InsuranceSchemeDAO())->getSchemePatients($s->getId()) ?>
		<div class="well"><?= count($patients) ?> patients currently covered by &laquo;<?= $s->getName() ?>&raquo;
			<span class="pull-right"><a href="/excel.php?dataSource=insurancePatientsList&scheme=<?=$s->getId()?>&filename=<?= urlencode($s->getName(). ' Patients')?>">Export</a></span>
		</div>
		<table id="list" class="table table-striped">
			<thead>
			<tr>
				<th>Patient</th>
				<th>EMR</th>
				<th>Phone Number</th>
				<th>Enrollee Id</th>
			</tr>
			</thead><?php foreach ($patients as $p) { ?>
				<tr>
					<td><i class="icon-user"></i><?= (!$p->active ? '<s>' : '') ?><a <?= (!$p->active ? 'x' : '') ?>href="/patient_profile.php?id=<?= $p->PatientId ?>" target="_blank"><?= $p->PatientName; ?></a><?= (!$p->active ? '</s>' : '') ?></td>
					<td><?= $p->PatientId ?></td>
					<td><?= $p->Phone ?></td>
					<td><?= $p->EnrolleeNumber ?></td>
				</tr>
			<?php } ?>
		</table>
	</div>
<?php } ?>

<script type="text/javascript">
	$(document).ready(function () {
//  setTimeout(function () {
		$('#list').dataTable();
//   $('.dataTables_length select').select2();
//  },100);
	});
</script>