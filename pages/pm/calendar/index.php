<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/22/17
 * Time: 4:32 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
$data = (new ResourceDAO())->getResources();
?>
<h5>Available Calendar Resources <span class="pull-right"><a id="new_resource" href="javascript:">Add</a></span></h5>
<table class="table table-striped">
	<?php foreach ($data as $datum){//$datum=new Resource();?>
		<tr>
			<td><a href="javascript:" class="_resource_lnk" data-href="/pages/pm/calendar/resource.view.php?id=<?=$datum->getId()?>" data-title="View Resource Details"><?=$datum->getName()?></a></td>
			<td><a href="javascript:" class="_resource_lnk" data-href="/pages/pm/calendar/resource.edit.php?id=<?=$datum->getId()?>" data-title="Edit Resource">Edit</a></td>
		</tr>
	<?php }?>
</table>
<script type="text/javascript">
	$(document).on('click','#new_resource', function (e) {
		if(!e.handled){
			Boxy.load('/pages/pm/calendar/new_resource.php', {title: 'New Resource'});
			e.handled = true;
		}
	}).on('click','._resource_lnk', function (e) {
		
		if(!e.handled){
			Boxy.load($(this).data('href'), {title: $(this).data('title')});
			e.handled = true;
		}
	});
	
	var resourceHandler = {
		onStart:function () {
			$(document).trigger('ajaxSend');
		}, onComplete: function (s) {
			$(document).trigger('ajaxStop');
			var data=s.split(':');
			if(data[0]==='error'){
				Boxy.warn(data[1]);
			} else if(data[0]==='success'){
				Boxy.get($('.close')).hideAndUnload();
				Boxy.info(data[1], function () {
					Boxy.get($('.close')).hideAndUnload();
					hideAll(24);
				})
			}
		}
	}
</script>
