<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 8/27/15
 * Time: 11:51 AM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php');

$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');
$pps = (new PrescriptionDAO())->getRefillPrescriptionByCode($_GET['pCode']);
?>
<div>
    <form id="transfer_RegimenForm" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
        <label> Business Unit/Service Center <input type="text" name="_pharmacy" id="_pharmacy" readonly="readonly" value="<?= $pps->getServiceCentre()->getName() ?>"></label>
        <label>Transfer to <select id="pharmacy_id" name="pharmacy_id" placeholder="-- Select pharmacy--"><option value=""></option>
            <?php foreach($pharmacies as $k=>$pharm){ if($pps->getServiceCentre()->getId()!=$pharm->getId()){ ?>
                <option value="<?= $pharm->getId() ?>"><?= $pharm->getName() ?></option>
            <?php } } ?>
            </select></label>

        <div class="btn-block">
            <input type="hidden" name="cid" value="<?= $pps->getId() ?>"/>
            <input type="hidden" name="action" value="transfer"/>
            <button class="btn" type="submit">Save</button>
            <button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
<script>
$(document).ready(function(){
    $("#transfer_RegimenForm").on('submit', function(){
        if(confirm("Are you sure you want to transfer this prescription?")){
            $.ajax({
                url:'/api/regimens.php',
                data: $(this).serialize(),
                type:'POST',
                complete:function(xhr, status){
                    var x = xhr.responseText.split(":");
                    if(status=="success" && x[1] =="true"){
                        Boxy.get($(".close")).hideAndUnload();
                        Boxy.info("Prescription transferred to pharmacy");
                    }
                    else {
                        Boxy.alert(x[1]);
                    }
                }
            });
        }
        return false;
    });
});
</script>
