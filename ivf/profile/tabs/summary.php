<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/19/16
 * Time: 2:51 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
$instance = (new IVFEnrollmentDAO())->get($_GET['aid'], TRUE);
?>
<div class="menu-head">
	<span class="pull-right"><a target="_blank" href="summary.php?aid=<?= $_GET['aid'] ?>&view=print">Print</a></span>
</div>

<div class="paper-card">
	<div class="heading fadedText">Wife's Labs Details</div>
	<div class="card-content">
		<table class="table table-striped">
			<tr><td width="25%">FSH: </td><td width="25%"><?= !is_blank($instance->getHormone()['fsh']) ? $instance->getHormone()['fsh']: 'N/a'?></td><td width="25%">LH: </td><td><?= $instance->getHormone()['lh']?></td></tr>
			<tr><td width="25%">PROL: </td><td width="25%"><?= !is_blank($instance->getHormone()['prol']) ? $instance->getHormone()['prol']:'N/a'?></td><td>AMH: </td><td><?= $instance->getHormone()['amh']?></td></tr>
		</table>
		<table class="table table-striped">
			<tr><td width="25%">HIV:</td><td width="25%"><?= $instance->getSerology()['hiv'] ?></td><td width="25%">Hep B:</td><td width="25%"><?= $instance->getSerology()['hep_b'] ?></td></tr>
			<tr><td width="25%">Hep C:</td><td><?= $instance->getSerology()['hep_c'] ?></td><td>VDRL:</td><td><?= $instance->getSerology()['vdrl'] ?></td></tr>
			<tr><td width="25%">Chlamydia:</td><td><?= $instance->getSerology()['chlamydia'] ?></td><td></td><td></td></tr>
			<tr><td width="25%">Genotype:</td><td><?= $instance->getPatient()->getBloodType()?></td><td>Blood Group:</td><td><?= $instance->getPatient()->getBloodgroup() ?></td></tr>
		</table>
	</div>
</div>

<div class="paper-card">
	<div class="heading fadedText">Husband's Labs Details</div>
	<div class="card-content">
		<table class="table table-bordered table-striped">
			<tr><td>FSH: </td><td><?= $instance->getHusbandHormone()['fsh']?></td><td>LH: </td><td><?= $instance->getHusbandHormone()['lh']?></td></tr>
			<tr><td>PROL: </td><td><?= $instance->getHusbandHormone()['prol']?></td><td>Testosterone: </td><td><?= $instance->getHusbandHormone()['testosterone']?></td></tr>
		</table>
		<table class="table  table-striped">
			<tr><td colspan="6">SFA</td></tr>
			<tr><td>Count:</td><td><?= $instance->getSfa()['count']?></td><td>Motility:</td><td><?= $instance->getSfa()['motility']?></td><td>Morphology:</td><td><?= $instance->getSfa()['morphology']?></td></tr>
		</table>
		<table class="table table-striped">
			<tr><td>HIV:</td><td><?= $instance->getHusbandSerology()['hiv'] ?></td><td>Hep B:</td><td><?= $instance->getHusbandSerology()['hep_b'] ?></td></tr>
			<tr><td>Hep C:</td><td><?= $instance->getHusbandSerology()['hep_c'] ?></td><td>VDRL:</td><td><?= $instance->getHusbandSerology()['vdrl'] ?></td></tr>
			<tr><td>RBS:</td><td><?= $instance->getHusbandSerology()['rbs'] ?></td><td>FBS:</td><td><?= $instance->getHusbandSerology()['fbs'] ?></td></tr>
			<tr><td>Genotype:</td><td><?= $instance->getHusband()? $instance->getHusband()->getBloodType():'- -'?></td><td>Blood Group:</td><td><?= $instance->getHusband() ? $instance->getHusband()->getBloodgroup() :'- -' ?></td></tr>
		</table>
	</div>
</div>

<div class="paper-card">
	<div class="heading">Booking</div>
	<div class="card-content">
		<table class="table table-striped">
			<tr><td>Assessment</td><td class="fadedText"><?= $instance->getIndication()?></td></tr>
			<tr><td>Package on enrollment</td><td class="fadedText"><?= $instance->getPackage()->getName()?></td></tr>
		</table>
	</div>
</div>

<div class="paper-card">
	<div class="heading">Treatment Details</div>
	<div class="card-content">
		<table class="table table-striped">
			<tr><td>Cycle Month/Year</td><td><?= $instance->getStimulation()['cycle'] ?></td></tr>
			<tr><td>LMP</td><td><?= date(MainConfig::$dateFormat, strtotime($instance->getStimulation()['lmp_date'])) ?></td></tr>
			<tr><td>Treatment Plan</td><td><?= $instance->getStimulation()['method']->getName()?></td></tr>
		</table>
	</div>
</div>

<div class="paper-card">
	<div class="heading">Long Protocol Stimulation</div>
	<div class="card-content">
		<table class="table table-bordered table-striped">
			<thead>
			<tr>
				<th>Action</th>
				<th>Date</th>
				<th>Remarks</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>Downreg (Scan on D21)</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Stimulation scan commences</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Last scan of stimulation</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Egg collection and semen preparation</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Embryo transfer</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Embryo cryo-preserved</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Embryo discarded</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Pregnancy test</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td colspan="3">Notes:</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
<?php exit ?>
<div class="paper-card">
	<div class="heading">Short Protocol</div>
	<div class="card-content">
		<table class="table table-bordered table-striped">
			<thead>
			<tr>
				<th>Action</th>
				<th>Date</th>
				<th>Remarks</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>Day 2 scan</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Last scan</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Egg collection and husband semen production</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Embryo transfer</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td>Pregnancy test</td>
				<td>N/a</td>
				<td>N/a</td>
			</tr>
			<tr>
				<td colspan="3">Notes:</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>