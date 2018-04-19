<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/14
 * Time: 5:16 PM
 */

$page = "pages/imaging/index.php";
$script_block = <<<END
var LOADING_TEXT = '<div class="ball"></div>';
function LoadDocument(where, url){
    $.ajax({
        url: url,
        beforeSend: function(){
            where.html(LOADING_TEXT);
        },
        complete: function(s){
            where.html(s.responseText);
        }
    });
}

\$(document).ready(function(){
    setTimeout(function(){\$('#scanHomeMenuLinks a:first-child').click();},10);
    \$('#scanHomeMenuLinks a.tab:not(.pull-right)').live('click', function(){
        \$('#scanHomeMenuLinks a').each(function(){
            \$(this).removeClass('on');
        });
        \$(this).addClass('on');
        LoadDocument($('.inner'), $(this).data('href'));
    });

    \$('#newScanLink').live('click', function(e){//, .boxy-link
        Boxy.load($(this).data('href'), {title:$(this).data('title'), afterHide:function(){setTimeout(function(){\$('#scanHomeMenuLinks a:first-child').click();},10);}});
        e.preventDefault();
    });
    $('.scan_actions a').live('click', function(){
        Boxy.load($(this).data('href'), {title:$(this).data('title')});
    });
    $('a._newDialog_[data-href]').live('click',function(e){
        if(!e.handled){
            id = $(this).data("id");
            Boxy.load($(this).data("href"), { title: $(this).data("title") ,afterHide:function(){
                setTimeout(function(){Boxy.get($(".close")).hideAndUnload();}, 50);
            }});
            e.handled = true;
            e.preventDefault();
        }
    });
    $('a._editDialog_').live('click',function(e){
        if(!e.handled){
            id = $(this).data("id");
            title_ = $(this).data("title");
            Boxy.load($(this).data("href"), { afterHide:function(){
                setTimeout(function(){Boxy.get($(".close")).hideAndUnload();}, 50);
            }});
            e.handled = true;
            e.preventDefault();
        }
    });
    /*$('a.printNote').live('click', function(e){
        if(!e.handled){
            window.open('/imaging/printNotes.php?id='+$(this).data("page-id"));
            e.handled=true;
        }
    });

    $('.submitToApprove').live('click', function(e){
        if(!e.handled) {
            var scanId = $(this).data('id');
            Boxy.ask("Submit for approval 1?", ["Yes", "No"], function(choice){
                if(choice == "Yes"){
                    $.post('/imaging/ajax.approve_.php', {id: scanId}, function (s) {
                        if (s.trim() == "ok") {
                            Boxy.info("Scan request sent for approval");
                            $('#scanHomeMenuLinks a.approve').click();
                        } else {
                            Boxy.alert("An error occurred");
                        }
                    });
                }
                else {
                }
            });
            e.handled = true;
        }
    });*/
});
END;
$title = " Imaging";
include "../template.inc.in.php";
