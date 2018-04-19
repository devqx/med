<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/7/18
 * Time: 1:05 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFAnalysisTemplatesDAO.php';
$data = (new IVFAnalysisTemplatesDAO())->all();
?>
	<div class="text-right clearfix clear">
		<a href="javascript:" class="action newBtn" data-href="new/new_analysis_template.php">New Template</a>
	</div>
<?php if (count($data) < 1) { ?>
	<div class="alert-box notice">No Analysis Template configured</div>
<?php } else { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Template Name</th>
			<th>Type</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data as $item) { ?>
			<tr>
				<td><?= $item->getName() ?></td>
				<td><?= $item->getType() ?></td>
				<td><a class="edit" href="javascript:" data-href="/ivf/pm/edit/edit_analysis_templates.php?id=<?= $item->getId() ?>">Edit</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>