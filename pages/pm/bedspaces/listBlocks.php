<?php 
    require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BlockDAO.php';
    $blocks = (new BlockDAO())->getBlocks(TRUE);
    
    if (sizeof($blocks) > 0){ ?>
        <table class="table table-striped">
            <thead><tr><th>SN</th><th>Name</th><th>Description</th><th>*</th></tr></thead>
            <tbody>
                <?php foreach ($blocks as $key=>$b){?>
                    <tr>
                        <td><?= $key+1 ?></td>
                        <td><?= $b->getName() ?></td>
                        <td><?= $b->getDescription() ?></td>
                        <td><a class="__phL" href="javascript:;" data-href="/pages/pm/bedspaces/editBlock.php?id=<?= $b->getId()?>">Edit</a> </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php
    }else {
        echo '<div class="well">No Block currently exists</div>';
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