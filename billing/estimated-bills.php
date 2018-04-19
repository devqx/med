<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/3/15
 * Time: 3:23 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/EstimatedBillsDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false);

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$patientId = !is_blank(@$_POST['patient_id']) ? @$_POST['patient_id'] : null;
$staffId = !is_blank(@$_POST['staff_id']) ? @$_POST['staff_id'] : null;


//$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/barcode.php?text=$code";



$data = (new EstimatedBillsDAO())->EstimatedBills($patientId,$page,$pageSize,null);
$totalSearch = $data->total;
?>



<div class="row-fluid">
    <label class="span12">
        <input type="hidden" name="patient_id">
    </label>
<!--    <label class="span6">-->
<!--        <input type="hidden" name="staff_id" placeholder="-- Filter by Staff --">-->
<!--    </label>-->
</div>

<?php
 if ($this_user->hasRole($protect->bill_auditor)){
     ?>
     <div class="menu-line">
         <div>
             <a href="javascript:void(0)" id="to_approve">Pending Approval</a> | <a href="javascript:void(0)" id="open_bills">Open Estimates</a>
         </div>
     </div>
<?php
 }

?>

<div id="estimatedBills">
    <div class="menu-head">
        <span id="newLink"><a href="javascript:void(0)" onClick="Boxy.load('/billing/generate_est_bills.php?id=<?= (isset($_GET['id'])) ?><?= (isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '') ?>',{title: 'Generate Bill Estimate'})">Generate Estimated Bill</a></span>
    </div>
    <table id="estimatedBills" class="table table-striped">
        <thead>
        <tr>
            <th>ESB#</th>
            <th>Patient</th>
            <th>Insurance Scheme</th>
            <th>Valid Till</th>
            <th>Date Created</th>
            <th>Estimated Total</th>
            <th>*</th>
        </tr>
        </thead>
        <?php
        //var_dump($data);
           foreach ($data->data as $estimated_bill){

               ?>
               <tr>
                   <td><?= $estimated_bill->escode ?></td>
                   <td><a href="/patient_profile.php?id=<?=$estimated_bill->PatientID?>"><?= $estimated_bill->Patient ?></a></td>
                   <td><?= $estimated_bill->Scheme ?></td>
                   <td><?= $estimated_bill->period ?></td>
                   <td><?= $estimated_bill->created_on?></td>
                   <td><?=$estimated_bill->total?></td>
                   <td>
                     <?php if ($estimated_bill->status ==='approved'){
                         ?>
                         <a target="_blank"  href="/billing/estimated_bill_sheet.php?id=<?=$estimated_bill->esid?>&pid=<?=$estimated_bill->PatientID?>&reprint">View-Print</a>
                       <?php
                     }
                     else{
                         ?>
                         <span class="fadedText">Pending Approval</span> &nbsp;|&nbsp;<a href="javascript:void(0)" onclick="Boxy.load('/billing/boxy.edit_estimated_bills.php?id=<?=$estimated_bill->esid?>&pid=<?=$estimated_bill->PatientID?>')">View-Edit</a>
                       <?php

                     }
                     ?>
                   </td>
               </tr>
         <?php
           }
        ?>

    </table>
    <div class="list12 dataTables_wrapper no-footer">
        <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
            results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
        </div>
        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a id="DataTables_Table_1_first" data-page="0"
               class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
                records</a>
            <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
               class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
                records</a>
            <a id="DataTables_Table_1_last"
               class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
               data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_next"
               class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
               data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
</div>

<script type="text/javascript" src="/js/JsBarcode.all.min.js"></script>
<script type="text/javascript" src="/assets/jquery-print/jQuery.print.js"></script>
<link rel="stylesheet" href="/style/def.css" media="print"/>
<link href="/style/bootstrap.css" rel="stylesheet" type="text/css" media="print"/>

<script>
    function post(e){
        if (!e.handled) {
            reload(0);
            e.handled = true;
        }
    }
    $(document).ready(function () {
        $('input[name="patient_id"]').select2({
            placeholder: "Search by any of Patient's Name, PatientID, EMRID",
            allowClear: true,
            minimumInputLength: 3,
            width: '100%',
            formatResult: function (data) {
                return data.fullname + " -" + data.id + (data.lid.trim() !== "" ? "[" + data.lid + "]" : "") + ", Phone: " + data.phone;
            },
            formatSelection: function (data) {
                return data.fullname + " -" + data.id + ", " + data.sex + ", " + moment(data.dob).fromNow(true) + " old " + (typeof data.vitalSigns !== "undefined" && typeof data.vitalSigns.weight !== "undefined" ? ", " + data.vitalSigns.weight.value + "kg" : "");
            },
            formatNoMatches: function (term) {
                return "Sorry no record found for '" + term + "'";
            },
            formatInputTooShort: function (term, minLength) {
                return "Please enter the patient name or ID";
            },
            ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
                url: '/api/search_patients.php',
                dataType: 'json',
                data: function (term, page) {
                    return {
                        q: term, // search term
                        limit: 100,
                        asArray: true,
                        medical: true
                    };
                },
                results: function (data, page) { // parse the results into the format expected by Select2.
                    // since we are using custom formatting functions we do not need to alter remote JSON data
                    return {results: data};
                }
            }
        }).change(function (evt) {
            if (evt.added !== undefined) {
                $.post("/billing/estimated-bills.php", {patient_id: evt.added.id, page: 0}, function (s) {
                    $("#estimatedBills").html($(s).filter("#estimatedBills").html());
                });
            } else if (evt.removed !== undefined && evt.added == undefined) {
                $.post("/billing/estimated-bills.php", {page: 0}, function (s) {
                    $("#estimatedBills").html($(s).filter("#estimatedBills").html());
                });
            }
        });

        $('[name="staff_id"]').select2({
            placeholder: $(this).attr('placeholder'),
            allowClear: true,
            minimumInputLength: 3,
            width: '100%',
            formatResult: function (data) {
                return data.fullname + "; " + (data.specialization == null ? "" : data.specialization.name);
            },
            formatSelection: function (data) {
                return data.fullname + "; " + (data.specialization == null ? "" : data.specialization.name);
            },
            ajax: {
                url: '/api/search_staffs.php',
                dataType: 'json',
                data: function (term, page) {
                    return {
                        q: term, // search term
                        limit: 100,
                        asArray: true
                    };
                },
                results: function (data, page) {
                    return {results: data};
                }
            }
        }).change(function(e){
            post(e);
        });

        $('input[name="date_start"]').datetimepicker({format: 'Y-m-d', timepicker: false});
        $('input[name="date_stop"]').datetimepicker({format: 'Y-m-d', timepicker: false});

        $('#bill_source_ids').select2();
    });
    $(document).on('click', '.authorize_transaction_link', function (e) {
        var tid = $(e.target).data("id");
        if (!e.handled) {
            Boxy.load("authorize_transaction.php?tid=" + tid, {
                title: "Authorize Transaction", afterHide: function () {
                    reload(0);
                }
            });
            e.handled = true;
        }
    });
    $(document).on('click', '.list12.dataTables_wrapper a.paginate_button', function (e) {
        if (!e.clicked) {
            var page = $(this).data("page");
            if (!$(this).hasClass("disabled")) {
                reload(page);
            }
            e.clicked = true;
        }
    });

    $('#date_filter').live('click', function (e) {
        if (!e.handled) {
            reload(0);
            e.handled = true;
        }
    });

    function reload(page) {
        data = {page: page, patient_id: $('[name="patient_id"]').val(), staff_id: $('[name="staff_id"]').val(), bill_source_ids: $('#bill_source_ids').val(), date_from: $('#date_start').val(), date_to: $('#date_stop').val()};
        $.post("/billing/estimated-bills.php", data, function (s) {
            $("#estimatedBills").html($(s).filter("#estimatedBills").html());
        });
    }

    function generateBill(id) {
        Boxy.load('/billing/generate_est_bills.php?id='+id,{title: 'Generate Estimated Bill'});
    }

    function approveBill(id,pid) {
        jQuery.ajax({
            url: '/api/approve_estimated_bill.php',
            type:'POST',
            data:{id:id,pid:pid},
            cache:false,
            success:function (data) {
                Boxy.info('Approved');
                reload();
                //Boxy.get($(".close")).hideAndUnload();

            },
            error:function () {
                Boxy.alert('Failed to approve');
            }
        });

    }

//    $('#to_approve').click(function (e) {
//        if (!e.handled){
//            $('#contentPane').load('/billing/bill_estimates_toapprove.php');
//            e.handled = true;
//        }
//    });

    $(document).on('click','#to_approve',function (e) {
       if (!e.handled){
           $('#estimatedBills').load('/billing/bill_estimates_toapprove.php');
           e.handled = true;
       }
    }).on('click','#open_bills',function (e) {
        if (!e.handled) {
            $('#estimatedBills').load('/billing/open_bill_estimates.php');
            e.handled = true;
        }
    });
</script>

