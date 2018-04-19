<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/16/16
 * Time: 9:45 AM
 */
$pid = isset($_GET['pid']) ? $_GET['pid'] : (isset($_POST['pid']) ? $_POST['pid'] : null);

?>
<div class="menu-head">
	<div class="row-fluid">
		<div class="span6">
			<a href="javascript:" onclick="Boxy.load('/medical_exam/request_new.php?pid=<?=$pid?>', {afterHide:function(){
	showTabs(19);
				}})">New Request</a>
		</div>
	</div>
</div>
<?php
$pager = "list4";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientMedicalReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$data = (new PatientMedicalReportDAO())->forPatient($pid, $page, $pageSize);
$totalSearch = $data->total;
include "template.php";
?>
<script type="text/javascript">
	$(document).on('click', '.list4.dataTables_wrapper a.paginate_button', function(e){
		if(!e.clicked){
			var page = $(this).data("page");
			if(!$(this).hasClass("disabled")){
				$.post('/medical_exam/patient_requests.php', {'page':page, pid: '<?= $pid?>'}, function(s){
					$('#report_container').html( $(s).filter('#report_container').html());
				});
			}
			e.clicked=true;
		}
	}).on('click', '.request_link_open', function(e){
		var $id = $(this).data('id');
		var $title = $(this).data('heading');
		if(!e.handled){
			Boxy.load('/medical_exam/request.details.php?id='+$id, {title: $title});
			e.handled = true;
		}
	});
</script>
