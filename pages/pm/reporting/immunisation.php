
<div ><a href='/pm/reporting/index.php'><input type='button' class='btn' value='<< Back'></a></div><br>
<h2>Immunisation</h2>
<div style="display:block;">
    <div class="input-prepend">
        <button type="submit" class="btn remainder">&laquo; From </button>
        <input type="text" name="from" id="from" style="width: 40%;" readonly="readonly" placeholder="Click/touch to select Start Date"/>
        &nbsp;  
        <button type="submit" class="btn remainder">To &raquo;</button>
        <input type="text" name="to" id="to" style="width: 40%;" readonly="readonly" placeholder="Click/touch to select End Date"/>
    </div>
</div>

<div class="tablescroll table">
    <div class="tablescroll_wrapper">
        <table class="table-bordered table-hover table tablescroll_body" style="width: 90%;">
            <thead>
                <tr>
                    <th>LGA</th>
                    <th>Vaccine Shot</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="javascript:">Eleme LGA</a></td>
                    <td>PB</td>
                    <td>267</td>
                </tr>
             </tbody>
        </table>
    </div>
</div>
<div id="searchBox">
    
    <script type="text/javascript">
        $(document).ready(function(){
            $("table.table").tableScroll({height:200});
            var now=new Date().toISOString().split('T')[0];
            $(function(){
                $('#from').datetimepicker({
                    format:'Y-m-d',
                    formatDate:'Y-m-d',
                    timepicker:false,
                    onShow:function( ct ){
                        this.setOptions({
                            maxDate: now
                        });
                    },
                    onChangeDateTime:function(){$("#to").val("")}
                });
                $('#to').datetimepicker({
                    format:'Y-m-d',
                    formatDate:'Y-m-d',
                    timepicker:false,
                    onShow:function( ct ){
                        this.setOptions({
                            maxDate: now,
                            minDate: $("#from").val()? $("#from").val():false
                        });
                    },
                    onChangeDateTime:function(){
                        if($("#from").val()){
//                            window.location.href="/pm/reporting/bill.php?from="+$("#from").val()+"&to="+$("#to").val();
                        }
                    }
                });
            });
        }); 
    </script>
</div>