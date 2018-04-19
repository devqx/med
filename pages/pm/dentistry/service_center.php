<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Department.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$service_centers = (new ServiceCenterDAO())->all('Dentistry');

?>
<div>
	<div><span><a id="new_center" href="javascript:;">Add Business Unit/Service Center</a> </span></div>
	<h6>Available Business unit/Service Centers</h6>
	<div class="three-column s_center">
		<?php foreach ($service_centers as $k => $s) { ?>
			<div class="column tag"><?= $s->getName() ?>
				<span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="<?= $s->getId() ?>" class="editSCenterLink">Edit</a></span>
			</div>
		<?php } ?>
	</div>
</div>

<script>
	
	function reloadServices() {
		$.ajax({
			url: '/api/get_service_centers.php?type=Dentistry',
			dataType: 'json',
			complete: function (s) {
				var html = '';
				$.each(s.responseJSON, function (idx, s_center) {
					html += '<div class="column tag">' + s_center.name + ' <span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="' + s_center.id + '" class="editSCenterLink">Edit</a></span></div>';
				});
				$('div.three-column.s_center').html(html);
			},
			error: function () {

			}
		});
	}
	
	$('#new_center').live('click', function (e) {
		if(e.handled !== true){
			Boxy.load("/pages/pm/dentistry/add_new_service_center.php", {
				title: "Add Business Unit/Service Center", afterHide: function () {
					reloadServices();
				}
			});
			e.handled = true;
		}
	})
</script>