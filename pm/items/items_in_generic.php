
<div style="width: 1000px">
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';


    $items = (new ItemGenericDAO())->get_items_by_generic($_GET['id']);
    $formatter_begin = '<span class="notify-bar">';
    $formatter_end = '</span>';
    $count = sizeof($items);
    ?>
    <div class="well well-small"><?= $count ?> Item<?php echo(($count == 1) ? '' : 's') ?> in Generic.
        <span class="pull-right"><a href="javascript:;" onclick="refreshTable()">Refresh</a></span>
    </div>
    <table class="table table-hover table-striped small">
        <thead>
        <tr>
            <th style="">Item Name</th>
            <th>Price</th>
            <th>Description</th>
            <th>Code</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php if (sizeof($items) > 0) {
            foreach ($items as $i => $d) { ?>
                <tr>
                <td nowrap style="width:20%"><?= $d ? $d->getItem()->getName() : '' ?></td>
                <td nowrap style="width:20%"><?= $d ? $d->getItem()->getBasePrice() : '' ?></td>
                <td nowrap style="width:20%"><?= $d->getItem()->getDescription() ?></td>
                <td nowrap style="width:20%"><?= $d->getItem()->getCode() ?></td>
                <td nowrap style="width: 15%">
                    <i class="icon-edit"></i><a href="javascript:;" class="boxylink" data-href="/pm/items/editItem.php?id=<?= ($d->getItem()->getId()) ?>" data-title="Edit <?= $d->getItem()->getName() ?>:">Edit</a></a>
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

    var refreshTable = function () {
        Boxy.get($(".close")).hideAndUnload();
        Boxy.load("/pm/items/items_in_generic.php", {
            afterShow: function () {
                $('.table.table-hover.table-striped.small').dataTable();
            }
        });
    }
</script>

