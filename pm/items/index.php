<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
if (!isset($_SESSION)) {
	session_start();
}

?>
<link rel="stylesheet" href="/style/insurance_items.css?i=<?= rand(9, 10000000) ?>" media="screen">
<script type="text/javascript">
	$('a.boxy').live('click', function (e) {
		if (!e.handled) {
			Boxy.load($(this).attr('href'), {title: $(this).attr('title') || $(this).text() || $(this).data('tooltipster-initialTitle')});
			e.preventDefault();
			e.handled = true;
		}
	});
	$('a.editItem, a.editCategory, a.editCenters, a.editGeneric, a.editGroup').live('click', function (e) {
		if (!e.handled) {
			Boxy.load($(this).data('href'), {title: $(this).attr('title') || $(this).text() || $(this).data('tooltipster-initialTitle')});
			e.preventDefault();
			e.handled = true;
		}
	});

</script>
<div style="width: 1000px">
	<div style="width: 70%; float:right !important;">
		<ul class="list-blocks" style="column-count: 5">
			<li class="no-border action"><a href="/pm/items/boxy_items.php" class="boxy" title="Manage Items">Items</li>
            <li class="no-border action"><a href="/pm/items/item_categories.php" class="boxy" title="Manage Item Generic Categories">Categories</a></li>
            <li class="no-border action"><a href="/pm/items/item_generics.php" class="boxy" title="Manage Item  Generics">Generics</a></li>
            <li class="no-border action"><a href="/pm/items/item_service_centers.php" class="boxy" title="Manage Item Service Centers">Business Unit/Service Centers </a></li>
            <li class="no-border action"><a href="/pm/items/groups.php" class="boxy" title="Manage Item Groups">Groups</a></li>

        </ul>
	</div>

	<h4>Stock Control</h4>
	<div id="stockcontrol">
		<?php
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemCategoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGrpScDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
		
		$items = (new ItemDAO())->getItems();
		$cats = (new ItemCategoryDAO())->getCategories();
		$centers = (new ServiceCenterDAO())->all('item');
		$generics = (new ItemGenericDAO())->list_();
		$groups = (new ItemGroupDAO())->all();
		?>
		<div style="display: inline-block; clear: both; width: 100%;">
			<div class="lab-collapse-header">
				<div class="pull-left">Items</div>
				<span class="pull-right">
				 <input data-type="items" type="search" class="inline-input">
				 <button data-type="items" type="button" class="filter btn btn-small1"><i class="icon-search"></i></button>
				 <button data-type="items" type="button" class="clear btn btn-small1"><i class="icon-remove"></i></button>
			 </span>
			</div>
			<div class="contained_items">
				<ul class="list-blocks items"></ul>
				<span class="clearfix"><a href="javascript:" class="load-more" data-type="items"> Load More ...</a></span>
			</div>
		</div>

        <div style="display: inline-block; clear: both; width: 100%;">
            <div class="lab-collapse-header">Item Groups
                <span class="pull-right">
				 <input data-type="group" type="search" class="inline-input">
				 <button data-type="group" type="button" class="filter btn btn-small1"><i class="icon-search"></i></button>
				 <button data-type="group" type="button" class="clear btn btn-small1"><i class="icon-remove"></i></button>
			 </span>
            </div>
            <div class="contained_items">
                <ul class="list-blocks group"></ul>
                <span class="clearfix"><a href="javascript:" class="load-more" data-type="group"> Load More ...</a></span>
            </div>
        </div>

		<div style="display: inline-block; clear: both; width: 100%;">
			<div class="lab-collapse-header">Item Service Centers
				<span class="pull-right">
				 <input data-type="centers" type="search" class="inline-input">
				 <button data-type="centers" type="button" class="filter btn btn-small1"><i class="icon-search"></i></button>
				 <button data-type="centers" type="button" class="clear btn btn-small1"><i class="icon-remove"></i></button>
			 </span>
			</div>
			<div class="contained_items">
				<ul class="list-blocks centers"></ul>
				<span class="clearfix"><a href="javascript:" class="load-more" data-type="centers"> Load More ...</a></span>
			</div>
		</div>

	</div>
</div>
<script type="text/javascript">
	$('.boxy-content').on('click', '.load-more', function (e) {
		if (!e.handled) {
			lazyLoad($(this).data("type"), $(this));
			e.handled = true;
		}
	}).on('click', '.filter.btn.btn-small1', function (e) {
		if (!e.handled) {
			search($(this).prev('input[type="search"]').val(), $(this).data("type"));
			e.handled = true;
		}
	}).on('click', '.clear.btn.btn-small1', function (e) {
		if (!e.handled) {
			clear($(this).data("type"));
			e.handled = true;
		}
	});

	function lazyLoad(type, loader) {
		var lastItemId = $("ul." + type + " li:last a.edit").data("id");
		if (typeof lastItemId === "undefined") {
			lastItemId = 0;
		}
		$.ajax({
			url: '/api/get_item_' + type + '.php',
			type: "POST",
			data: {last_item_id: lastItemId},
			dataType: "json",
			beforeSend: function () {
				$(loader).html("Loading...");
			},
			complete: function (xhr, status, n) {
				var data = xhr.responseJSON;
				 if (type === "items") {
					for (var a = 0; a < data.length; a++) {
						$("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[a].id + '" class="edit editItem" href="javascript:;" title="Edit ' + data[a].name + '" data-href="/pm/items/editItem.php?id=' + data[a].id + '"><i class="icon-edit"></i></a><a title="Manage ' + data[a].name + ' Batch" class="boxy"  href="/pm/items/batchMgt.php?id=' + data[a].id + '">' + data[a].name + ' <i class="icon-plus-sign"></i></a></li>');
					}
				} else if (type === "centers") {
                     for (var c = 0; c < data.length; c++) {
                         $("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[c].id + '" class="edit editCenters" href="javascript:;" title="Edit ' + data[c].name + '" data-href="/pm/items/editItemCenter.php?c_id=' + data[c].id + '"><i class="icon-edit"></i></a>' +
                             '<a title="Manage Groups in ' + data[c].name + '" class="boxy" href="/pm/items/groups_in_center.php?c_id=' + data[c].id + '&name=' + data[c].name + '">' + data[c].name + '</a>' +
                             '<a title="Manage Groups in  ' + data[c].name + '"  class="boxy" href="/pm/items/groups_in_center.php?c_id=' + data[c].id + '&name=' + data[c].name + '"><i class="icon-plus-sign"></i></a></li>');
                     }


                 } else {
					for (var j = 0; j < data.length; j++) {
						$("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[j].id + '" class="edit editGroup" href="javascript:;" title="Edit ' + data[j].name + '"  data-href="/pm/items/edit_group.php?id=' + data[j].id + '"><i class="icon-edit"></i></a><a title="Manage Generics in ' + data[j].name + '" class="boxy" href="/pm/items/generics_group.php?id=' + data[j].id + '">' + data[j].name + '<i class="icon-plus-sign"></i></a></li>');
					}
				}
				if (data.length > 0) {
					$(loader).html("Load More...");
				} else {
					$(loader).html("All loaded !");
				}

			}
		});
	}

	function search(filter, type) {
		$("ul." + type).html('');
		$.ajax({
			url: '/api/get_item_' + type + '.php',
			type: "POST",
			data: {search: filter},
			dataType: "json",
			beforeSend: function () {
			},
			complete: function (xhr, status, g) {
				var data = xhr.responseJSON;
               if (type === "items") {
                    for (var a = 0; a < data.length; a++) {
                        $("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[a].id + '" class="edit editItem" href="javascript:;" title="Edit ' + data[a].name + '" data-href="/pm/items/editItem.php?id=' + data[a].id + '"><i class="icon-edit"></i></a><a title="Manage ' + data[a].name + ' Batch" class="boxy"  href="/pm/items/batchMgt.php?id=' + data[a].id + '">' + data[a].name + ' <i class="icon-plus-sign"></i></a></li>');
                    }
                } else if (type === "centers") {
                    for (var c = 0; c < data.length; c++) {
                        $("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[c].id + '" class="edit editCenters" href="javascript:;" title="Edit ' + data[c].name + '" data-href="/pm/items/editItemCenter.php?c_id=' + data[c].id + '"><i class="icon-edit"></i></a>' +
                            '<a title="Manage Groups in ' + data[c].name + '" class="boxy" href="/pm/items/groups_in_center.php?c_id=' + data[c].id + '&name=' + data[c].name + '">' + data[c].name + '</a>' +
                            '<a title="Manage Groups in  ' + data[c].name + '"  class="boxy" href="/pm/items/groups_in_center.php?c_id=' + data[c].id + '&name=' + data[c].name + '"><i class="icon-plus-sign"></i></a></li>');
                    }

                } else {
                    for (var j = 0; j < data.length; j++) {
                        $("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[j].id + '" class="edit editGroup" href="javascript:;" title="Edit ' + data[j].name + '"  data-href="/pm/items/edit_group.php?id=' + data[j].id + '"><i class="icon-edit"></i></a><a title="' + data[j].name + ' Items" class="boxy" href="/pm/items/generics_group.php?gid=' + data[j].id + '">' + data[j].name + '<i class="icon-plus-sign"></i></a></li>');
                    }
                }
			}
		});
	}

	function clear(type) {
		$("ul." + type).html('');
		$('input[type="search"][data-type="' + type + '"]').val('');
	}

</script>