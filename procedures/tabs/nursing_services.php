<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/18/16
 * Time: 1:10 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientProcedureDAO.php';
$p = (new PatientProcedureDAO())->get($_GET['id']);

?>
<?php if(in_array($p->getStatus(),["open","started"]) && (@$_GET['add']!="false")){?>
    <div class="menu-head">
        <a href="javascript:;" onclick="addNursingService(<?= $_GET['id']?>)">Add a Nursing Service</a>
    </div>
<?php }?>

<p></p>
<table class="table table-striped">
    <thead>
    <tr>
        <th>Date</th>
        <th>Task</th>
        <th>By</th>
    </tr>
    </thead>
    <?php foreach ($p->getTasks() as $task) { //$task=new PatientProcedureNursingTask();?>
        <tr>
            <td title="<?=date("d M, Y h:i A",strtotime($task->getWhen()))?>"><?= date("Y.m.d H:i A", strtotime($task->getWhen())) ?></td>
            <td><?= $task->getTask()->getName() ?></td>
            <td><a href="/staff_profile.php?id=<?= $task->getCreator()->getId()?>"><?= $task->getCreator()->getUsername() ?></a></td>
        </tr>
    <?php } ?>
</table>


<script type="text/javascript">
    function addNursingService(key){
        Boxy.load('/procedures/dialogs/new-nurse-task.php?id='+key, {afterHide: function () {
            // refresh this tab?
            $("#tab-container").easytabs('select', '#nursing_tasks');
        }});
    }
</script>