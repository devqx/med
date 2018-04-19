<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/31/17
 * Time: 1:36 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
$sch_resources = (new ResourceDAO)->getResources();
$pro = [];

$serviceCentreId = isset($_REQUEST['service_centre_id']) ? $_REQUEST['service_centre_id'] : null;
$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : null;
$sch_res_id = isset($_REQUEST['resource_id']) ? $_REQUEST['resource_id'] : null;

if (isset($_GET['date1']) && $_GET['date1'] !== ',') {
	$date1 = explode(",", $_GET['date1']);
	$start1 = $date1[0];
	$stop1 = $date1[1];
} else {
	$start1 = null;//date("Y-m-d");
	$stop1 = null;//date("Y-m-d");
}
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$data = (new PatientProcedureDAO())->all(null, null, $start1, $stop1, $serviceCentreId, $category_id, $sch_res_id, $page, $pageSize, "scheduled");
$totalSearch = $data->total;
$pro = $data->data;
?>
	<div class="row-fluid ui-bar-c">
		<div class="span6">
			Filter by Schedule dates:
			<form method="post" name="dateFilter">
				<div class="input-prepend">
					<span class="add-on">From</span>
					<input class="span4" type="text" name="date_start1" value="<?= isset($start1) ? $start1 : '' ?>" placeholder="Start Date">
					<span class="add-on">To</span>
					<input class="span4" type="text" name="date_stop1" value="<?= isset($stop1) ? $stop1 : '' ?>" placeholder="Stop Date">
					<button class="btn" type="button" id="date_filter1">Apply</button>
				</div>
			</form>
		</div>
		<label class="span6">Filter by Resource
			<input type="hidden" name="resource_id" placeholder="Select Resource" value="<?= isset($_REQUEST['resource_id'])?$_REQUEST['resource_id']:'' ?>">
		</label>
	</div>

<?php
include_once 'templater.php'; ?>
	<script type="text/javascript">
		var resources__ = <?= json_encode($sch_resources, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
		$(document).ready(function () {
			$('#date_filter1').live('click', function (e) {
				if (!e.handled) {
					var url = "<?=$_SERVER['REQUEST_URI'] ?>&date1=" + encodeURIComponent($('input[name="date_start1"]').val()) + "," + encodeURIComponent($('input[name="date_stop1"]').val());
					$('#procedure_container').load(url, function (responseText, textStatus, req) {
					});
					e.handled = true;
				}
			});
			$('input[name="date_start1"]').datetimepicker({
				format: 'Y-m-d',
				timepicker: false
			});
			$('input[name="date_stop1"]').datetimepicker({
				format: 'Y-m-d',
				timepicker: false
			});
			
			$('input[name="resource_id"]').select2({
				placeholder: 'Select',
				width: '100%',
				allowClear: true,
				data: {results: resources__, text: 'name'},

				formatResult: function (data) {
					return data.name;
				},
				formatSelection: function (data) {
					return data.name;
				}
			}).change(function (e) {
				$('select[name="service_centre_id"]').change();
			});
		});
	</script>
<?php exit;