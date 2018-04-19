
<script type="text/javascript">
$(document).ready(function(){
 showTabs(1);
    var ntot = $("#tabbedPane ul li").length;
    $("#tabbedPane ul li").css({'min-width':($("#tabbedPane").width() / ntot)- 30});
 
});
<!--
function loading(){
 $("#contentPane").html('<table align="center" width="100%" height="100%"><tr><td valign="middle" align="center"><img src="/images/loading_large.gif" class="preloader" /></td></tr></table>');
}
function activateTab(t,urn,linkText,linkURL){
 $('.active').attr('class','');
 $('#tab-'+t).parent().attr('class','active');
 t1Link = '<div style="float:right;margin-bottom:-15px"><a href="javascript:void(o)" onclick="Boxy.load(\''+linkURL+'\',{title:\''+linkText+'\'})">'+linkText+'</a></div></div>';
 
 $('#newLink').html(t1Link);//
 $.ajax({
  url: urn,
    cache: true,
  success: function(s){
   $("#contentPane").html(s);
  }, beforeSend: function(){
   loading();
  }
 }); 
}
function showTabs(t){
 xLink = '';
 if(t==1){
  activateTab(t,'insurance/insuranceprofiles.php','Add new Profile','insurance/boxy.addprofile.php');}  
 else if(t==2){
  activateTab(t,'insurance/insuranceschemes.php','New insurance Scheme','insurance/boxy.addscheme.php');}
 else if(t==3){
  activateTab(t,'insurance/billableitems.php','Add Billable Item','insurance/boxy.addbillableitem.php');}
 else{}
}
//-->
</script>

<div id="tabbedPane" style="margin-bottom: -6px;">
    <ul class="tabs">
        <li><a href="javascript:void(0)" class="insurance_conf_tab_link" id="tab-1" onClick="showTabs(1)"><span></span> Insurance Profiles</a></li>
        <li><a href="javascript:void(0)" class="insurance_conf_tab_link" id="tab-2" onClick="showTabs(2)"><span></span> Insurance Programs/Schemes</a></li>
        <li><a href="javascript:void(0)" class="insurance_conf_tab_link" id="tab-3" onClick="showTabs(3)"><span></span> Billable Items</a></li>
    </ul>
<!--	<div><h3 class="header"><span class="header-2"><span class="header-3"></span></span></h3></div>-->
<!--	<div><h3 class="header"><span class="header-2"><span class="header-3">  </span></span></h3></div>-->
<!--	<div><h3 class="header"><span class="header-2"><span class="header-3">  </span></span></h3></div>-->

</div>
<!--<br clear="left" />-->
<div id="contentPane_">
<div><span id="newLink"></span></div>
<span id="contentPane"></span>
</div>
<br clear="left" />
<style type="text/css">
    @import url("/styles/patient.profile.css");
    #contentPane {
        height: 	200px;
        max-height: 200px;
        overflow-y:	auto;
        border-top:	3px solid #cfcfcf;
    }
</style>