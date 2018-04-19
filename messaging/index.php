<?php
$script_block = <<< EOF
\$(document).ready(function(){
    \$("#tab-container").easytabs({
        animate:false,
        tabsClass:"nav nav-tabs",
        tabClass: "tabClass",
        updateHash: false,
        cache:false
    });
//    \$('table.table1').tableScroll({height:200});
    setTimeout(function(){
       $('#due table, #sent table').dataTable();
    }, 200);

    \$("#tab-container").bind("easytabs:after", function(){
        //get the active tab?
        //after you navigate to the other tab and back, the table scroll scatters,
        //so we have to re-init it
        //\$('table.table1').tableScroll({height:200});
        //\$('table.table2').tableScroll({height:200});
        setTimeout(function(){
           $('#due table, #sent table').dataTable();
        }, 200);
    });

    \$('#selectAll').live('change', function(e){
        // when master is checked/unchecked, select all the family members
        \$('input:checkbox[name="messages[]"]').prop('checked', this.checked).iCheck('update');

        // and tell those members that "we have changed"
        \$('input:checkbox[name="messages[]"]').trigger('change');
    });
    //when a family member changes, count the number of members who are 'on'
    \$('input:checkbox[name="messages[]"]').live('change',function(){
        var count_ = \$('input:checkbox[name="messages[]"]:checked').length;
        \$('#console').html(count_+ ' selected');
    });
    //initialize the trigger, and let "our family" be aware of a initial change state
    \$('input:checkbox[name="messages[]"]').trigger('change');
    \$('#dispatchBtn').live('click',function(e){
        if(e.handled != true){
            $.ajax({
                url:'/messaging/send_messages_new.php',
                data:$('#senderForm').serialize(),
                type:'post',
                beforeSend:function(){\$.blockUI({
                    message: '<h6 class="fadedText" style="font-size:200%">Dispatching messages...</h6>',
                    css: {
                        borderWidth: '0',
                        //color: '#f00',
                        //'text-shadow':'none',
                        backgroundColor:'transparent',
                        }
                    });
                },
                complete:function(rs, st){
                    var s = $.parseJSON(rs.responseText);
                    reportDeliveryStatus(s);
                }
            });
            e.handled = true;
        }
    });

    \$('#sendBtn').live('click',function(e){
        if(e.handled != true){
            Boxy.load('/pages/messaging/boxy.new.message.dialog.php',{title:'Compose Message'});
            e.handled = true;
        }
    });
});

//var totalSent = 0;
function reportDeliveryStatus(s, mode){
    \$.unblockUI();
//    console.log(s);
    for ( var i = 0; i < s.length; i++){
        if(s[i].status && s[i].status.split("|")[0]=="error"){
            \$("#notify").notify("create", { title:'Messaging', text:'<img src="/img/check48.png"> '+s[i].status.split("|")[1]},{expires:5000});
        }else if(s[i].response != 1801 ) {
            \$("#notify").notify("create", { title:'Messaging', text:'<img src="/img/check48.png"> Server returned an unexpected result'},{expires:5000});
        }else if(s[i].response == 1801){
            \$("#notify").notify("create", { title:'Messaging', text:'<img src="/img/check48__.png"> Message was dispatched successfully'},{expires:5000});
        }
        $("#tab-container").easytabs('select', '#due');
    }
}
EOF;

//todo: play around this function `reportDeliveryStatus' and check the return status

$page = "pages/messaging/message_app_index.php";
$extra_script = ['/js/jquery.easytabs.min.js'];
$extra_style = ['/style/easy.tabs.custom.css'];
//$title = "Messages";
include "../template.inc.in.php";