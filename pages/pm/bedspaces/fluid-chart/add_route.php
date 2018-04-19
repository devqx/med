<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/19/16
 * Time: 1:19 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FluidRoute.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$routeTypes = getTypeOptions('type', 'fluid_route');
if($_POST){
	if(is_blank($_POST['name'])){
		exit("error:Route name is required");
	}
	if(is_blank($_POST['type'])){
		exit("error:Input or Output?");
	}

	$route = (new FluidRoute())->setType($_POST['type'])->setName($_POST['name'])->add();
	if($route !== null){
		exit("success:Fluid route added");
	}
	exit("error:Failed to add fluid route");
}
?>

<section style="width: 500px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: _0909})">
		<label>Name <input type="text" name="name" placeholder="E.g. Intravenous Fluid, Urine, ..."></label>
		<label>Type <select name="type" data-placeholder="Input or Output ?"><option></option>
			<?php foreach ($routeTypes as $routeType){?><option value="<?= $routeType ?>"><?= ucwords($routeType) ?></option><?php }?></select> </label>
		<div class="clear"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function _0909(s) {
		var data = s.split(":");
		if(data[0]==="success"){
			Boxy.get($(".close")).hideAndUnload();
			showTabs(8);
		} else {
			Boxy.alert(data[1]);
		}
	}
</script>
