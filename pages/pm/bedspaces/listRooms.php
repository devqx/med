<?php 
    require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RoomDAO.php';
    $rooms = (new RoomDAO())->getRooms(TRUE);

    if (sizeof($rooms) > 0){ ?>
        <table class="table table-striped">
            <thead><tr><th>SN</th><th>Name</th><th>Category</th><th>Ward</th><th>*</th></tr></thead>
            <tbody>
                <?php foreach ($rooms as $key=>$r){?>
                    <tr>
                        <td><?= $key+1 ?></td>
                        <td><?= $r->getName() ?></td>
                        <td><?= $r->getRoomType()->getName()?></td>
                        <td><?= is_null($r->getWard())? "N/A":$r->getWard()->getName() ?></td>
                        <td><a class="__phL" href="javascript:;" data-href="/pages/pm/bedspaces/editRoom.php?id=<?= $r->getId()?>">Edit</a> </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php
    }else {
        echo '<div class="well">No Room currently exists</div>';
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