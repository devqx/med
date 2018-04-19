<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/8/15
 * Time: 1:56 PM
 */

require_once $_SERVER['DOCUMENT_ROOT']. "/Connections/dbconnection.php";
require_once $_SERVER['DOCUMENT_ROOT']. "/classes/DAOs/PatientDemographDAO.php";
mysql_select_db($database_dbconnection, $dbconnection);
$patientID = mysql_real_escape_string($_GET['id']);

require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

    $sql = "SELECT * FROM patient_demograph p LEFT OUTER JOIN  socio_economic_status s on (p.socio_economic=s.id) LEFT OUTER JOIN life_style l on  (p.lifestyle=l.id) WHERE patient_ID='".$patientID."' AND patient_ID IN (SELECT patient_id FROM enrollments_immunization)  LIMIT 1";
    $pid = $patientID;
    $chk=mysql_query($sql,$dbconnection);
    $row_data = mysql_fetch_assoc($chk);
    $retVal = "";
    if((new PatientDemographDAO())->getPatient($pid, FALSE) === NULL || mysql_num_rows($chk)==0){?>
    <div class="notify-bar"><p align="center">Sorry, Patient is not enrolled into immunization<br /> Return to <a href="/immunization/">Home</a></p></div>
<?php }else {  do {?>
<div class="mini-tab">
    <a class="tab on" id="vaccine-chart" onclick="loadTab(1)" href="javascript:;" title="Vaccine Chart">Vaccine Chart</a>
    <a class="tab" id="vaccine-booster" onclick="loadTab(2)" href="javascript:;" title="Vaccine Boosters">Booster</a>
</div>

<div id="innerContentPane_" style="width:1020px;overflow-x:scroll"></div>

<script type="text/javascript">
    var loadTab=function(i){
        var urn;
        $('.tab.on').removeClass('on');
        if (i == 1) {
            urn = "tabs/vaccineChart.php?view=vaccine&id=<?= $_GET['id'] ?>";
            $('#vaccine-chart').addClass('on');
        } else if (i == 2) {
            urn = "tabs/vaccineChart.php?view=boosters&id=<?= $_GET['id'] ?>";
            $('#vaccine-booster').addClass('on');
        }

        $("#innerContentPane_").load(urn, function(){
            if(i===1)
                setTimeout(function(){$('.vaccine-block[title]').tooltipster({contentAsHTML: true});},200);
            if(i===2)
                setTimeout(function(){$("#contentPane table").tableScroll({height:250});},200);
        });
    };

    $(document).ready(function () {
        loadTab(1);
        $('a.boosterHistory').live('click', function(e){
            if(!e.handled){
                var bid = $(this).data('id');
                Boxy.load('/immunization/boxy.show_vaccinebooster_history.php?id=' + bid);
                e.preventDefault();
                e.handled = true;
            }
        });

        $('a.dueNow').live('click', function(e){
            if(!e.handled){
                var bid = $(this).data('id');
                $.post("/immunization/ajax.take_boostervaccine.php", { b: bid }, function(ret){
                    var j = JSON.parse(ret);
                    Boxy.info(j.message);
                    loadTab(2);
                });
                e.preventDefault();
                e.handled = true;
            }
        });

        $('#directUpdateWithoutPay').live('click', function(e){
            Boxy.load('/immunization/boxy.update_without_pay.php?id=$id');
            e.preventDefault();
        });
    });
</script>
<script src="/js/jquery.PrintArea.js_4.js"></script>
<script src="/js/core.js"></script>
<?php   }while($row_data = mysql_fetch_assoc($chk));}?>