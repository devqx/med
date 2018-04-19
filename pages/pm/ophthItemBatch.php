<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 2:05 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/OphthalmologyItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/OphthalmologyItemBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';

$item = (new OphthalmologyItemDAO())->get($_GET['id']);
$batches = (new OphthalmologyItemBatchDAO())->getItemBatches( $item );
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

?>
<section style="width: 700px;">
    <p>Available Batches For <em><?=$item->getName()?></em>
        <a class="pull-right" href="javascript:;" id="addNewBatch" data-id="<?=$_GET['id']?>">New</a></p>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Batch #</th><th>Quantity Balance</th><th>Service Center</th><th></th>
        </tr>
        </thead>
        <?php if(count($batches)>0){ foreach ($batches as $b) {//$b=new OphthalmologyItemBatch();?>
            <tr>
                <td><?= $b->getName()?></td><td><?=$b->getQuantity()?></td><td><?= ($b->getServiceCentre() != NULL) ? $b->getServiceCentre()->getName() : '- -'?></td>
                <td style="text-align: right;">
                    <a href="javascript:;" class="add_Stock" data-id="<?=$b->getId()?>">Add Stock</a>
                    | <a href="javascript:;" class="adjust_Stock" data-id="<?=$b->getId()?>">Adjust Stock</a>
                </td>
            </tr>
        <?php } } else {?>
            <tr>
                <td colspan="4"><div class="notify-bar">No batches exist for <em><?=$item->getName()?></em></div></td>
            </tr>
        <?php } ?>

    </table>
</section>

<script type="text/javascript">
    $(document).ready(function () {
        $('#addNewBatch').live('click',function (e) {
            $ID= $(this).data("id");
            if(!e.handled){
                Boxy.load('/pages/pm/ophthalmologyItems/batch.new.php?d_id='+$ID, {afterHide:function(){
                    setTimeout(function () {
                        Boxy.get($(".close")).hideAndUnload();
                        setTimeout(function () {
                            Boxy.load('/pages/pm/ophthItemBatch.php?id='+$ID);
                        }, 100);
                    }, 100);
                }});
                e.handled = true;
            }
        });

        $('a.add_Stock').bind('click',function () {
            $ID = $(this).data("id");
            Boxy.load('/pages/pm/ophthalmologyItems/batch.add.php?batch='+$ID,{afterHide:function(){
                setTimeout(function () {
                    Boxy.get($(".close")).hideAndUnload();
                    setTimeout(function () {
                        Boxy.load('/pages/pm/ophthItemBatch.php?id=<?=$_GET['id']?>');
                    }, 100);
                }, 100);
            }});
        });
        $('a.adjust_Stock').bind('click', function () {
            $ID = $(this).data("id");
            Boxy.load('/pages/pm/ophthalmologyItems/batch.adjust.php?batch='+$ID,{afterHide:function(){
                setTimeout(function () {
                    Boxy.get($(".close")).hideAndUnload();
                    setTimeout(function () {
                        Boxy.load('/pages/pm/ophthItemBatch.php?id=<?=$_GET['id']?>');
                    }, 100);
                }, 100);
            }});
        });
    })
</script>