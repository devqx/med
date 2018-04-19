
<div style="width: 1000px">
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';

    $gens = (new ItemGenericDAO())->list_();
    $formatter_begin = '<span class="notify-bar">';
    $formatter_end = '</span>';
    $count = sizeof($gens);
    ?>
    <div class="well well-small"><?= $count ?> Generic<?php echo(($count == 1) ? '' : 's') ?> in store.
        <span class="pull-right"><a href="javascript:;" onclick="refresh()">Refresh</a></span>
        <a href="/pm/items/add_item_generic.php" class="boxy">Add Generic</a>
    </div>
    <table class="table table-hover table-striped small">
        <thead>
        <tr>
            <th style="">Generic Name</th>
            <th style="">Category</th>
            <th style="">Description</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php if (sizeof($gens) > 0) {
            foreach ($gens as $i => $d) {

                ?>
                <tr>
                <td nowrap style="width:20%"><?= $d->getName() ?></td>
                <td><?= $d->getCategory() ? $d->getCategory()->getName() : '--' ?></td>
                <td><?= $d->getDescription() ? $d->getDescription() : '--' ?></td>
                <td nowrap style="width: 15%">
                    <i class="icon-edit"></i><a href="javascript:;" class="boxylink" data-href="/pm/items/editGeneric.php?id=<?= ($d->getId()) ?>" data-title="Edit <?= $d->getName() ?>:">Edit</a></a>
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
        Boxy.load("/pm/items/item_generics.php", {
            afterShow: function () {
                $('.table.table-hover.table-striped.small').dataTable();
            }
        });
    }
</script>

