<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/18/16
 * Time: 1:05 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$instances = (new IVFEnrollmentDAO())->getInstances($_GET['pid'], FALSE);
?>
<section style="width: 720px">
	<div class="well">
		Recorded IVF instances
	</div>
	<div>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Enrollment Date</th>
				<th>Indication</th>
				<th>Status</th>
				<th>*</th>
			</tr>
			</thead>
			<?php foreach ($instances as $instance) {
				//$instance = new IVFEnrollment();
				$link = '/ivf/patients/patient_ivf_profile.php?id='.$instance->getPatient()->getId().'&aid='.$instance->getId();
				?>
				<tr>
					<td nowrap><?= date(MainConfig::$dateFormat, strtotime($instance->getDateEnrolled())) ?></td>
					<td><?= truncate($instance->getIndication(), 50, TRUE) ?></td>
					<td><?= (bool)$instance->getActive() ? 'Active' : 'Closed on '.date(MainConfig::$dateTimeFormat, strtotime($instance->getClosedDate())) ?> </td>
					<td>
						<a href="<?= $link ?>">Open</a>
					</td>
				</tr>
			<?php } ?>

		</table>
	</div>


	<div class="btn-block">
		<div class="pull-left"><a class="btn" href="javascript:;" onclick="Boxy.get(this).hideAndUnload()">Close</a></div>

	</div>
</section>
