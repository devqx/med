<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/25/17
 * Time: 9:43 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->get($_GET['id']);
if($_POST){
	if(is_blank($_POST['code'])){exit('error:Code is blank');}
	if(is_blank($_POST['title'])){exit('error:Title is blank');}
	if(is_blank($_POST['symbol'])){exit('error:Symbol is blank');}
	
	$currency = (new CurrencyDAO())->get(base64_decode($_POST['id']))
		//->setActive( isset($_POST['active']) ? TRUE : FALSE )
		->setCode($_POST['code'])
		->setTitle($_POST['title'])
		->setSymbolRight( $_POST['position'] == 'right' ? $_POST['symbol'] : '' )
		->setSymbolLeft( $_POST['position'] == 'left' ? $_POST['symbol'] :'' )
		->update();
	
	if($currency != null){
		exit('success:Updated successfully');
	}
	exit('error:Failed to update currency');
}
?>
<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, formHandler__)">
	<div class="row-fluid">
		<label class="span4 no-label"><input type="checkbox" name="active" <?= $currency->isActive() ? 'checked':'' ?>> Active</label>
		<label class="span8">Code <input type="text" name="code" value="<?=$currency->getCode() ?>"></label>
	</div>
	<input type="hidden" name="id" value="<?= base64_encode($_GET['id']) ?>">
	<label>Title <input type="text" name="title" value="<?=$currency->getTitle()?>"> </label>
	
	<div class="row-fluid">
		<label class="span4">Symbol <input type="text" name="symbol" value="<?= $currency->getSymbolRight().$currency->getSymbolLeft() ?>"></label>
		<label class="span8">Position <select name="position">
				<option value="left" <?= !is_blank($currency->getSymbolLeft()) ? 'selected':'' ?>>Left</option>
				<option value="right"<?= !is_blank($currency->getSymbolRight()) ? 'selected':'' ?>>Right</option>
			</select></label>
	</div>
	
	<div class="btn-block">
		<button class="btn" type="submit">Update</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</div>
</form>
<script type="text/javascript">
	var formHandler__ = {onStart: function () {
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