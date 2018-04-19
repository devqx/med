<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/3/15
 * Time: 5:35 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

?>
<section>
	<div>
		<?php $DATA = (new CostCenterDAO())->all(); ?>
		<p class="clear">&nbsp;</p>
		<h5>Existing Cost Centres <button href="javascript:" class="drop-btn addBtnCostCentre pull-right">Add Cost Centre</button></h5>
		<p class="clear"></p>
		<table class="table table-hover table-striped">
			<thead>
			<tr>
				<th>Name</th>
				<th>Code</th>
				<th>Description</th>
				<th>*</th>
			</tr>
			</thead>
			<?php foreach ($DATA as $csc) {//$csc=new CostCenter();?>
				<tr>
					<td><?= $csc->getName() ?></td>
					<td><?= $csc->getAnalyticalCode() ?></td>
					<td><?= $csc->getDescription() ?></td>
					<td><a href="javascript:" class="editCentreBtn" data-href="/pages/pm/costCentreEdit.php?id=<?= $csc->getId() ?>">Edit</a></td>
				</tr>
			<?php } ?>
		</table>
	</div>
</section>
<script type="text/javascript">
	$(document).on('click', '.addBtnCostCentre', function (e) {
		if (!e.handled) {
			Boxy.load('/pages/pm/costCentreAdd.php', {title: 'New Cost Centre'});
			e.handled = true;
		}
	}).on('click', '.editCentreBtn', function (e) {
		if (!e.handled) {
			Boxy.load($(e.target).data('href'), {title: 'New Cost Centre'});
			e.handled = true;
		}
	});

	function loadCostCentres() {
		showTabs(5);
		$('#creator').load('/pages/pm/costCentres.php', function () {
			$(this).find('table').dataTable();
		}).attr('data', "costCentres");
		$('span.error').html("");
	}
</script>
