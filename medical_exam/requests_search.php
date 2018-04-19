<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/15/16
 * Time: 5:39 PM
 */
$pager = "list3";
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientMedicalReportDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
	$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
	$pageSize = 10;
	$data = (new PatientMedicalReportDAO())->find($_POST['query'], $page, $pageSize);
	$totalSearch = $data->total;

	include "template.php";
	exit;
}
?>
<div class="row-fluid">
	<label class="span10"><input placeholder="Find by Patient EMR or Request Id" type="text" name="q" id="q"></label>
	<button class="btn span2" onclick="search()">Search</button>
</div>
<div id="search_results">

</div>
<script type="text/javascript">
	function search() {
		$.post('<?= $_SERVER['REQUEST_URI'] ?>', {query: $("#q").val()}, function (data) {
			$('#search_results').html(data);
		});
	}

	$(document).on('click', '.<?=$pager?>.dataTables_wrapper a.paginate_button', function(e){
		if(!e.clicked){
			var page = $(this).data("page");
			if(!$(this).hasClass("disabled")){
				$.post('/medical_exam/requests_search.php', {page:page, query: $("#q").val()}, function(s){
					$('#search_results').html(s);
				});
			}
			e.clicked=true;
		}
	})
</script>
