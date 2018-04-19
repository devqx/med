<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/23/15
 * Time: 10:21 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabResultDAO.php';
$lab = (new LabResultDAO())->getLabResult($_GET['id'], true);

?>
<div style="width:600px;">
	<table class="table table-striped table-bordered table-hover">
		<tr>
			<th>Field</th>
			<th>Value</th>
		</tr>
		<?php foreach ($lab->getData() as $k => $data) { // $data=new LabResultData(); ?>
			<tr>
				<td><?= $data->getLabTemplateData()->getMethod()->getName() ?><?php if ($data->getLabTemplateData()->getReference() != "") { ?><br>
						(Reference: <?= $data->getLabTemplateData()->getReference() ?>)<?php } ?></td>
				<td><?= $data->getValue() ?></td>
			</tr>
		<?php } ?>
		<tr>
			<td colspan="2"><label><input name="abnormal" type="checkbox" data-id="<?= $lab->getId() ?>" class="__setAbnormal"<?= ($lab->getAbnormalValue()) ? ' checked' : '' ?>> Abnormal Lab Result</label></td>
		</tr>
		<tr>
			<td colspan="2">
				<a href="javascript:" data-lab-id="<?= $lab->getPatientLab()->getLabGroup()->getGroupName() ?>" data-id="<?= $lab->getId() ?>" class="__aprove">Approve</a> |
				<a href="javascript:" onclick="editLabResult(<?= $lab->getPatientLab()->getTest()->getId() ?>, <?= $lab->getPatientLab()->getId() ?>, '<?= $lab->getPatientLab()->getTest()->getName() ?>')" title="Edit Result">Edit</a> |
				<a href="javascript:" data-id="<?= $lab->getId() ?>" class="__reject">Reject</a>
			</td>
		</tr>
	</table>
</div>
<script>
	var editLabResult = function (testId, plId, testName) {
		Boxy.load('/labs/editResult.php?testId=' + testId + '&plId=' + plId, {
			title: testName, afterHide: function () {
				setTimeout(function () {
					Boxy.get($(".close")).hideAndUnload();
					setTimeout(function () {
						Boxy.load('/labs/viewLabResults.php?id=<?= $lab->getId() ?>');
					}, 100);
				}, 100);
			}
		});
	};
	$(document).ready(function () {
		$('input[name="abnormal"]').on('click', function (e) {
			var chkd = $(this).is(":checked");
			var abnVal = (chkd === true) ? 1 : 0;
			var id = $(this).data('id');

			if (e.enabled !== true) {
				Boxy.ask("Would you want to mark this result as " + (chkd ? "AB" : "") + "NORMAL?", ["Yes", "Not really"], function (dat) {
					if (dat === "Yes") {
						$.post('/labs/result.action.php', {id: id, a: abnVal, action: "abnormal"});
					}
				});
				e.enabled = true;
			}
		});
	});
</script>