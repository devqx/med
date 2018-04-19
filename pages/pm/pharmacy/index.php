<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
if (!isset($_SESSION)) {
	session_start();
}
?>

<script type="text/javascript">
	$('a.boxy').live('click', function (e) {
		if (!e.handled) {//cool stuff to prevent the live event from firing multiple times
			Boxy.load($(this).attr('href'), {title: $(this).attr('title') || $(this).text() || $(this).data('tooltipster-initialTitle')});
			e.preventDefault();
			e.handled = true;
		}
	});
	$('a.editCategory, a.editGeneric').live('click', function (e) {
		if (!e.handled) {//cool stuff to prevent the live event from firing multiple times
			Boxy.load($(this).data('href'), {title: $(this).attr('title') || $(this).text() || $(this).data('tooltipster-initialTitle')});
			e.preventDefault();
			e.handled = true;
		}
	});
	function start() {
		$(".boxy-content").animate({scrollTop: 0}, "slow");
		$("span.error").html('<img src="/img/loading.gif"> please wait ...');
	}
</script>
<div style="width:1000px">

	<div style="float:right; width: 70%">
		<ul class="list-blocks" style="column-count: 5">
			<li class="no-border action">
				<a href="/pages/pm/pharmacy/pharmacies.php" class="boxy" title="Pharmacies">Pharmacies</a>
			</li>
			<li class="no-border action">
				<a href="/pages/pm/pharmacy/boxy.drugs.php" class="boxy" title="All Drugs in Inventory">All Drugs</a>
			</li>

			<li class="no-border action">
				<a href="/pages/pm/pharmacy/add_drugs.php" class="boxy" title="Add New Drugs to Inventory">Add Drug</a>
			</li>
			<li class="no-border action">
				<a title="Add Drug Category" href="/pages/pm/pharmacy/drugcat.php" class="boxy">Add Drug Category</a></li>
			<li class="no-border action">
				<a title="Add Drug Generic Name" href="/pages/pm/pharmacy/drug-generic-add.php" class="boxy">Add Generic</a></li>

			<li class="no-border action">
				<a title="Add Drug Generic Name" href="/pages/pm/pharmacy/super_generics.php" class="boxy">Super Generic</a></li>

			<li class="no-border action">
				<a class="boxy" href="/pages/pm/pharmacy/drugman.php" title="Add Drug Manufacturer">Add Manufacturer</a>
			</li>
			<li class="no-border action">
				<a class="boxy" href="/pages/pm/pharmacy/requisitions/index.php" title="Drug Requisitions">Drug Requisitions</a>
			</li>
			<li class="no-border action">
				<a class="boxy" href="/pages/pm/pharmacy/formularies.php" title="Drug Formularies">Drug Formularies</a>
			</li>
		</ul>
	</div>
	<h4>Stock Control</h4>
	<div id="stockcontrol">
		<?php
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugCategoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
		$categories = (new DrugCategoryDAO())->getCategories(null);
		$generics = (new DrugGenericDAO())->getGenerics(null, false);
		
		?>
		<div style="display: inline-block; clear:both;width:100%">
			<div class="lab-collapse-header">Drugs by Category
				<span class="pull-right">
                    <input data-type="categories" type="search" class="inline-input">
                    <button data-type="categories" type="button" class="filter btn btn-small1"><i class="icon-search"></i></button>
                    <button data-type="categories" type="button" class="clear btn btn-small1"><i class="icon-remove"></i></button>
                </span>
			</div>
			<p></p>
			<ul class="list-blocks categories">
				<?php foreach ($categories as $c) {
					//echo '<li class="rowlike tag"><a class="editCategory" href="javascript:;" data-href="/pages/pm/pharmacy/edit.category.php?id='.$c->getId().'"><i class="icon-edit"></i></a><a title="'.$c->getName().' Drugs" class="boxy" href="/pages/pm/pharmacy/boxy.drugs.php?type=category&Id='.$c->getId().'">'.ucwords(strtolower($c->getName())).'</a></li>';
				} ?>
			</ul>
			<span class="clearfix"><a href="javascript:" class="load-more" data-type="categories">Load More...</a></span>
		</div>
		<div style="display: inline-block; clear:both;width:100%">
			<div class="lab-collapse-header">Drugs by Generics
				<span class="pull-right">
                    <input data-type="generics" type="search" class="inline-input">
                    <button data-type="generics" type="button" class="filter btn btn-small1"><i class="icon-search"></i></button>
                    <button data-type="generics" type="button" class="clear btn btn-small1"><i class="icon-remove"></i></button>
                </span>
			</div>
			<p></p>
			<ul class="list-blocks generics">
				<?php foreach ($generics as $c) {
					//echo '<li class="rowlike tag"><a class="editGeneric" href="javascript:;" data-href="/pages/pm/pharmacy/edit.generic.php?id='.$c->getId().'"><i class="icon-edit"></i></a><a title="'.$c->getName().' Drugs" class="boxy" href="/pages/pm/pharmacy/boxy.drugs.php?type=generic&Id='.$c->getId().'">'.ucwords(strtolower($c->getName())).'</a></li>';
				} ?>
			</ul>
			<span class="clearfix"><a href="javascript:" class="load-more" data-type="generics">Load More...</a></span>
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
			url: '/api/get_drug_' + type + '.php',
			type: "POST",
			data: {last_item_id: lastItemId},
			dataType: "json",
			beforeSend: function () {
				$(loader).html("Loading...");
			},
			complete: function (xhr, status, g) {
				var data = xhr.responseJSON;
				if (type == "generics") {
					for (var i = 0; i < data.length; i++) {
						$("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[i].id + '" class="edit editGeneric" href="javascript:;" title="Edit Generic: ' + data[i].name + '" data-href="/pages/pm/pharmacy/edit.generic.php?id=' + data[i].id + '"><i class="icon-edit"></i></a><a title="' + data[i].name + ' Drugs" class="boxy" href="/pages/pm/pharmacy/boxy.drugs.php?type=generic&Id=' + data[i].id + '">' + data[i].weight + ' ' + data[i].name + '</a></li>');
					}
				} else {
					for (var j = 0; j < data.length; j++) {
						$("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[j].id + '" class="edit editCategory" href="javascript:;" title="Edit Category: ' + data[j].name + '"  data-href="/pages/pm/pharmacy/edit.category.php?id=' + data[j].id + '"><i class="icon-edit"></i></a><a title="' + data[j].name + ' Drugs" class="boxy" href="/pages/pm/pharmacy/boxy.drugs.php?type=category&Id=' + data[j].id + '">' + data[j].name + '</a></li>');
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
			url: '/api/get_drug_' + type + '.php',
			type: "POST",
			data: {search: filter},
			dataType: "json",
			beforeSend: function () {
				//$(loader).html("Loading...");
			},
			complete: function (xhr, status, g) {
				var data = xhr.responseJSON;
				if (type == "generics") {
					for (var i = 0; i < data.length; i++) {
						$("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[i].id + '" class="edit editGeneric" href="javascript:;" title="Edit Generic: ' + data[i].name + '" data-href="/pages/pm/pharmacy/edit.generic.php?id=' + data[i].id + '"><i class="icon-edit"></i></a><a title="' + data[i].name + ' Drugs" class="boxy" href="/pages/pm/pharmacy/boxy.drugs.php?type=generic&Id=' + data[i].id + '">' + data[i].weight + ' ' + data[i].name + '</a></li>');
					}
				} else {
					for (var j = 0; j < data.length; j++) {
						$("ul." + type + ":last").append('<li class="rowlike tag"><a data-id="' + data[j].id + '" class="edit editCategory" href="javascript:;" title="Edit Category: ' + data[j].name + '"  data-href="/pages/pm/pharmacy/edit.category.php?id=' + data[j].id + '"><i class="icon-edit"></i></a><a title="' + data[j].name + ' Drugs" class="boxy" href="/pages/pm/pharmacy/boxy.drugs.php?type=category&Id=' + data[j].id + '">' + data[j].name + '</a></li>');
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