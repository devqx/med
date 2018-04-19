<?php include("datecalendar.php");?>

<script src="/js/jquery.js"></script>
<script src="/js/highchart/highcharts.js"></script>
<script src="/js/highchart/modules/exporting.js"></script>

<?php 
    $start='1972-01-01';
    $end=date("Y-m-d",time());//'2012-08-02';
    require $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
    $invreport = new Reports();
    $hospital="Government";
    $all = $invreport->inventoryreport($hospital);
    //sleep(2);
?>

<script type="text/javascript">   
    $(document).ready(function(){
        $("#inventoryreport").hide();
        $("#tableHead").click(function(){
            $("#inventoryreport").toggle("slow")
        });
    });
	function start(){
		$('#inv').html("<em>loading ...</em>");}
	function finished(s) {
		$('#inv').html(s);
	}
    
        var cats=<?php echo json_encode($all[1]) ?>;
        var rawData=<?php echo json_encode($all[2]) ?>;
        var total=<?php echo json_encode($all[3]) ?>;

//            for(var i=0; i<rawData.length; i++){
//                x=new Array();
//                y=new Array();
//                for(var j=0; j<rawData[i][1].length; j++){
//                    console.log(i+":  "+111111+"  "+rawData.length)
//                    x[j]=rawData[i][1][j][0];
//                    y[j]=rawData[i][1][j][1];
//                    console.log(j+":  "+222222+"    "+rawData[i][1][j][1]+"   :    "+rawData[i][1])
//                }
//                console.log("_________________a___________________")
//                console.log(x)
//                console.log(y)
//                console.log("_________________b___________________")
//                data[i]={
//                    y:10, 
//                    color:Highcharts.getOptions().colors[i], 
//                    drilldown: {
//                        name: cats[i],
//                        categories: x, 
//                        data: y, 
//                        color: Highcharts.getOptions().colors[i]
//                    }
//                };
//            }
             
        $(function () {
            dataa =new Array();
            for(var i=0; i<rawData.length; i++){
                x=new Array();
                y=new Array();
                for(var j=0; j<rawData[i][1].length; j++){
                    x[j]=parseInt(rawData[i][1][j][0]);
                    y[j]=rawData[i][1][j][1];
                }
                console.log(x)
                console.log(y)
                dataa [i]={
                    y:parseInt(rawData[i][1][rawData[i][1].length-1][2]), 
                    color:Highcharts.getOptions().colors[((i>9)? (i-10):i)], 
                    drilldown: {
                        name: cats[i],
                        categories: y, 
                        data: x, 
                        color: Highcharts.getOptions().colors[((i>9)? (i-10):i)]
                    }
                };
            }
            var colors = Highcharts.getOptions().colors,
            categories = cats,
            name = 'Drug Categories',
            data  = dataa ; 
    
    
    
   
    
        function setChart(name, categories, data, color) {
            chart.xAxis[0].setCategories(categories, false);
            chart.series[0].remove(false);
            chart.addSeries({
                    name: name,
                    data: data,
                    color: color || 'white'
            }, false);
            chart.redraw();
        }
    
        var chart = $('#container').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: ""//'Current Drug inventory Report'
            },
            subtitle: {
                text: 'Click the columns to view drug brands. Click again to view Categories.'
            },
            xAxis: {
                categories: categories
            },
            yAxis: {
                title: {
                    text: 'Total drug available'
                }
            },
            plotOptions: {
                column: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function() {
                                var drilldown = this.drilldown;
                                if (drilldown) { // drill down
                                    setChart(drilldown.name, drilldown.categories, drilldown.data, drilldown.color);
                                } else { // restore
                                    setChart(name, categories, data );
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        color: colors[0],
                        style: {
                            fontWeight: 'bold'
                        },
                        formatter: function() {
                            return this.y;
                        }
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    var point = this.point,
                        s = this.x +': <b>'+ this.y +' units remaining</b><br/>';
                    if (point.drilldown) {
                        s += 'Click to view '+ point.category +' brands';
                    } else {
                        s += 'Click to return to browser categories';
                    }
                    return s;
                }
            },
            series: [{
                name: name,
                data: data,
                color: 'white'
            }],
            exporting: {
                enabled: false
            }
        })
        .highcharts(); // return chart
    });
</script>
<div ><a href='/pm/reporting/index.php'><input type='button' class='btn' value='<< Back'></a></div>
<div class="reportTitle"><h2>DRUG INVENTORY REPORT </h2></div>

<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
<div id="inv" align="center">
<?php
    echo $all[0];
?>
    <br></div>
<?php ?>