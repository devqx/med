
<div style="width: 1000px">
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';

    $gens = (new ServiceCenterDAO())->all('item');
    $formatter_begin = '<span class="notify-bar">';
    $formatter_end = '</span>';
    $count = sizeof($gens);
    ?>
    <div class="well well-small"><?= $count ?> Business Unit/Service Center<?php echo(($count == 1) ? '' : 's') ?> in store.
        <span class="pull-right"><a href="javascript:;" onclick="refreshCenter()">Refresh</a></span>
        <a href="/pm/items/item_center_new.php" class="boxy">Add Business Unit/Service Center</a>
    </div>
    <table class="table table-hover table-striped small">
        <thead>
        <tr>
            <th style="">Center Name</th>
            <th style="">Cost Center</th>
            <th style="">Department</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php if (sizeof($gens) > 0) {
            foreach ($gens as $i => $d) {

                ?>
                <tr>
                <td nowrap style="width:20%"><?= $d->getName() ?></td>
                <td><?= $d->getCostCentre()->getName() ?></td>
                <td><?= $d->getDepartment()->getName() ?></td>
                <td nowrap style="width: 15%">
                    <i class="icon-edit"></i><a href="javascript:;" class="boxylink" data-href="/pm/items/editItemCenter.php?c_id=<?= ($d->getId()) ?>" data-title="Edit <?= $d->getName() ?>:">Edit</a> |
                    <a class="boxy" href="/pm/items/editCenterGroups.php?c_id=<?= $d->getId() ?>">Add Group</a>
                    |<a class="boxy" href="/pm/items/groups_in_center.php?c_id=<?= $d->getId() ?>&name=<?= $d->getName() ?>">Views Groups</a>
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

    var refreshCenter = function () {
        Boxy.get($(".close")).hideAndUnload();
        Boxy.load("/pm/items/item_service_centers.php", {
            afterShow: function () {
                $('.table.table-hover.table-striped.small').dataTable();
            }
        });
    }
</script>

