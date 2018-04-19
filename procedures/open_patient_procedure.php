<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
$pro = [];

$serviceCentreId = isset($_REQUEST['service_centre_id']) ? $_REQUEST['service_centre_id'] : null;
$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : null;

if (isset($_GET['date']) && $_GET['date'] !== ',') {
	$date = explode(",", $_GET['date']);
	$start = $date[0];
	$stop = $date[1];
} else {
	$start = null;//date("Y-m-d");
	$stop = null;//date("Y-m-d");
}
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$data = (new PatientProcedureDAO())->all($start, $stop, null, null, $serviceCentreId, $category_id, null, $page, $pageSize, "open");
$totalSearch = $data->total;
$pro = $data->data;

?>
	<div class="ui-bar-c row-fluid">
		<div class="span6">
			Filter by Request date:
			<form method="post" name="dateFilter">
				<div class="input-prepend">
					<span class="add-on">From</span>
					<input class="span4" type="text" name="date_start" value="<?= isset($start) ? $start : '' ?>" placeholder="Start Date">
					<span class="add-on">To</span>
					<input class="span4" type="text" name="date_stop" value="<?= isset($stop) ? $stop : '' ?>" placeholder="Stop Date">
					<button class="btn" type="button" id="date_filter">Apply</button>
				</div>
			</form>
		</div>
	</div>

<?php
include_once 'templater.php'; ?>
	<script type="text/javascript">
		$(document).ready(function () {
			$('input[name="date_start"]').datetimepicker({
				format: 'Y-m-d',
				timepicker: false
			});
			$('input[name="date_stop"]').datetimepicker({
				format: 'Y-m-d',
				timepicker: false
			});

			$('#date_filter').live('click', function (e) {
				if (!e.handled) {
					var url = "<?=$_SERVER['REQUEST_URI'] ?>&date=" + encodeURIComponent($('input[name="date_start"]').val()) + "," + encodeURIComponent($('input[name="date_stop"]').val());
					$('#procedure_container').load(url, function (responseText, textStatus, req) {
					});
					e.handled = true;
				}
			});
		})
	</script>
<?php exit;