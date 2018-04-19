<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/14/16
 * Time: 11:02 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioItemsRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysiotherapyItemBatchDAO.php';

$request = (new PhysioItemsRequestDAO())->get($_REQUEST['req-id']);
if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
    $pdo = (new MyDBConnector())->getPDO();
    $pdo->beginTransaction();
    $result = [];
    foreach ($_POST['batch'] as $itemId => $batchId) {
        $batch = (new PhysiotherapyItemBatchDAO())->get($batchId, $pdo);
        $batch->setQuantity(1);//we are just increasing by 1
        $result[] = (new PhysiotherapyItemBatchDAO())->stockUp($batch, $pdo) ;
    }
    if(!in_array(null, $result)){
        $request->setStatus('Received');
        if( (new PhysioItemsRequestDAO())->updateStatus($request, $pdo)){
            $pdo->commit();
            exit("success:Items Received into Batch Successfully");
        }
    }
    $pdo->rollBack();
    exit("error:Operation failed");
}
?>
<section style="width: 500px">
    Receive Items on behalf of <span class="profile" data-pid="<?=$request->getPatient()->getId()?>"><?=$request->getPatient()->getFullname()?></span>
    <form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: null, onComplete: received})">
        <input type="hidden" name="req-id" value="<?=$_REQUEST['req-id']?>">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Item</th>
            <th>Store/Batch</th>
        </tr>
        </thead>
        <?php foreach ($request->getItems() as $i) {?>
        <tr>
            <td><?= $i->getItem()->getName() ?></td>
            <td>
                <label><select name="batch[<?= $i->getItem()->getId()?>]"><?php
                $batches = (new PhysiotherapyItemBatchDAO())->getItemBatches($i->getItem());
                foreach ($batches as $b) {//$b=new PhysiotherapyItemBatch();?>
                    <option value="<?= $b->getId()?>"><?=$b->getName() ?></option>
                <?php } ?></select></label>
            </td>
        </tr>
        <?php } ?>

    </table>

    <button class="btn" type="submit">Receive</button>
    <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
    </form>
</section>
<script type="text/javascript">
    var received = function(s){
        var data= s.split(":");
        if(data[0]==="error"){
            Boxy.alert(data[1])
        } else if(data[0]==="success"){
            $('a[data-url="items/requests_list_open.php"]').click();
            Boxy.get($(".close")).hideAndUnload();
            Boxy.info(data[1]);
        } else {
            Boxy.warn("unknown response");
            Boxy.get($(".close")).hideAndUnload();
        }
    }
</script>
