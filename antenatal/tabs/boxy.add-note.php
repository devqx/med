<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/26/15
 * Time: 1:50 PM
 */

//require_once $_SERVER['DOCUMENT_ROOT']. '/classes/functions/func.php';
//sessionExpired();
//
//require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
//mysql_select_db($database_dbconnection, $dbconnection);
//$pid = mysql_real_escape_string($_GET['id']);
//require $_SERVER['DOCUMENT_ROOT'].    '/classes/class.patient.php';
//$assessment = new Manager();
//if(!isset($_SESSION)){session_start();}
//$staff = $assessment->STAFF;
//if (isset($_POST['noteText'])){
//    echo $assessment->savePatientVisitNote($_POST['pid'], $_POST['noteText'], $_POST['note_type']);
//    exit;
//}

?>
<div style="width: 700px">
    <div class="mini-tab">
        <a class="tab sub" href="javascript:void(0)" onclick="soapTab(1)">Subjective Note/Complaint</a>
        <!--<a class="tab obj" href="javascript:void(0)" onclick="soapTab(2)">Objective Note/Exam</a>
        <a class="tab ass" href="javascript:void(0)" onclick="soapTab(3)">Assessment</a>
        <a class="tab pln" href="javascript:void(0)" onclick="soapTab(4)">Plan/Summary</a>
        <a class="tab lab" href="javascript:void(0)" onclick="soapTab(5)">Lab/Investigation</a>-->
    </div>
    <div id="cnt"></div>
</div>

<script type="text/javascript">
    function soapTab(o){
        $('a.tab').each(function(){
            $(this).removeClass('on');
        });
        if(o===1){
            $('#cnt').load('tabs/boxy.add-note-subjective.php?instance=<?= $_GET['instance']?>');
            $('a.tab.sub').addClass('on');
        }
    }
    soapTab(1);
</script>