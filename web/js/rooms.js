$(function () {

    (function () {

        var width = 11,
            height = 7,
            size = 50;

        //container
        var svg = d3.select('#measurements-floor-1')
            .append("svg:svg")
            .attr("width", width * size + "px")
            .attr("height", height * size + "px");

        d3.json('../measurements', function(error, response) {

            var data = response.data;

            var colorScale = d3.scale.quantile() // scale.quantize breaks if min == max
                .domain([d3.min(data, function(d) { return d.rssi; }), d3.max(data, function(d) { return d.rssi; })])
                .range(d3.range(9));

            var measurement = svg.selectAll('.measurement')
                .data(data)
                .enter()
                .append('rect')
                .attr('x', function (d) { return size * (d.x) + 'px'; })
                .attr('y', function (d) { return size * (d.y - 1) + 'px'; })
                .attr('width', size)
                .attr('height', size)
                .attr('class', function(d) { return "q" + colorScale(d.rssi) + "-9"; });
        });

    })();
});

