<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
if (!isset($_SESSION)) {@session_start();}

$aid = $_GET['ipid'];
$ip = (new InPatientDAO())->getInPatient($aid, FALSE, NULL);

$pds = (new PrescriptionDataDAO())->aggregateIPPrescriptionData($aid, TRUE, NULL);

if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/Bill.php';
    require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/BillDAO.php';
    require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
    $pdo=NULL;
    try {
        $pdo=(new MyDBConnector())->getPDO();
        $pdo->beginTransaction();
    }catch(PDOException $e) {
        exit( 'ERROR: Database connectivity error' );
    }
    //get a fuller patient object
    $pat = (new PatientDemographDAO())->getPatient($ip->getPatient()->getId(), FALSE, $pdo);

    $amount = 0;
    foreach ($_POST['quantity'] as $drug_id=>$quantity) {
        //get the price of this drug for this patient
        $DRUG = (new DrugDAO());
        $drug = $DRUG->getDrug($drug_id, FALSE, $pdo);
        $price = (new InsuranceItemsCostDAO())->getItemPriceByCode($drug->getCode(), $ip->getPatient()->getId(), TRUE, $pdo);
        $amount += $price * $quantity;
        //get the available batch that can fulfill this request
        $available_drug_batch = $DRUG->getDrugAvailableBatch($drug, $quantity, $pdo);

        if($available_drug_batch != null){
            $deplete = (new DrugBatchDAO())->depleteStock($DRUG->getDrugAvailableBatch($drug, $quantity, $pdo), $quantity, $pdo);
            $dispense = (new DrugDAO())->dispenseDrug($drug, $quantity, $available_drug_batch, $pat, $pdo);
        } else {
            //break the transaction
            $pdo->rollBack();
            exit("error:".$drug->getName()." has not enough stock");
        }


    }
    //create a new bill for this price,
    $bil=new Bill();
    $bil->setPatient($pat);
    $bil->setDescription("In-patient medication charge");
    $bil->setItem(new Drug());
    $bil->setSource( (new BillSourceDAO())->findSourceById(5, $pdo) );
    $bil->setSubSource( (new BillSourceDAO())->findSourceById(2, $pdo) );
    $bil->setTransactionType("credit");
    $bil->setAmount($amount);
    $bil->setDiscounted(NULL);
    $bil->setDiscountedBy(NULL);
    $bil->setInPatient($ip);
    $bil->setCostCentre( $ip->getWard() ? $ip->getWard()->getCostCentre() : NULL );

    $staff=(new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE, $pdo);
    $bil->setClinic($staff->getClinic());
    $bil->setBilledTo($pat->getScheme());
    $bill=(new BillDAO())->addBill($bil, 1, $pdo, $ip->getId());

    // then mark the instance `$aid` 's bill status as 'Cleared' in the in-patient
    $clearBill = (new InPatientDAO())->clearBill($aid, $pdo);

    if($bill != NULL && $clearBill == TRUE){
        $pdo->commit();
        exit("success:Medication Bills have been computed");
    }else {
        $pdo->rollBack();
        exit("error:Server error occurred");
    }
}?>

<div class="content">
<h6>Medications given to <?= $ip->getPatient()->getFullname() ?> during admission</h6>
<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: submitted})">
    <table class="table table-condensed table-hover table-striped">
        <thead>
        <tr>
            <th>Medication</th>
            <th>Quantity given</th>
            <th>Quantity to Bill (<span class="fadedText">based on stock uom</span>)</th>
            <!--<th>sub-total</th>-->
        </tr>
        </thead>
        <tbody>
        <?php foreach ($pds as $pd) { //$pd=new Drug();?>
            <tr>
                <td><?= $pd->getDrug()  ? $pd->getDrug()->getName() : '--' ?> (<?= $pd->getDrug() ? $pd->getDrug()->getGeneric()->getWeight() : '--' ?> <?= $pd->getDrug() ? $pd->getDrug()->getGeneric()->getForm() : '--'?> of <?= $pd->getDrug() ? $pd->getDrug()->getGeneric()->getName() : ''?>)</td>
                <td><?= $pd->getQuantity() ?> <?=$pd->getGeneric()->getForm() ?></td>
                <td>
                    <label class="row-fliud">
                        <input class="amount span3" value="0" type="number" min="0" step="0.5" name="quantity[<?= $pd->getDrug() ? $pd->getDrug()->getId() : '' ?>]" data-quantity="<?= $pd->getQuantity() ?>" data-did="<?= $pd->getDrug() ? $pd->getDrug()->getId() : '' ?>" data-dose="<?= $pd->getDose() ?>" >
                        <span class="span1 fadedText" style="float:right"><?=ucwords($pd->getDrug() ? $pd->getDrug()->getStockUOM() : '--')?></span>

                    </label>
                </td>
<!--                <td class="amount">0</td>-->
            </tr>
        <?php } ?>
        </tbody>
    </table>

<div>
    <input type="hidden" name="aid" value="<?=$aid ?>">
    <button type="submit" class="btn" name="save" >Save</button>
    <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()" >Cancel</button>
</div>
</form>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('input[name*="price"]').on("input",function () {
            //the "oninput" event of input::number fires the change
            // when the spinner is used to change the value as well as
            // when the value is typed directly.

            //update(this);
        });
    });

    function update(element){
//        var cost = $(element).data("quantity");
//        var quantity = $(element).val();
//        var newPrice = (cost * quantity);
//        //console.info(newPrice.toFixed(2));
//        $(element).parent().parent().next('td').html(newPrice.toFixed(2));
    }

    function submitted(s){
        var data = s.split(":");

        if(data[0]=="success"){
            Boxy.get($(".close")).hideAndUnload();
            loadTab(5);
            Boxy.info(data[1]);
        } else if (data[0]=="error"){
            Boxy.alert(data[1]);
        } else {
            Boxy.alert("Unknown error has occurred");
        }
    }
</script>