<?php
/**
 * Created by PhpStorm.
 * User: emnity3
 * Date: 2/2/15
 * Time: 1:54 PM
 */

@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDentistryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$staff_id = $_SESSION ['staffID'];

$service = (new PatientDentistryDAO())->get($_GET['id']);
$approvedBy = ($service->getApprovedBy() == null) ? '' : $service->getApprovedBy()->getId();
?>
<div style="width: 750px">
    <table class="table table-striped">
        <tr>
            <td colspan="4"><strong>Requested Services: </strong></td>
        </tr><tr>
            <td colspan="4"><?php $names=[];foreach($service->getServices() as $name){$names[]=$name->getName();} echo implode(", ", $names); ?></td>
        </tr>
        <tr>
            <td colspan="4"><strong>Request Note/Reason</strong></td>
        </tr>
        <tr>
            <td colspan="4"><?= ($service->getRequestNote() != null) ? $service->getRequestNote() : "N/A" ?></td>
        </tr>
        <tr>
            <td colspan="4"><strong>Notes</strong> <?php if (!$service->getApproved() && !$service->getCancelled()) { ?>
                    <span class=""><i class="icon-comments-alt"></i>
                <a data-title="New Note: <?= $service->getRequestCode() ?> (<?php $names=[];foreach($service->getServices() as $name){$names[]=$name->getName();} echo implode(", ", $names); ?>)"
                   href="javascript:;" class="_newDialog_" data-reload="true"
                   data-href="/dentistry/add_note.php?scan_id=<?= $service->getId() ?>"
                   data-id="<?= $service->getId() ?>">Add</a></span><?php } ?>
            </td>
        </tr>
        <?php if (count($service->getNotes()) > 0) { ?>
            <tr class="fadedText">
                <td class="nowrap"><strong>Date</strong></td>

                <td colspan="3"><strong>Note</strong></td>
            </tr>
            <?php foreach ($service->getNotes() as $note) { ?>
                <tr>
                <td class="nowrap"><?= date("d M, Y h:iA", strtotime($note->getDateAdded())) ?></td>

                <td colspan="3">
                    <div><?php if (!$service->getCancelled()) {
                            if (!$service->getApproved() || ($approvedBy == $staff_id)) { ?>
                                <a data-title="Edit Note: <?= $service->getRequestCode() ?> (<?php $names=[];foreach($service->getServices() as $name){$names[]=$name->getName();} echo implode(", ", $names); ?>)"
                                   href="javascript:;" class="_editDialog_" data-reload="true"
                                   data-href="/dentistry/boxy.edit_note.php?scan_id=<?= $service->getId() ?>&id=<?= $note->getId() ?>"
                                   data-id="<?= $service->getId() ?>">Edit</a>
                            <?php }
                        } ?></div>
                    <div><?= $note->getNote() ?></div>
                </td>
                </tr><?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4">
                    <div class="notify-bar"><i class="icon-info-sign"></i> No notes available</div>
                </td>
            </tr><?php } ?>

    </table>
</div>