
<div style="width: 1000px">
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGrpScDAO.php';


    $items = (new ItemGrpScDAO())->getByCenter($_GET['c_id']);
    $formatter_begin = '<span class="notify-bar">';
    $formatter_end = '</span>';
    $count = sizeof($items);
    ?>
    <div class="well well-small"><?= $count ?> Group<?php echo(($count == 1) ? '' : 's') ?> in <?= $_GET['name'] ?>.
        <span class="pull-right"><a href="javascript:;" onclick="refreshGroupCenter()"></a></span>
        <a href="/pm/items/editCenterGroups.php?c_id=<?=$_GET['c_id'] ?>" class="boxy">Add Group</a>
    </div>
    <table class="table table-hover groups_center table-striped small">
        <thead>
        <tr>
            <th style="">Group Name</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <?php if (sizeof($items) > 0) {
            foreach ($items as $i => $d) { ?>
                <tr>
                <td nowrap style="width:20%"><?= $d ? $d->getItemGroup()->getName() : '' ?></td>
                <td nowrap style="width:20%"><?= $d->getItemGroup()->getDescription() ?></td>
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
        $('.table.table-hover.groups_center.table-striped.small').dataTable();
        $(document).on('click', 'a.boxylink', function (e) {
            if (!e.handled) {
                Boxy.load($(this).data('href'), {title: $(this).attr('title') || $(this).data('title')});
                e.handled = true;
            }
        });
    });

    var refreshGroupCenter = function () {
        Boxy.get($(".close")).hideAndUnload();
        Boxy.load("/pm/items/groups_in_center.php", {
            afterShow: function () {
                $('.table.table-hover.groups_center.table-striped.small').dataTable();
            }
        });
    }
</script>

