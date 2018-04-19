<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/13/14
 * Time: 4:17 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MessageDispatchDAO.php';

$messages = (new MessageDispatchDAO())->getItems();
$sent = 0;
foreach ($messages as $m_) {
    if($m_->getSmsDeliveryStatus() == true /* join other channel delivery status*/) $sent += 1;
}?>
<h5>Sent Messages (<?= $sent?>)</h5>
<table class="table table2 table-hover table-striped">
    <thead>
    <tr>
        <th></th>
        <th>Phone</th>
        <th>Email</th>
        <th>Patient</th>
        <th>Message</th>
        <th>Responsible</th>
    </tr>
    </thead>
    <?php
    foreach ($messages as $m_) {
        if ($m_->getSmsDeliveryStatus() == true /* join other channel delivery status*/) {
            ?>
            <tr>
                <td><label><input type="checkbox" name="messages[]" value="<?= $m_->getId() ?>"></label></td>
                <td><?= $m_->getSmsChannelAddress() ?></td>
                <td><?= $m_->getEmailChannelAddress() ?></td>
                <td><em class="fadedText">[<?= $m_->getPatient()->getFullName() ?>]</em></td>
                <td><?= $m_->getMessage() ?></td>
                <td><?= $m_->getUser() ? $m_->getUser()->getUsername() : '- -' ?></td>
            </tr>
        <?php }
    } ?>
</table>