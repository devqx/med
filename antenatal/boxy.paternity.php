<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/4/15
 * Time: 5:23 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/AntenatalEnrollmentDAO.php';
$antenatal = (new AntenatalEnrollmentDAO())->get($_GET['a_id']);

?>
<div>
    <table class="patient_more_details table">
        <tr>
            <th colspan="2">Paternity Details</th>
        </tr>
        <tr>
            <th>Name</th>
            <td><?= !is_blank($antenatal->getBabyFatherName()) ? $antenatal->getBabyFatherName() : 'N/A'?></td>
        </tr>
        <tr>
            <th>Contact</th>
            <td><?= !is_blank($antenatal->getBabyFatherPhone()) ? $antenatal->getBabyFatherPhone() : 'N/A'?></td>
        </tr>
        <tr>
            <th>Blood Group</th>
            <td><?= !is_blank($antenatal->getBabyFatherBloodGroup()) ? $antenatal->getBabyFatherBloodGroup() : 'N/A'?></td>
        </tr>
    </table>
</div>

