<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/7/16
 * Time: 3:27 PM
 */
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Department.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';

$departments = (new DepartmentDAO())->getDepartments();
$cost_centres = (new CostCenterDAO())->all();

if ($_POST) {
    $sc = new ServiceCenter();
    if (is_blank($_POST['department_id_'])) {
        exit("error:Select a department");
    }
    if (is_blank($_POST['cost_centre_id'])) {
        exit("error:Select a cost centre");
    }
    if (is_blank($_POST['name'])) {
        exit("error:The Voucher center name is required");
    }
    $sc->setDepartment(new Department($_POST['department_id_']));
    $sc->setCostCentre(new CostCenter($_POST['cost_centre_id']));
    $sc->setName($_POST['name']);
    $sc->setType('Voucher');
    if ((new ServiceCenterDAO())->add($sc) !== null) {
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
        <div class="btn-block">
            <button class="btn" type="submit">Add Voucher Center</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
<script>
    function __ry(){}
    function __cm(s){
        if(s.split(":")[0]=='error'){
            $('span.error').html('<span class="alert alert-error">' + s.split(":")[1] + '</span>');
        }
        else {
            Boxy.get($('.close')).hideAndUnload();
            $('#existingLabCenters').load("/pages/pm/voucher-centers.php", function () {
                $('table.table').dataTable();
            });
        }
    }
</script>