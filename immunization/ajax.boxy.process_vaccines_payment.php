<?php /**
 * Created by JetBrains PhpStorm.
 * User: peter
 * Date: 10/24/13
 * Time: 1:24 PM
 * To change this template use File | Settings | File Templates.
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.vaccines.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$bill = new Bills ();
if($_POST){
    $totalPaying = 0;
    for($i=0;$i<count($_POST['amount']);$i++){
        if(!is_numeric($_POST['amount'][$i])){
            exit('error:One or more amounts is invalid');
        } else {
            $totalPaying += $_POST['amount'][$i];
        }
    }
    if (count(array_unique($_POST['payment_method'])) < count($_POST['payment_method'])){
        exit('error:Repeated payment methods.');
    }

    if($_POST['total_cost'] > $totalPaying) {
        exit('error:Your payment(s) total is '.number_format(($_POST['total_cost'] - $totalPaying),2).' short');
    }

    //exit( 'error:'.print_r($_POST, true));


    $patient_id = $_POST['pid'];
    require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
    mysql_select_db ( $database_dbconnection, $dbconnection );


    $sql2 = "INSERT INTO `patient_vaccine_queue` (`patient_id`, `vaccine_id`, `vaccine_level`, `time_queued`) VALUES ";
    $sql2_part = array();

    $VAC = new Vaccine_();

    $debit_part = 0;
    $vac_items_descriptions = array();
    $bill_source = '';
    for ($i = 0; $i < count($_POST['vaccine']); $i++) {
        $data = explode("|", $_POST['vaccine'][$i]);
        //note that the next two variables are
        $vacc_id = $data[0];
        $vacc_level = $data[1];
        $sql = "UPDATE patient_vaccine SET paid = TRUE WHERE patient_id = '$patient_id' AND vaccine_id = $vacc_id AND vaccine_level = $vacc_level";

        $sql2_part[] = " ('$patient_id', '$vacc_id', '$vacc_level', CURRENT_TIMESTAMP) ";//use to join the vaccine queue [multiple insert line]
        $result = mysql_query($sql);

        $vaccine = $VAC->getVaccineDetails($vacc_id);

        $bill_description = 'Vaccine charges ['.$vaccine['label'].']';
        $vac_items_descriptions[] = $vaccine['label'];

        $bill_amount = $vaccine['price'];// the price of the item according to patient insurance? handle it in the 'getting price'
        $debit_part = $debit_part + $bill_amount;
        $bill_source = 'vaccines';
        //bill the just "Administered" vaccine
//        $bill->addBillToQueue($patient_id, $bill_description,$bill_amount, $bill_source,'credit');//the credit part of the bill.

        if (!$result) {
            exit('error:An error has occurred, we could not finish processing the payment');
        }
    }
    $payment_description = 'Payment for '.implode(", ", $vac_items_descriptions)." vaccines" ;
    //register these payments
    for($i=0;$i<count($_POST['amount']);$i++){
//        $bill->payBill($patient_id,$payment_description, $_POST['amount'][$i], $bill_source, $_POST['payment_method'][$i],$_POST['payment_reference'][$i]) ;//the debit part of the bill.
    }
    $sql2 .= implode(", ", $sql2_part);
    //this is the insert into patient_vaccine_queue
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/PatientQueue.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/PatientDemograph.php';
    $patient = new PatientDemograph();
    $patient->setId($patient_id);
    $que = new PatientQueue();
    $que->setStatus('Active');
    $que->setType('Vaccination');
    $que->setPatient( $patient );
    $st = (new PatientQueueDAO())->addPatientQueue($que);

    $result2 = mysql_query($sql2);
    if($st===null){
        //how do we then treat this patient since he won't be on queue
        exit('error:An error has occurred, we could not add the patient to the waiting queue');
    }
    unset($vacc_id,$vacc_level);
    //we are done with "those" variables
    echo 'success:The selected vaccines have been successfully paid for, they can now be administered';
    exit;
}else { ?>

<div style="width:600px">
    <form method="post" action="<?=$_SERVER['SCRIPT_NAME'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
        <div class="loader"></div>
    <?php
    parse_str(rawurldecode($_REQUEST['data']));
    if ($action == "summarize_vaccine") {
        //require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.vaccines.php';
        $vaccine_post_data = $vaccine;
        $VACCINE=new Vaccine_();
        $pid = $patient;
        $ret = '<table class="table table-hover table-bordered">';
        if (count($vaccine_post_data) <= 0) {
            $ret .= '<tr><td><span class="alert alert-error">Oops! You selected no vaccine. Close this dialog and try again</span></td></tr>';
        } else {
            $ret .= '<thead><tr><th>Vaccine</th><th>Stage</th><th>Price</th></tr></thead>';
            for ($i = 0; $i < count($vaccine_post_data); $i++) {
                $vacc = explode("|", $vaccine_post_data[$i]);
                $vacc_id = $vacc[0];
                $vacc_level = $vacc[1];
                $V=$VACCINE->getVaccineDetails($vacc_id);
                $ret .= '<tr><td><input type="hidden" name="vaccine[]" value="'.$vacc_id.'|'.$vacc_level.'"/> '. $V['label'] .'</td><td>'.$vacc_level.'</td><td class="amount">'. number_format( $V['price'], 2) .'</td></tr>';
            }
            $ret .= '<tr><td colspan="3"><input type="hidden" name="pid" value="'.$pid.'"/><span class="alert alert-info">You selected the above vaccines costing a total of '.$currency->getSymbolLeft().number_format($totalCost,2).$currency->getSymbolRight().'. Select the payment method and Confirm the payment for them by clicking the button. <!--You have a credit balance of ('.x.' )--></span></td></tr>';
        }
        $ret .= '</table><input type="hidden" name="total_cost" value="'.$totalCost.'">';
    }
    echo $ret;
?>
        <label>Payments <span class="pull-right"><i class="icon-plus-sign"></i><a href="javascript:void(0)" id="addPaymentLine">add</a></span></label>
        <table class="table payments_container">
            <tr>
                <td valign="middle" style="vertical-align: middle"><a href="javascript:void(0)" onclick="removeRow(this)" class="action round_btn"><i class="icon-remove-sign"></i></a></td>
                <td><label>method<select name="payment_method[]" id="payment_method"></select></label></td>
                <td><label>amount<input type="text" name="amount[]" onchange="showIt()"></label> </td>
                <td><label>reference<input type="text" name="payment_reference[]" maxlength="20"></label> </td>
            </tr></table>
        <label><span class="pull-right" id="PaymentMessageIndicator"></span></label>
    <div class="btn-block">
        <?php if (count($vaccine_post_data) > 0) {?><button class="btn" type="submit">Confirm Payment</button><?php }?>
        <button type="button" class="btn-link" onclick="Boxy.get(this).hide()">Cancel</button>
    </div>
    </form>
    <script type="text/javascript">
        function removeRow(item){
            if($('.payments_container tr').length > 1){
                $(item).parents('.payments_container tr').remove();
            }
        }
        function showIt(){
            $("#PaymentMessageIndicator").html();
        }
        $(function(){
            $("#addPaymentLine").on('click', function(){
                //TODO: drop the data entered data during cloning.
                $('table.payments_container tr:last-child').after($('table.payments_container tr:first-child').clone());
            });
            setTimeout(function(){
                $.ajax({
                    url: '/billing/ajax.get_payment_methods.php',
                    dataType:'json',
                    beforeSend:function(){},
                    success:function(s){
                        var html = '';
                        for(var i=0;i< s.length;i++){
                            html += '<option value="'+s[i].id+'">'+s[i].name+'</option>';
                        }
                        $('[name="payment_method[]"]').html(html);
                    },
                    error:function(){}
                });
                //$('table.table').tableScroll({height: 200});
            },10);

        });
        function start(){
            $(".loader").html('<img src="/img/loading.gif"> processing the payment...please wait...');
        }
        function finished(s){
            var ret= s.split(":");
            if (ret[0]=="error"){
                $(".loader").html(ret[1]).addClass('alert-error');
            }else{
                if($(".loader").hasClass('alert-error')){
                    $(".loader").removeClass('alert-error')
                }
                $(".loader").html(ret[1]).addClass('alert-success');
                setTimeout(function(){
                    $('.close').click();
                },1000);
            }
        }
    </script>
    </div><?php }?>