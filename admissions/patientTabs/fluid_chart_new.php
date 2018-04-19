<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/19/16
 * Time: 5:12 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FluidRouteDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FluidChart.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$routes = (new FluidRouteDAO())->all();
$types = getTypeOptions('type', 'fluid_route');
if($_POST){
	if(is_blank($_POST['route_id'])){exit("error:Route is required");}
	if(is_blank($_POST['volume'])){exit("error:Volume is required");}

	$instance = (new InPatientDAO())->getInPatient($_POST['aid']);
	$data = (new FluidChart())->setPatient($instance->getPatient())->setInPatient($instance)->setRoute( (new FluidRouteDAO())->get($_POST['route_id']) )->setVolume(parseNumber($_POST['volume']))->add();

	if($data !== null){
		exit("success:Reading Saved");
	}
	exit("error:Failed to save");
}
?>
<section style="width: 500px;">

	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: st1,onComplete: new_0809})">
		<div class="row-fluid">
			<?php foreach ($types as $type) {?>
				<label class="span6"><input type="radio" name="route_type" value="<?= $type ?>"> <?= ucwords($type) ?></label>
			<?php }?>
		</div>
		<label>Fluid Route <input name="route_id" type="hidden" data-placeholder="Select Route"></label>
		<label>Volume <span class="pull-right fadedText">Volume in ml</span> <input type="number" step="0.01" name="volume"> </label>
		<input type="hidden" name="aid" value="<?= $_REQUEST['aid']?>">
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var routes = <?= json_encode($routes)?>;
	var $filtered = [];
	$(document).ready(function () {
		$('input[name="route_id"]').select2({
			width: '100%',
			allowClear: true,
			data: function () {
				return {results: $filtered, text: 'name'};
			},
			formatResult: function (source) {
				return source.name + " (" + ucwords(source.type) + ")";
			},
			formatSelection: function (source) {
				return source.name + " (" + ucwords(source.type) + ")";
			}
		});

		$('[name="route_type"]').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function(event){
			$(event.currentTarget).trigger('change');
		});
	}).on('change', '[name="route_type"]', function (e) {
		$('input[name="route_id"]').select2("val","");
		$filtered = _.filter(routes, ["type", $(e.currentTarget).val()]);
	});

	function st1() {
		$(document).trigger("ajaxSend");
	}

	function new_0809(s) {
		$.unblockUI();
		var data = s.split(":");
		if(data[0]=="error"){
			Boxy.alert(data[1]);
		} else if(data[0] == "success"){
			Boxy.get($(".close")).hideAndUnload();
			showTabs(15);
		}
	}
</script>
