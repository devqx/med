<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/7/16
 * Time: 11:47 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
if (@$_GET['mode'] == "insurance") {
	$claims = (new ClaimDAO())->forScheme($_GET['sid'], $_GET['provider_id']);  ?>
	<table class="table table-striped table-bordered_">
		<thead>
		<tr>
			<th class="hide">Claim Id</th>
			<th>Date</th>
			<th>Patient</th>
			<th>Scheme</th>
			<th>Type</th>
			<th></th>
		</tr>
		</thead>
		<?php foreach ($claims as $claim) {// $claim=new Claim();?>
			<tr>
				<td class="hide"><?= $claim->getId() ?></td>
				<td><?= date("d/m/Y", strtotime($claim->getCreateDate())) ?></td>
				<td><?= $claim->getPatient()->getFullname() ?></td>
				<td><?= $claim->getScheme()->getType() ?></td>
				<td><?= $claim->getScheme()->getName() ?></td>
				<td>

					<div class="dropdown pull-right" >
						<button class="drop-btn dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
						<ul class="img dropdown-menu" role="menu" aria-labelledby="dLabel">
							<li>
								<a class="btn btn-small1" target="_blank" href="/billing/claims_sheet.php?id=<?= $claim->getId() ?>&type=<?= $claim->getType() ?>&reprint">Print As Claims</a>
							</li>
							<li>
								<a class="btn btn-small1" target="_blank" href="/billing/invoice_sheet.php?id=<?= $claim->getId() ?>&type=<?= $claim->getType() ?>&reprint">Print As Invoice</a>
							</li>
							<li>
								<a class="btn btn-small1" target="_blank" href="/billing/invoice_grouped.php?id=<?= $claim->getId() ?>&type=<?= $claim->getType() ?>&reprint">Print As Invoice group</a>
							</li>
							<li>
								<a class="btn btn-small1" target="_blank" href="/billing/claims_sheet.php?id=<?= $claim->getId() ?>&type=<?= $claim->getType() ?>&reprint&tabular">View-Print (Tabular)</a>
							</li>
							<li>
								<a class="edit_claim_link btn btn-small1" href="javascript:" data-href="/billing/boxy.claim_processing.php?id=<?= $claim->getId() ?>">Edit</a>
							</li>
						</ul>
					</div>
				
				</td>
			</tr>
		<?php } ?>
	</table>
<?php } else if (!isset($_GET['mode'])) {
	$claims = (new ClaimDAO())->forPatient($_GET['pid']); ?>
	<table class="table table-striped table-bordered_">
		<thead>
		<tr>
			<th class="hide">Claim Id</th>
			<th>Date</th>
			<th>Insurance Scheme</th>
			<th>Type</th>
			<th></th>
		</tr>
		</thead>
		<?php foreach ($claims as $claim) {// $claim=new Claim();?>
			<tr>
				<td class="hide"><?= $claim->getId() ?></td>
				<td><?= date("d/m/Y g:ia", strtotime($claim->getCreateDate())) ?></td>
				<td><?= $claim->getScheme()->getName() ?></td>
				<td><?= $claim->getScheme()->getType() ?></td>
				<td>

					<div class="dropdown pull-right" >
						<button class="drop-btn dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
						<ul class="img dropdown-menu" role="menu" aria-labelledby="dLabel">
							<li>
								<a class="btn btn-small1" target="_blank" href="/billing/claims_sheet.php?id=<?= $claim->getId() ?>&type=<?= $claim->getType() ?>&reprint">Print As Claim</a>
							</li>
							<li>
								<a class="btn btn-small1" target="_blank" href="/billing/invoice_sheet.php?id=<?= $claim->getId() ?>&type=<?= $claim->getType() ?>&reprint">Print As Invoice</a>
							</li>
							<li>
								<a class="btn btn-small1" target="_blank" href="/billing/invoice_grouped.php?id=<?= $claim->getId() ?>&type=<?= $claim->getType() ?>&reprint">Print As Invoice group</a>
							</li>
							
							<li>
								<a class="btn btn-small1" target="_blank" href="/billing/claims_sheet.php?id=<?= $claim->getId() ?>&type=<?= $claim->getType() ?>&reprint&tabular">View-Print (Tabular)</a>
							</li>
							<li>
								<a class="edit_claim_link btn btn-small1" href="javascript:" data-href="/billing/boxy.claim_processing.php?id=<?= $claim->getId() ?>">Edit</a>
							</li>
						</ul>
					</div>
				</td>
			</tr>
		<?php } ?>
	</table>
<?php } ?>

<script type="text/javascript">
	$(document).on('click', '.edit_claim_link', function (e) {
		if(!e.handled){
			Boxy.load( $(e.target).data('href') );
			e.handled = true;
		}
	})
</script>

