<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/28/15
 * Time: 3:13 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';

$patient = (new PatientDemographDAO())->getPatient($_GET['id'], FALSE);
$bill_sources = (new BillSourceDAO())->getBillSources();

$return = (object)null;

$protect = new Protect();
$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true);
if(!$staff->hasRole($protect->accounts) && !$staff->hasRole($protect->hmo_officer)){
    exit($protect->ACCESS_DENIED);
}
if($_POST){
    if(!$staff->hasRole($protect->accounts) && !$staff->hasRole($protect->hmo_officer)){
        $return->status = "error";
        $return->message = "You do not have access to this function";
        exit(json_encode($return));
    }
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Bill.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/MiscellaneousItem.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/BillDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/BillSourceDAO.php';
    require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

    $pdo = (new MyDBConnector())->getPDO();
    $pdo->beginTransaction();
    $pat = (new PatientDemographDAO())->getPatient($_POST['patient_id'], FALSE, $pdo, null);
    //    service_group
    if(isset($_POST['item_id']) && !is_blank($_POST['item_id']) && $_POST['service_group'] != 9 ){
        $item = getItem($_POST['item_id'], $pdo);
    } else if($_POST['service_group'] == 9) {
        //you're trying to transfer a miscellaneous item
        $item = (new MiscellaneousItem())->setCode('MS00001')->setId(1)->setName($_POST['description']);
    } else {
        //todo: try to use the service_group category to create a new object item, or so
        $return->status = "error";
        $return->message = "Cannot determine item for transfer";
        exit(json_encode($return));
    }

    $src = (new BillDAO())->getSourceId($item);

    //charge the patient's hmo for this item
    $bil = new Bill();
    $bil->setPatient($pat);
    $bil->setDescription("".$_POST['description']);
    $bil->setSource( (new BillSourceDAO())->findSourceById($src, $pdo) );
    $bil->setItem($item);//$item
    $bil->setTransactionType("transfer-credit");
    $bil->setTransactionDate(date("Y-m-d H:i:s"));
    $bil->setAmount(floatval($_POST['amount']));
    $bil->setDiscounted(NULL);
    $bil->setDiscountedBy(NULL);
    $bil->setClinic($staff->getClinic());
    $bil->setBilledTo($pat->getScheme());
    if(!is_blank($_POST['auth_code'])){
        $bil->setAuthCode($_POST['auth_code']);
        $bil->setReviewed(TRUE);
    } else {
        $bil->setAuthCode(NULL);
        $bil->setReviewed(FALSE);
    }

    //patient pays for it
    $bil2 = new Bill();
    $bil2->setPatient($pat);
    $bil2->setDescription("".$_POST['description']);
    $bil2->setItem($item);//$item
    $bil2->setSource( (new BillSourceDAO())->findSourceById( $src, $pdo ) );
    $bil2->setTransactionType("transfer-debit");
    $bil2->setTransactionDate(date("Y-m-d H:i:s"));
    $bil2->setAmount(0 - floatval($_POST['amount']) );
    $bil2->setDiscounted(NULL);
    $bil2->setDiscountedBy(NULL);
    $bil2->setClinic($staff->getClinic());
    $bil2->setActiveBill('not_active');
    $bil2->setBilledTo( (new InsuranceScheme(1)) );
    $bil2->setReviewed(TRUE);

    //if there's a write-off and difference is greater than 0
    if(@$_POST['writeoff'] === "on"){
        if($_POST['difference'] > 0) {
            $bil3 = new Bill();
            $bil3->setPatient($pat);
            $bil3->setDescription("".$_POST['description']);
            $bil3->setItem($item);//$item
            $bil3->setSource( (new BillSourceDAO())->findSourceById( $src, $pdo ) );
            $bil3->setTransactionType("write-off");
            // like a debit
            $bil3->setTransactionDate(date("Y-m-d H:i:s"));
            $bil3->setAmount(0-$_POST['difference']);
            // amount just like a debit
            $bil3->setDiscounted(NULL);
            $bil3->setDiscountedBy(NULL);
            $bil3->setClinic($staff->getClinic());
            $bil3->setBilledTo( (new InsuranceScheme(1)) );
            $bil3->setAuthCode($_POST['auth_code']);
	          $bil3->setCancelledOn(date('Y-m-d H:i:s'));
	          $bil3->setCancelledBy(new StaffDirectory($_SESSION['staffID']));
        }
    }
    $bill=(new BillDAO())->addBill($bil, $_POST['quantity'], $pdo, NULL);
    $bill2=(new BillDAO())->addBill($bil2, $_POST['quantity'], $pdo, NULL);

    if(@$_POST['writeoff'] === "on" && $_POST['difference'] > 0 ){
        $bill3=(new BillDAO())->addBill($bil3, $_POST['quantity'], $pdo, NULL);
        if(is_null($bill3)){
            $pdo->rollBack();
            $return->status = "error";
            $return->message = "Write-Off action failed";
            exit(json_encode($return));
        }
    }
    if(is_null($bill) || is_null($bill2)){
        $pdo->rollBack();
        $return->status = "error";
        $return->message = "Sorry, transfer failed";

        exit(json_encode($return));
    }else {
        $pdo->commit();
        $return->status = "success";
        $return->message = "Credit Transferred";
        exit(json_encode($return));
    }
}
?>

<section style="width: 600px">
    <span></span>
    <form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: _form_saveHandler})">
        <label>Patient <select name="patient_id" required="required">
                <option value="<?=$patient->getId()?>"><?= $patient->getFullname()?></option>
            </select></label>
        <label>Insurance <select name="scheme_id" required="required">
                <option value="<?= $patient->getScheme()->getId() ?>"><?= $patient->getScheme()->getName() ?></option>
            </select></label>
        <label>Service Group <select name="service_group" required="required" placeholder="Select Service Group">
                <option></option>
                <?php foreach ($bill_sources as $source) {//$source=new BillSource();
                    //i saw blood,?
                    ?>
                <option value="<?=$source->getId()?>"><?= ucwords($source->getName())?></option>
                <?php } ?>

            </select></label>
        <label>Service/Item <select name="item_id" required="required" placeholder="Select a service/Item"><option></option></select></label>
        <label>Description <textarea name="description" required="required"></textarea></label>
        <label>Unit Amount Charged <input name="_amount" type="number" step="0.01" min="0" required="required"> </label>
        <label>Quantity <input name="quantity" value="1" type="number" step="1" min="1" required="required"> </label>
        <label>Total Amount Charged <span class="pull-right fadedText">(Amount to Transfer)</span><input name="amount" type="number" step="0.01" min="0" required="required"> </label>
        <div class="row-fluid">
            <label class="span9">Difference <input type="number" name="difference" readonly value="0"> </label>
            <label class="span3" style="margin-top: 25px;"><input type="checkbox" disabled name="writeoff"> Write Off Difference  </label>
        </div>

        <div class="row-fluid">
            <label class="span9">Auth. Code <input name="auth_code" type="text" required="required"> </label>
            <label class="span3" style="margin-top: 25px;"><input type="checkbox" name="reviewed"> To be reviewed</label>
        </div>


        <div class="btn-block">
            <button class="btn" type="submit"> Transfer </button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</section>
<script type="text/javascript">
    $(document).ready(function(){
        $('select[name="service_group"]').on('change', function () {
            var $this = $(this);
            var $scheme = $('select[name="scheme_id"]');
            if($this.val() != "" && $this.val()!=='9') { /*not miscellaneous and not blank*/
                $('select[name="item_id"]').prop("required", true);
                $.getJSON('/api/get_item_cost.php?s=' + $scheme.val()+'&group_id='+$this.val(), function (data) {
                    var items = ['<option value=""></option>'];
                    $.each( data, function( key, val ) {
                        /*if($this.val() == 2 /!*"drugs"*!/){
                            items.push( '<option value="' + val.item.code + '" data-price="'+val.sellingPrice+'">' + val.item.name + ' ('+val.item.generic.weight+' '+val.item.generic.name+' '+val.item.stockUOM+')</option>' );
                        } else */{
                            items.push( '<option value="' + val.item_code + '" data-price="'+val.selling_price+'">' + val.item_description + '</option>' );
                        }
                    });
                    $('select[name="item_id"]').html( items.join(""));
                });
            } else if($this.val() != "" && $this.val()==9){ // for miscellaneous
                var items = ['<option value=""></option>'];
                $('select[name="item_id"]').html( items.join(""));
                $('select[name="item_id"]').removeAttr("required");
            }
            $('select[name="item_id"]').val('').select2("val","").trigger('change');
            //reset the items
        }).trigger('change');
        var $select;
        $('select[name="item_id"]').on('change', function () {
            $select = $(this).select2("data");
            if ($select != null && typeof $select.element != "undefined"){
                pp = $($select.element[0]).data("price");
                $('input[name="_amount"]').val(pp).attr('max',pp).trigger('change');
                $('textarea[name="description"]').val($($select.element[0]).text());
            } else {
                $('input[name="_amount"]').val('').removeAttr('max').trigger('change');
                $('textarea[name="description"]').val('');
                $('input[name="amount"]').val(0).trigger('change');
                $('input[name="quantity"]').val(1).trigger('change');
            }
        }).trigger('change');


        $('input[name="_amount"],input[name="quantity"]').on('change', function(){
            //mainPrice = $($select.element[0]).data("price");

            newPrice = $('input[name="_amount"]').val()*$('input[name="quantity"]').val();
            $('input[name="amount"]').val(newPrice).trigger('change');
        });

        $('input[name="amount"]').on('change', function () {
            $('input[name="difference"]').val( (parseFloat($('input[name="_amount"]').val()*$('input[name="quantity"]').val() - $(this).val() )).toFixed(2) ).trigger('change');
        });

        $('input[name="difference"]').bind('change', function () {
           if($(this).val() > 0) {
               $('input[name="writeoff"]').prop('disabled', false);
           } else {
               $('input[name="writeoff"]').prop({'disabled': true, 'checked': false});
           }
        });
        $('input[name="reviewed"]').bind('change', function () {
           if($(this).is(":checked")) {
               $('input[name="auth_code"]').prop('required', false);
           } else {
               $('input[name="auth_code"]').prop({'required': true}).val('');
           }
        });
    });

    function _form_saveHandler(s){
        var data = JSON.parse(s);
        if(data.status==="error"){
            $('section > span').html(data.message).removeClass('warning-bar').addClass('warning-bar');
            $(".boxy-content").animate({ scrollTop: 0 }, "slow");
        } else if(data.status==="success"){
            Boxy.info(data.message);
            Boxy.get($(".close")).hideAndUnload();
            //the tab will auto-refresh because this window was invoked passing the `afterHide` option
        }
    }
</script>