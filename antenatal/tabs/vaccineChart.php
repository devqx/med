<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/8/15
 * Time: 2:05 PM
 */

include($_SERVER['DOCUMENT_ROOT'] . "/classes/functions/func.php");
sessionExpired();
require $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/class.labs.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Connections/dbconnection.php';

require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/PatientVaccineBoosterDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineBoosterHistoryDAO.php';

mysql_select_db($database_dbconnection, $dbconnection);
$patient = new Manager();
$id = mysql_real_escape_string($_GET['id']);
if (!isset($_SESSION)) {
    session_start();
}

function formatStatus($status){
    if ($status == 1) {
        return 'SENT';
    } else if ($status == 0) {
        return 'UNSENT';
    } else {
        return 'UNKNOWN';
    }
}
if (isset($_GET['view']) && $_GET['view'] == "vaccine") {
    echo '<style type="text/css">@import "/style/vaccine.css";@import "/style/fixed_table_rc.css";</style>';
    //echo '<script type="text/javascript" src="/js/fixed_table_rc.js"></script>';
    echo '<script type="text/javascript">
            $(document).ready(function(){
                $(".vaccine-block").on("click", function(e){
                    if($(this).hasClass("red") || $(this).hasClass("yellow") ){
                        Boxy.load("/immunization/boxy.select_administer_due_vaccines.php?id='.$id.'&direct_access_id="+$(this).data("id"),{title:"Apply Due Vaccines"});
                    }
                    e.returnValue = false;
                    e.preventDefault();
                });
            });
          </script>';
    echo $patient->getPatientVaccineMap($id);
    exit;
} else if (isset($_GET['view']) && $_GET['view'] == "reminders") {
    $msgQs = $patient->getPatientVaccineReminder($id);
    $html = '';
    $index = 0;
    if ($msgQs && count($msgQs) > 0) {
        $html .= '<div class="notify-bar">You have <strong>' . count($msgQs) . '</strong> notifications.</div>';
        $html .= '<table id="notificationTable" class="table table-bordered table-hover">';
        $html .= '<thead><th>Date Created</th><th>Status</th><th>Channel</th><th>Message Content</th></thead>';
        while ($index < count($msgQs)) {
            $html = $html . '<tr><td>' . date("Y M, d", strtotime($msgQs[$index]->getDateSent())) . '</td><td>' . formatStatus($msgQs[$index]->getMessage_status()) . '</td><td>' . strtoupper($msgQs[$index]->getSource()) . '</td> <td>' . $msgQs[$index]->getMessage_content() . '</td></tr>';
            $index++;
        }
        $html = $html . "</table>";
    } else {
        $html = $html . '<div class="notify-bar">No Reminders.</div>';
    }
    echo $html . "";
    exit;
} else if (isset($_GET['view']) && $_GET['view'] == "boosters") {
    $boosters = (new PatientVaccineBoosterDAO())->getPatientVaccineBoosterByPatient($id, TRUE);
    $html = '';
    echo '<table class="table table-striped" id="boosters">';
    if(count($boosters) > 0){
        echo '<thead><tr><th>Vaccine</th><th>Last Taken</th><th>Next Due Date</th><th>History</th><th>Due</th></tr></thead>';
        foreach($boosters as $key=>$b){
            $history = (new VaccineBoosterHistoryDAO())->getHistory($b->getId());
            $html .= '<tr><td>'.$b->getVaccineBooster()->getVaccine()->getName().' ('.$b->getVaccineBooster()->getVaccine()->getDescription().')</td>';
            $html .= '<td>'.($b->getLastTaken() != '' ? date("Y M, d", strtotime($b->getLastTaken())) : "").'</td>';
            $html .= '<td>'.date("Y M, d", strtotime($b->getNextDueDate())).'</td>';
            $html .= '<td>'. ((count($history)>0)? '<a href="javascript:;" class="boosterHistory" data-id="'.$b->getId().'">History</a>': 'No History') .'</td>';
            $html .= '<td><a href="javascript:;" class="dueNow" data-id="'.$b->getId().'">Take Now</a></td>';
            $html .= '</tr>';
        }
        echo $html;
    } else {
        echo '<tr><td><div class="notify-bar">No booster vaccine records</div></td></tr>';
    }
    echo '</table>';
    exit;
} else if(isset($_GET['view']) && $_GET['view']=="update-vaccine"){
    echo "This function is not available at the moment";
    exit;
}