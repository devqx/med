<?php
require '../Connections/dbconnection.php';
mysql_select_db($database_dbconnection, $dbconnection);
//$pid = mysql_real_escape_string($_GET['id']);
require '../class.patient.php';
$assessment = new Manager();
//$pname = $assessment->getPatientName($pid);
if (!isset($_SESSION)) {
    session_start();
}
$staff = $assessment->STAFF;
// if (isset($_POST['vaccine'])){	
// 	$hospid=$a->getStaffHospitalID($_SESSION['staffID']);
// 	echo $pt->savePatientVisitNote($_POST['pid'], $_POST['noteText'], $_POST['note_type'],$hospid);
// 	exit;
// } 
?>
<div style="width: 450px; min-height:150px">
<!--    <script type="text/javascript" src="../scripts/jquery-1.8.3.min.js"></script>-->
<!--    <script type="text/javascript" src="../scripts/boxy/javascripts/jquery.boxy.js"></script>-->
<!--    <script type="text/javascript" src="../scripts/webtoolkit.aim.js"></script>-->
    <script type="text/javascript">
        $(document).ready(function () {
            $('#add').click(function () {
                if (!isOk()) {
                    return;
                }
            });
        });
        function isOk() {
            var status = true;
            if (parseInt($("#level").val()) < 1) {
                status = false;
                $("#level").css("border", "1px solid yellow");
                alert(1 + ": " + !isNaN($("#level").val()) + " " + $("#level").val());
            } else {
                $("#level").css("border");
            }
//        
//        if(!isNaN($("#startAge").val())){
//            status=false;
//            $("#startAge").css("border", "1px solid yellow");
//            alert(2+": "+!isNaN($("#startAge").val())+" "+$("#startAge").val());
//        }else{            
//            $("#startAge").css("border");
//        }

            if (parseInt($("#endAge").val()) < parseInt($("#startAge").val())) {
                status = false;
                $("#endAge").css("border", "1px solid yellow");
                alert(3 + ": " + !isNaN($("#endAge").val()) + " " + $("#endAge").val());
            } else {
                $("#endAge").css("border");
            }
            return status;
        }
    </script>
    <div style="float:left">
        <table width="310" border="0" cellspacing="10">
            <thead>
            <tr align="left" valign="middle">
                <th colspan="4"><strong>Vaccine Details</strong></th>
            </tr>
            <tr valign="middle">
                <th width="50" align="left">Vaccine:</th>
                <th>&nbsp;</th>
                <th colspan="2"><input type="text" name="vaccine" id="vaccine"/></th>
            </tr>
            <tr>
                <th colspan="4">&nbsp;</th>
            </tr>
            <tr>
                <th align="left">Level</th>
                <th align="center"><strong>Start Age</strong></th>
                <th align="center"><strong>End Age</strong></th>
                <th width="5px" align="center"><strong>Action</strong></th>
            </tr>
            <tr valign="middle" style="margin-bottom: 15px; display:">
                <th align="left" scope="row"><br><input type="number" name="level" id="level" placeholder="level"
                                                        readonly="readonly"/></th>
                <th align="center"><br><input type="number" name="startAge" id="startAge" placeholder="In month"/></th>
                <th align="center"><br><input type="number" name="endAge" id="endAge" placeholder="In month"/></th>
                <th align="center"><a href="javascript:void(0)" id="add">Add</a></th>
            </tr>
            <tr align="left" valign="middle" style="border-bottom: 1px solid #959595; height: 30px;">
                <th id="errMsg" colspan="4" align="center"></th>
            </tr>
            <tr align="left" valign="middle"
                style="border-bottom: 1px solid #959595; height: 30px; background-color: #efefef">
                <th colspan="4" align="center"><strong>Vaccine Administration Levels</strong></th>
            </tr>
            <tr valign="middle" style="border-bottom: 1px solid #959595; height: 20px;">
                <th>Level</th>
                <th><strong>Start Age</strong></th>
                <th><strong>End Age</strong></th>
                <th width="5px" align="center">&nbsp;</th>
            </tr>
            </thead>
            <tr valign="middle">
                <td align="left">&nbsp;</td>
                <td></td>
                <td></td>
                <td><a href="">Edit</a></td>
            </tr>
        </table>
    </div>
    <br/><br/>

    <div>
        <button type="button" onclick="$('#apply_immunization').submit()">Save &raquo;</button>
        <br>
        <button type="button" onclick="$('.close').click()">Cancel &raquo;</button>
    </div>
</div>
