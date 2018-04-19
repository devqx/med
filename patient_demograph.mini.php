<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/6/17
 * Time: 11:39 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';

$all = isset($_GET['_a']) ? $_GET['_a'] : null;
$patient = (new PatientDemographDAO())->getPatient($ARR['patient_ID'], true, null, $all);
?>
<div class="card" style="display: block;">
	<div class="row-fluid">
		<div style="clear:none" class="span4">
			<div class="pull-left"><img class="passport" src="<?= $patient->getPassportPath(); ?>" width="53"/></div>
			<div>
				<h6 class="uppercase"><?= $patient->getFullname() ?></h6>
				<?php $badge = (new InsuranceSchemeDAO())->get($patient->getScheme()->getId())->getBadge() ?>
				<h6 class="pull-right" title="Badge"><?= $badge ? html_entity_decode($badge->getIcon()) : '' ?></h6>
			</div>
			<div class="item_block">
				<span>Sex</span>
				<span><?= ucwords($patient->getSex()) ?></span>
			</div>
		</div>
		<div style="clear:none" class="span6">
			
			<div class="item_block">
				<span>DOB</span>
				<span><?= date(MainConfig::$dateFormat, strtotime($patient->getDateOfBirth())) ?> (<em><?= $patient->getAge() ?></em>)</span>
			</div>
			<div class="item_block">
				<span>Insurance Status</span>
				<span<?= ((bool)!$patient->getInsurance()->getActive() ? ' class="abnormal" title="Insurance is not active"' : '') ?>><?= strtoupper($patient->getScheme()->getName()) . " (<em>" . strtoupper($patient->getScheme()->getType()) . "</em>)" ?></span>
			</div>
		</div>
	</div>
</div>

