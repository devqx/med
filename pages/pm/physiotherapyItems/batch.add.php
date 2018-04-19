<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/26/14
 * Time: 3:42 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysiotherapyItemBatchDAO.php';
$DAO= new PhysiotherapyItemBatchDAO();
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ServiceCenter.php';
$all = (new ServiceCenterDAO())->all('Physiotherapy');
$batch = $DAO->get($_GET['batch']);
if($_POST){
    $batch->setQuantity($_POST['quantity']);
    if(!is_blank($_POST['service_centre_id'])){
        $batch->setServiceCentre( new ServiceCenter($_POST['service_centre_id']) );
    }else {
        exit("error:Physiotherapy location is required");
    }

    $new = $DAO->stockUp($batch);
    if($new !== NULL){
        exit("ok");
    }
    exit("error:Failed to update Stock");
}
?>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: completed})">
    <div class="fadedText">Available: <?=$batch->getQuantity()?></div>
    <label>Physiotherapy Service Center <select name="service_centre_id">
            <?php foreach ($all as $center) {?>
                <option value="<?= $center->getId() ?>"<?= (($batch->getServiceCentre()!=null && $batch->getServiceCentre()->getId()==$center->getId())?' selected="selected"':'') ?>><?= $center->getName() ?></option>
            <?php }?>

        </select> </label>
    <label>Quantity to Add to stock of Batch  <input type="number" name="quantity" value="0" required="required"> </label>

    <div class="btn-block">
        <button class="btn" type="submit">Add</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
    </div>
</form>
<script type="text/javascript">
    function completed(s){
        var data = s.split(":");
        if(data[0]=="error"){
            Boxy.alert(data[1]);
        } else if(s == "ok"){
            Boxy.get($(".close")).hideAndUnload();
        }
    }
</script>