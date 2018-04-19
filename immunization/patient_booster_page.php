<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/26/16
 * Time: 2:24 PM
 */
$id = $_GET['id'];
$boosters = (new PatientVaccineBoosterDAO())->getPatientVaccineBoosterByPatient($id, TRUE); ?>
	<table class="table table-striped" id="boosters">
		<?php if (count($boosters) > 0) { ?>
			<thead>
			<tr>
				<th>Vaccine</th>
				<th>Last Taken</th>
				<th>Next Due Date</th>
				<th>History</th>
				<th>Due</th>
			</tr>
			</thead><?php
			foreach ($boosters as $key => $b) {
				//$b = new PatientVaccineBooster();
				$history = (new VaccineBoosterHistoryDAO())->getHistory($b->getId()); ?>
				<tr>
					<td><?= $b->getVaccineBooster()->getVaccine()->getName() ?>
						(<?= $b->getVaccineBooster()->getVaccine()->getDescription() ?>)
					</td>
					<td><?= ($b->getLastTaken() != '' ? date(MainConfig::$dateFormat, strtotime($b->getLastTaken())) : '<span class="fadedText">Not yet administered</span>') ?></td>
					<td><?= date(MainConfig::$dateFormat, strtotime($b->getNextDueDate())) ?></td>
					<td><?= ((count($history) > 0) ? '<a href="javascript:;" class="boosterHistory" data-id="' . $b->getId() . '">History</a>' : '<span class="fadedText">No History</span>') ?></td>
					<td>
						<?php if(!$b->getCharged()){?><a href="javascript:" class="boosterCharge" data-id="<?= $b->getId() ?>">Charge</a><?php } else ?>
						<?php if($b->getCharged()){?><a href="javascript:;" class="dueNow" data-id="<?= $b->getId() ?>">Administer</a><?php }?>
					</td>
				</tr>
			<?php }
		} else { ?>
			<tr>
				<td>
					<div class="notify-bar">No booster vaccine records</div>
				</td>
			</tr>
		<?php } ?>
	</table>
<?php exit;