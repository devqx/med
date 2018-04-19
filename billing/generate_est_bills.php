<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/13/17
 * Time: 11:27 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/EstimatedBillsDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/EstimatedBillLineDAO.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/EstimatedBills.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/EstimatedBillLine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$pid = isset($_GET['id'])? $_GET['id']: '';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;

$es_bills = (new EstimatedBillsDAO())->EstimatedBills(null,$page,$pageSize);
$total = $es_bills->total+1;
if ($total < 10){
    $code = '000'.$total;
}
elseif ($total < 100){
    $code = '00'.$total;
}
elseif ($total < 1000){
    $code = '0'.$total;
}
else{
    $code = $total;
}
$service_category = (new BillSourceDAO())->getBillSources();

if ($_POST){
    //$aid = isset($_POST['inpatient_id'])? $_POST['inpatient_id']:1;
    $estimate_gen = new EstimatedBills();
    $estimate_gen->setPatient((new PatientDemographDAO())->getPatient($_POST['pid'],False));
    $estimate_gen->setInpatient(1);

    $escode = 'ESB'.$code;
    $estimate_gen->setEsCode($escode);
    $narration = $_POST['narration'];
    $valid = $_POST['valid_till'];
    $scheme_id = $_POST['scheme_id'];

    $estimate_gen->setTotalEstimate(floatval($_POST['total']));
    $today = date('Y-m-d H:i:s');
    $estimate_gen->setDateCreated($today);
    $estimate_gen->setLastModified($today);
    $estimate_gen->setScheme($scheme_id);
    $estimate_gen->setNarration($narration);
    $estimate_gen->setValidTill($valid);
    $estimate_gen->setCreatedBy($this_user);
    $estimate_gen->setStatus('draft');


    if ($valid === ''){
        exit("error: Please Choose a valid date");
    }
    if ($valid < date('Y-m-d')){
        exit('error: Please choose recent date');
    }

    if ($narration === ''){
        exit("error: Please write a narration");
    }

    if ($_POST['pid'] === ''){
        exit("error:Please select a patient");
    }

    $es_bill_lines = !is_blank($_POST['item_request'])? $_POST['item_request']:'';
    if ($es_bill_lines === ''){
        exit("error: You have not added any item");
    }

    $data = json_decode($es_bill_lines);

    //$estimate_gen->setEstimateBillLines($data);
    $pdo->beginTransaction();
    $es = (new EstimatedBillsDAO())->AddEstimatedBill($estimate_gen,$pdo);
    if ($es === null){
        $pdo->rollBack();
        exit("error: Failed to generate estimated bill");
    }
    $lastId = $pdo->lastInsertId();
    //$bill_lines = new EstimatedBillLine();
    $pref_lines = [];
    foreach ($data as $es_lines){
        $es_lines = (object)$es_lines;
        $es_lines->estimated_bill_id = $lastId;
        $pref_lines[] = $es_lines;
    }
    //file_put_contents('/tmp/data.txt', json_encode($pref_lines));
    $bl= (new EstimatedBillLineDAO())->addEsBillLines($pref_lines,$pdo);

    if ($bl === null){
        $pdo->rollBack();
        exit("error: Failed to add bill lines");
    }
    $pdo->commit();

    exit("success: Estimated Bill generated");

}


?>
<style type="text/css">
    th{
        border-left: 1px solid #F9F9F9;
    }
    td{
        border-left: 1px solid #F9F9F9;
    }
</style>

<div style="width: 1000px;">
    <form method="post" action="/billing/generate_est_bills.php"
          onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
        <!--	<label class="output well well-small"></label> -->

       <div class="row-fluid">
           <div class="span6">
               <?php if ($pid === '') { ?><label>Patient </label><?php } ?>
               <label><input type="hidden" name="pid"
                             value="<?= (isset($_GET['id']) ? $_GET['id'] : null) ?>"></label>
           </div>
           <div class="span6">
               <label>Valid Date</label>
               <label><input type="text" name="valid_till" id="valid_till" placeholder="Choose valid Date"></label>
           </div>
       </div>
        <input type="hidden" name="inpatient_id" value="<?= (isset($_GET['aid'])) ? $_GET['aid'] : null?>">

        <div class="row-fluid">
         <div class="span12">
             <label>Note</label>
             <label><textarea name="narration" id="narration" cols="30" rows="4"></textarea></label>
         </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <label>Service Category</label>
                <select name="service_category" id="service_category">
                    <option>--Service Category--</option>
                    <?php
                    foreach ($service_category as $s_cat){
                        ?>
                        <option value="<?=$s_cat->getId()?>"><?= $s_cat->getName() ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>

        </div>
        <input type="hidden" name="item_request" id="item_request">
        <input type="hidden" name="scheme_id" id="scheme_id">
        <div class="row-fluid">
            <div class="span12">
                <label>Service Item</label>
                <label><input type="hidden" name="service_item" id="service_item"></label>
            </div>
            <div class="clear">
                <button class="btn btn-mini right" type="button" id="add-item"><i class="icon-plus-sign"></i></button>
            </div>
        <div class="row-fluid">
            <table class="table table-striped" width="950">

              <tr>
                  <td>
                      <div style="height:150px; overflow:scroll; border: solid 10px #f9f9f9;">
                          <table class="table table-striped" width="900" id="item_list">
                            <thead><tr>
                                <th>Item</th>
                                <th>Service Unit</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Amount</th>
                                <th>*</th>
                            </tr>
                            </thead>


                          </table>

                      </div>
                  </td>
              </tr>
            </table>

        </div>
        <div class="row-fluid">
<!--            <div class="span6"><input type="text" id="insured_bills" name="insured_bills" readonly></div>-->
            <div class="span12">Total Estimate:<input type="text" id="total" name="total" readonly></div>
        </div>
        <div class="btn-block">
            <button class="btn" type="submit" name="btn">Generate &raquo;</button>
            <?php
                 if ($this_user->hasRole($protect->bill_auditor)){
                     ?>
                     <!--<button class="btn" type="button" onclick="approveAndPrint()"></button>-->
            <?php
                 }
            ?>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>

<script type="application/javascript">
    var total = 0;
    var insure_bills = 0;
    var req_items = [];
    var selected_list = [];
    var str = [];
    var exist_item = [];
    var exist_id = [];
    $(document).ready(function () {
        $('#narration').summernote(SUMMERNOTE_MINI_CONFIG);
        $('select[name="service_category"]').on('change',function (c) {
            if (!c.handled){
                var cid = $(this).val();
                if (cid !== ''){
                    getServiceItems(cid);
                }

            }

        });

    });

    $('#valid_till').datetimepicker({
        //format: 'Y-m-d H:i',
        format: 'Y-m-d',
        timepicker: true,
        onShow: function (ct) {
            this.setOptions({minDate: new Date().toISOString().split('T')[0]});
        }
    });


    function start() {
        $(document).trigger('ajaxSend');
    }
    function done(s) {
        $.unblockUI();
        var returnData = s.split(":");
        if (returnData[0] === "error") {
            Boxy.alert(returnData[1]);
        }
        else if (returnData[0] ==='success'){
            Boxy.info(returnData[1]);
            reload();
            Boxy.get($('.close')).hideAndUnload();
        }

    }
    function getServiceItems(category_id) {
        $.getJSON('/api/get_item_cost.php?group_id='+category_id,function (data) {
            setServiceItems(data);
        })

    }

    function setServiceItems(data) {
        $('input[name="service_item"]').select2({
            placeholder:"select Items",
            width:'100%',
            allowClear:true,
            multiple:true,
            data: {results:data, text:'item_description'},
            formatResult:function (data) {
                return data.item_description+'['+data.default_price+']';

            },
            formatSelection: function (data) {
                return data.item_description+'['+data.default_price+']';

            }
        }).on('change',function (s_item) {
           if (! s_item.handled){
               if(s_item.added !==undefined) {
                   if (_.includes(exist_id, s_item.added.item_code)) {
                       Boxy.alert(s_item.added.item_description + '[' + s_item.added.selling_price + ']' + ' exist in the list');
                       $('input[name="service_item"]').select2('data','');
                   }
                   else {
                       getQuantity(s_item.added, function () {
                           _.each($(s_item.target).select2("data"), function (i) {
                               //request.push({id: i.id, quantity: i.quantity});

                           });
                           //$('[name="lab-reqs2"]').val(JSON.stringify(request));
                       });
                   }

                   s_item.handled = true;
               }
           }

        });

    }

    var getQuantity = function (obj, callbackFn) {
        vex.dialog.prompt({
            message: 'Specify the quantity',
            placeholder: '',
            value: 1,
            overlayClosesOnClick: false,
            beforeClose: function (e) {
                e.preventDefault();
            },
            callback: function (value) {
                if (value !== false && value !== '') {
                    obj.quantity = value;
                } else {
                    obj.quantity = 1;
                }
                if (typeof callbackFn !== "undefined") {
                    callbackFn();
                }
            }, afterOpen: function ($vexContent) {
                var $submit = $($vexContent).find('[type="submit"]');
                $submit.attr('disabled', true);
                $('.vex-dialog-prompt-input').attr('autocomplete', 'off');
                $vexContent.find('.vex-dialog-prompt-input').on('input', function () {
                    if ($(this).val().trim() != '') {
                        $submit.removeAttr('disabled');
                    } else {
                        $submit.attr('disabled', true);
                    }
                }).trigger('input');
            }
        });
    }



    $('#add-item').on('click',function () {
        var it = $('#service_item').val() == ''? '': $('#service_item').val();
        if (it !== ''){
            var sit = $("#service_category option:selected");
            var sel_data = $('#service_item').select2('data');
            _.each(sel_data,function (sel) {
                sel.service_description = sit.text();
                req_items.push(sel);
                exist_item.push(sel.item_code);
                var amount = sel.quantity * sel.selling_price;
                str.push('<tr class="remove_item" id="'+sel.item_code+'"><td>'+sel.item_description+'</td><td>'+sit.text()+'</td><td>'+sel.quantity+'</td><td>'+sel.selling_price+'</td><td>'+amount+'</td></tr>');

            })
        }

        $('#item_list').append(str);
        $('#item_list').on('click','.cancel',function () {
            var id = $(this).data('id');
            $.grep(selected_list, function(e){
                return e.item_code != id;
            });
            //console.log(selected_list);
            $(this).parent().parent().remove();
        });

        $('input[name="service_item"]').select2('data','');
        _.each(exist_item,function (eit) {
            exist_id.push(eit);
        });
        _.each(req_items,function (rqt) {
              selected_list.push(rqt);
              total = parseFloat(total) + rqt.quantity * parseFloat(rqt.selling_price);
              $('input[name="total"]').val(total);

        });
        $('input[name="item_request"]').val(JSON.stringify(selected_list));
        //console.log(all);
        exist_item = [];
        req_items = [];
        str = [];
    })

//    var  d = $.grep(selected_list,function (evt) {
//        return evt.item_code != id;
//
//    })
    <?php if($pid === ''){?>
    $('.boxy-content [name="pid"]').select2({
        placeholder: "Search and select patient to generate estimated bill",
        minimumInputLength: 3,
        width: '100%',
        allowClear: true,
        ajax: {
            url: "/api/search_patients.php",
            dataType: 'json',
            data: function (term, page) {
                return {
                    q: term
                };
            },
            results: function (data, page) {
                return {results: data};
            }
        },
        formatResult: function (data) {
            var details = [];
            details.push(data.patientId ? "EMR ID:"+data.patientId : null);
            details.push(data.fname ? data.fname : null);
            details.push(data.mname ? data.mname : null);
            details.push(data.lname ? data.lname : null);
            return implode(" ", details);
            //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
        },
        formatSelection: function (data) {
            var details = [];
            details.push(data.patientId ? "EMR ID:"+data.patientId : null);
            details.push(data.fname ? data.fname : null);
            details.push(data.mname ? data.mname : null);
            details.push(data.lname ? data.lname : null);
            return implode(" ", details);
            //return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
        },
        id: function (data) {
            return data.patientId;
        }
    }).on('change',function (p) {
        $('input[name="scheme_id"]').val(p.added.insurance.scheme.id);
       // console.log(p.added.insurance.scheme.id);

    });
    <?php } ?>

    function approveAndPrint() {

        Boxy.ask("Are you sure you want to approve and print this document, this action is not reversible?", ['Yes', 'No'], function (choice) {
            if (choice == 'Yes') {
                var item_request = $('input[name="item_request"]').val();
                var pid  = $('input[name="pid"]').val();
                var valid_till = $('input[name="valid_till"]').val();
                var narration = $('textarea[name="narration"]').val();
                var scheme_id = $('input[name="scheme_id"]').val();
                var total = $('input[name="total"]').val();

                jQuery.ajax({
                    url:'/billing/bill_estimate_apr.php',
                    type:'POST',
                    cache:false,
                    data:{pid:pid,valid_till:valid_till,narration:narration,scheme_id:scheme_id,total:total,item_request:item_request},
                    success: function (data) {
                        var obj = jQuery.parseJSON(data);
                        Boxy.info('Successful!');
                        reload();
                        Boxy.get($('.close')).hideAndUnload();
                        var url = '/billing/estimated_bill_sheet.php?id='+obj.esid+'&pid='+obj.pid+'&reprint';
                        window.open(url, '_blank');
                    },
                    error:function (data) {
                        Boxy.alert(data);
                    }
                });
            }
        });

    }

</script>