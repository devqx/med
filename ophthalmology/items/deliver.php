<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/14/16
 * Time: 11:02 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphItemsRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyItemBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.bills.php';
$bills=new Bills();
$request = (new OphItemsRequestDAO())->get($_REQUEST['req-id']);
$pat=(new PatientDemographDAO())->getPatient($request->getPatient()->getId(),FALSE,NULL, NULL);
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;
if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
    $pdo = (new MyDBConnector())->getPDO();
    $pdo->beginTransaction();
    $result = [];
    foreach ($_POST['batch'] as $itemId => $batchId) {
        $batch = (new OphthalmologyItemBatchDAO())->get($batchId, $pdo);
        $result[] = (new OphthalmologyItemBatchDAO())->depleteStock($batch, 1, $pdo) ;
    }
    if(!in_array(null, $result)){
        $request->setStatus('Delivered');
        if( (new OphItemsRequestDAO())->updateStatus($request, $pdo)){
            $pdo->commit();
            exit("success:Items Delivered to Patient Successfully");
        }
    }
    $pdo->rollBack();
    exit("error:Operation failed");
}
?>
<section style="width: 500px">
    Deliver Items to <span class="profile" data-pid="<?=$request->getPatient()->getId()?>"><?=$request->getPatient()->getFullname()?></span>
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
                                $batches = (new OphthalmologyItemBatchDAO())->getItemBatches($i->getItem());
                                foreach ($batches as $b) {/*$b=new OphthalmologyItemBatch();*/if($b->getQuantity() > 0){?>
                                    <option value="<?= $b->getId()?>"><?=$b->getName() ?></option>
                                <?php } } ?></select></label>
                    </td>
                </tr>
            <?php } ?>

        </table>
        <?=($selfOwe - $creditLimit > 0)? ' <div class="warning-bar">Patient has an outstanding balance of <span class="naira"></span>'.$selfOwe.'</div><p></p>':'' ?>
        <button class="btn" type="submit" <?=($selfOwe > 0)? ' disabled="disabled"':'' ?>>Deliver</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
    </form>
</section>
<script type="text/javascript">
    var received = function(s){
        var data= s.split(":");
        if(data[0]==="error"){
            Boxy.alert(data[1])
        } else if(data[0]==="success"){
            $('a[data-url="items/requests_list_received.php"]').click();
            Boxy.get($(".close")).hideAndUnload();
            Boxy.info(data[1]);
        } else {
            Boxy.warn("unknown response");
            Boxy.get($(".close")).hideAndUnload();
        }
    }
</script>