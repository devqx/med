<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/24/14
 * Time: 4:38 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';

$drug = (new DrugDAO())->getDrug($_GET['id']);
$batches = (new DrugBatchDAO())->getDrugBatches($drug);
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

?>
<section style="width: 600px;">
	<p>Available Batches For <em><?= $drug->getName() ?></em>
		<a class="pull-right" href="javascript:;" id="addNewBatch">New</a></p>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Batch #</th>
			<th>Quantity Balance</th>
			<th>Pharmacy</th>
			<th>Expiration</th>
			<th></th>
		</tr>
		</thead>
		<?php if (count($batches) > 0) {
			foreach ($batches as $b) {//$b=new DrugBatch();?>
				<tr>
					<td><?= $b->getName() ?></td>
					<td><?= $b->getQuantity() ?></td>
					<td><?= ($b->getServiceCentre() != null) ? $b->getServiceCentre()->getName() : '- -' ?></td>
					<td><?= date("Y M, d", strtotime($b->getExpirationDate())) ?></td>
					<td style="text-align: right;">
						<a href="javascript:;" class="add_Stock" data-id="<?= $b->getId() ?>">Add</a>
						<?php if ($this_user->hasRole($protect->pharmacy_super)) { ?>| <a href="javascript:;" class="adjust_Stock" data-id="<?= $b->getId() ?>">Adjust</a><?php } ?>
						| <?php if ($this_user->hasRole($protect->pharmacy_super)) { ?>
							<a href="javascript:;" class="edit_Stock" data-id="<?= $b->getId() ?>">Edit</a>
					 <?php } ?>
					</td>
				</tr>
			<?php }
		} else { ?>
			<tr>
				<td colspan="5">
					<div class="warning-bar">No batches exist for <em><?= $drug->getName() ?></em></div>
				</td>
			</tr>
		<?php } ?>

	</table>
</section>

<script type="text/javascript">
	$(document).ready(function () {
		$('#addNewBatch').click(function () {
			Boxy.load('/pages/pm/pharmacy/batch.new.php?d_id=<?=$_GET['id']?>', {
				afterHide: function () {
					setTimeout(function () {
						Boxy.get($(".close")).hideAndUnload();
					}, 100);
				}
			});
		});

		$('a.add_Stock').click(function () {
			$ID = $(this).data("id");
			Boxy.load('/pages/pm/pharmacy/batch.add.php?batch=' + $ID, {
				afterHide: function () {
					setTimeout(function () {
						Boxy.get($(".close")).hideAndUnload();
						setTimeout(function () {
							Boxy.load('/pages/pm/pharmacy/batchMgt.php?id=<?=$_GET['id']?>');
						}, 100);
					}, 100);
				}
			});
		});
		$('a.adjust_Stock').click(function () {
			$ID = $(this).data("id");
			Boxy.load('/pages/pm/pharmacy/batch.adjust.php?batch=' + $ID, {
				afterHide: function () {
					setTimeout(function () {
						Boxy.get($(".close")).hideAndUnload();
						setTimeout(function () {
							Boxy.load('/pages/pm/pharmacy/batchMgt.php?id=<?=$_GET['id']?>');
						}, 100);
					}, 100);
				}
			});
		});
		
		$('a.edit_Stock').click(function () {
			$ID = $(this).data("id");
			Boxy.load('/pages/pm/pharmacy/batch.edit.php?batch=' + $ID, {
				afterHide: function () {
					setTimeout(function () {
						Boxy.get($(".close")).hideAndUnload();
						setTimeout(function () {
							Boxy.load('/pages/pm/pharmacy/batchMgt.php?id=<?=$_GET['id']?>');
						}, 100);
					}, 100);
				}
			});
		});
	})
</script>