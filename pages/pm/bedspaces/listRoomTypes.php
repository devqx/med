<?php
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RoomTypeDAO.php';
$types = (new RoomTypeDAO())->getRoomTypes();

if (sizeof($types) > 0) {
    ?>

    <table class="table table-striped">
        <thead><tr><th>SN</th><th>Name</th><th>Default Price</th><th>*</th></tr></thead>
    <tbody>
        <?php foreach ($types as $key => $t) { ?>
            <tr>
                <td><?= $key + 1 ?></td>
                <td><?= $t->getName() ?></td>
                <td class="amount"><?= ($t->getDefaultPrice() === NULL) ? "N/A" : $t->getDefaultPrice() ?></td>
                <td><a class="__phL" href="javascript:;" data-href="/pages/pm/bedspaces/editRoomType.php?id=<?= $t->getId()?>">Edit</a> </td>
            </tr>
        <?php } ?>
    </tbody>
    </table>
    <?php
} else {
    echo '<div class="well">No Room Category currently exists</div>';
}
?>
<script type="text/javascript">
    $('a.__phL').live('click', function(e){
        if(!e.handled){
            Boxy.load($(this).data("href"));
            e.handled = true;
        }
    })
</script>
