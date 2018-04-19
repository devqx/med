<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/25/14
 * Time: 2:45 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ServiceCenterDAO.php';
$all = (new ServiceCenterDAO())->all();
if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/OphthalmologyItemBatchDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/OphthalmologyItemBatch.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/OphthalmologyItem.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
    $batch = new OphthalmologyItemBatch();

    if(!is_blank($_POST['name'])){
        $batch->setName($_POST['name']);
        $batch->setItem(new OphthalmologyItem($_POST['item_id']));
    }else {
        exit("error:Batch Identification is required");
    }
    if(!is_blank($_POST['quantity'])){
        $batch->setQuantity($_POST['quantity']);
    }else {
        exit("error:Batch Quantity is required");
    }

    if(!is_blank($_POST['service_centre_id'])){
        $batch->setServiceCentre( new ServiceCenter($_POST['service_centre_id']) );
    }else {
        exit("error:Item location is required");
    }

    $new = (new OphthalmologyItemBatchDAO())->add($batch);
    if($new !== NULL){
        exit("ok");
    }
    exit("error:Failed to add batch");
}

?>
<section style="width: 650px">
    <form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: saved})">
        <label>Ophthalmology Center <select name="service_centre_id">
                <?php foreach ($all as $center) { if($center->getType()==="Ophthalmology"){?>
                    <option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
                <?php }}?>

            </select> </label>
        <label>Batch #
            <input type="text" name="name" placeholder="Identify the Batch; Number or anything"> </label>
        <label>Available Stock Quantity<input type="number" name="quantity" value="0"> </label>

        <input type="hidden" name="item_id" value="<?=$_GET['d_id']?>">

        <div class="btn-block">
            <button  class="btn" type="submit">Save</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
        </div>
    </form>
    <script type="text/javascript">
        function saved(s){
            var data = s.split(":");
            if(data[0]=="error"){
                Boxy.alert(data[1]);
            } else if(s == "ok"){
                Boxy.get($(".close")).hideAndUnload();
            }
        }
    </script>
</section>
