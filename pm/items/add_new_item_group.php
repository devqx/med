<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/20/17
 * Time: 9:18 AM
 */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_item_centers.php';

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ItemGroup.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ItemGroupData.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ItemGroupDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ItemGrpSc.php';
    require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';

    $generic__ids = array_filter(explode(",",$_POST['generic']));
	if(empty($_POST['name'])){
		exit("error: name required");
	}

	$store = (new ItemGroup())->setName($_POST['name'])->setDescription($_POST['description'])->add();
    if(count($generic__ids) > 0){
        $data = (new ItemGroupData())->setGroup((new ItemGroupDAO())->getItemGroup($store->getId()))->setGeneric($generic__ids)->add();
    }
	if($store !== NULL){
        if(!is_blank($_POST['service_id'])){
            (new ItemGrpSc())->setItemGroup((new ItemGroupDAO())->getItemGroup($store->getId()))->setServiceCenter((new ServiceCenterDAO())->get($_POST['service_id']))->add();
        }
		echo "success: Item Group Added";
	}else {
		echo "error:Cannot add Item Group";
	}
}?>
<div>
	<form id="formItem" action="<?= $_SERVER['REQUEST_URI']?>" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : success})">
		<label for="item_group">Name<input type="text" name="name" id="item_group"></label>
        <label for="generic">Generic<input type="hidden" name="generic" class="generic" id="generic">
            <label>Service Center<span class="pull-right"><a href="javascript:;" id="addCentLink">Add Service Center</a></span>
                <input type="hidden" name="service_id" id="service_id">
            </label>
            <label>Description <textarea name="description"></textarea> </label>
            <div>
			<button class="btn" name="itemCatBtn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()" >Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
    var itemCenter = <?= json_encode($centers, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;

    function success(s) {
		var s1=s.split(":");
		if(s1[0]==="success"){
            Boxy.info(s1[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
		}else {
			if(s1[0]==="error"){
                Boxy.alert("Unable to Save Item Group");

            }
		}
	}

    function getGenerics() {
        $.ajax({
            url: '/api/get_item_generic.php',
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
            },
            success: function (result) {
                setGenerics(result);
            },

        });
    }

    $(document).ready(function () {
        getGenerics();
        $('#addCentLink').on('click', function () {
            Boxy.load('/pm/items/add_new_center.php', {
                afterHide: function () {
                    $.ajax({
                        url: "/api/get_item_centers.php",
                        type: "POST",
                        dataType: "json",
                        success: function (c) {
                            itemCenter = c;
                            refreshCenter();
                        }
                    });
                }
            });
        });
    });


    function setGenerics(data) {
        $('input[name="generic"]').select2({
            width: '100%',
            allowClear: true,
            placeholder: "select item generic name",
            multiple: true,
            data: function () {
                return {results: data, text: 'name'};
            },
            formatResult: function (source) {
                return source.name;
            },
            formatSelection: function (source) {
                return source.name;
            }
        });
    }

    function refreshCenter() {
        $("#service_id").select2("destroy");
        setTimeout(function () {
            $("#service_id").select2({
                width: '100%',
                allowClear: true,
                placeholder: 'Select service center',
                data: {results: itemCenter, text: 'name'},
                formatResult: function (source) {
                    return source.name;
                },
                formatSelection: function (source) {
                    return source.name;
                }
            });
        }, 50);
    }

    refreshCenter();

</script>
