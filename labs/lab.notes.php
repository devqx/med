<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/25/14
 * Time: 5:26 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
$lab = (new PatientLabDAO())->getLab($_GET['id']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
//TODO: print lab notes, and specimen notes,
?>
<div>
    <table class="table table-striped">
        <tr><td>Request Date</td><td><?= date("d M, Y h:i A", strtotime($lab->getLabGroup()->getRequestTime())) ?></td></tr>
        <tr><td>Requested By</td><td><?= $lab->getLabGroup()->getRequestedBy() ?></td></tr>
        <tr><td>Specimen Collected By</td><td><?= ($lab->getSpecimenCollectedBy() != NULL) ? $lab->getSpecimenCollectedBy()->getFullname() : 'N/A' ?></td></tr>
        <tr><td>Specimen Collected Date</td><td><?= !empty($lab->getSpecimenDate()) ? date("d M, Y h:i A", strtotime($lab->getSpecimenDate())) : 'N/A' ?></td></tr>
        <tr><td>Preferred Specimen(s)</td><td><?php
                if ($lab->getLabGroup()->getPreferredSpecimens() != NULL) {
                    $specs = array();
                    foreach ($lab->getLabGroup()->getPreferredSpecimens() as $s) {
                        $specs[] = $s->getName();
                    }
                    echo implode(", ", $specs);
                } else {
                    echo 'N/A';
                }
                ?></td></tr>
        <tr><td><span class="ui-bar-c block">Specimen(s) Used</span></td>
            <td><?php
                if ($lab->getSpecimens() != NULL) {
                    $specs = array();
                    foreach ($lab->getSpecimens() as $s) {
                        $specs[] = $s->getName();
                    }
                    echo implode(", ", $specs);
                } else {
                    echo 'N/A';
                }
                ?>
            </td>
        </tr>
        <tr><td>Specimen Note</td><td><span class="more"><?= $lab->getSpecimenNote() ?></span></td></tr>
        <tr><td><span class="ui-bar-c block">Test Values</span></td><td><?= $lab->isResultApproved() ? $lab->getValue() : ($this_user->hasRole($protect->lab_super) ? $lab->getValue() : 'N/A') ?></td></tr>
        <tr><td>Approved </td><td><?= (($lab->getLabResult() && $lab->getLabResult()->getApprovedDate()) ? "Yes" : 'No') ?></td></tr>
        <tr><td>Reported By</td><td><?= ($lab->getPerformedBy() != NULL) ? $lab->getPerformedBy()->getFullname() : 'N/A' ?></td></tr>
        <tr><td>Reported At</td><td><?= (!is_null($lab->getTestDate())) ? date("d M, Y h:i A", strtotime($lab->getTestDate())) : 'N/A' ?></td></tr>
	     <tr><td>Approved By</td><td><?= (($lab->getLabResult() && $lab->getLabResult()->getApprovedDate()) ? $lab->getLabResult()->getApprovedBy()->getFullname() : 'N/A') ?></td></tr>
	       <tr><td>Approved At</td><td><?= (($lab->getLabResult() && $lab->getLabResult()->getApprovedDate()) ? $lab->getLabResult()->getApprovedDate() : 'N/A') ?></td></tr>

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