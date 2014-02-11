$(function () {

    d3.json('dashboard/logs/', function(error, response) {
        
        var format = d3.time.format("%Y-%m-%d");
        
        var stack = d3.layout.stack()
            .offset("silhouette")
            .values(function(d) { return d.values; })
            .x(function(d) { return d.date; })
            .y(function(d) { return d.hours; });
        
        var nest = d3.nest()
            .key(function(d) { return d.light; });
        
        var data = response.data;
        data.forEach(function(d) {
            d.date = format.parse(d.date);
        });
        
        var layers = stack(nest.entries(data));
        
        var element = d3.select('#graph');
        
        var width = parseInt(element.style('width')),
            height = parseInt(element.style('height')),
            padding = 10;
        
        var x = d3.time.scale()
            .domain(d3.extent(data, function(d) { return d.date; }))
            .range([0, width]);
        
        var xAxis = d3.svg.axis()
            .scale(x)
            .orient("bottom")
            .ticks(d3.time.mondays)
            .tickSize(16, 0)
            .tickFormat(d3.time.format("%e. %b"));
        
        var y = d3.scale.linear()
            .domain([0, d3.max(data, function(d) { return d.y0 + d.hours; })])
            .range([height - 3 * padding, padding]);
        
        var color = d3.scale.linear()
            .domain([0, layers.length - 1])
            .range(["#F9D76C", "#f6c323"]);
        
        var area = d3.svg.area()
            .interpolate("cardinal")
            .x(function(d) { return x(d.date); })
            .y0(function(d) { return y(d.y0); })
            .y1(function(d) { return y(d.y0 + d.hours); });
        
        var svg = d3.select("#graph").append("svg")
            .attr("width", width)
            .attr("height", height);
        
        svg.selectAll("path")
            .data(layers)
          .enter().append("path")
            .attr("d", function (d) { return area(d.values) })
            .style("fill", function(d, i) { return color(d.key); });   
            
        svg.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + (height - 2 * padding) + ")")
            .call(xAxis)
            .selectAll(".tick text")
            .attr("x", 6)
            .attr("y", 6)
            .style("text-anchor", "start");
    });
});
