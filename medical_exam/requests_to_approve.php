<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/15/16
 * Time: 5:31 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientMedicalReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$data = (new PatientMedicalReportDAO())->all($page, $pageSize, "to_approve");
$totalSearch = $data->total;
include "template.php";
?>

<script type="text/javascript">
	$(document).on('click', '.list2.dataTables_wrapper a.paginate_button', function(e){
		if(!e.clicked){
			var page = $(this).data("page");
			if(!$(this).hasClass("disabled")){
				$.post('/medical_exam/requests_to_approve.php', {'page':page}, function(s){
					$('#report_container').html(s);
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
