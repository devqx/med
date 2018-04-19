<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/24/16
 * Time: 8:05 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralsQueueDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$data = (new ReferralsQueueDAO())->filter($_GET['patient_id']);
?>
<div class="menu-head">
	<a href="javascript:" onclick="showTabs(1)"><i class="fa fa-chevron-left"></i> Back to Encounters</a>
	| <a href="javascript:" onclick="refreshRefs()"><i class="fa fa-refresh"></i></a>
</div>

<table class="table">
	<thead><tr><th>Date</th><th>Specialization</th><th>External?</th><th>Acknowledged</th><th>*</th></tr></thead>
	<?php foreach ($data->data as $item) {//$item=new ReferralsQueue();?>
		<tr><td><?= date(MainConfig::$dateTimeFormat, strtotime($item->getWhen()))?></td><td><?= $item->getSpecialization() ? $item->getSpecialization()->getName() : '- -'?></td><td><?= $item->getExternal() ? 'YES':'NO' ?></td><td><?=$item->getAcknowledged() ? 'YES':'NO' ?></td>
			<td>
				<a class="referral_details" href="javascript:" data-id="<?= $item->getId() ?>">Details</a> |
				<a target="_blank" href="patient_referrals_print.php?id=<?=$item->getId()?>">Print</a>
			</td></tr>
	<?php }?>
</table>
<script type="text/javascript">
	function refreshRefs() {
		$('#contentPane').load('/patient_referrals.php?patient_id=<?=$_GET['patient_id']?>');
	}

	$(document).on('click', '.referral_details', function (e) {
		if (!e.clicked) {
			Boxy.load('/patient_referrals_detail.php?id='+$(e.target).data('id'));
			e.clicked = true;
		}
	});
</script>