$(function () {
    
    var width = 290,
        height = 290,
        padding = 18,
        radius = Math.min(width, height) / 2 - padding;
    
    function graph(selector, url) {
        
        //container
        var svg = d3.select(selector)
            .append("svg:svg")
            .attr("width", width)
            .attr("height", height)
            .append("g")
            .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
        
        var pi = Math.PI;
        
        var chartContainer = svg.append("g")
            .attr('class', 'some_class');
        
        d3.json(url, function(error, response) {
                        
            var data = response.data,
                keys = d3.keys(data),
                max = d3.max(data, function(d) { return d.lights; }),
                size = d3.scale.linear()
                    .domain([0, max])
                    .range([0, radius]),
                radian = d3.scale.linear()
                    .domain([0, data.length])
                    .range([0, 2 * Math.PI]),
                degree = d3.scale.linear()
                    .domain([0, data.length])
                    .range([0, 360]);
            
            var arc = d3.svg.arc()
                .innerRadius(0)
                .outerRadius(function(d) { return size(d.lights); })
                .startAngle(function(d,i) { return radian(i); })
                .endAngle(function(d,i) { return radian(i + 1) + 0.01; });
            
            chartContainer.selectAll("path")
                .data(data)
                .enter()
                .append("path")
                .attr("d", arc)
                .attr("fill", "#F9D76C")
                .attr("class", "arc");
            
            // add line axes
            var lineAxes;
            
            lineAxes = svg.selectAll('.line-ticks')
                .data(data)
                .enter().append('svg:text')
                .attr('dy', -(padding/2))
                .attr("transform", function (d, i) {
                    return "translate(0," + (-radius) + ") rotate(" + (degree(i) + 7) +
                        ",0," + radius + ")";
                })
                .attr("class", "line-ticks")
                .style("fill", "#777")
                .style("font-size","11px")
                .text(function(d,i){ return d.hour; })
                .attr("text-anchor", "middle");
                    
            // add circle axes
            var circleAxes, i;
            
            svg.selectAll('.circle-ticks').remove();
            
            circleAxes = svg.selectAll('.circle-ticks')
                .data(d3.range(1, max + 1))
                .enter().append('svg:g')
                .attr("class", "circle-ticks");
            
            circleAxes.append("svg:circle")
                .attr("r", function (d) { return size(d); })
                .attr("class", "circle")
                .style("stroke", "#CCC")
                .style("opacity", 0.5)
                .style("fill", "none");
            
            circleAxes.append("svg:text")
                .attr("text-anchor", "center")
                .attr('dx', -3)
                .attr("dy", function(d) { return - size(d) - 2 })
                .style("fill", "#777")
                .style("font-size","11px")
                .text(function(d,i) {
                    if (d < max) {
                        if (d == 1) {
                           return d + ' Light';
                        } else {
                            return d;
                        }
                    }
                });
        });
    }
    
    graph('#day-1', 'day/today');
    graph('#day-2', 'day/yesterday');
    graph('#day-3', 'day/-2days');
});

