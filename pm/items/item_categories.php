<div style="width: 1000px">
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemCategoryDAO.php';

    $gens = (new ItemCategoryDAO())->getCategories();
    $formatter_begin = '<span class="notify-bar">';
    $formatter_end = '</span>';
    $count = sizeof($gens);
    ?>
    <div class="well well-small"><?= $count ?> Categor<?php echo(($count > 1) ? 'ies' : 'y') ?> in store.
        <span class="pull-right"><a href="javascript:;" onclick="refreshCategories()">Refresh</a></span>
        <a href="/pm/items/add_category.php" class="boxy">Add Category</a>
    </div>
    <table class="table table-hover table-striped small">
        <thead>
        <tr>
            <th style="">Name</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php if (sizeof($gens) > 0) {
            foreach ($gens as $i => $d) { ?>
                <tr>
                    <td nowrap style="width:20%"><?= $d->getName() ?></td>
                    <td nowrap style="width: 15%">
                        <i class="icon-edit"></i><a href="javascript:;" class="boxylink"
                                                    data-href="/pm/items/editCategory.php?id=<?= ($d->getId()) ?>"
                                                    data-title="Edit <?= $d->getName() ?>:">Edit</a></a>
                    </td>
                </tr>
            <?php }
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

    var refreshCategories = function () {
        Boxy.get($(".close")).hideAndUnload();
        Boxy.load("/pm/items/item_categories.php", {
            afterShow: function () {
                $('.table.table-hover.table-striped.small').dataTable();
            }
        });
    }
</script>

