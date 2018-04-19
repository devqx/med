<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/15/16
 * Time: 2:39 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/sti_clinic/classes/DAOs/CareEntryPointDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/sti_clinic/classes/DAOs/ModeOfTestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/sti_clinic/classes/DAOs/PriorARTDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
if($_POST){
    if(is_blank($_POST['patient_id'])){exit("error:Patient ID required");}
    if(is_blank($_POST['unique_id'])){exit("error:Unique Enrollment ID is required");}
    if(is_blank($_POST['date_enrolled'])){exit("error:Enrollment Date is required");}
    if(is_blank($_POST['care_entry_point_id'])){exit("error:Care Entry Point detail is required");}
    if(is_blank($_POST['hiv_confirm_date'])){exit("error:HIV Confirmation Test date is required");}
    if(is_blank($_POST['mode_of_test_id'])){exit("error:Mode of HIV Test is required");}
    if(is_blank($_POST['where'])){exit("error:Place of Test is required");}
    if(is_blank($_POST['prior_art_id'])){exit("error:Prior ART information is required");}
    exit("success:Message");
}
?>
<section style="width: 700px;">
    <p></p>
    <form method="post" action="<?=$_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: started_transak, onComplete: finised_transak})">
        <input type="hidden" name="patient_id" required value="<?= $_GET['pid']?>" autofocus>
        <label>Unique ID <input type="text" required name="unique_id"> </label>
        <label>Date Enrolled into Care <input type="text" required name="date_enrolled" value="<?= date("Y/m/d")?>"> </label>
        <label>Care Entry Point <select name="care_entry_point_id">
                <?php foreach( (new CareEntryPointDAO())->all() as $cep){?><option value="<?=$cep->getId()?>"><?= $cep->getName()?></option><?php }?>
            </select></label>
        <label>Date of Confirmed HIV test <input type="text" required name="hiv_confirm_date" value="<?= date("Y/m/d")?>"></label>
        <label>Mode of HIV Test <select name="mode_of_test_id" required>
                <?php foreach( (new ModeOfTestDAO())->all() as $mot ){?><option value="<?=$mot->getId()?>"><?=$mot->getName()?></option> <?php }?>
            </select></label>
        <label>Where <textarea name="where"></textarea></label>
        <label>Prior ART <select name="prior_art_id" required>
                <?php foreach( (new PriorARTDAO())->all() as $part){?>
                    <option value="<?= $part->getId()?>">(<?= $part->getCode()?>) <?= $part->getName()?></option>
                <?php }?>
            </select></label>

        <div class="btn-block"></div>
        <div class="btn-block">
            <button class="btn" type="submit">Save</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</section>
<script>
    $(document).ready(function () {
        $("[name='date_enrolled']").datetimepicker({
            format:'Y/m/d',
            formatDate:'Y-m-d',
            timepicker:false,
            onChangeDateTime:function(dp,$input){
            }
        });
        $("[name='hiv_confirm_date']").datetimepicker({
            format:'Y/m/d',
            formatDate:'Y-m-d',
            timepicker:false,
            onChangeDateTime:function(dp,$input){
            }
        });
    });
    var started_transak = function (){
        $('section > p').html('<img src="/img/loading.gif"> Please wait...');
    };
    var finised_transak = function (s){
        $('section > p').html(null);
        var data = s.split(":");
        if(data[0]=="error"){
            Boxy.warn(data[1]);
        } else if(data[0]=="success"){
            Boxy.info(data[1]);
            Boxy.get($(".close")).hideAndUnload();
            $('a[data-id="<?= $_GET['pid']?>"]').parent().parent().remove();
        }
    };
</script>

