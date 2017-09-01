<!DOCTYPE html>
<head>
<title></title>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link href="css/style.css" rel="stylesheet">
<!-- <script src="https://use.typekit.net/gti7qvg.js"></script> -->
<!-- <script>try{Typekit.load({ async: true });}catch(e){}</script> -->
<script src="https://use.typekit.net/ulv2nrf.js"></script> 
<script>try{Typekit.load({async: true});} catch(e) {}</script> 
</head>   
<body ng-app="myApp" ng-controller="myCtrl">
	<div id="cover"></div>
	<form class="form-inline dropdown">
		<div class="form-group">
			<label><img src="images/chart.png">Compare:</label>
			<input type="text" class="form-control" placeholder="Search customer company name/ticker">
			<ul class="nav nav-tabs dropdown-menu">
				<li class="active"><a data-toggle="tab" id="type1">Type1</a></li>
				<li><a data-toggle="tab" id="type2">Type2</a></li>
				<li><a data-toggle="tab" id="other"><i class="fa fa-search"></i></a></li>
				<div class="tab-content">
					<table class="table">
						<tr><th>Company Name</th><th>Ticker</th><th>ISIN</th><th>Related Rate</th></tr>
						<tr ng-repeat="d in typeData track by $index" ng-click="selectCompany($index)"><td>{{d.company}}</td><td>{{d.ticker}}</td><td>{{d.isin}}</td><td>{{d.related}}</td></tr>
					</table>
				</div>			
			</ul>
		</div>
	</form>
	<div id="container"></div>
	<div id="news">
		<div class="header">
			<span>News Developments</span>
			<button class="btn btn-default btn-news"><span>+ News Subjects</span></button>
			<div class="dropdown-news">
				<div class="checkbox">
					<label><input type="checkbox"> Select All</label>
				</div>
				<ul class="categories">
					<li ng-repeat="d in categories track by $index" ng-class="getCategoryClass(d)" ng-click="changeIconStatus($index)">
						<a style='width:100px' class='category-name'><span ng-click="clickCategoryItem(d)">{{d.name}}</span></a>
						<img src='images/edit.png' ng-click="showSecondCategories(d.name, $event)" ng-show="showIcon[$index]">
					</li>
				</ul>
			</div>
			<div class="second-category-dropdown">
				<div class="row">
					<div class="col-xs-6">
						<span>{{category}}</span>
					</div>
					<div class="col-xs-6">
						<div class="checkbox">
							<label><input type="checkbox"> Select All</label>
							<span ng-click="closeSecondDropdown()"><i class="fa fa-close"></i></span>
						</div>
					</div>
				</div>
				<ul class="second-categories">
					<li ng-repeat="d in secondCategories track by $index" ng-class="getClass(d)" ng-click="clickItem(d)"><a class='category-name'><span>{{d}}</span></a></li>
				</ul>
			</div>
		</div>
		<div class="selected-categories"></div>
		<div class="filter-checkbox">
			<label><input type="checkbox"> PRIMARY</label>
			<label><input type="checkbox"> SIGNIFICANT NEWS</label>
		</div>
		<div class="content"></div>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
	var dateGroupIndex, newsData, chart;
	var maxDate = 0;
	var displayCount = 500;
	var groupingArryIndex = [];
	var intraFlag = 0;
</script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="js/highstock.src.js"></script>
<script src="js/flags-grouping.js"></script>
<script src="js/d3.v4.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
<script src="js/script.js"></script>
<script>
var app = angular.module('myApp', []);
app.controller('myCtrl', function($scope, $compile) {
	$scope.companyData;
	$scope.typeData = [];
	$scope.currentType;
	$scope.categories = [];
	$scope.newsSections;
	$scope.secondCategories = [];
	$scope.showIcon = [];
	$scope.currentCompanies = [];

	$(document).ready(function(){
		var chartData3;

		$scope.getData = function(company) {
			var totalData = [];
			var stockVolume = [];
			var sentiments = [];
			var newsData = [];
			$.post( "stockprice.php", {company: company}, function(res) {
				var data = JSON.parse(res);
				data.map(function(d) {
					totalData.push({
						Ticker: d.isin,
						date: d.s_timestamp,
						close: d.price,
						volumn: 0,
						sentiment: null
					});
				});
			});

			$.post( "stockvolume.php", {company: company}, function(res) {
				var data = JSON.parse(res);
				data.map(function(d) {
					stockVolume.push({
						date: d.s_timestamp,
						volumn: d.volume
					});
				});
			});

			$.post( "newsdata.php", {company: company}, function(res) {
				var data = JSON.parse(res);

				data.map(function(d) {
					var dateStr = d.display_date;
					var year = dateStr.substring(0, 4);
					var month = dateStr.substring(4, 6);
					var day = dateStr.substring(6, 8);
					var date = month + "/" + day + "/" + year;

					var hour = parseInt(dateStr.substring(9, 11));
					var minute = dateStr.substring(11, 13);
					var second = dateStr.substring(13, 15);
					var time = (hour > 12 ? hour - 12 : hour) + ":" + minute + ":" + second + " " + (hour > 12 ? "PM" : "AM");

					newsData.push({
						Ticker: d.symbol,
						date: date,
						close: '',
						volume: '',
						sentiment: '',
						headline: d.headline,
						News_Body: d.lexicon,
						Time: time,
						seq:d.id
					});
				});
				newsData.sort(compareNew);
			});
			$.post( "sentiment.php", {company: company}, function(res) {
				var data = JSON.parse(res);
				data.map(function(d) {
					sentiments.push({
						Ticker: d.isin,
						date: d.s_timestamp,
						close: null,
						volumn: null,
						sentiment: d.price
					});
				});
			})			

			$scope.timer = setTimeout(function(){
				if(totalData.length >= 0 && stockVolume.length >= 0 && sentiments.length >= 0 && newsData.length >= 0) {
					console.log(totalData, stockVolume, newsData, sentiments)
					// clearInterval($scope.timer);
					totalData.map(function(t) {
						stockVolume.map(function(s) {
							if(t.date == s.date) {
								t.volume = s.volume;
							}
						});
					});
					sentiments.map(function(d) {
						totalData.push(d);
					});
					totalData.sort(compare);
					chartData3 = getChartData(totalData);
					drawChart(chartData3);
					var startInd = getIndex(1, "month", "1m", 0, chartData3);
					displayNews(startInd, newsData.length-1, -1);
				}
			}, 1000);
		}	

		d3.csv("data/company.csv", function(data) {
			$scope.companyData = data;
			$scope.addTableBody("type1");
			$scope.currentCompanies.push($scope.typeData[0]);
			$scope.getData($scope.currentCompanies[$scope.currentCompanies.length - 1].isin);
		});
		d3.csv("data/news-category.csv", function(data) {
			$scope.newsSections = data;
			var tempCategory = [];
			data.map(function(d) {
				if(d.default == "Y") {
					if(tempCategory.indexOf(d.category) < 0) {
						tempCategory.push(d.category);
					}
				}
				var flag = false;
				$scope.categories.map(function(c) {
					if(c.name == d.category) {
						flag = true;
					}
				});
				if(!flag) {
					var selected = tempCategory.indexOf(d.category) >= 0 ? "Y" : "N";
					$scope.categories.push({name: d.category, selected: selected, count: 0});
				}
			});
			for(var i = 0 ; i < $scope.categories.length ; i ++) {
				$scope.showIcon[i] = false;
				if($scope.categories[i].selected == "Y") {
					$scope.changeFilteredCategories(i);
				}
			}
			$scope.$apply();
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
			$(".dropdown-news").hide();
			$("#cover").hide();
			$(".second-category-dropdown").hide();
		});

		$("#type1").on("click", function() {
			$scope.addTableBody("type1");
		});

		$("#type2").on("click", function() {
			$scope.addTableBody("type2");
		});

		$("#other").on("click", function() {
			$scope.addTableBody("other");
		});

		$(".btn-news").on("click", function() {
			$(".dropdown-news").show();
			$("#cover").show();
		});
	});

	$scope.addTableBody = function (type) {
		$scope.currentType = type;
		$scope.typeData = [];
		$scope.companyData.map(function(d) {
			if(d.type == type) {
				$scope.typeData.push(d);
			}
		});
		$scope.$apply();
	}

	$scope.selectCompany = function(index) {
		var i = 0;
		console.log(index);
		$scope.companyData.map(function(data) {
			if(data.type == $scope.currentType) {
				if(i == index) {
					$scope.getData(data.isin);
					$(".form-control").val(data.company);
				}
				i ++;
			}
		});
	}

	$scope.showSecondCategories = function(category, event) {
		$scope.secondCategories = [];
		$scope.category = category;
		$scope.newsSections.map(function(d) {
			if(d.category == category) {
				$scope.secondCategories.push(d.name);
			}
		});
		$(".second-category-dropdown").show();
		$(".second-category-dropdown").css("top", $(event)[0].screenY - 80);
		$("#cover").show();
	}

	$scope.clickItem = function(name) {
		$scope.newsSections.map(function(d) {
			if(d.name == name && d.default == "N") {
				d.default = "Y";
			} else if(d.name == name && d.default == "Y") {
				d.default = "N";
			}
		});
		$scope.refreshFilters();
	}

	$scope.getClass = function(name) {
		var className = '';
		$scope.newsSections.map(function(d) {
			if(d.name == name && d.default == "N") {
				className = '';
			} else if(d.name == name && d.default == "Y") {
				className = 'selected';
			}
		});
		return className;
	}

	$scope.clickCategoryItem = function(data) {
		$scope.categories.map(function(d) {
			if(d.name == data.name && data.selected == "N") {
				d.selected = "Y";
			} else if(d.name == data.name && data.selected == "Y") {
				d.selected = "N";
			}
		});
	}

	$scope.getCategoryClass = function(data) {
		var className = '';
		var i = 0;
		$scope.categories.map(function(d) {
			i ++;
			if(d.name == data.name && data.selected == "N") {
				className = '';
			} else if(d.name == data.name && data.selected == "Y") {
				className = 'selected';
			}
		});
		return className;
	}

	$scope.refreshFilters = function() {
		var selected = "N";
		$scope.categories.map(function(c) {
			$scope.newsSections.map(function(d) {
				if(d.default == "Y" && c.name == d.category) {
					selected = "Y";
					c.selected = "Y";
				}
			});
			if(selected == "N") {
				c.selected = "N";
			}
		});		
	}

	$scope.closeSecondDropdown = function() {
		$(".second-category-dropdown").hide();
	}

	$scope.changeIconStatus = function(index) {
		for(var i = 0 ; i < $scope.showIcon.length ; i ++) {
			$scope.showIcon[i] = false;
		}
		$scope.showIcon[index] = $scope.showIcon[index] == false ? true : false;
		$scope.changeFilteredCategories(index);
	}

	$scope.changeFilteredCategories = function(index) {
		var temp = [];
		for(var i = 0 ; i < $(".selected-categories span").length ; i ++) {
			var str = $($(".selected-categories span")[i]).text();
			str = str.substring(0, str.indexOf("(") - 1);
			temp.push(str);
		}
		if($scope.categories[index].selected == "Y") {
			if(temp.indexOf($scope.categories[index].name) < 0) {
				var $el = $(".selected-categories").append("<span class='category"+ index +"'>"+$scope.categories[index].name + " (" + $scope.categories[index].count +") <i class='fa fa-close' data-ng-click='removeCategory("+ index +")'></i></span>");
				$compile($el)($scope);
			}
		} else {
			for(var i = 0 ; i < $(".selected-categories span").length ; i ++) {
				var str = $($(".selected-categories span")[i]).text();
				str = str.substring(0, str.indexOf("(") - 1);
				if(str == $scope.categories[index].name) {
					console.log(this);
					$($(".selected-categories span")[i]).remove();
					break;
				}
			}			
		}	
	}

	$scope.removeCategory = function(index) {
		$(".selected-categories span.category" + index).remove();
		$scope.categories[index].selected = "N";
	}
});
</script>
</body>