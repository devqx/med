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
$unsent = 0;
foreach ($messages as $m_) {
	if ($m_->getSmsDeliveryStatus() == false /* join other channel delivery status*/) $unsent += 1;

} ?>
<button class="btn" id="sendBtn" type="button" title="New Message">Compose Message</button>
<button class="btn" id="dispatchBtn" type="button" title="Send Selected Messages">Dispatch selected Messages</button>

<form class="form-actions_" id="senderForm" style="padding: 0">
	<!--<span class="fadedText"><?= ($unsent) ?> messages</span>-->
	<span id="console" style="/*padding-left: 50px; */font-style: italic; font-size: 95%"></span>
	<table class="table table1 table-hover table-striped">
		<thead>
		<tr>
			<th><label><input type="checkbox" id="selectAll" title="select/deselect all"></label></th>
			<th>Phone</th>
			<th>Email</th>
			<th>Patient</th>
			<th>Message</th>
			<th>Responsible</th>
		</tr>
		</thead>
		<?php
		foreach ($messages as $m_) {
			if ($m_->getSmsDeliveryStatus() == false /* join other channel delivery status*/) {
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
</form>