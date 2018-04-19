<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/13/17
 * Time: 11:42 AM
 */

require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Department.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ItemGrpSc.php';

$departments = (new DepartmentDAO())->getDepartments();
$cost_centres = (new CostCenterDAO())->all();

if ($_POST) {
    $group__ids = array_filter(explode(",",$_POST['group_id']));
    $sc = new ServiceCenter();
	if (is_blank($_POST['department_id_'])) {
		exit("error:Select a department");
	}
	if (is_blank($_POST['cost_centre_id'])) {
		exit("error:Select a cost centre");
	}
	if (is_blank($_POST['name'])) {
		exit("error:The Lab center name is required");
	}
	$sc->setDepartment(new Department($_POST['department_id_']));
	$sc->setCostCentre(new CostCenter($_POST['cost_centre_id']));
	$sc->setName($_POST['name']);
	$sc->setType('Item');
	$sc_obj = (new ServiceCenterDAO())->add($sc);
	if(count($group__ids) > 0){
        $c_data = (new ItemGrpSc())->setServiceCenter($sc_obj)->setItemGroup($group__ids)->add();
    }
	if($sc_obj !== null){
        exit("ok");
    }
	exit("error:Error occurred");
}
?>
<div><span class="error"></span>
	<form id="hide-45" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {onStart: __ry, onComplete: __cm})">
		<label>Name/Code <input type="text" name="name"> </label>
		<label>Department <select name="department_id_">
				<?php foreach($departments as $k=>$dept){ ?>
					<option value="<?= $dept->getId() ?>"><?= $dept->getName() ?></option>
				<?php }?>
			</select></label>
		<label>Cost Centre <select name="cost_centre_id">
				<?php foreach($cost_centres as $k=>$cs){ ?>
					<option value="<?= $cs->getId() ?>"><?= $cs->getName() ?></option>
				<?php }?>
			</select> </label>
        <label for="generic">Groups<input type="hidden" name="group_id" class="group_id" id="group_id">

            <div class="btn-block">
			<button class="btn" type="submit">Add Item Center</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script>
    $(document).ready(function () {
        getGroups();
    });

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

    function setGroups(data) {
        $('input[name="group_id"]').select2({
            width: '100%',
            allowClear: true,
            multiple: true,
            placeholder: "select group",
            data: function () {
                return {results: data, text: 'name'};
            },
            formatResult: function (source) {
                return source.name;
            },
            formatSelection: function (source) {
                return source.name;
            }
        });
    }

	function __ry(){}
	function __cm(s){
		if(s.split(":")[0]=='error'){
			$('span.error').html('<span class="alert alert-error">' + s.split(":")[1] + '</span>');
		}
		else {
			Boxy.get($('.close')).hideAndUnload();
			$('#existingItemCenters').load("/pm/items/item_centers.php", function () {
				$('table.table').dataTable();
			});
		}
	}
</script>
