<?php
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/HistoryTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/HistoryTemplateDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientHistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/HistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/PatientHistory.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/History.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/PatientHistoryData.php';
$patient = (new PatientDemographDAO())->getPatient($_GET['pid'], FALSE);
$HISTORY = (new HistoryDAO())->all();

if(!$_SESSION){session_start();}
if($_POST){
    foreach ($_POST['history_data'] as $historyId => $Value) {
        $pHistory = new PatientHistory();
        $pHistory->setPatient(new PatientDemograph($_POST['pid']));
        $pHistory->setCreator(new StaffDirectory($_SESSION['staffID']));
        $pHistDATA = [];
        $history = (new HistoryDAO())->get($historyId);

        $pHistory->setHistory($history);

        foreach ($Value as $tplId => $comment) {
//            if (!is_blank($comment)) {
            $pHistDatum = new PatientHistoryData();
            $pHistDatum->setHistoryTemplateData(new HistoryTemplate($tplId));
            $pHistDatum->setValue($comment);

            $pHistDATA[] = $pHistDatum;
//            }
        }
        $pHistory->setData($pHistDATA);
        $resp = (new PatientHistoryDAO())->add($pHistory);
        if ($resp != NULL) {
            $response[] = $resp;
        }
    }
    if ( count($response) === count($_POST['history_data'])){
        exit("success:History Data Saved");
    }
    exit("error:Failed to save History Data");
}
?>

<section style="width: 700px">
    <p>Add Data for <?= $patient->getFullname()?> <span class="pull-right"></span></p>
    <form autocomplete="off" method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: pot1, onComplete: pot2})">
        <select id="view" placeholder="-- Select a history category --">
            <option></option>
            <?php foreach ($HISTORY as $H) {?><option value="<?= $H->getId() ?>"><?= $H->getTemplate()->getLabel()?></option><?php }?>
        </select>
        <?php foreach ($HISTORY as $H) {//$tpl = new History();?>
            <dl class="history_data_item hide template<?=$H->getId()?>" style="width:97%;overflow:auto;height:350px;float:none;padding-right:10px">
                <dt class=""><?= $H->getTemplate()->getLabel()?></dt>
                <?php foreach ((new HistoryTemplateDataDAO())->byTemplate($H->getTemplate()->getId()) as $item) {//$item=new HistoryTemplateData();?>
                    <dd><label><?= $item->getLabel()?><input name="history_data[<?=$H->getId()?>][<?= $item->getId()?>]" <?= $item->renderType()?>></label></dd><?php }?>
            </dl>
        <?php }?>
        <div class="clear"></div>
        <div class="btn-block"></div>
        <div class="btn-group" style="font-size:40px;">
            <button class="btn" type="submit">Save</button>
            <button class="btn" type="reset">Reset</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>

        <input type="hidden" name="pid" value="<?= $patient->getId()?>">
    </form>
</section>
<script type="text/javascript">
    $(document).on('change', '#view', function(e){
        var id = $(this).val();
        if(!e.handled){
            $("dl.history_data_item").removeClass("hide").addClass("hide");
            $("dl.history_data_item.template"+id).removeClass("hide");
            //console.log("dl.history_data_item.template"+id);
            Boxy.get($(".close")).center();
            e.handled = true;
        }
    });

    var pot1 = function () {
        jQuery('input[name*="history_data"]').filter(function() {
            return !this.value;
        }).attr('disabled', 'disabled');
        $('section > p > span').html('Saving...');
    };

    var pot2 = function(data) {
        state = data.split(":");
        if(state[0] === "error"){
            jQuery('input[name*="history_data"]').filter(function() {
                return !this.value;
            }).removeAttr('disabled');
            $('section > p > span').html('<img src="/assets/alert/error.png"> '+state[1]);
        } else if(state[0] === "success"){
            $('section > p > span').html('<img src="/assets/alert/success.png"> '+state[1]);
            setTimeout(function () {
                Boxy.get($(".close")).hideAndUnload();
                setTimeout(function(){
                    Boxy.get($(".close")).hideAndUnload();
                    showMedicalHistory();
                }, 500);
            }, 1500);
        }
    };



</script>