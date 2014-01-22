$(function () {

    setInterval(function(){
        $.ajax({url: '/beacons/B9407F30-F5F8-466E-AFF9-25556B57FE6D/21023/64576', success: function(data) {
            if (data.proximity == 'immediate') {
                $('#beacon .light').addClass('on');
            } else {
                $('#beacon .light').removeClass('on');
            }
        }, dataType: "json"});
    }, 500);

    var width = 300,
        height = 300,
        radius = Math.min(width, height) / 2 - 10,
        color = d3.scale.category20c();
    
    var svg = d3.select("#graph").append("svg")
        .attr("width", width)
        .attr("height", height)
      .append("g")
        .attr("transform", "translate(" + width / 2 + "," + height * .52 + ")");
    
    var partition = d3.layout.partition()
        .sort(null)
        .size([2 * Math.PI, radius * radius])
        .value(function(d) { return 1; });
    
    var arc = d3.svg.arc()
        .startAngle(function(d) { return d.x; })
        .endAngle(function(d) { return d.x + d.dx; })
        .innerRadius(function(d) { return Math.sqrt(d.y); })
        .outerRadius(function(d) { return Math.sqrt(d.y + d.dy); });
    
    d3.json("dashboard/day", function(error, root) {
      var g = svg.datum(root).selectAll("path")
          .data(partition.nodes)
        .enter().append("g")
          .attr("display", function(d) { return d.depth ? null : "none"; }); // hide inner ring
    
    var path = g.append('path').attr("d", arc)
        .style("stroke", "#fff")
        .style("fill", function(d) { return d.on == '1' ? '#F9D76C' : '#E1516B'; })
        .style("fill-rule", "evenodd")
        .each(stash)
        .on("mouseover", mouseover);
    
    
    /*g.append("text")
        .attr("transform", function(d) { return "rotate(" + (d.x + d.dx / 2 - Math.PI / 2) / Math.PI * 180 + ")"; })
        .attr("x", function(d) { return Math.sqrt(d.y); })
        .attr("dx", "20") // margin
        .attr("dy", ".35em") // vertical-align
        .text(function(d) { if (d.depth == 7 && (d.hour == '23:00' || d.hour == '00:00')) {return d.hour;} });*/
    });
    
    function mouseover(d) {
    
      var hour = d.hour;
    
      d3.select("#hour")
          .text(hour);
      }
    
    // Stash the old values for transition.
    function stash(d) {
      d.x0 = d.x;
      d.dx0 = d.dx;
    }
    
    // Interpolate the arcs in data space.
    function arcTween(a) {
      var i = d3.interpolate({x: a.x0, dx: a.dx0}, a);
      return function(t) {
        var b = i(t);
        a.x0 = b.x;
        a.dx0 = b.dx;
        return arc(b);
      };
    }
    
    d3.select(self.frameElement).style("height", height + "px");

    
});
