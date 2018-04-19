<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/14/16
 * Time: 1:11 PM
 */


require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AllergenCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SuperGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAllergensDAO.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->pharmacy)) {
	exit($protect->ACCESS_DENIED);
}
$data = (new PatientAllergensDAO())->forPatient(escape($id));

if (count($data) < 1) {?>
<div class="well">This patient does not have any recorded allergens</div>
<?php } else {?>
	<table class="table table-hover table-striped"><thead>
		<tr>
			<th>Date</th>
			<th>Category</th>
			<th>Drug</th>
			<th>Allergen</th>
			<th>Reaction</th>
			<th>Severity</th>
			<th>Noted By</th>
		</tr>
		</thead>
	<?php foreach ($data as $datum){//$datum=new PatientAllergens();?>
		<tr>
			<td><?= date(MainConfig::$dateTimeFormat, strtotime($datum->getDateNoted()))?></td>
			<td><?= $datum->getCategory() ? $datum->getCategory()->getName() : 'N/A'?></td>
			<td><?= $datum->getSuperGeneric() ? $datum->getSuperGeneric()->getName() : '- -' ?></td>
			<td><?= $datum->getAllergen() ? ucfirst($datum->getAllergen()) : '- -' ?></td>
			<td><?= $datum->getReaction()?></td>
			<td><?= $datum->getSeverity()?></td>
			<td><?= $datum->getNotedBy() ? $datum->getNotedBy()->getFullname(): '- -'?></td>
		</tr>
	<?php }?></table>
<?php }?>