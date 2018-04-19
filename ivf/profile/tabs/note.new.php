<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/20/16
 * Time: 2:25 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFNote.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFNoteTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
@session_start();
$templates = (new IVFNoteTemplateDAO())->all();
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();

	if (is_blank($_POST['remarks'])) {
		exit('error:Note is required');
	}

	$pdo->beginTransaction();
	$note = (new IVFNote())->setNote($_POST['remarks'])->setInstance(new IVFEnrollment($_POST['enrolment_id']))->setDate(date(MainConfig::$mysqlDateTimeFormat))->setUser(new StaffDirectory($_SESSION['staffID']))->add($pdo);
	if ($note == null) {
		$pdo->rollBack();
		exit('error:Action failed');
	}

	$pdo->commit();
	exit('success:Note saved');

}
?>
<section style="width: 900px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: __wrap8e3dd93a601__, onComplete: __impl4cf1e782hg1__})">
		<label>Template
			<span class="pull-right">
				<a class="template_handler" data-href="/ivf/profile/tabs/template.new.php" href="javascript:">New Template</a> |
				<a class="template_handler" data-href="/ivf/profile/tabs/template.showall.php" href="javascript:">Manage Templates</a>
			</span>
			<select name="template_id" id="template_list_id" data-placeholder="Select ...">
				<option></option>
				<?php foreach ($templates as $template) { ?>
					<option value="<?= $template->getId() ?>"><?= $template->getTitle() ?></option>
				<?php } ?>
			</select>
			</select></label>
		<label>Note <textarea class="wide" name="remarks" rows="10"></textarea></label>
		<input type="hidden" name="enrolment_id" value="<?= $_GET['id'] ?>">
		<div class="clear" style="margin-bottom: 10px;"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	__impl4cf1e782hg1__ = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1]);
		}
	};

	__wrap8e3dd93a601__ = function () {
		$(document).trigger('ajaxSend');
	};

	$('[name="remarks"]').summernote(SUMMERNOTE_CONFIG);

	$('.template_handler').click(function (e) {
		if (!e.handled) {
			Boxy.load($(e.target).data('href'), {
				/*afterHide: function () {
						reloadTemplates();
				}*/
			});
			e.handled = true;
		}
	});

	$("#template_list_id").change(function (e) {
		if (e.added) {
			var d = $('[name="remarks"]').code();
			$.getJSON('/ivf/api/get_tpl.php', {id: $(this).val()}, function (data) {
				var s = (data);
				$('[name="remarks"]').code(d + s.content);
			});
		}

	});

	function reloadTemplates() {
		$.getJSON('/ivf/api/get_tpl_list.php', function (data) {
			var str = '';
			for (var i = 0; i < data.length; i++) {
				str += '<option value="' + data[i].id + '">' + data[i].title + '</option>';
			}
			$('#template_list_id').html(str);
		});
	}
</script>