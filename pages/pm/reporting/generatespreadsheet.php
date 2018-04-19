<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.patient.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/insuranceSchemes.php';

$hmorpt = $hmorpt1 = $hmorpt2 = $hmorpt3 = $hmorpt4 = array();
$h_ = new Manager();
if(isset($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['scheme'])){
//    $hmorpt = $h_->getHMOReports_($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['scheme']);
//    $hmorpt1 = $h_->getHMOReports_one($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['scheme']);
//    $hmorpt2 = $h_->getHMOReports_two($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['scheme']);
//    $hmorpt3 = $h_->getHMOReports_three($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['scheme']);
    $hmorpt4 = $h_->getHMOReports_four($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['scheme']);
//    error_log(json_encode($hmorpt3));
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientDemographDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/StaffDirectoryDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientLabDAO.php";
$table_data = array();

if(count($hmorpt4)>0){
    $report = array();
    for($r=0; $r<count($hmorpt4); $r++){
        $patient = (new PatientDemographDAO())->getPatient($hmorpt4[$r]['patient_ID'], FALSE, NULL, TRUE);
        $age = ($h_->getPatientAgeInMonths($hmorpt4[$r]['patient_ID']) > 12) ? round($h_->getPatientAgeInMonths($hmorpt4[$r]['patient_ID']) / 12, 0, PHP_ROUND_HALF_DOWN) . ' Year(s)' : $h_->getPatientAgeInMonths($hmorpt4[$r]['patient_ID']) . ' Month(s)';

        $bill_source_amount = array_values(array_unique(explode(',',$hmorpt4[$r]['bs_amount']), SORT_REGULAR));
        $bills = array();
        for($i=0; $i<count($bill_source_amount); $i++){$bills[] = explode('=', $bill_source_amount[$i]);}
        $registration_amt_ = $consultancy_amt_ = $procedure_amt_ = $scans_amt_= $admissions_amt_ = $labs_amt_ = $drugs_amt_ = $misc_amt_ = array();
        foreach($bills as $k=>$b){
            if(isset($b[0]) && $b[0]=='registration'){ $registration_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='consultancy'){ $consultancy_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='procedure'){ $procedure_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='scans'){ $scans_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='admissions'){ $admissions_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='labs'){ $labs_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='drugs'){ $drugs_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='misc'){ $misc_amt_[] = $b[1]; }
        }
        $registration_amt = count($registration_amt_)> 0 ? array_sum(array_values($registration_amt_)) : '0';
        $consultancy_amt = count($consultancy_amt_)> 0 ? array_sum(array_values($consultancy_amt_)) : '0';
        $procedure_amt = count($procedure_amt_)> 0 ? array_sum(array_values($procedure_amt_)) : '0';
        $scans_amt = count($scans_amt_)> 0 ? array_sum(array_values($scans_amt_)) : '0';
        $admissions_amt = count($admissions_amt_)> 0 ? array_sum(array_values($admissions_amt_)) : '0';
        $labs_amt = count($labs_amt_)> 0 ? array_sum(array_values($labs_amt_)) : '0';
        $drugs_amt = count($drugs_amt_)> 0 ? array_sum(array_values($drugs_amt_)) : '0';
        $misc_amt = count($misc_amt_)> 0 ? array_sum(array_values($misc_amt_)) : '0';
        $total_bills = $registration_amt + $consultancy_amt + $procedure_amt + $scans_amt + $admissions_amt + $labs_amt + $drugs_amt + $misc_amt;

        $diagnosis = $consultant = $consultant_ = $drugs = array();

        $labGroup = (new PatientLabDAO())->getLabbyPatientDate($hmorpt4[$r]['patient_ID'], $hmorpt4[$r]['when']);
        $diagnosis_ = $h_->getDiagnosis($hmorpt4[$r]['patient_ID'], $hmorpt4[$r]['when']);
        if(count($diagnosis_)>0){
            for($c=0; $c<count($diagnosis_); $c++){
                if(isset($diagnosis_['case'][$c])){
                    $diagnosis[] = $diagnosis_['case'][$c];
                }
                if(isset($diagnosis_['consultant'][$c]) && $diagnosis_['consultant'][$c]!='00000000000'){
                    $consultant_[] = $diagnosis_['consultant'][$c];
                }
            }
        }

        $drugs_given = $h_->getdrugrGiven($hmorpt4[$r]['patient_ID'], $hmorpt4[$r]['when']);
        if(count($drugs_given)>0){
            for($d=0; $d<count($drugs_given); $d++){
                if(isset($drugs_given['drugs'][$d])){
                    $drugs[] = $drugs_given['drugs'][$d];
                }
                if(isset($drugs_given['consultant'][$d]) && $drugs_given['consultant'][$d]!='00000000000'){
                    $consultant_[] = $drugs_given['consultant'][$d];
                }
            }
        }

        $consultant_ = array_unique($consultant_);
        if(count($consultant_)>0){
            foreach($consultant_ as $c){
                $consultant[] = (new StaffDirectoryDAO())->getStaff($c)->getFullname();
            }
        }

        $table_data[$r]['S/N'] =  ($r + 1) ;
        $table_data[$r]['Enrollee Name'] =  (isset($patient))? $patient->getFullname() : '';
        $table_data[$r]['Enrollee ID'] =  $hmorpt4[$r]['enrollee_number'];
        $table_data[$r]['Hospital Number'] =  $hmorpt4[$r]['legacy_patient_id'];
        $table_data[$r]['Company'] = '';
        $table_data[$r]['Date of Encounter'] =  $hmorpt4[$r]['when'] ;
        $table_data[$r]['Age'] =  $age ;
        $table_data[$r]['Sex'] =  ucfirst($hmorpt4[$r]['sex']);
        $table_data[$r]['Blood Pressure'] =  implode(', ', $h_->getBloodPressure($hmorpt4[$r]['patient_ID'], $hmorpt4[$r]['when']));
        $table_data[$r]['P.A. Code'] =  $hmorpt4[$r]['auth_code'];
        $table_data[$r]['Diagnosis'] =  implode(', ', $diagnosis);
        $table_data[$r]['Retiree'] = '';
        $table_data[$r]['Spouse / Insured'] = '';
        $table_data[$r]['Police Officer'] = '';
        $table_data[$r]['Spouse / Dependant'] = '';
        $table_data[$r]['Lab ID'] = (is_null($labGroup))? '' : $labGroup->getLabGroup()->getGroupName();
        $table_data[$r]['Drugs Given'] = implode(', ', $drugs);
        $table_data[$r]['Attending Specialist'] = implode(', ', $consultant);
        $table_data[$r]['REG'] =  number_format($registration_amt,2);
        $table_data[$r]['ANC REG'] = '';
        $table_data[$r]['Consultant'] =  number_format($consultancy_amt,2);
        $table_data[$r]['Sp. Consultant'] = '';
        $table_data[$r]['Procedure'] =  number_format($procedure_amt,2);
        $table_data[$r]['Phototherapy / USS / X-Ray'] =  number_format($scans_amt,2);
        $table_data[$r]['Acc/Feeding Nurse Care'] =  number_format($admissions_amt,2);
        $table_data[$r]['Lab'] =  number_format($labs_amt,2);
        $table_data[$r]['Drugs'] =  number_format($drugs_amt,2);
        $table_data[$r]['Misc Amt'] =  number_format($misc_amt,2);
        $table_data[$r]['Total'] =  number_format($total_bills,2);
    }

    /*for($i=0; $i<count($table_data); $i++) {
        for($a=0; $a<count($hmorpt3); $a++){
            if($table_data[$i]['Date of Encounter']==$hmorpt3[$a]['when'] && $table_data[$i]['EID']==$hmorpt3[$a]['patient_id']) {
                $table_data[$i]['EEID'] = $hmorpt3[$a]['patient_id'];
                $table_data[$i]['Lab ID'] = $hmorpt3[$a]['lab_test'];
                $table_data[$i]['Drugs Given'] = $hmorpt3[$a]['drug_name'];
                $table_data[$i]['Attending Specialist'] = $hmorpt3[$a]['consultant'];
            }
            else {
                $table_data[$i]['EEID'] = $hmorpt3[$a]['patient_id'];
                $table_data[$i]['Lab ID'] = ' ';
                $table_data[$i]['Drugs Given'] = ' ';
                $table_data[$i]['Attending Specialist'] = ' ';
            }
        }
    }*/
}
/*if(count($hmorpt) > 0){
    $report = array();
    for ($r=0; $r<count($hmorpt); $r++) {
        $patient = (new PatientDemographDAO())->getPatient($hmorpt[$r]['pid']);
        $enrollee_number_ = explode(',', $hmorpt[$r]['enrollee_number']);
        $enrollee_number = array_unique($enrollee_number_);
        $age = ($h_->getPatientAgeInMonths($hmorpt[$r]['pid']) > 12) ? round($h_->getPatientAgeInMonths($hmorpt[$r]['pid']) / 12, 0, PHP_ROUND_HALF_DOWN) . ' Year(s)' : $h_->getPatientAgeInMonths($hmorpt[$r]['pid']) . ' Month(s)';
        $drug_ = array_filter(array_unique(explode(',',$hmorpt[$r]['drug_name'])));
        $drugs = $drugs_ = array();
        foreach($drug_ as $k=>$d){ $drugs[$k] = explode('=', $d); }
        $drug = array_values($drugs);
        for($t=0; $t<count($drug); $t++){ $drugs_[] = ($drug[$t][0]!='')? $drug[$t][0]: $drug[$t][1];  }
        $consultant = explode(',',$hmorpt[$r]['consultant']);
        $consultant_ = array();
        if (count($consultant) > 0) {
            if(($key = array_search('00000000000', $consultant)) !== false) {
                unset($consultant[$key]);
            }
            foreach ($consultant as $c) {
                if(isset($c)) {
                    $consultant_[] = (new StaffDirectoryDAO())->getStaff($c);
                }
            }
        }
        $bill_source_amount = array_values(array_unique(explode(',',$hmorpt[$r]['bill_source_amount']), SORT_REGULAR));
        $bills = array();
        for($i=0; $i<count($bill_source_amount); $i++){$bills[] = explode('=', $bill_source_amount[$i]);}
        $registration_amt_ = $consultancy_amt_ = $procedure_amt_ = $scans_amt_= $admissions_amt_ = $labs_amt_ = $drugs_amt_ = $misc_amt_ = array();
        foreach($bills as $k=>$b){
            if(isset($b[0]) && $b[0]=='registration'){ $registration_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='consultancy'){ $consultancy_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='procedure'){ $procedure_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='scans'){ $scans_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='admissions'){ $admissions_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='labs'){ $labs_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='drugs'){ $drugs_amt_[] = $b[1]; }
            if(isset($b[0]) && $b[0]=='misc'){ $misc_amt_[] = $b[1]; }
        }
        $registration_amt = count($registration_amt_)> 0 ? array_sum(array_values($registration_amt_)) : '0';
        $consultancy_amt = count($consultancy_amt_)> 0 ? array_sum(array_values($consultancy_amt_)) : '0';
        $procedure_amt = count($procedure_amt_)> 0 ? array_sum(array_values($procedure_amt_)) : '0';
        $scans_amt = count($scans_amt_)> 0 ? array_sum(array_values($scans_amt_)) : '0';
        $admissions_amt = count($admissions_amt_)> 0 ? array_sum(array_values($admissions_amt_)) : '0';
        $labs_amt = count($labs_amt_)> 0 ? array_sum(array_values($labs_amt_)) : '0';
        $drugs_amt = count($drugs_amt_)> 0 ? array_sum(array_values($drugs_amt_)) : '0';
        $misc_amt = count($misc_amt_)> 0 ? array_sum(array_values($misc_amt_)) : '0';
        $total_bills = $registration_amt + $consultancy_amt + $procedure_amt + $scans_amt + $admissions_amt + $labs_amt + $drugs_amt + $misc_amt;

        $table_data[$r]['S/N'] =  ($r + 1) ;
        $table_data[$r]['Enrollee Name'] =  (isset($patient))? $patient->getFullname() : '';
        $table_data[$r]['Enrollee ID'] =  implode(", ", $enrollee_number);
        $table_data[$r]['Hospital Number'] =  $hmorpt[$r]['legacy_patient_id'];
        $table_data[$r]['Company'] = '';
        $table_data[$r]['Date of Encounter'] =  $hmorpt[$r]['when'] ;
        $table_data[$r]['Age'] =  $age ;
        $table_data[$r]['Sex'] =  ucfirst($hmorpt[$r]['sex']);

        $table_data[$r]['Blood Pressure'] =  $hmorpt[$r]['bp'];
        $table_data[$r]['P.A. Code'] =  $hmorpt[$r]['auth_code'];
        $table_data[$r]['Diagnosis'] =  $hmorpt[$r]['case'];
        $table_data[$r]['Retiree'] = '';
        $table_data[$r]['Spouse / Insured'] = '';
        $table_data[$r]['Police Officer'] = '';
        $table_data[$r]['Spouse / Dependant'] = '';

        $table_data[$r]['Lab ID'] = $hmorpt[$r]['lab_test'];
        $table_data[$r]['Drugs Given'] =  implode(", ", $drugs_) ;
        $table_data[$r]['Attending Specialist'] =  implode(", ", $consultant_) ;
        $table_data[$r]['REG'] =  number_format($registration_amt,2) ;
        $table_data[$r]['ANC REG'] = '';
        $table_data[$r]['Consultant'] =  number_format($consultancy_amt,2) ;
        $table_data[$r]['Sp. Consultant'] = '';
        $table_data[$r]['Procedure'] =  number_format($procedure_amt,2) ;
        $table_data[$r]['Phototherapy / USS / X-Ray'] =  number_format($scans_amt,2) ;
        $table_data[$r]['Acc/Feeding Nurse Care'] =  number_format($admissions_amt,2) ;
        $table_data[$r]['Lab'] =  number_format($labs_amt,2) ;
        $table_data[$r]['Drugs'] =  number_format($drugs_amt,2) ;
        $table_data[$r]['Misc Amt'] =  number_format($misc_amt,2) ;
        $table_data[$r]['Total'] =  number_format($total_bills,2) ;
    }
}*/

if(isset($_GET['ex_'])){
    require_once $_SERVER['DOCUMENT_ROOT']. '/classes/json2csv.class.php';
    $JSON2CSV = new JSON2CSVutil;
    $JSON2CSV->readJSON(json_encode($table_data));
    $JSON2CSV->flattenDL("HMO_Reports.csv");
    exit;
}
?>
<style type="text/css">
    .filter .btn {float: right;margin-top: 24px;}
</style>
<script src="/assets/blockUI/jquery.blockUI.js"></script>
<div class=""><i class="icon-reply"></i><a href='/pm/reporting/index.php'>Back</a></div>
<h5 >HMO Spreadsheet Report</h5>

<form id="filterForm" method="post" action="/pm/reporting/generatespreadsheet.php">
    <div class="row-fluid filter">
        <label class="span6">Scheme:<select name="scheme" id="scheme">
                <option value="0">--- select scheme ---</option>
                <?php foreach($insSchemes as $is){
                    if(!empty($_REQUEST['scheme']) && $is->getId()===$_REQUEST['scheme']){
                        $schemeName=$is->getName();
                    }
                    if($is->getType()=='insurance') {
                        echo '<option value="' . $is->getId() . '" ' . (!empty($_REQUEST['scheme']) && $is->getId() === $_REQUEST['scheme'] ? ' selected' : '') . '>' . $is->getName() . '</option>';
                    }
                }?>
            </select></label>
        <label class="span2">From:<input required type="text" value="<?=(isset($_REQUEST['from'])?$_REQUEST['from']:'') ?>" name="from" id="from" placeholder="From:"></label>
        <label class="span2">To:<input required type="text" value="<?=(isset($_REQUEST['to'])?$_REQUEST['to']:'') ?>" name="to" id="to" placeholder="To:"></label>

        <button class="btn span1" id="exportIT">Export</button>
        <button type="submit" class="btn span1">Show</button>
    </div>
</form>

<?php if (isset($_REQUEST['from']) && $_REQUEST['from']!='' && isset($_REQUEST['to'])) { ?>
<div class="document">
    <h5 style="text-align: center">Medical HMO Spreadsheet Report for  <span> [<?= (isset($_REQUEST['from']) ? date("Y M d", strtotime($_REQUEST['from'])):date('Y M d')) . "&ndash;" . (isset($_REQUEST['to'])&& $_REQUEST['to']!=''?date("Y M d", strtotime($_REQUEST['to'])):date('Y M d')) ?>]</span></h5>

    <table id="scrollIt" class="table table-bordered table-hover">
        <thead>
        <tr><?php if(isset($table_data[0])) {foreach ($table_data[0] as $i=>$h) { ?><th><?= $i ?></th><?php } } else {?><th>*</th><?php }?></tr>
        </thead>
        <tbody>
        <?php foreach ($table_data as $i=>$h) {?>
            <tr><?php foreach ($h as $f=>$k) { ?>
                <td nowrap><?=$k?></td>
            <?php } ?>
            </tr><?php } ?>
        </tbody>
    </table>
</div>
<?php } ?>

<script type="text/javascript">
$(document).ready(function(){
    $("#scrollIt").dataTable({aaSorting:[], "scrollX": true});
    $("#scheme").select2({width: '100%'});
    var now=new Date().toISOString().split('T')[0];
    $('#from').datetimepicker({
        format:'Y-m-d',
        formatDate:'Y-m-d',
        timepicker:false,
        onShow:function( ct ){
            this.setOptions({
                maxDate: now
            });
        },
        onChangeDateTime:function(e){
            if($("#scheme").val()==="0"){
                $.growlUI("Please select Insurance Scheme first");
                $("#from").val("");
            }
            $("#to").val("");
        }
    });
    $('#to').datetimepicker({
        format:'Y-m-d',
        formatDate:'Y-m-d',
        timepicker:false,
        onShow:function( ct ){
            this.setOptions({
                maxDate: now,
                minDate: $("#from").val()? $("#from").val():false
            });
        },
        onChangeDateTime:function(){
            if($("#scheme").val()==="0"){
                $.growlUI('Sorry you need to select Insurance Scheme first');
                $("#from").val("");
                $("#to").val("");
            }
        }
    });

    $('#exportIT').on('click', function(e){
        if(!e.handled) {
            window.open('/pages/pm/reporting/generatespreadsheet.php?ex_=xsl&scheme=<?=(isset($_REQUEST['scheme']))?$_REQUEST['scheme']:''?>&from=<?=(isset($_REQUEST['from']))?$_REQUEST['from']:''?>&to=<?=(isset($_REQUEST['to']))?$_REQUEST['to']:''?>','_blank');
            e.handled = true;
            e.preventDefault();
        }
    });
});
</script>