<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/28/17
 * Time: 12:04 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabMethod.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SForm.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$options = SForm::$options;
$options = ['text', /*'radio', 'checkbox', 'longtext', */'number', 'date'];

if($_POST){
	if(is_blank($_POST['name'])){exit('error:Method name is required');}
	if(is_blank($_POST['type'])){exit('error:Data Type is required');}
	$method = (new LabMethod())->setName($_POST['name'])->setType($_POST['type'])->add();
	if($method != null){
		exit('success:Method added');
	}
	exit('error:Failed to add method');
}
?>
<section style="width: 250px;">
	<form method="post" action="<?= $_SERVER['PHP_SELF']?>" onsubmit="return AIM.submit(this, formHandler)">
		<label>
			Lab Method name:
			<input type="text" name="name" placeholder="Example: Creatinine">
		</label>
		<label>Type:
			<select name="type" data-placeholder="Data Type">
				<?php foreach ($options as $option){?><option value="<?=$option?>"><?=$option?></option><?php }?>
			</select>
		</label>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var formHandler = {onStart: function () {
		$(document).trigger('ajaxSend');
	}, onComplete: function (s) {
		$(document).trigger('ajaxStop');
		var data=s.split(':');
		if(data[0]==='error'){
			Boxy.warn(data[1]);
		} else if(data[0]==='success'){
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1]);
			$.getJSON('/api/lab_methods.php', function (data) {
				var str = '';
				_.each(data, function (item) {
					str += '<option value="'+item.id+'">'+item.name+'</option>';
				});
				//update the existing options
				//update the new options
				//update the options in the template?
				$('select[name="method_id[]"]').html(str);
				$('select[name^="method_id"]').html(str);
			});
			
		}
	}};
</script>