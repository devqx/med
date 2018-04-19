<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/11/14
 * Time: 12:43 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientProcedureDAO.php';
$p = (new PatientProcedureDAO())->get($_GET['id']);

?>
<?php if(in_array($p->getStatus(),["open","started"]) && (@$_GET['add']!="false")){?>
<div class="menu-head">
    <a href="javascript:;" onclick="addReport(<?= $_GET['id']?>)">New Report</a>
</div>
<?php }?>

<p></p>
<table class="table table-striped">
    <thead>
    <tr>
        <th>Date</th>
        <th>Note</th>
        <th>By</th>
    </tr>
    </thead>
	<div class="dropdown pull-right">
		<button class="drop-btn large dropdown-toggle" data-toggle="dropdown" style="padding:10px">
			Action
			<span class="caret"></span>
		</button>
		<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
				<li>				<a href="javascript:" data-id="<?= $p->getId() ?>" class="printLink">Print</a>
				</li>
		</ul>
	</div>
	<div valign="top" class="pull-right">
		<em>
			<div>
			</div>

	</div>
    <?php foreach ($p->getReports() as $note) {//$note=new PatientProcedureMedicalReport();?>
        <tr>
            <td valign="top" title="<?=date("d M, Y h:i A",strtotime($note->getRequestTime()))?>"><?= date("Y.m.d H:i A", strtotime($note->getRequestTime())) ?></td>
            <td><?= $note->getContent() ?></td>

            <td valign="top"><em><?= $note->getCreateUser()->getFullname() ?></td>
	       
        </tr>
    <?php } ?>
</table>


<script type="text/javascript">
    function addReport(key){
        Boxy.load('/procedures/dialogs/new-medical-report.php?id='+key, {afterHide: function () {
            // refresh this tab?
            $("#tab-container").easytabs('select', '#reports');
        }});
    }
     $('.printLink').on('click', function (evt) {
	    var id = $(evt.target).data("id");
	    if(!evt.handled){
		    window.open("tabs/printNotes.php?id="+id);
		    evt.handled = true;
	    }
    });
</script>