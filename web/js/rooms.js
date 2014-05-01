$(function () {

    (function () {

        var width = 22,
            height = 10,
            size = 50;

        //container
        var svg = d3.select('#measurements-floors')
            .append('svg:svg')
            .attr('width', width * size + 'px')
            .attr('height', height * size + 'px');

        function render () {

            var beacon = $('#select-beacon option:selected').val(),
                power = $('#select-power option:selected').val();

            d3.json('../measurements?beacon=' + beacon + '&power=' + power, function(error, response) {

                // clear
                svg.selectAll('rect, g, line, text').remove();

                var data = response.data;

                var colorScale = d3.scale.quantile() // scale.quantize breaks if min == max
                    .domain([-97, -70])
                    .range(d3.range(9));

                var measurement = svg.selectAll('.measurement')
                    .data(data)
                    .enter()
                    .append('rect')
                    .attr('x', function (d) { return size * (d.x) + 'px'; })
                    .attr('y', function (d) { return size * (d.y - 1) + 'px'; })
                    .attr('width', size)
                    .attr('height', size)
                    .attr('class', function(d) { return 'q' + colorScale(d.rssi) + '-9'; });

                svg.append('text')
                    .text('Floor 1')
                    .style('fill', '#666')
                    .attr('x', 1 * size)
                    .attr('y', 6 * size + 20);

                svg.append('text')
                    .text('Floor 2')
                    .style('fill', '#666')
                    .attr('x', 12 * size)
                    .attr('y', 6 * size + 20);

                var legend = svg.selectAll('.legend')
                    .data([colorScale.domain()[0]].concat(colorScale.quantiles()), function(d) { return d; })
                    .enter().append('g')
                    .attr('class', 'legend');

                legend.append('rect')
                    .attr('x', function(d, i) { return (i + 1) * size; })
                    .attr('y', (height - 2) * size)
                    .attr('width', size)
                    .attr('height', 20)
                    .attr('class', function(d, i) { return 'q' + i + '-9'; });

                legend.append('line')
                    .attr('x1', function(d, i) { return (i + 1) * size; })
                    .attr('y1', (height - 2) * size)
                    .attr('x2', function(d, i) { return (i + 1) * size; })
                    .attr('y2', (height - 1) * size)
                    .style('fill', 'none')
                    .style('stroke', '#ccc')
                    .style('shape-rendering', 'crispEdges');

                legend.append('text')
                    .text(function(d) { return '≥ ' + Math.round(d * 10) / 10; })
                    .style('fill', '#666')
                    .attr('x', function(d, i) { return (i + 1) * size + 7; })
                    .attr('y', (height - 1) * size);

                svg.append('text')
                    .text('Color scale')
                    .style('fill', '#666')
                    .attr('x', 1 * size)
                    .attr('y', (height - 2) * size - 10);

                svg.append('text')
                    .text('dBm')
                    .style('fill', '#666')
                    .attr('x', 10 * size)
                    .attr('y', (height - 1) * size);
            });

        }

        render();

        $('#select-beacon, #select-power').change(function () {
            render();
        });

    })();
});

