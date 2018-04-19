<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAttachmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AttachmentCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
@session_start();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$protect = new Protect();

$page = (isset($_GET['page'])) ? $_GET['page'] : 0;
$pageSize = 10;
$categoryId = (isset($_GET['category_id']) && !is_blank($_GET['category_id']) ? $_GET['category_id'] : null);
$categories = (new AttachmentCategoryDAO())->all();

if (isset($_GET['pid']) && !is_blank($_GET['pid'])) {
	$uploads = (new PatientAttachmentDAO())->patient($_GET['pid'], $categoryId, $page, $pageSize);
} else {
	$uploads = (new PatientAttachmentDAO())->all($categoryId, $page, $pageSize);
}
$totalSearch = $uploads->total;
?>
<br>
<div class="document">
	<p class="pull-left">
		<button class="btn" type="button" id="_attch">New Attachment</button>
	</p>
	<div id="area11">
	    <span class="pull-right">
		    <label><select name="category_id" id="category_id" data-placeholder="Filter by category"><option></option><?php foreach ($categories as $category) { ?>
					    <option value="<?= $category->getId() ?>"><?= $category->getName() ?></option>
				    <?php } ?></select></label>
	    </span>
		<div content="reloading">
		<table class="t3 table table-striped">
			<thead>
			<tr>
				<th width="10%">Date</th>
				<th>Category</th>
				<th>Name</th><?php if (!isset($_GET['pid']) || is_blank(@$_GET['pid'])) { ?>
					<th width="30%">Patient</th><?php } ?>
				<th style="width: 2%">*</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($uploads->data as $attachment) {
				//$attachment=new PatientAttachment();?>
				<?php if ($attachment->getPatient()) { ?>
					<tr>
						<td><?= date("Y/m/d", strtotime($attachment->getDateAdded())) ?></td>
						<td><?= $attachment->getCategory() ? $attachment->getCategory()->getName() : 'N/A' ?></td>
						<td><?= $attachment->getNote() ?></td><?php if (is_blank(@$_GET['pid'])) { ?>
							<td><a href="/patient_profile.php?id=<?= $attachment->getPatient()->getId() ?>"
							       target="_blank"><?= $attachment->getPatient()->getFullname() ?></a></td><?php } ?>
						<td nowrap>
							<?php if (count(array_intersect($this_user->getRolesRaw(), $attachment->getCategory() ? $attachment->getCategory()->getRoles() : [])) > 0) { ?>
								<a class="action pdf_viewer" href="/documents/attachment.php?id=<?= $attachment->getId() ?>">
									<i class="fa fa-2x fa-file-pdf-o"></i>
								</a>
								
								<?php if ($this_user->hasRole($protect->mgt)) { ?>
									<a class="action" href="javascript:void (0)" onclick="deleteDoc(<?= $attachment->getId() ?>,<?= $this_user->getId() ?>)"><i class="fa fa-2x fa-trash-o abnormal" aria-hidden="true"></i></a>
								<?php } ?>
							<?php } else { ?>
								---
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			<?php } ?>
			</tbody>
		</table>
		<div class="list11 dataTables_wrapper no-footer">
			<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
				results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
			</div>
			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0"
				   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
				   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_last"
				   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
				   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_next"
				   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
				   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
			</div>
		</div>
	</div>
	</div>
</div>
<script>
	$(document).ready(function () {
		//$('table.t3').dataTable();
		$("#_attch").click(function (e) {
			if (!e.handled) {
				Boxy.load('/documents/new.php<?php if(!is_blank(@$_GET['pid'])){?>?pid=<?= $_GET['pid']?><?php }?>', {
					title: "New Attachment", afterHide: function () {
						<?php if(!is_blank(@$_GET['pid'])){?>
						<?php if(isset($_GET['aid']) && !is_blank($_GET['aid'])){?>showTabs(12);
						<?php }else {?>showTabs(15);
						<?php }
						}?>
						<?php if(is_blank(@$_GET['pid'])){?>location.reload();<?php }?>
					}
				});
				e.handled = true;
			}
		});
		$('#category_id').select2({width: '200px', allowClear: true}).change(function (evt) {
			if (evt.added !== undefined) {
				$.get("/documents/patient_docs.php", {
					category_id: evt.added.id,
					page: 0<?php if(isset($_GET['pid']) && !is_blank($_GET['pid'])){?>,
					pid: '<?= $_GET['pid']?>'<?php }?>}, function (s) {
					$("#area11 > div[content='reloading']").html($(s).find("#area11 > div[content='reloading']").html());
					$('#category_id').select2({width: '200px', allowClear: true})
				});
			} else {
				$.get("/documents/patient_docs.php", {
					page: 0<?php if(isset($_GET['pid']) && !is_blank($_GET['pid'])){?>,
					pid: '<?= $_GET['pid']?>'<?php }?>}, function (s) {
					$("#area11 > div[content='reloading']").html($(s).find("#area11 > div[content='reloading']").html());
					$('#category_id').select2({width: '200px', allowClear: true})
				});
			}
		});
	});
	$(document).on('click', '.list11.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.get("/documents/patient_docs.php", {
					category_id: $('#category_id').val(),
					page: page<?php if(isset($_GET['pid']) && !is_blank($_GET['pid'])){?>,
					pid: '<?= $_GET['pid']?>'<?php }?>}, function (s) {
					$("#area11 > div[content='reloading']").html($(s).find("#area11 > div[content='reloading']").html());
				});
			}
			e.clicked = true;
		}
	});
	function deleteDoc(id, user_id) {
		Boxy.ask("You are about to delete a document, proceed?", ['Yes', 'No'], function (choice) {
			if (choice === 'Yes') {
				$.ajax({
					url: '/api/delete_doc.php',
					type: 'POST',
					data: {id: id, user_id: user_id},
					success: function (data) {
						Boxy.info(data);
						location.reload();
					},
					error: function (data) {
						Boxy.warn(data);
					}
				});
			}
		});
	}
</script>
