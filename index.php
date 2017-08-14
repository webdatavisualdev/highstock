<!DOCTYPE html>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link href="css/style.css" rel="stylesheet">
<body>
	<div id="cover"></div>
	<form class="form-inline dropdown">
		<div class="form-group">
			<label><i class="fa fa-bar-chart"></i>Compare:</label>
			<input type="text" class="form-control" placeholder="Search customer company name/ticker">
			<ul class="nav nav-tabs dropdown-menu">
				<li class="active"><a data-toggle="tab" id="type1">Type1</a></li>
				<li><a data-toggle="tab" id="type2">Type2</a></li>
				<li><a data-toggle="tab" id="other"><i class="fa fa-search"></i></a></li>
				<div class="tab-content">
					<table class="table"></table>
				</div>			
			</ul>
		</div>
	</form>
	<div id="container"></div>
	<div id="news">
		<div class="header">
			<span>News Developments</span>
			<button class="btn btn-default btn-news">+ News Subjects</button>
			<div class="dropdown-news">
				<input type="checkbox">
				<ul class="categories"></ul>
			</div>
		</div>
		<div class="content">
		</div>
	</div>
	<div id="myModal" class="modal fade">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <div class="modal-header">
	                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	                <h4 class="modal-title">Confirmation</h4>
	            </div>
	            <div class="modal-body">
	                <p>Do you want to save changes you made to document before closing?</p>
	            </div>
	            <div class="modal-footer">
	                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	            </div>
	        </div>
	    </div>
	</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
	var dateGroupIndex, newsData, chart, totalData;
	var maxDate = 0;
	var displayCount = 500;
	var groupingArryIndex = [];
	var intraFlag = 0;
</script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="js/highstock.src.js"></script>
<script src="js/flags-grouping.js"></script>
<script src="js/d3.v4.js"></script>
<script src="js/script.js"></script>
<script>
	var companyData;
	var currentType;
	var categories = [];
	var newsSections;

	$(document).ready(function(){
		var chartData3;
		//makeGrouping();
		d3.json("data/data.json", function(data3){
			d3.json("data/news.json", function(data){
				data.sort(compareNew);
				newsData = data;
				data3.sort(compare);
				totalData = data3;
				chartData3 = getChartData(data3);
				drawChart(chartData3);
	            var startInd = getIndex(1, "month", "1m", 0, chartData3);
	            displayNews(startInd, newsData.length-1, -1);
			});
		});
		d3.csv("data/company.csv", function(data) {
			companyData = data;
			addTableBody("type1");
		});
		d3.csv("data/news-category.csv", function(data) {
			newsSections = data;
			data.map(function(d) {
				if(categories.indexOf(d.category) < 0) {
					categories.push(d.category);
				}
			});
			addCategories();
		});

		$('body').on('click', function(e) {
			var obj = $(e.target);
			if(obj.closest('.highcharts-tracker').length){
            	d3.selectAll('.highcharts-markers.highcharts-tracker path').attr('fill','white');
			}else if (obj.closest('.flag').length) {
            	var index = obj.data('index');
            	$('#myModal .modal-title').html(newsData[index].headline);
            	$('#myModal .modal-body').html(newsData[index].News_Body);
				$("#myModal").modal('show');
            	$('.eachContent').removeClass('highlight');
            	d3.selectAll('.highcharts-markers.highcharts-tracker path').attr('fill','white');
            	obj.parent().addClass('highlight');
            }else if(obj.closest('.right').length){
            	$('.eachContent').removeClass('highlight');
            	obj.parent().parent().addClass('highlight');
            	var index = obj.parent().data('index');
            	var selectable;
            	d3.selectAll('.highcharts-markers.highcharts-tracker path').attr('fill','white');
            	var i;
            	for(i = 0; i < groupingArryIndex.length; i++){
            		if(i == 0 && index <= groupingArryIndex[i] || (index > groupingArryIndex[i-1] && index <= groupingArryIndex[i])){
            			break;
            		}
            	}
            	var start, end;
                if(i == 0){
            		start = chartData3[3][0][index].x;
            		end = chartData3[3][0][groupingArryIndex[0]].x;
                }else{
                	start = chartData3[3][0][index].x;
                	end = chartData3[3][0][groupingArryIndex[i]].x;
                }
                for(var seriesIndex = 0; seriesIndex < chart.series.length; seriesIndex++){
	                var series = chart.series[seriesIndex];
	                if(series.type === 'flags'){
		        		var opts = series.chart.options.flagsGrouping;
			            if (end - start < opts.minSelectableDateRange) {
			                var timeExtendTo = (opts.minSelectableDateRange - (end - start)) / 2;

			                start -= timeExtendTo;
			                end += timeExtendTo;

			                // Shift the result date range if after extension it exceeds the possible values
			                if (start < series.xAxis.dataMin) {
			                    end += series.xAxis.dataMin - start;
			                    start = series.xAxis.dataMin;
			                } else if (end > series.xAxis.dataMax) {
			                    start -= end - series.xAxis.dataMax;
			                    end = series.xAxis.dataMax;
			                }
			            }
		            	series.xAxis.setExtremes(start, end, true, true);
	                }
                }

            	$('.highcharts-markers.highcharts-tracker text').each(function(){
            		var sel = d3.select(this);
            		var text = sel.text();
            		var firstLetterKey = text.charCodeAt(0);
            		if(firstLetterKey >= 65 && firstLetterKey <= 90){
	            		var title = String.fromCharCode(65+index%26)+(parseInt(index/26)+1);
	            		if(title == text){
	            			selectable = d3.select(this.parentNode);
	            			selectable.select('path').attr('fill','red');
	            		}
            		}else if(groupingArryIndex[i] == text){
            			selectable = d3.select(this.parentNode);
            			selectable.select('path').attr('fill','red');
            		}
            	});
            }
		});

		$(".form-control").on("click", function() {
			$(".nav-tabs").show();
			$("#cover").show();
		});

		$(".nav-tabs li").on("click", function() {
			$(".nav-tabs li").removeClass("active");
			$(this).addClass("active");
			$(".dropdown-menu").css({display: "block"});
		});

		$("#cover").on("click", function() {
			$(".nav-tabs").hide();
			$("#cover").hide();
		});

		$("#type1").on("click", function() {
			addTableBody("type1");
		});

		$("#type2").on("click", function() {
			addTableBody("type2");
		});

		$("#other").on("click", function() {
			addTableBody("other");
		});

		$(".btn-news").on("click", function() {
			$(".dropdown-news").show();
		});
	});

	function addTableBody(type) {
		var table = $(".tab-content table");
		var th = "<tr><th>Company Name</th><th>Ticker</th><th>ISIN</th><th>Related Rate</th></tr>";
		table.html("");
		table.append(th);
		currentType = type;
		var index = 1;
		companyData.map(function(d) {
			if(d.type == type) {
				tr = "<tr onClick='selectCompany("+index+")'><td>"+d.company+"</td><td>"+d.ticker+"</td><td>"+d.isin+"</td><td>"+d.related+"</td></tr>";
				table.append(tr);
				index ++;
			}
		});
	}

	function addCategories() {
		var section = $(".dropdown-news .categories");
		categories.map(function(c) {
			var li = "<li style='background-size:"+100+"px'><a>"+c+"</a><div class='category-name'></div><a class='btn-edit-category'></a></li>";
			section.append(li);
		});
	}

	function selectCompany(index) {
		var i = 1;
		companyData.map(function(data) {
			if(data.type == currentType) {
				if(i == index) {
					$(".form-control").val(data.company);
				}
				i ++;
			}
		});
	}
</script>