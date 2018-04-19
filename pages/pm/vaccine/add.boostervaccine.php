<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/31/15
 * Time: 10:35 AM
 */

require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineBoosterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VaccineBooster.php';

$vaccine = (new VaccineDAO())->getVaccines();
if($_POST){
    $object = (object)null;
    $start_age = $_POST['startAge'];
    $start_age_scale = $_POST['startAgeScale'];
    $interval = $_POST['interval'];
    $interval_scale = $_POST['scale'];

    $vb = new VaccineBooster();

    if(empty($_POST['vaccine']) || $_POST['vaccine']==''){
        $object->status = "error";
        $object->message = "Select a vaccine";
        exit(json_encode($object));
    }
    else{
        $bv = (new VaccineBoosterDAO())->getVaccineBoosterByVaccine($_POST['vaccine']);
        if($bv === null){
            $v = (new VaccineDAO())->getVaccine($_POST['vaccine']);
            $vb->setVaccine($v);
        }
        else {
            $object->status = "error";
            $object->message = "Booster for this vaccine has been set, select another vaccine";
            exit(json_encode($object));
        }
    }
    if($start_age==''){
        $object->status = "error";
        $object->message = "Enter vaccine start age";
        exit(json_encode($object));
    }
    else{
        $vb->setStartAge($start_age);
    }
    $vb->setStartAgeScale($start_age_scale);
    if($interval==''){
        $object->status = "error";
        $object->message = "Enter vaccine booster interval";
        exit(json_encode($object));
    }
    else{
        $vb->setInterval($interval);
    }
    $vb->setIntervalScale($interval_scale);

    $vb2 = (new VaccineBoosterDAO())->addVaccineBooster($vb);
    if($vb2 != NULL){
        $object->status = "success";
        $object->message = "Vaccine Booster added successfully";
        exit(json_encode($object));
    } else {
        $object->status = "error";
        $object->message = "An error occurred while saving the changes";
        exit(json_encode($object));
    }
}
?>
<div>
    <form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: beginSave, onComplete: endSave})">
        <span class="well well-small"></span>
        <div class="clear"></div>
        <label>Name
            <select name="vaccine" id="vaccine">
                <option value="">Select vaccine</option>
                <?php
                foreach($vaccine as $v){ ?>
                <option value="<?= $v->getId() ?>"><?= $v->getDescription()?> (<?= $v->getName()?>)</option>
                <?php }
                ?>
            </select>
        </label>
        <label>Start Age:
            <div class="row">
                <span class="span4"><input type="number" name="startAge" id="startAge" placeholder="example: 2"></span>
                <span class="span4"><select name="startAgeScale" id="startAgeScale">
                    <option value="DAY" selected="selected">Days</option>
                    <option value="WEEK">Weeks</option>
                    <option value="MONTH">Months</option>
                    <option value="YEAR">Years</option>
                </select></span>
            </div>
        </label>
        <label>Booster Interval:
            <div class="row">
                <span class="span4"><input type="number" name="interval" id="interval" placeholder="example: 2"></span>
                <span class="span4"><select name="scale" id="scale">
                    <option value="DAY" selected="selected">Days</option>
                    <option value="WEEK">Weeks</option>
                    <option value="MONTH">Months</option>
                    <option value="YEAR">Years</option>
                </select></span>
            </div>
        </label>
        <div class="btn-block">
            <button class="btn" type="submit">Save</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $("#vaccine").select2({
        placeholder: "Select a Vaccine",
        allowClear: true
    });
});

function beginSave(){
    $('span.well').html('<img src="/img/loading.gif"> updating...').show();
}

function endSave(s){
//    console.log(s);
    var ret = JSON.parse(s);
    if(ret.status =="success"){
        Boxy.info(ret.message, function () {
            Boxy.get($('.close')).hideAndUnload();
        })
    }else {
        Boxy.alert(ret.message);
    }
    $('span.well').html(ret.message).show();
}
</script>