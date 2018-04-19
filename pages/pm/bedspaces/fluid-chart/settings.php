<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/19/16
 * Time: 10:12 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FluidRoute.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FluidRouteDAO.php';
$f_routes = (new FluidRouteDAO())->all();
?>
<section>
	<div class="ui-bar-c menu-head"><span class="pull-left">Fluid Routes</span> <span class="pull-right"><a href="javascript:" class="action add_fluid_route">Add Route</a></span></div>
	<div class="clear"></div>
	<ul class="list-blocks"><?php foreach ($f_routes as $f_route) {?>
			<li class="tag"><?= ucwords($f_route->getType())?>: <strong><?= $f_route->getName() ?></strong> <span class="pull-right">| <a href="javascript:" class="editRoute" data-id="<?= $f_route->getId()?>">Edit</a></span></li>
		<?php }?></ul>
</section>
<script type="text/javascript">
	$(document).on('click', '.add_fluid_route', function (e) {
		if(!e.handled){
			Boxy.load('/pages/pm/bedspaces/fluid-chart/add_route.php', {title: 'Add Fluid Route', afterHide: function () {
				showTabs(8);
			}});
			e.handled = true;
		}
	}).on('click', '.editRoute', function (e) {
		var id = $(this).data("id");
		if(!e.handled){
			Boxy.load('/pages/pm/bedspaces/fluid-chart/edit_route.php?id='+id, {title: 'Edit Fluid Route', afterHide: function () {
				showTabs(8);
			}});
			e.handled = true;
		}
	})
</script>
