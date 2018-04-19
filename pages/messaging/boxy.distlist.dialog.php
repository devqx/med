<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 2/19/15
 * Time: 2:36 PM
 */

require_once $_SERVER['DOCUMENT_ROOT']."/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DistributionList.php';
require_once $_SERVER['DOCUMENT_ROOT']."/classes/DAOs/DistributionListDAO.php";

$dist_list = (new DistributionListDAO())->getDistributionLists();
$DL = new DistributionListDAO();
?>
<div style="width: 660px;">
    <label><a href="javascript:;" id="newDistList">Create New List</a></label>
    <table class="distlist table table-hover table-striped">
        <thead>
        <tr>
            <th>S/N</th>
            <th>Distribution List</th>
            <th>Number of Contacts</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php if(isset($dist_list) && count($dist_list)>0){
            foreach($dist_list as $key => $dlst){ ?>
                <tr>
                    <td><?= ($key +1) ?></td>
                    <td><?= $dlst->getName() ?></td>
                    <td><?= $DL->shownumberofpatients($dlst->getSqlQuery()) ?></td>
                    <td><input class="distlist" type="checkbox" name="distlist[]" value="<?=$dlst->getId() ?>"></td>
                    <td><a href="javascript:;" id="editDist" data-id="<?=$dlst->getId() ?>"><i class="icon-edit"></i></a> <a href="javascript:;" id="delDist" data-id="<?=$dlst->getId() ?>"><i class="icon-remove"></i></a></td>
                </tr>
            <?php
            }
        }
        else {
        	echo '<tr><td colspan="4">No Distribution List Available</td></tr>';
        }
        	?>
        </tbody>
    </table>
    <label></label>
    <div class="btn-block">
        <a href="javascript:;" class="btn" id="addRecipients">Add to recipients</a>
        <a href="javascript:;" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</a>
    </div>
</div>
<script>
    $(document).ready(function() {
        $(".distlist").dataTable();
        $("#newDistList").live('click', function (e) {
            if (e.handled != true ) {
                Boxy.load('/pages/messaging/boxy.newdistlist.dialog.php', {title: 'New Distribution List'});
                e.handled = true;
            }
        });

        $("#editDist").live('click', function (e) {
            if (e.handled != true) {
                Boxy.load('/pages/messaging/boxy.newdistlist.dialog.php?q='+$(this).data('id'), {title: 'Edit Distribution List'});
                e.handled = true;
            }
        });

        $("#delDist").live('click', function (e) {
            if (e.handled != true) {
                if(confirm("Do you really want to delete this list?") == true ){
                    $.ajax({
                        url: "/api/del_distribution_list.php",
                        data: { q: $(this).data('id') },
                        dataType: "html",
                        success: function(a){
                            var s = $.parseJSON(a);

                            if(s.status && s.status.indexOf('error') != -1){
                                var data = s.status.split("|");
                                Boxy.alert(data[1])
                            }else {
                                Boxy.get($('.close')).hideAndUnload(function(){
                                    Boxy.load('/pages/messaging/boxy.distlist.dialog.php',{title:'Distribution List'});
                                });
                            }
                        }
                    });
                }
                e.handled = true;
            }
        });

        $("#addRecipients").live('click', function (e) {
            if (e.handled != true) {
                var list = [];
                $("input.distlist").each(function(){
                    if($(this).is(":checked")){
                        list.push($(this).val());
                    }
                });
                Boxy.get( $('.close')).hideAndUnload(function(){
                    $.ajax({
                        url: "/api/get_contacts_list.php",
                        data: { q:JSON.stringify(list) },
                        dataType: "json",
                        success: function(data){
                            $("#recipients").select2('data', data);
                        }
                    });
                });
                e.handled = true;
            }
        });
    });
</script>