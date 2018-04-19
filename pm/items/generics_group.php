
<div style="width: 1000px">
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';


    $items = (new ItemGroupDAO())->getGenericByGroup($_GET['id']);
    $formatter_begin = '<span class="notify-bar">';
    $formatter_end = '</span>';
    $count = sizeof($items);
    ?>
    <div class="well well-small"><?= $count ?> Generic<?php echo(($count == 1) ? '' : 's') ?> in <?= $_GET['name'] ?>.
        <span class="pull-right"><a href="javascript:;" onclick="refreshGroupGeneric()"></a></span>
        <a href="/pm/items/add_group_data.php?gid=<?=$_GET['id'] ?>" class="boxy">Add Generic</a>
    </div>
    <table class="table table-hover table-striped small">
        <thead>
        <tr>
            <th>Generic Name</th>
            <th>Category</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <?php if (sizeof($items) > 0) {
            foreach ($items as $i => $d) { ?>
                <tr>
                <td nowrap style="width:20%"><?= $d ? $d->getGeneric()->getName() : '' ?></td>
                <td nowrap style="width:20%"><?= $d->getGeneric()->getCategory() ? $d->getGeneric()->getCategory()->getName() : '' ?></td>
                <td nowrap style="width:20%"><?= $d->getGeneric()->getDescription() ?></td>
                </tr><?php }
        } else { ?>
            <tr>
                <td colspan="10"><span class="notify-bar">No Generic to show in this view</span></td>
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

    var refreshGroupGeneric = function () {
        Boxy.get($(".close")).hideAndUnload();
        Boxy.load("/pm/items/generics_group.php", {
            afterShow: function () {
                $('.table.table-hover.table-striped.small').dataTable();
            }
        });
    }
</script>

