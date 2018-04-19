<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 11:03 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyItemDAO.php';
$items = (new OphthalmologyItemDAO())->all();?>
<ul class="list-blocks">
    <?php foreach ($items as $item) {?>
        <li class="tag"><?= $item->getName()?><span><a class="editItem" href="javascript:;" data-href="/pages/pm/ophthItemEdit.php?id=<?= $item->getId() ?>">Edit Item</a> | <a class="manageBatch" data-href="/pages/pm/ophthItemBatch.php?id=<?= $item->getId() ?>" href="javascript:">Manage Batches</a></span></li>
    <?php }?>
</ul>