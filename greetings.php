<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/1/15
 * Time: 11:30 AM
 */

$message = "";// "Please the queue COUNTER has been deactivated temporarily to speed up things";
?>
<style type="text/css">
	.ui-notify-message {
		background: #000;
		background-size: 100% auto;
		background-position: 0% 0%;
		background-repeat: no-repeat;
		box-shadow: 0 0 6px #333;
		color: #ffffff !important;
		/*text-shadow: 0px 2px 3px #FFFFFF;*/
		text-align: center;
		/*border-radius: 10px !important;*/
	}

	.ui-notify-message p {
		font-size: small;
		/*text-shadow: 0px 2px 3px #FFFFFF;*/
		font-weight: bolder !important;
	}

	.ui-notify-message h1 {
		/*font-weight: bolder !important;*/
		font-size: 20px;
		color: #fff !important;
	}
</style>
<script type="text/javascript">
	$(document).ready(function () {
		var serverTime = moment('<?= date("Y-m-d H:i")?>').format('X');
		var clientTime = moment(moment(new Date()).format('YYYY-MM-DD HH:mm')).format('X');
		if ( Math.abs( parseFloat(clientTime) - parseFloat(serverTime) ) >= 300 ) {
			//300 is `seconds` equivalent of `minutes`
			//if the time difference is really up to 5 minutes, scream!
			$("#notify2").notify({speed: 500, expires: false});
			$("#notify2").notify("create", {
				title: "Your system time is so different from the server time. Please verify",
				text: 'click to hide'
			}, {
				expires: false,
				//custom: true,
				icon:'alert.png',
				click: function (e, instance) {
					instance.close();
				}
			});
		}
		<?php if(strlen($message) > 0){?>
		$("#notify2").notify({speed: 500, expires: false});
		$("#notify2").notify("create", {title: "<?= $message ?>", text: 'click to hide'}, {
			expires: false,
			custom: true,
			//icon:'alert.png',
			click: function (e, instance) {
				instance.close();
			}
		});
		<?php }?>

		/*var cookieName = "message";
		 var re = new RegExp('[; ]'+cookieName+'=([^\\s;]*)');
		 var sMatch = (' '+document.cookie).match(re);
		 if (!(cookieName && sMatch)) {
		 $("#notify2").notify({speed: 500, expires: false});
		 $("#notify2").notify("create", {title:"",text:'click to hide' }, {
		 expires: false,
		 custom: true,
		 //icon:'alert.png',
		 click: function (e, instance) {
		 var today = new Date();
		 //var expire = new Date();
		 var expire = new Date( (new Date()).getFullYear(), (new Date()).getMonth(), (new Date()).getDate()+1 );
		 //expire.setTime(today.getTime() + 3600000*24);
		 document.cookie = cookieName+"="+escape("yes")+ ";expires="+expire.toGMTString()+";path=/";
		 instance.close();
		 }
		 });
		 }*/
	});
</script>
<div id="notify2" style="display:none; z-index:999999">
	<div id="sticky2">
		<h1>#{title}</h1>
		<p>#{text}</p>
	</div>
</div>
