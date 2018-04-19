<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 1/9/17
 * Time: 1:49 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/ItemBatchDAO.php';
$item = (new ItemDAO())->getItem($_GET['id']);
$batches = (new ItemBatchDAO())->getItemBatches($item);
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
?>

<section style="width: 700px;">
	<p>Available Batches For <em><?=$item->getName()?></em>
		<a class="pull-right" href="javascript:" onclick="Boxy.load('/pm/items/add_new_batch.php?it_id=<?=$_GET['id']?>', {title:'New Item Batch'})">New</a></p>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Batch #</th><th>Quantity Balance</th><th>Business Center</th><th>Expiration</th><th></th>
		</tr>
		</thead>
		<?php if(count($batches)>0){ foreach ($batches as $it) { ?>
			<tr>
				<td><?= $it->getName()?></td><td><?=$it->getQuantity()?></td><td><?= ($it->getServiceCenter() != NULL) ? $it->getServiceCenter()->getName() : '- -'?></td> <td><?= date("Y M, d", strtotime($it->getExpirationDate()))?></td>
				<td style="text-align: right;">
					<a href="javascript:" class="add_Stock" data-id="<?=$it->getId()?>">Add Stock</a> |
					 <a href="javascript:" class="adjust_Stock" data-id="<?=$it->getId()?>">Adjust Stock</a> | <a  href="javascript:" onclick="Boxy.load('/pm/items/edit_batch.php?bid=<?= $it->getId() ?>&&id=<?= $_GET['id'] ?>')">Edit</a>
				</td>
			</tr>
		<?php } } else {?>
			<tr>
				<td colspan="4"><div class="warning-bar">No batches exist for <em><?=$item->getName()?></em></div></td>
			</tr>
		<?php } ?>

	</table>
</section>

<script type="text/javascript">
	$(document).ready(function () {
   
		$('a.add_Stock').click(function () {
			$ID = $(this).data("id");
			Boxy.load('/pm/items/addToStock.php?batch='+$ID,{afterHide:function(){
				setTimeout(function () {
					Boxy.get($(".close")).hideAndUnload();
					setTimeout(function () {
						Boxy.load('/pm/items/batchMgt.php?id=<?=$_GET['id']?>');
					}, 100);
				}, 100);
			}});
		});

		$('a.adjust_Stock').click(function () {
			$ID = $(this).data("id");
			Boxy.load('/pm/items/adjust_item_batch.php?batch='+$ID,{afterHide:function(){
				setTimeout(function () {
					Boxy.get($(".close")).hideAndUnload();
					setTimeout(function () {
						Boxy.load('/pm/items/batchMgt.php?id=<?=$_GET['id']?>');
					}, 100);
				}, 100);
			}});
		});

	});

</script>
