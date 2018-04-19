<div>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InsuranceBillableItemDAO.php';
$items = (new InsuranceBillableItemDAO())->getInsuranceBillableItems(TRUE);
?><br>
    <h6>All Billable Items</h6>
    <table class="table table-striped">
        <thead>
        <tr><th>Code</th><th>Name</th><th>Category</th></tr>
        </thead>
        <?php
foreach ($items as $item) {//$item = new InsuranceBillableItem();?>
    <tr>
        <td><?= $item->getItem()->getCode()?></td>
        <td><?= $item->getItemDescription() ?></td>
        <td><?= ucwords($item->getItemGroupCategory()->getName())?></td>
    </tr>
<?php }?></table>
</div>