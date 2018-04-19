
<div ><a href='/pm/reporting/index.php'><input type='button' class='btn' value='<< Back'></a></div><br>
<h2>Vaccine Shot</h2>
<div style="display:block;">
    <div class="input-prepend">
        <button type="submit" class="btn remainder">&laquo; From </button>
        <input type="text" name="q" id="q" style="width: 40%;" placeholder="Enter Start Date"/>
        &nbsp;  
        <button type="submit" class="btn remainder">To &raquo;</button>
        <input type="text" name="q" id="q" style="width: 40%;" placeholder="Enter End Date"/>
    </div>
</div>

<div class="tablescroll table">
    <table class="tablescroll_head" cellspacing="0">
        <thead>
            <tr>
                <th>LGA</th>
                <th>Vaccine Name</th>
                <th>Shot Count</th>
            </tr>
        </thead>
    </table>
    <div class="tablescroll_wrapper">
        <table class="table-bordered table-hover table tablescroll_body" style="width: 90%;">
            <tbody>
                <tr>
                    <td><a href="javascript:">Eleme LGA</a></td>
                    <td>Anti Malaria</td>
                    <td>1245</td>
                </tr>
             </tbody>
        </table>
    </div>
</div>
<div id="searchBox">
    
    <script type="text/javascript">
        $(document).ready(function(){
            $("table.table").tableScroll({height:200});
        });
    </script>
</div>