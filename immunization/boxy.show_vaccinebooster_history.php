<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/5/15
 * Time: 5:24 PM->getShortname()
 */

require_once $_SERVER   ['DOCUMENT_ROOT'].'/classes/DAOs/VaccineBoosterHistoryDAO.php';

$bid = isset($_GET['id'])? $_GET['id'] :'';
$vaccinesHistory = (new VaccineBoosterHistoryDAO())->getHistory($bid);
$vaccine = $vaccinesHistory[0]->getPatientVaccineBooster()->getVaccineBooster();
?>
<div>
    <h5><?= $vaccine->getVaccine()->getName().' ('.$vaccine->getVaccine()->getDescription().')' ?> History</h5>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Date Taken</th>
            <th>Taken By</th>
        </tr>
        </thead>
        <?php
        if(count($vaccinesHistory)> 0){
        foreach($vaccinesHistory as $k=>$history){
        ?>
        <tr>
            <td><?= $history->getDateTaken() ?></td>
            <td><?= $history->getTakenBy()->getShortName() ?></td>
        </tr>
        <?php }} ?>
    </table>
</div>