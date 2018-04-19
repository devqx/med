<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 9:38 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SpermPreparationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SpermAnalysisDAO.php';
$spermPrep = (new SpermPreparationDAO())->forInstance($_GET['aid']);
$spermAnalysis = (new SpermAnalysisDAO())->forInstance($_GET['aid']);
?>
<div class="menu-head"></div>
<div class="paper-card">
	<div class="heading">Sperm Preparation
		<a style="font-size: 14px" class="pull-right" href="javascript:" data-href="/ivf/profile/tabs/embryology/sperm-coll-new.php?aid=<?= $_GET['aid'] ?>" onclick="Boxy.load($(this).data('href'))">New
			Record</a></div>
	<div class="card-content">
		<?php if (count($spermPrep) == 0) { ?>
			No data available to display
		<?php } else { ?>
			<?php foreach ($spermPrep as $item) {//$item=new SpermPreparation();?>
				<div class="row-fluid divider">
					<div class="span12"><strong>Embryologist:</strong> <?= $item->getUser()->getFullname() ?></div>
				</div>
				<div class="row-fluid divider">
					<div class="span3"><strong class="block">Source</strong> <?= $item->getSource()->getName() ?></div>

					<div class="span3"><strong class="block">State</strong> <?= $item->getState()->getName() ?></div>
					<div class="span3"><strong class="block">Method of
							production</strong> <?= $item->getProcedure()->getName() ?></div>
					
					<div class="span3"><strong class="block">Donor Code</strong> <?= $item->getDonorCode() ?></div>
				</div>
				<div class="row-fluid divider">
					<div class="span3"><strong class="block">Abstinence:</strong> <?= $item->getAbstinenceDays() ?> days</div>
					<div class="span3"><strong class="block">Sample Collected
							on</strong> <?= date(MainConfig::$dateTimeFormat, strtotime($item->getCollectionDate())) ?></div>
					<div class="span3"><strong class="block">Sample produced
							on</strong> <?= date(MainConfig::$dateTimeFormat, strtotime($item->getProductionTime())) ?></div>
					
				</div>
				<div class="row-fluid divider">
					<div class="span3"><strong class="block">Sample Analysed
							on </strong><?= date(MainConfig::$dateTimeFormat, strtotime($item->getAnalysisTime())) ?></div>
					<div class="span9"><strong class="block">Method of preparation</strong> <?= $item->getPreparationMethod() ?>
					</div>
				</div>
				<div class="row-fluid divider">
					<div class="span12"><strong>Analysis Reports</strong></div>
				</div>
				<div class="row-fluid divider">
					<div class="span3">Pre</div>
					<div class="span9 fadedText"><?= $item->getPreAnalysisReport() ?></div>
				</div>
				<div class="row-fluid divider">
					<div class="span3">Post</div>
					<div class="span9 fadedText"><?= $item->getPreAnalysisReport() ?></div>
				</div>

				<table class="table table-bordered table-striped">
					<thead>
					<tr>
						<th>Witnesses:</th>
					</tr>
					</thead>
					<?php if (count($item->getWitnesses()) > 0) {
						foreach ($item->getWitnesses() as $witness) { ?>
							<tr>
								<td><?= $witness->getFullname() ?></td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr>
							<td>No witness available</td>
						</tr>
					<?php } ?>
				</table>
				<hr class="border">
			<?php } ?>
		<?php } ?>
	</div>
</div>

<div class="paper-card">
	<div class="heading">Analysis
		<a style="font-size:14px" class="pull-right" href="javascript:" data-href="/ivf/profile/tabs/embryology/treatment-new.php?aid=<?= $_GET['aid'] ?>" onclick="Boxy.load($(this).data('href'))">New
			Record</a></div>
	<div class="card-content">
		<?php if (count($spermAnalysis) == 0) { ?>No data available to display<?php } else { ?>
			<table class="table table-striped">
				<tr>
					<th>*</th>
					<th class="amount">Vol (ml)</th>
					<th class="amount">Cells</th>
					<th class="amount">Density</th>
					<th class="amount">Motility (%)</th>
					<th class="amount">PROG</th>
					<th class="amount">Abnormals (%)</th>
					<th class="amount">Mar</th>
					<th class="amount">Aggl</th>
				</tr>
				<?php foreach ($spermAnalysis as $i => $item) { //$item=new SpermAnalysis();?>
					<tr>
						<th><?= ($i+1) ?></th>
						<td class="amount"><?= $item->getVolume() ?></td>
						<td class="amount" data-decimals="0"><?= $item->getCellNo() ?></td>
						<td class="amount"><?= $item->getDensity() ?></td>
						<td class="amount" data-decimals="1"><?= $item->getMotility() ?> (%)</td>
						<td class="amount"><?= $item->getProg() ?></td>
						<td class="amount"><?= $item->getAbnormal() ?></td>
						<td class="amount"><?= $item->getMar() ?></td>
						<td class="amount"><?= $item->getAggl() ?></td>
					</tr>
				<?php } ?>
			</table>
			<?php foreach ($spermAnalysis as $i => $item) { ?>
				<table class="table table-bordered table-striped">
					<thead>
					<tr>
						<th>Witnesses:</th>
					</tr>
					</thead>
					<?php if (count($item->getWitnesses()) > 0) {
						foreach ($item->getWitnesses() as $witness) { ?>
							<tr>
								<td><?= $witness->getFullname() ?></td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr>
							<td>No witness available</td>
						</tr>
					<?php } ?>
				</table>
				<hr class="border">
			<?php } ?>
		<?php } ?>
	</div>
</div>
