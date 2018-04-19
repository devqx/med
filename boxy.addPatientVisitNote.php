<?php

require_once $_SERVER['DOCUMENT_ROOT']. '/classes/functions/func.php';
sessionExpired();

require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
mysql_select_db($database_dbconnection, $dbconnection);
$pid = mysql_real_escape_string($_GET['id']);
require $_SERVER['DOCUMENT_ROOT'].    '/classes/class.patient.php';
$assessment = new Manager();
if(!isset($_SESSION)){session_start();}
$staff = $assessment->STAFF;
if (isset($_POST['noteText'])){
	echo $assessment->savePatientVisitNote($_POST['pid'], $_POST['noteText'], $_POST['note_type']);
	exit;
} ?>

<div >
    <div>

<script type="text/javascript">
function showAssessmentBoxy(){
    $('#cnt').load('/boxy.physicalassessment.php?id=<?php echo $_GET['id'];?>');
}
function start(){$('textarea[name="noteText"]').attr("readonly","readonly");$('textarea[name="noteText"]').attr("style","width:100%;background-color: #e4e4e4");}
function done(s){
    if(s=='ok'){Boxy.info('Saved !');
        //and reload this tab,
        showTabs(1);
        //then close this dialog,
        $('.close').click();
    }else{
        $('textarea[name="noteText"]').removeAttr("readonly");//$('textarea[name="noteText"]').removeAttr("style");
        $('textarea[name="noteText"]').attr("style","width:100%;background-color: #e4e4e4");
        Boxy.alert(s);
    }
}
var note_subj = '<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {\'onStart\' : start, \'onComplete\' : done});"><label>Note Description: (subjective)<textarea cols="50" rows="6" name="noteText" style="width:100% !important"></textarea></label><input type="hidden" name="pid" value="<?php echo $pid; ?>" /><input type="hidden" name="note_type" value="subj"><div align="right"><button class="btn" type="submit">Save &raquo;</button> <button class="btn-link" type="reset">Reset</button></div></form>';
var note_plan = '<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {\'onStart\' : start, \'onComplete\' : done});"><label>Note Description: (plan)<textarea cols="50" rows="6" name="noteText" style="width:100% !important"></textarea></label><input type="hidden" name="pid" value="<?php echo $pid; ?>" /><input type="hidden" name="note_type" value="plan"><div align="right"><button type="submit" class="btn">Save &raquo;</button> <button class="btn-link" type="reset">Reset</button></div></form>';

function soapTab(o){
    $('a.tab').each(function(){
        $(this).removeClass('on');
    });
    if(o===1){
        $('#cnt').html(note_subj);
        $('a.tab.sub').addClass('on');
    } else if(o === 2){
        showAssessmentBoxy();
        $('a.tab.obj').addClass('on');
    }else if(o === 3){
       $('#cnt').load('boxy.addDiagnosis.php?id=<?php echo $pid; ?>',{title:'Title'});
        $('a.tab.ass').addClass('on');
    }else if(o === 4){
        $('#cnt').html(note_plan);
        $('a.tab.pln').addClass('on');
    }else if(o === 5){
        $("#cnt").load('/labs/allLabs.php?id=<?php echo $_GET['id'] ?>',{title: 'Lab Request'});
        $('a.tab.lab').addClass('on');
    }
}
soapTab(1);
</script>
<div class="mini-tab" style="float:right">
    <a class="tab sub" href="javascript:void(0)" onclick="soapTab(1)">Subjective Note/Complaint</a> |
    <a class="tab obj" href="javascript:void(0)" onclick="soapTab(2)">Objective Note/Exam</a> |
    <a class="tab ass" href="javascript:void(0)" onclick="soapTab(3)">Assessment</a> |
    <a class="tab pln" href="javascript:void(0)" onclick="soapTab(4)">Plan/Summary</a> |
    <a class="tab lab" href="javascript:void(0)" onclick="soapTab(5)">Lab/Investigation</a></div>
    <!-- <div style="float:right"><label class="sub"><?php if (!$assessment->isAdmitted($pid)){ ?><input type="checkbox" name="ger89" value="<?php echo $pid; ?>" onClick="if($(this).attr('checked')){Boxy.ask('Are you sure you want to place this patient on admission?',['Yes', 'No'],function(choice){if(choice=='Yes'){sendAdmission('<?php echo $pid; ?>');}else{$('input[name=ger89]').attr('checked',false)}});}">Request Admission<?php } else{?><input type="checkbox" name="ger89" value="<?php echo $pid; ?>" onClick="if($(this).attr('checked')){Boxy.ask('Are you sure you want to discharge this patient?',['Yes', 'No'],function(choice){if(choice=='Yes'){sendDischarge('<?php echo $pid; ?>');}else{$('input[name=ger89]').attr('checked',false)}});}">Discharge Patient<?php }?></label></div> --><br/>
<br/>
<div id="cnt" style="height:100%;"></div>
</div>
</div>