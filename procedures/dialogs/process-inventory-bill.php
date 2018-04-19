<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/10/17
 * Time: 4:45 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drugs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugBatch.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

$bills = new Bills();
$procedure = (new PatientProcedureDAO())->get($_REQUEST['id']);
$protect = new Protect();
$pat = (new PatientDemographDAO())->getPatient($procedure->getPatient()->getId(), false, null, null);

$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');


if (!isset($_SESSION)) {
    session_start();
}
if ($_POST) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedureRegimen.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugBatch.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureRegimenDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';




    $regimen = new PatientProcedureRegimen();
    $updatereg = new PatientProcedureRegimenDAO();


    $regimen->setPatientProcedure((new PatientProcedureDAO())->get($_GET['id'], $pdo)); //new PatientProcedure() );

    $dao = new  DrugDAO();
    $dr = !is_blank($_POST['drug']) ? $_POST['drug'] : 'NULL';
    $drug = $dao->getDrug($dr, true, $pdo);
    $price = !is_blank($_POST['price']) ? $_POST['price'] : 0;
    $quantity = !is_blank($_POST['quantity']) ? $_POST['quantity'] : 0;
    $units = !is_blank($_POST['units']) ? $_POST['units'] : 'NULL';
    $amount = $price * $quantity;
    $batch_id = !is_blank($_POST['batch']) ? $_POST['batch'] : 'NULL';
    $note = !is_blank($_POST['note']) ? $_POST['note'] : 'NULL';
    $batch_quantity = !is_blank($_POST['batch_quantity']) ? $_POST['batch_quantity'] : 0;
    $expiry_date = !is_blank($_POST['expdate']) ? $_POST['expdate'] : null;
    $batch = (new DrugBatchDAO())->getBatch($batch_id, $pdo);


    if (!is_null($expiry_date) && $expiry_date < date('Y-m-d')) {
        exit("error: Batch has expired");
    }
    if ($quantity > $batch_quantity) {
        exit("error: Quantity of batch requested not available");
    }


            $s_center = (new ServiceCenterDAO())->get($_POST['pharmacy_id'], $pdo);
            $regimen->setRequestingUser((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], $pdo));


            $pres = new Prescription();
            $inpatient = isset($_GET['aid']) ? $_GET['aid'] : null;
            $pres->setPatient($pat);
            //  $pres->setInPatient($inpatient);
            $pres->setPrescribedBy($this_user);
            $pres->setRequestedBy($this_user);
            $pres->setNote($_POST['note']);
            $pres->setHospital($this_user->getClinic());
            $pres->setServiceCentre($s_center);
            $pres->setEncounter(!is_blank(@$_REQUEST['encounter_id']) ? new Encounter(@$_REQUEST['encounter_id']) : null);
            $pds = array();

            $pd = new PrescriptionData();
            $g = new DrugGeneric();
            $d = new Drug();
            $d->setId($_POST['drug']);
            $d->setStockQuantity($_POST['quantity']);
            $g->setId($_REQUEST['gid']);
            $d->setGeneric($g);

            $pd->setDrug($d);
            $pd->setGeneric($g);
            $pd->setDose($_REQUEST['dose']);
            $pd->setStatus('completed');
            $pd->setDuration(1);
            $pd->setFrequency(1);
            $pd->setRequestedBy($this_user);
            $pd->setRefillNumber(1);
            $pd->setHospital($this_user->getClinic());
            $pd->setBatch($batch);
            $pd->setCompletedBy($this_user);
            $pds[] = $pd;
            $pres->setData($pds);
            $p = (new PrescriptionDAO())->addPrescription($pres, $pdo);
            if ($p == null){
             exit("error: Failed to add prescription");
            }
            $bil = new Bill();
            $bil->setPatient($pat);
            $bil->setDescription("Drug Prescription: ");

            $bil->setItem($drug);
            $bil->setSource((new BillSourceDAO())->findSourceById(2, $pdo));
            $bil->setTransactionType("credit");
            $bil->setAmount($amount);
            //$bil->setInPatient($lab->getInPatient());
            $bil->setDiscounted(null);
            $bil->setDiscountedBy(null);
            $bil->setClinic($this_user->getClinic());
            $bil->setBilledTo($pat->getScheme());

            $bil->setCostCentre(null);

            $bill = (new BillDAO())->addBill($bil, $quantity, $pdo, $inpatient);
            if ($p === null) {
                exit("error: Billing Failed");
            }

            $regimen->setStatus('completed');
            $regimen->setId($_REQUEST['ppr_id']);
            $updatereg->updateStatus($regimen,$pdo);
            if ($updatereg === null){
                exit("error:Failed to update regimen");
            }
            $drug_batchDao = new DrugBatchDAO();
            $drug_batch = new DrugBatch();

            $rem_quantity = $batch_quantity - $quantity;
            $drug_batch->setQuantity($rem_quantity);
            $drug_batch->setServiceCentre($s_center);
            $drug_batch->setId($batch_id);
            $batch_update = $drug_batchDao->stockAdjust($drug_batch, $pdo);
            if ($batch_update === null) {
                exit("error: Failed to update inventory");
            }
            else{
                exit("success:Saved and processed");

            }
}





$activeGenericsOnly = true;
$_GET['suppress'] = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drugs.php';
?>
<section style="width: 600px;">
     <h5>Process Inventory and Bill</h5><br>
    <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>"
          onsubmit="return AIM.submit(this, {onStart:__start,onComplete:__done})">
        <label> Business Unit/Service Center
            <select id="pharmacy_id" name="pharmacy_id" data-placeholder="-- Select pharmacy --">
                <option value=""></option>
                <?php foreach ($pharmacies as $k => $pharm) { ?>
                    <option value="<?= $pharm->getId() ?>"><?= $pharm->getName() ?></option>
                <?php } ?>
            </select></label>
        <input type="hidden" name="price" id="price">
        <input type="hidden" name="expdate" id="expdate">
        <input type="hidden" name="batch_quantity" id="batch_quantity">
        <div class="row-fluid inventroy" id="inventory">
            <label>Drug Name<input type="text" name="drug" id="drug"></label>
            <label>Batch<input type="text" name="batch" id="batch"></label>
            <div class="row-fluid">
                <div class="span4"><label>Unit<input type="text" name="units" id="units" readonly></label></div>
                <div class="span8"><label>Quantity<input type="number" min="0" name="quantity" id="quantity"></label></div>
            </div>
            <label style="margin-bottom: -5px">Note <textarea cols="5" name="note"></textarea></label>


        </div>
        <div class="btn-block">
            <button type="submit" class="btn">Save</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>

    </form>
</section>

<script type="application/javascript">

    function __start() {
    }

    function __done(s) {
        var data = s.split(":");
        if (data[0] === "error") {
            Boxy.alert(data[1]);
        } else if (data[0] === "success") {
            Boxy.info(data[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
        }
    }

    var batches_inuse = [];
    var expired_batches = [];
    $(document).ready(function () {
        getDrug();
    })
        

  function getDrug() {
      $.ajax({
          url: '/api/get_drugs.php?gid='+<?= json_encode($_REQUEST['gid'])?>,
          type: 'POST',
          dataType: 'json',
          success: function (result) {
              showDrugs(result);

          }
      });
  }

      function showDrugs(data) {
          $('input[name="drug"]').select2({
              width: '100%',
              allowClear: true,
              placeholder: '--Drugs--',
              data: function () {
                  return {results: data, text: 'name'};
              },
              formatResult: function (result) {
                  return result.name;
              },
              formatSelection: function (result) {
                  return result.name;

              }
      }).on('change',function (d) {
              if (!d.handled) {
                  var id = $(this).val();
                  if (id !== '') {
                      //getUnit(e.added.stockUOM);
                      $("#units").val(d.added.stockUOM);
                      getPrice(d.added.basePrice);
                      getBatch(id);
                  }
                  else {
                      $('input[name="batch"]').select2('data', '');
                      $('input[name="units"]').val("");
                      $('input[name="price"]').val('');
                  }
              }

              
          });

  }

    function  filterBatch(data) {

        var dateObj = new Date();
        var month = dateObj.getUTCMonth() + 1; //months from 1-12
        var day = dateObj.getUTCDate();
        var year = dateObj.getUTCFullYear();

       var newdate = year + "-" + month + "-" + day;
        $.each(data, function (key,value) {
            if (value.expirationDate < newdate && value.quantity>0){
                batches_inuse.push(value);

            }
            else{
                expired_batches.push(value);

            }
        });
        return batches_inuse == null? '': batches_inuse;
    }

    function setBatch(data) {
       // filterBatch(data);
        $('input[name="batch"]').select2({
            width: '100%',
            allowClear: true,
            placeholder: '--Batch--',
            data: function () {
                return {results: filterBatch(data), text: 'name'}
            },
            formatResult: function (result) {
                return result.name
            },
            formatSelection: function (result) {
                return result.name;

            }
        }).on('change', function (b) {
            if (!b.handled) {

                if (b.val !== '') {
                    $("#expdate").val(b.added.expirationDate);
                    $("#batch_quantity").val(b.added.quantity);
                }
                else {
                    $('input[name="batch"]').select2('data', '');
                }
            }


        });
    }

    function getBatch(id) {
        $.ajax({
            url: '/api/get_batches.php',
            type: 'POST',
            dataType: 'json',
            data: {did: id},
            success: function (result) {
                setBatch(result);

            }
        });
    }


    function getPrice(price) {
        if (price !== '') {
            $('#price').val(price);
        } else {
            $('#price').val('');
        }

    }

</script>