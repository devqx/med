<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 10/3/17
 * Time: 4:43 PM
 */
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsurerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProgressNoteDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';


$currency = (new CurrencyDAO())->getDefault();

$insurance_id = (new InsuranceSchemeDAO())->getInsuranceSchemes();
$provider = (new InsurerDAO())->getInsurers(FALSE);

$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? TRUE : FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 100;
$totalSearch = 0;

$claimsReport = array();

if ($date === TRUE) {
    $data = (new ClaimDAO())->getClaimsReport($_REQUEST['from'], $_REQUEST['to'], @$_REQUEST['insurance_scheme_id'], @$_REQUEST['provider'], $page, $pageSize);
    $totalSearch = $data->total;
    $claimsReport = $data->data;
}
$return = [];
foreach ($claimsReport as $lines) {
	$pnotes_ = "";
	$line_ids = array_filter(explode(',', $lines->line_ids));
	
     if( $lines->type == 'op'){
	     $opnotes = (new VisitNotesDAO())->getEncounterNotes($lines->encounter_id, 'a');
	     foreach ($opnotes as $note){
		     $pnotes_  .= "<li>$note->description</li>";
	     }
	     
     }else { // use progress note table and pass in_patient_id
	     $ipnotes = (new ProgressNoteDAO())->getProgressNotes($lines->encounter_id, True, 'g');
	     foreach ($ipnotes as $note){
		     $pnotes_ .= "<li>$note->note</li>";
	     }
     }
	   	
	
	
    foreach ($line_ids as $id) {
        $line = (new BillDAO())->getBill($id, true);
        $re = new stdClass();
        
        if($line){
		        $re->claimId = $lines->id;
		        $re->claimDate = $lines->create_date;
		        $re->type_ = $lines->type == 'op' ? 'Out Patient': 'In Patient';
		        $re->reason = $lines->reason;
            $re->Diagnosis = $pnotes_;
            $re->item_code = $line->getItemCode();
            $re->transaction_date = $line->getTransactionDate();
            $re->Description = $line->getDescription();
            $re->BillSource = $line->getSource()->getName();
            $re->Amount = $line->getAmount();
            $re->Code = $line->getAuthCode();
            $re->quantity = $line->getQuantity();
            $re->insurance = $line->getBilledTo()->getName();
            $re->Patient = $line->getPatient()->getFullName();
            $re->Phone = $line->getPatient()->getPhoneNumber();
            $re->cliniId = $line->getPatient()->getId();
            $re->errolleeId = (new PatientDemographDAO())->getPatient($line->getPatient()->getId(), TRUE)->getInsurance()->getEnrolleeId();

        }
        $return[] = $re;
    }
}

?>


<style type="text/css">
    .filter .btn {
        float: right;
        margin-top: 24px;
        white-space: nowrap;
    }

    .filter .span1 {
        margin-left: 0;
    }

    #exportIT {
        margin-left: 1%;
        width: 8%;
    }
</style>
<div><a class="btn-link" href="/pm/reporting/index.php">&laquo; Back</a></div>
<form id="filterForm" class="document" method="post" action="/pm/reporting/claims.php">
    <h4>Claims Report</h4>
    <div class="clearfix filter row-fluid">
        <label class="span2">From<input type="text" name="from"
                                        value="<?= (isset($_REQUEST['from']) ? $_REQUEST['from'] : '') ?>" id="from"
                                        placeholder="Select start date"/></label>
        <label class="span2">To:<input type="text" name="to"
                                       value="<?= (isset($_REQUEST['to']) ? $_REQUEST['to'] : '') ?>" id="to"
                                       placeholder="Select end date" disabled="disabled"/></label>
        <label class="span2">
            Filter by Payer
            <select id="provider" name="provider" data-placeholder="Select provider">
                <option></option>
                <?php foreach ($provider as $k => $refs) { ?>
                    <option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['provider']) && $_REQUEST['provider'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
                <?php } ?>
            </select>
        </label>
        <label class="span3">
            Filter by Insurance Scheme
            <select id="insurance_scheme_id" name="insurance_scheme_id" data-placeholder="Select insurance scheme">
                <option></option>
                <?php foreach ($insurance_id as $k => $refs) { ?>
                    <option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['insurance_scheme_id']) && $_REQUEST['insurance_scheme_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
                <?php } ?>

            </select>
        </label>

        <button class="btn span" id="exportIT" type="button"><i class="fa fa-file-excel-o"></i> Export</button>
        <button type="submit" class="btn span1">Show</button>
    </div>
</form>
<div class="document" style="overflow: scroll;">
    <?php if (isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from'] != '') { ?>
        <h3 style="text-align: center">Claims Report for
            <br>PERIOD:
            <span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' . (($_REQUEST['to'] == '') ? date('Y M d') : date("Y M d", strtotime($_REQUEST['to']))) ?>
                ]</span></h3>
    <?php } ?>
    <div id="claim_report_container">
        <?php if ($totalSearch < 1) {
            echo '<div class="notify-bar">There are no claims reports</div>';
        } else { ?>
            <table class="table table-striped table-hover no-footer table-bordered">
                <thead>
                <tr>
                    <th>Claim ID</th>
                    <th>Claim Date</th>
                    <th>Hospital ID</th>
                    <th>Patient</th>
                    <th>Phone Number</th>
                    <th>Scheme Name</th>
                    <th>Enrolle ID</th>
                    <th>Type</th>
                    <th>Transaction Date</th>
                    <th>Service</th>
                    <th>Description</th>
	                  <th>Item Code</th>
	                 <th>Reason</th>
                    <th>Diagnosis</th>
                    <th>PA Code</th>
                    <th>Quantity</th>
                    <th>Amount (<?= $currency ?>)</th>
	                  
                </tr>
                </thead>
                <?php if (isset($return) && sizeof($return) > 0) {
                    foreach ($return as $report) {?>
                            <tr>
                                <td><?= $report->claimId ?></td>
                                <td nowrap><?= date('M jS, Y', strtotime($report->claimDate)) ?></td>
                                <td nowrap><?= $report->cliniId ?></td>
                                <td nowrap><?= $report->Patient ?></td>
                                <td nowrap><?= $report->Phone ?></td>
                                <td  nowrap><?= $report->insurance ?></td>
                                <td nowrap><?= $report->errolleeId ?></td>
                                <td nowrap><?= $report->type_ ?></td>
                                <td nowrap><?= date('M jS, Y', strtotime($report->transaction_date)) ?></td>
                                <td  nowrap><?= ucfirst(str_replace('_', ' ', $report->BillSource) ) ?></td>
                                <td nowrap><?= ucwords($report->Description) ?></td>
	                              <td nowrap> <?= $report->item_code ?> </td>
	                            <td nowrap> <?= ucwords($report->reason)  ?> </td>
                                <td nowrap><ul>
                                    <?= $report->Diagnosis ?>
                                    </ul>
                                    </td>
                                <td nowrap><?= $report->Code ?></td>
                                <td nowrap><?= $report->quantity ?></td>
                                <td class="amount" nowrap><?= number_format(abs($report->Amount), 2) ?></td>
                            </tr>
                        <?php }
                } ?>
            </table>
            <div class="list1 dataTables_wrapper no-footer">
                <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                     aria-live="polite"> <?= $totalSearch ?>
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
                       data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
                        records</a>
                    <a id="DataTables_Table_1_next"
                       class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
                       data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
                        records</a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
        if (!e.clicked) {
            var page = $(this).data("page");
            if (!$(this).hasClass("disabled")) {
                $.post('/api/find_claims.php', {
                    from:'<?=@$_REQUEST['from']?>',
                    to:'<?=@$_REQUEST['to']?>',
                    insurance_scheme_id:'<?=@$_REQUEST['insurance_scheme_id']?>',
                    provider:'<?=@$_REQUEST['provider']?>',
                    page: page
                }, function (s) {
                    $('#claim_report_container').html(s);
                });
            }
            e.clicked = true;
        }
    });
    $(document).ready(function () {
        $("#from").datetimepicker({
            format: 'Y-m-d',
            formatDate: 'Y-m-d',
            timepicker: false,
            onChangeDateTime: function (dp, $input) {
                if ($input.val().trim() != "") {
                    $("#to").val('').removeAttr('disabled');
                }
                else {
                    $("#to").val('').attr({'disabled': 'disabled'});
                }

            }
        });
        $("#to").datetimepicker({
            format: 'Y-m-d',
            formatDate: 'Y-m-d',
            timepicker: false,
            onShow: function (ct) {
                this.setOptions({minDate: $("#from").val() ? $("#from").val() : false});
            },
            onSelectDate: function (ct, $i) {

            }
        });

        if ($("#from").val().trim() != "") {
            $("#to").removeAttr('disabled');
        }
        $("#insurance_scheme_id").select2({
            allowClear: true,
            width: '100%'
        });
        $("#provider").select2({
            allowClear: true,
            width: '100%'
        });
        $('#exportIT').on('click', function (e) {
            if (!e.handled) {
                window.open('/excel.php?dataSource=claim&filename=Claim_Report&from=<?=(isset($_REQUEST['from'])) ? $_REQUEST['from'] : ''?>&to=<?=(isset($_REQUEST['to'])) ? $_REQUEST['to'] : ''?>&insurance_scheme_id=<?=(isset($_REQUEST['insurance_scheme_id'])) ? $_REQUEST['insurance_scheme_id'] : ''?>&provider=<?=(isset($_REQUEST['provider'])) ? $_REQUEST['provider'] : ''?>', '_blank');
                e.handled = true;
                e.preventDefault();
            }
        });
        $('select[name="provider"]').on('change', function () {
	        var output = "";
	        var prov_id = $(this).val();
	        if(prov_id != '' && prov_id != undefined){
	        	$.getJSON('/api/insuranceSchemes.php?full="full"', function (data) {
	        		_.each(data, function (obj) {
	        			if(obj.insurer.id == prov_id){
					        output += '<option value="' + obj.id + '" >' + obj.name + '</option>';
				        }
			        });
	        		$('select[name="insurance_scheme_id"]').html(output);
		        });
	        }else{
		        $.getJSON('/api/insuranceSchemes.php', function (data) {
			        _.each(data, function (obj) {
				        output += '<option value="' + obj.id + '" >' + obj.name + '</option>';
			        });
			        $('select[name="insurance_scheme_id"]').html(output);
		        })
	        }
        });

    });

</script>