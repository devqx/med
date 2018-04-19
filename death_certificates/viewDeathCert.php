<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 7/12/16
 * Time: 12:28 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DeathDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
if (!isset($_SESSION)) {
	@session_start();
}
$death = (new DeathDAO())->get($_GET['id']);
$this_user = $death->getValidatedBy() ? (new StaffDirectoryDAO())->getStaff($death->getValidatedBy()->getId()):null;

$clinic = (new ClinicDAO())->getClinic(1);

if ($death !== null) { ?>
	<div class="container" style="width:990px; overflow: hidden; height:auto; padding:5px;  border: 10px solid #787878">
		<div class="row-fluid">
			<div style="width:auto; height:auto; padding:10px; text-align:center; border: 5px solid #787878">
				<div style="text-align: center; font-size: 28px; margin-top: 10px">Death Certificate</div>

				<div class="content" style="width: auto; line-height: 200%; height: auto; padding: 50px; font-size: medium; font-family: Helvetica Neue Light, HelveticaNeue-Light, Helvetica Neue, Calibri, Helvetica, Arial, sans-serif ">
					<p>
						That I have medically attended to
						<?= $death->getPatient()->getFullname() ?>
						who was born <?= date('d/m/Y', strtotime($death->getPatient()->getDateOfBirth()) ) ?>
						at <?= $death->getPatient()->getState()->getName() ?>
						in <?= $death->getPatient()->getNationality()->country_name ?>.<br>
						who was apparently aged <?= $death->getPatient()->getAge() ?>, that i last saw at <?= date('g:i A', strtotime($death->getTimeOfDeath())) ?> on the <?= date('d/m/Y', strtotime($death->getTimeOfDeath())) ?>
						was then suffering<br> from <?= $death->getDeathCausePrimary() ?>
						died as i am aware, or informed on <?= date('dS', strtotime($death->getTimeOfDeath())) ?>
						day of <?= date('F', strtotime($death->getTimeOfDeath())) ?>,
						at <?= date('g:i A', strtotime($death->getTimeOfDeath())) ?>
						and that the<br> cause of death was to the best of my knowledge and belief as herein stated viz:
						<?= $death->getDeathCausePrimary() ?>.
						<?php if ($death->getDeathCausePrimary() !== null) { ?>
					<p>Primary cause: <?= $death->getDeathCausePrimary() ?></p><?php } ?>,
					<?php if ($death->getDeathCauseSecondary() !== null) { ?>
						<p>Secondary cause: <?= $death->getDeathCauseSecondary() ?>.</p><?php } ?>
					<hr>
					<?php if($death->getValidatedBy() !== null){?>
						<p>Witness under my hand this <?= date('D ', strtotime($death->getTimeOfDeath())) . ' ' . date(' dS ', strtotime($death->getTimeOfDeath())) . ' day ' . date('F', strtotime($death->getTimeOfDeath())) . ',' . date(' Y', strtotime($death->getTimeOfDeath())) ?> </p>

						<p>Name: <?= $this_user->getFullname() ?> </p>
						<p>Qualification: <?= $this_user->getProfession() ?></p>
						<p>Address: <?= $clinic->getAddress() ?></p>
					<?php } else {?>
						<div class="warning-bar">Not Validated Yet</div>
					<?php }?>

				</div>
			</div>
		</div>
	</div>
<?php } ?>
