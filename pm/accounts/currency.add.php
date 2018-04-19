<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/22/17
 * Time: 4:42 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Currency.php';
if($_POST){
	if(is_blank($_POST['code'])){exit('error:Code is blank');}
	if(is_blank($_POST['title'])){exit('error:Title is blank');}
	if(is_blank($_POST['symbol'])){exit('error:Symbol is blank');}
	
	$currency = (new Currency())
		->setActive( isset($_POST['active']) ? TRUE : FALSE )
		->setCode($_POST['code'])
		->setTitle($_POST['title'])
		->setSymbolRight( $_POST['position'] == 'right' ? $_POST['symbol'] : '' )
		->setSymbolLeft( $_POST['position'] == 'left' ? $_POST['symbol'] :'' )
		->add();
	
	if($currency != null){
		exit('success:Added successfully');
	}
	exit('error:Failed to add currency');
}
?>
<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, formHandler_)">
	<div class="row-fluid">
		<label class="span4 no-label"><input type="checkbox" name="active"> Active</label>
		<label class="span8">Code <input type="text" name="code"></label>
	</div>
	<label>Title <input type="text" name="title"> </label>
	
	<div class="row-fluid">
		<label class="span4">Symbol <input type="text" name="symbol"></label>
		<label class="span8">Position <select name="position">
				<option value="left">Left</option>
				<option value="right">Right</option>
			</select></label>
	</div>
	
	<div class="btn-block">
		<button class="btn" type="submit">Save</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</div>
</form>
<script type="text/javascript">
	var formHandler_ = {onStart: function () {
		$(document).trigger('ajaxSend');
	}, onComplete: function (s) {
		$(document).trigger('ajaxStop');
		var data=s.split(':');
		if(data[0]==='error'){
			Boxy.warn(data[1]);
		} else if(data[0]==='success'){
			showTabs(7);
			Boxy.get($('.close')).hideAndUnload();
		}
	}};
</script>
