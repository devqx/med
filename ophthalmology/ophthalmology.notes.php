<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/25/14
 * Time: 5:26 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
$lab = (new PatientOphthalmologyDAO())->get($_GET['id']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
//TODO: print lab notes, and specimen notes,
?>
<div>
    <table class="table table-striped">
        <tr><td>Request Date</td><td><?= date("d M, Y h:i A", strtotime($lab->getOphthalmologyGroup()->getRequestTime())) ?></td></tr>
        <tr><td>Approved</td><td><?= ($lab->isResultApproved() ? $lab->getApprover()->getFullname() : 'No') ?></td></tr>
        <tr><td>Performed By</td><td><?= ($lab->getPerformedBy() != NULL) ? $lab->getPerformedBy()->getFullname() : 'N/A' ?></td></tr>
        <tr><td>Date</td><td><?= (!is_null($lab->getTestDate())) ? date("d M, Y h:i A", strtotime($lab->getTestDate())) : 'N/A' ?></td></tr>
        <tr><td>Notes</td><td><span class="more"><?= $lab->getNotes() ?></span></td></tr>

    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('span.more').shorten({
            "showChars": 50,
            "moreText": "more",
            "lessText": "less"
        });
    });
</script>