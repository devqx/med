<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/20/17
 * Time: 9:18 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$units = (new ServiceCenterDAO())->all('item');

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGrpSc.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGroup.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

	if (is_blank($_POST['center'])) {
		exit("error: service center unit required");
	}

	if (count(array_filter($_POST['group'])) < 1) {
		exit('error:Item group data is required');
	}

	$groupData = array_filter(explode(',', $_POST['group'][0]));

	$comt = (new ItemGrpSc())->setServiceCenter((new ServiceCenterDAO())->get($_POST['center']))->setItemGroup($groupData)->add();
	if ($comt != null) {
		exit('success:Data added');
	}
	exit('error:Failed to save data');
}
?>
<div>
	<form id="formItem" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : success})">
		<label for="service_center">Service center/Business unit
			<select id="service_center" name="center" data-placeholder="Select service center/business unit">
				<option></option>
				<?php foreach ($units as $sc) { ?>
					<option value="<?= $sc->getId() ?>"><?= $sc->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label for="group">Name<input type="hidden" name="group[]" id="group"></label>
		<div>
			<button class="btn" name="itemCatBtn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">

	$(document).ready(function () {
		getGroups();
	});


	function setGroups(data) {
		$('input[name="group[]"]').select2({
			width: '100%',
			allowClear: true,
			multiple: true,
			placeholder: "select group",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (source) {
				return source.name + ' (' + source.id + ')';
			},
			formatSelection: function (source) {
				return source.name + ' (' + source.id + ')';
			}
		});

	}

	function start() {
		$(document).trigger('ajaxSend');
	}
	function success(s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');

		if (data[0] === "success") {
			Boxy.info(data[1], function(){
				Boxy.get($(".close")).hideAndUnload();
			});
		} else {
			Boxy.warn(data[1]);
		}
	}


	function getGroups() {
		$.ajax({
			url: '/api/get_item_group.php',
			dataType: 'json',
			data: function () {
			},
			success: function (result) {
				setGroups(result)
			}
		});
	}

</script>
