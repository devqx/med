<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/8/16
 * Time: 2:07 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ArvDrugDataDAO.php';
$data = (new ArvDrugDataDAO())->forPatient($_GET['pid']);
?>
<table class="table table-striped">
    <thead>
    <tr class="menu-head">
        <th width="10%">Date</th>
        <th>Drug Name</th>
        <th>Type</th>
        <th>Dose</th>
        <th>State</th>
        <th width="10%">Prescribed By</th>
        <th width="5%">*</th>
    </tr>
    </thead>
    <?php foreach ($data as $item){//$item=new ArvDrugData;?><tr class="arvDrugLine <?= $item->getState()?>">
        <td><?= date("Y/m/d h:ia", strtotime($item->getDatePrescribed()))?></td>
        <td><?= $item->getArvDrug()->getName()?></td>
        <td><?= $item->getType()?></td>
        <td><?= $item->getDose()?></td>
        <td><?= ucwords($item->getState())?></td>
        <td><?= $item->getPrescribedBy()->getUsername()?></td>
        <td>
            <select class="arvDrug" onchange="drugAction(this)"><!-- options available depend on the current state of this line -->
                <option></option>
                <option>cancel</option>
                <option>change dose</option>
            </select>
        </td>
    </tr><?php }?>
</table>

<script>
    function drugAction(element) {
        var chc = String($(element).val()).toLowerCase();
        if(chc == "cancel"){
            alert("cancelled");
        }else if(chc=="change dose"){
            alert("change dose");
        }
    }
</script>
