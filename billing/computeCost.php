
<style type="text/css">
    .table{
        margin-bottom: 0px; 
    }
</style>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';



if (!isset($_SESSION)) {
    @session_start();
}
//error_log(json_encode($_POST));

$pdo = (new MyDBConnector())->getPDO();
if (isset($_GET['ipid'])) {
    $aid = $_GET['ipid'];
//$pdo->beginTransaction()
    $_SESSION['ipDrugCost'] = "";
    $_SESSION['otherCost'] = "";
    $ip = (new InPatientDAO())->getInPatient($aid, TRUE, $pdo);
    $pds = (new PrescriptionDataDAO())->aggregateIPPrescriptionData($aid, TRUE, $pdo);
    $insurance = (new InsuranceDAO())->getInsurance($ip->getPatient()->getId(), TRUE, $pdo);
    $bedCost = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($ip->getBed()->getRoom()->getRoomType()->getCode(), $insurance->getScheme()->getId(), TRUE, FALSE, $pdo);
    $daysOnAdm = ((int) (new InPatientDAO())->getDaysOnAdmission($ip->getId(), NULL, $pdo)) + 1;
    $fixCosts = (new BillDAO())->getInPatientBill($aid, FALSE, $pdo);
} else if (isset($_POST['did'])) {
    //error_log("**************************************************************");
//    $dCost = drug_id, Cost, ip_id, description
    $dCost = "|" . $_POST["did"] . "," . $_POST["value"] . "," . $_POST["aid"] . "," . $_POST["desc"];
    if (trim($_SESSION['ipDrugCost']) == "") {
        $_SESSION['ipDrugCost'] = $_SESSION['ipDrugCost'] . ltrim($dCost, "|");
    } else {
        $x = NULL;

        foreach (explode("|", $_SESSION['ipDrugCost']) as $index => $o) {
            $dd = explode(",", $o);
            //error_log(json_encode($dd) . " >>>>****>>>>" . $dd[0] . "  :::  " . $_POST["did"]);
            if ($dd[0] === $_POST["did"]) {
                $x = $index;
                break;
            }
        }
        if ($x !== NULL) {
            $ddd = explode("|", $_SESSION['ipDrugCost']);
            $dd = explode(",", $ddd[$x]);
            $dd[1] = $_POST["value"];
            $ddd[$x] = implode(",", $dd);
            $_SESSION['ipDrugCost'] = implode("|", $ddd);
        } else {
            $_SESSION['ipDrugCost'] = $_SESSION['ipDrugCost'] . $dCost;
        }

        //error_log($_SESSION['ipDrugCost']);
    }
    http_response_code(200);
    exit("");
} else if (isset($_POST['otherCost'])) {
//    $dCost = drug_id, Cost, ip_id, description
    $dCost = "|" . ($_POST["name"] === "others" ? "others" : "others_desc") . "," . $_POST["value"] . "," . $_POST["aid"];

    if (trim($_SESSION['otherCost']) === "") {
        $_SESSION['otherCost'] = $_SESSION['otherCost'] . ltrim($dCost, "|");
    } else {
        $x = NULL;

        foreach (explode("|", $_SESSION['otherCost']) as $index => $o) {
            $dd = explode(",", $o);
            //error_log(json_encode($dd) . " >>>>>>>>" . $dd[0] . "  :::  " . $_POST["name"]);
            if ($dd[0] === $_POST["name"]) {
                $x = $index;
                break;
            }
        }
        if ($x !== NULL) {
            $ddd = explode("|", $_SESSION['otherCost']);
            $dd = explode(",", $ddd[$x]);
            $dd[1] = $_POST["value"];
            $ddd[$x] = implode(",", $dd);
            $_SESSION['otherCost'] = implode("|", $ddd);
        } else {
            $_SESSION['otherCost'] = $_SESSION['otherCost'] . $dCost;
        }
        //error_log("========== " . $_SESSION['otherCost']);
    }
    //error_log($_SESSION['otherCost']);

    http_response_code(200);
    exit("");
} else if (isset($_POST['save'])) {
    //error_log(json_encode($_POST));
    exit("done");
}
?>
<h5><strong><?= $ip->getPatient() ?></strong> <br >
    <em>Insurance Coverage: <strong><?= $insurance->getScheme()->getName() ?></strong></em> <br >
    <em># of days on admission: <strong><?= $daysOnAdm ?> days</strong></em></h5>

<div>
    <table class="table table-condensed table-hover table-striped">
        <thead>
            <tr>
                <th style="width: 40%"><a href="javascript:;" data-type="toggle">Bed cost  (<em><span class="naira"> <?= $bedCost->getSellingPrice() ?></span> per day</em>)</a></th>
                <th></th>
                <th style="width: 10%"> <span class="naira"> <?= $bedCost->getSellingPrice() * $daysOnAdm ?></span></th>
            </tr>
        </thead>
        <tbody style="display: none">
            <tr>
                <td>Bed Label</td>
                <td colspan="2"><?= $ip->getBed()->getName() ?></td>
            </tr>
            <tr>
                <td>Room</td>
                <td colspan="2"><?= $ip->getBed()->getRoom()->getName() ?></td>
            </tr>
            <tr>
                <td>Room Type</td>
                <td colspan="2"><?= $ip->getBed()->getRoom()->getRoomType()->getName() ?></td>
            </tr>
            <tr>
                <td>Ward</td>
                <td colspan="2"><?= $ip->getBed()->getRoom()->getWard()->getName() ?></td>
            </tr>
        </tbody>
    </table>
    <table class="table table-condensed table-hover table-striped">
        <thead>
            <tr>
                <th style="width: 40%"><a href="javascript:;" data-type="toggle">Admission fixed cost</a></th>
                <th></th>
                <th style="width: 10%"><span class="naira"> <?= $fixCosts[0]->getAmount() ?></span></th>
            </tr>
        </thead>
        <tbody style="display: none">
            <?php foreach ($fixCosts as $fc) { ?>
                <tr>
                    <td style="width: 40%"><?= $fc->getDescription() ?></td>
                    <td colspan="2"><strong><span class="naira"><?= $fc->getAmount() ?></span></strong></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <table class="table table-condensed table-hover table-striped">
        <thead>
            <tr>
                <th style="width: 40%"><a href="javascript:;" data-type="toggle">Medications</a></th>
                <th></th>
                <th style="width: 10%"><span class="naira"></span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pds as $pd) { ?>
                <tr>
                    <td style="width: 40%"><strong><?= $pd->getQuantity() ?></strong> <?= $pd->getDrug()->getName() . " (" . $pd->getGeneric()->getForm() . ")" ?></td>
                    <td colspan="2">
                        <span class="naira"> 
                            <a href="#" id="drug_<?= $pd->getDrug()->getId() ?>" data-type="number" data-pk="<?= $aid ?>" data-url="computeCost.php" data-title="Enter the cost" data-did="<?= $pd->getDrug()->getId() ?>" data-aid="<?= $aid ?>" data-desc="<?= 'Charge for ' . $pd->getDrug()->getName() . " (" . $pd->getGeneric()->getForm() . ") administered while on admission" ?>" data-dose="<?= $pd->getDose() ?>" ></a>
                        </span>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <table class="table table-condensed table-hover table-striped">
        <thead>
            <tr>
                <th><a href="javascript:;" data-type="toggle">Other Admission Bills</a> </th>
                <th></th>
                <th style="width: 10%"><span class="naira"></span></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 40%"><a href="#" id="others_desc" data-type="text" data-pk="others_desc" data-url="computeCost.php" data-title="Description for this cost" data-aid="<?= $aid ?>" ></a></td>
                <td colspan="2">
                    <span class="naira"> 
                        <a href="#" id="others" data-type="number" data-pk="others" data-url="computeCost.php" data-title="Enter the cost" data-aid="<?= $aid ?>" ></a>
                    </span>
                </td>
            </tr>
        </tbody>
    </table><br>
    <button type="button" class="btn btn-primary" name="save" >Save</button>
    <button type="button" class="btn btn-link" onclick="Boxy.get(this).close()" >Cancel</button>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $.fn.editable.defaults.mode = 'popup';
        $('a[id*="drug_"]').editable({
            params: function (params) {
                params.did = $(this).editable().data('did');
                params.aid = $(this).editable().data('aid');
                params.desc = $(this).editable().data('desc');
                params.dose = $(this).editable().data('dose');
                return params;
            },
            success: function (response, newValue) {
                if (response.status == 'error')
                    return response.msg; //msg will be shown in editable form
            }
        });
        $('#others').editable({
            params: function (params) {
                params.aid = $(this).editable().data('aid');
                params.otherCost = true;
                return params;
            },
            success: function (response, newValue) {
                if (response.status == 'error')
                    return response.msg; //msg will be shown in editable form
            }
        });
        $('#others_desc').editable({
            params: function (params) {
                params.aid = $(this).editable().data('aid');
                params.otherCost = true;
                return params;
            },
            success: function (response, newValue) {
                if (response.status == 'error')
                    return response.msg; //msg will be shown in editable form
            }
        });
        
        $("a[data-type='toggle']").click(function () {
            console.log($(this).parent().parent().parent().next().toggle("slow"));
        });
        
        $("button").click(function () {
            $.ajax({
                url: "computeCost.php",
                type: "post",
                data: {save: true},
                success: function (d) {
                    alert(d);
                },
                error: function () {
                    alert("error");
                }
            })
        })

    });
</script>