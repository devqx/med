
<div style="width: 1000px">
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';

	$items = (new ItemDAO())->getItems();
	$formatter_begin = '<span class="notify-bar">';
	$formatter_end = '</span>';
	$count = sizeof($items);
	?>
<div class="well well-small"><?= $count ?> Item<?php echo(($count == 1) ? '' : 's') ?> in store.
    <span class="pull-right"><a href="javascript:;" onclick="refresh()">Refresh</a></span>
    <a href="/pm/items/addItem.php" class="boxy">Add Item</a>
</div>
<table class="table table-hover table-striped small">
    <thead>
    <tr>
        <th style="">Item Name</th>
        <th style=""> Generic Name</th>
        <th>Description</th>
        <th>Batches</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php if (sizeof($items) > 0) {
        foreach ($items as $i => $d) {
            $bc = array();
            foreach ($d->getBatches() as $batch) {
                    $bc[] = $batch->getName();
            }
            ?>
            <tr>
            <td nowrap style="width:20%"><?= $d->getName() ?></td>
            <td><?= $d->getGeneric() ? $d->getGeneric()->getName() : '--' ?></td>
            <td><?= $d->getDescription() ? $d->getDescription() : '--' ?></td>
            <td><?= implode(', ', array_unique($bc)) ?></td>
            <td nowrap style="width: 15%">
                <i class="icon-edit"></i><a href="javascript:;" class="boxylink" data-href="/pm/items/editItem.php?id=<?= ($d->getId()) ?>" data-title="Edit <?= $d->getName() ?>:">Edit</a>
                <i class="icon-edit"></i><a href="javascript:;" class="boxylink" data-href="/pm/items/batchMgt.php?id=<?= ($d->getId()) ?>" data-title="Manage <?= $d->getName() ?> Batch:">Manage
                    batches</a>
            </td>
            </tr><?php }
    } else { ?>
        <tr>
            <td colspan="10"><span class="notify-bar">No Item to show in this view</span></td>
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

    var refresh = function () {
        Boxy.get($(".close")).hideAndUnload();
        Boxy.load("/pm/items/boxy_items.php", {
            afterShow: function () {
                $('.table.table-hover.table-striped.small').dataTable();
            }
        });
    }
</script>

