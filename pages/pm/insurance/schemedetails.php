<?php
$id = $_GET['id'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceItemsCostDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/BillSourceDAO.php";
$items = (new InsuranceItemsCostDAO())->getItemCosts($id);
$schemeName=(new InsuranceSchemeDAO())->get($id)->getName();
$sources = (new BillSourceDAO)->getBillSources();
?>
<div style="width: 1000px">
	<div class="pull-left">Insured services for <?=htmlentities($schemeName)?></div>
	
	<div class="row-fluid pull-right clear">
		<form method="get" action="/excel.php">
			<select class="no-label span10" name="source_id" data-placeholder="Filter by Service Category">
				<option></option>
				<?php foreach ($sources as $source){ ?>
					<option value="<?= $source->getId()?>"><?= ucwords($source->getName()) ?></option>
				<?php }?>
			</select>
			<input type="hidden" name="dataSource" value="schemeItems">
			<input type="hidden" name="id" value="<?=$_GET['id']?>">
			<input type="hidden" name="filename" value="<?=htmlentities($schemeName)?>">
			<button type="submit" class="btn span2">Export as .CSV</button>
		</form>
	</div>
	
	<div class="clear">
		<div style="overflow-y:auto">
			<table class="table table-striped table-bordered">
				<thead>
				<tr>
					<th>Item Code</th>
					<th>Insurance Code</th>
					<th>Category</th>
					<th>Description</th>
					<th class="amount">Price</th>
					<th>Type</th>
					<th>Capitation?</th>
				</tr>
				</thead>
				<?php foreach ($items as $item) { ?>
					<tr>
						<td><?= $item->item_code ?></td>
						<td><?= $item->insurance_code ?></td>
						<td><?= ucwords(str_replace('_', ' ', $item->item_category)) ?></td>
						<td><?= $item->item_description ?></td>
						<td class="amount"><?= $item->selling_price ?></td>
						<td><?= ucwords($item->type) ?></td>
						<td><?= ((bool)$item->capitated) ? 'Capitated' : '--' ?></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>
</div>