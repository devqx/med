<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/13/16
 * Time: 11:11 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/func.php';
$time = microtime(true);
$pageSize = 10;
$page = (isset($_GET['page'])) ? $_GET['page'] : 0;
$instance = (new IVFEnrollmentDAO())->get($_GET['aid']);
$pid = $instance->getPatient()->getId();

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
$source = (object)array('name'=>'ivf', 'instance'=> $_GET['aid']);
$serviceCentreId = isset($_REQUEST['service_centre_id']) ? $_REQUEST['service_centre_id'] : null;
$data = (new PatientProcedureDAO())->getPatientProcedures($pid, null, null, $page, $pageSize, $source, $serviceCentreId);
$totalSearch = $data->total;
$pro = $data->data;
$sourcePage = "/ivf/profile/tabs/procedures.php?aid=".$_GET['aid']."&pid=".$pid;
?>
<script type="text/javascript">
	var reloadCurrentTab=function () {
		$('#tabbedPane').find('li.active a').click();
	};
</script>
	<div class="menu-head">
		<div class="row-fluid">
			<div class="span8">
				<?php if ($instance->getActive()) { ?>
				<a href="javascript:void(0)" onclick="Boxy.load('/procedures/new_patient_procedure.php?aid=<?= $_GET['aid'] ?>&source=ivf&id=<?= $instance->getPatient()->getId()?>', {title: 'New IVF Procedure', afterHide: function(){reloadCurrentTab();}})">New
						Procedure</a><?php } ?>
			</div>
		</div>
	</div>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/procedures/templater.php';
exit;