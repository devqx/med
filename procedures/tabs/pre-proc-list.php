<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/7/16
 * Time: 1:06 PM
 */
if(!isset($_SESSION)){@session_start();}
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientProcedureDAO.php';
$p = (new PatientProcedureDAO())->get($_GET['id']);

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureActionListDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	$new = (new ProcedureActionListDAO())->get($_POST['id'])->setDone(filter_var($_POST['state'], FILTER_VALIDATE_BOOLEAN))->setDoneOn(date('Y-m-d H:i:s'))->setDoneBy(new StaffDirectory($_SESSION['staffID'])) ->update();
	if($new !== null){
		exit("success:Updated");
	}
	exit("error:Failed to update");
}

?>
<?php if(in_array($p->getStatus(),["open","started"]) && (@$_GET['add']!="false")){?>
	<div class="menu-head">
		<a href="javascript:;" onclick="addItem(<?= $_GET['id']?>)">Add a List Item</a>
	</div>
<?php }?>
<p></p>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Status</th>
		<th>Date</th>
		<th>Description</th>
		<th>By</th>
	</tr>
	</thead>
	<?php foreach ($p->getActionList() as $note) {//$note=new ProcedureActionList();?>
		<tr>
			<td><input type="checkbox" name="task_done[]" <?=$note->getDone() ? 'checked':'' ?> value="<?= $note->getId()?>" title="<?= var_export($note->getDone(), true) ?>"> </td>
			<td><?= date(MainConfig::$dateTimeFormat, strtotime($note->getTimeEntered())) ?></td>
			<td><?= $note->getDescription()?></td>
			<td><?= $note->getEnteredBy()->getUsername()?></td>
		</tr>
	<?php } ?>
</table>
<script type="text/javascript">
	$(document).ready(function () {
		$('[name^="task_done"]').change(function (evt){
			var id = $(evt.currentTarget).val();
			var state = $(evt.currentTarget).is(":checked");
			Boxy.ask("Apply Change?", ["Yes", "No"], function (answer) {
				if(answer === "Yes"){
					$.post('<?= $_SERVER['REQUEST_URI']?>', {id: id, state: state}, function (response) {
						var data = response.split(":");
						if(data[0]==="error"){
							Boxy.alert(data[1]);
						}else if(data[0]==="success"){
							Boxy.info(data[1]);
						}
					});
				}
				$("#tab-container").easytabs('select', '#pre-procs');
			})
		});
	});
	function addItem(key){
		Boxy.load('/procedures/dialogs/new-pre-list.php?id='+key, {afterHide: function () {
			// refresh this tab?
			$("#tab-container").easytabs('select', '#pre-procs');
		}});
	}
</script>
