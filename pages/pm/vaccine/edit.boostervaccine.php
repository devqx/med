<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/31/15
 * Time: 11:17 AM
 */

require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VaccineBooster.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineBoosterDAO.php';

$vaccine = (new VaccineBoosterDAO())->getVaccineBooster($_GET['id'], TRUE);
$intervalScale_ = $vaccine->getIntervalScale();
$startAgeScale_ = $vaccine->getStartAgeScale();
$scale = array("DAY"=>"Days","WEEK"=>"Weeks","MONTH"=>"Months","YEAR"=>"Years");

if(isset($_POST['vaccinebooster_id'])){
    $object = (object)null;
    $start_age = $_POST['startAge'];
    $start_age_scale = $_POST['startAgeScale'];
    $interval = $_POST['interval'];
    $interval_scale = $_POST['scale'];

    $vb = new VaccineBooster();
    $vb->setId($_POST['vaccinebooster_id']);

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

    $vb2 = (new VaccineBoosterDAO())->updateVaccineBooster($vb);
    if($vb2 != NULL){
        $object->status = "success";
        $object->message = "Vaccine Booster updated successfully";
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
        <label>Name <input type="text" name="vaccine" readonly value="<?= $vaccine->getVaccine()->getName()?> (<?= $vaccine->getVaccine()->getDescription()?>)"></label>
        <label>Start Age:
            <div class="row">
                <span class="span4"><input type="number" name="startAge" id="startAge" value="<?= $vaccine->getStartAge() ?>"></span>
                <span class="span4"><select name="startAgeScale" id="startAgeScale">
                        <?php
                        $startAgeScale = '';
                        foreach($scale as $key=>$sc){
                            $selected = ($key==$startAgeScale_)? ' selected="selected"' : '';
                            $startAgeScale .= '<option value="'.$key.'"'.$selected.'>'.$sc.'</option>';
                        }
                        echo $startAgeScale;
                        ?>
                    </select></span>
            </div>
        </label>
        <label>Booster Interval:
            <div class="row">
                <span class="span4"><input type="number" name="interval" id="interval" value="<?= $vaccine->getInterval() ?>"></span>
                <span class="span4"><select name="scale" id="scale">
                        <?php
                        $intervalScale = '';
                        foreach($scale as $key=>$sc){
                            $selected = ($key==$intervalScale_)? ' selected="selected"' : '';
                            $intervalScale .= '<option value="'.$key.'"'.$selected.'>'.$sc.'</option>';
                        }
                        echo $intervalScale;
                        ?>
                    </select></span>
            </div>
        </label>
        <div class="btn-block">
            <button class="btn" type="submit">Save</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
            <input type="hidden" name="vaccinebooster_id" value="<?= $vaccine->getId() ?>">
        </div>
    </form>
</div>
<script type="text/javascript">
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