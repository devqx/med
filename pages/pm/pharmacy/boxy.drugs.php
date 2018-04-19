<div style="width: 1000px">
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';

	$drugs = (new DrugDAO())->getDrugs();
	if (isset($_GET['type']) && $_GET['type'] == 'generic') {
		$drugs = (new DrugDAO())->getDrugsByGeneric($_GET['Id']);
	} else if (isset($_GET['type']) && $_GET['type'] == 'category') {
		$drugs = (new DrugDAO())->getDrugsByCategory($_GET['Id']);
	}
	$formatter_begin = '<span class="notify-bar">';
	$formatter_end = '</span>';
	$count = sizeof($drugs);
	?>
	<div class="well well-small"><?= $count ?> drug<?php echo(($count == 1) ? '' : 's') ?> in store.
		<span class="pull-right"><a href="javascript:;" onclick="refreshDrugsBoxy()">Refresh</a></span>
		<a href="/pages/pm/pharmacy/add_drugs.php" class="boxy">Add Drug</a>
	</div>
	<table class="table table-hover table-striped small">
		<thead>
		<tr>
			<th style="">Drug Name</th>
			<th style=""><?= (isset($_GET['type']) && $_GET['type'] == 'generic') ? $formatter_begin : '' ?>
				Generic Name<?= (isset($_GET['type']) && $_GET['type'] == 'generic') ? $formatter_end : '' ?></th>
			<th>Pharmacy</th>
			<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<?php if (sizeof($drugs) > 0) {
			foreach ($drugs as $i => $d) {
				$sc = array();
				foreach ($d->getBatches() as $batch) {
					if ($batch->getServiceCentre() != null) {
						$sc[] = $batch->getServiceCentre()->getName();
					}
				}
				?>
				<tr>
				<td nowrap style="width:20%"><?= $d->getName() ?></td>
				<td><?= $d->getGeneric()->getName() ?>, <?= $d->getGeneric()->getWeight() ?> <?= $d->getGeneric()->getForm() ?></td>
				<td><?= implode(', ', array_unique($sc)) ?></td>
				<td nowrap style="width: 15%">
					<i class="icon-edit"></i><a href="javascript:;" class="boxylink" data-href="/pages/pm/pharmacy/editdrug.php?id=<?= ($d->getId()) ?>" data-title="Edit <?= $d->getName() ?>:">Edit</a>
					<i class="icon-edit"></i><a href="javascript:;" class="boxylink" data-href="/pages/pm/pharmacy/batchMgt.php?id=<?= ($d->getId()) ?>" data-title="Manage <?= $d->getName() ?> Batch:">Manage
						batches</a>
				</td>
				</tr><?php }
		} else { ?>
			<tr>
				<td colspan="10"><span class="notify-bar">No drugs to show in this view</span></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('.table.table-hover.table-striped.small').dataTable();
		$(document).on('click', 'a.boxylink', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data('href'), {title: $(this).attr('title') || $(this).data('title')});
				e.handled = true;
			}
		});
	});

	var refreshDrugsBoxy = function () {
		Boxy.get($(".close")).hideAndUnload();
		Boxy.load("/pages/pm/pharmacy/boxy.drugs.php", {
			afterShow: function () {
				$('.table.table-hover.table-striped.small').dataTable();
			}
		});
	}
</script>