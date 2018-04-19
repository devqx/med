
<div style="width: 1000px">
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';

    $gens = (new ItemGroupDAO())->getItemGroups();
    $formatter_begin = '<span class="notify-bar">';
    $formatter_end = '</span>';
    $count = sizeof($gens);
    ?>
    <div class="well well-small"><?= $count ?> Group<?php echo(($count == 1) ? '' : 's') ?> in store.
        <span class="pull-right"><a href="javascript:;" onclick="refreshGroup()">Refresh</a></span>
        <a href="/pm/items/add_new_item_group.php" class="boxy">Add Group</a>
    </div>
    <table class="table table-hover table-striped small">
        <thead>
        <tr>
            <th style="">Group Name</th>
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
                <td><?= $d->getDescription() ? $d->getDescription() : '--' ?></td>
                <td nowrap style="width: 15%">
                    <i class="icon-edit"></i><a href="javascript:;" class="boxylink" data-href="/pm/items/edit_group.php?id=<?= ($d->getId()) ?>" data-title="Edit <?= $d->getName() ?>:">Edit</a> |
                    <a class="boxy" href="/pm/items/add_group_data.php?gid=<?= $d->getId() ?>">Add Generic</a> | <a class="boxy" href="/pm/items/generics_group.php?id=<?= $d->getId() ?>&name=<?= $d->getName() ?>">View Generic</a>
                </td>
                </tr><?php }
        } else { ?>
            <tr>
                <td colspan="10"><span class="notify-bar">No Group to show in this view</span></td>
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

    var refreshGroup = function () {
        Boxy.get($(".close")).hideAndUnload();
        Boxy.load("/pm/items/groups.php", {
            afterShow: function () {
                $('.table.table-hover.table-striped.small').dataTable();
            }
        });
    }
</script>

