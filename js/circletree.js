function CircleTree(root) {
  this.diameter = 960;
  this.root = root;
}

CircleTree.prototype.initialize = function() {
  this.tree = d3.layout.tree()
      .size([360, this.diameter / 2 - 120])
      .separation(function(a, b) { return (a.parent == b.parent ? 1 : 2) / a.depth; });

  this.diagonal = d3.svg.diagonal.radial()
      .projection(function(d) { return [d.y, d.x / 180 * Math.PI]; });

  this.svg = d3.select("body").append("svg")
      .attr("width", this.diameter)
      .attr("height", this.diameter - 150)
    .append("g")
      .attr("transform", "translate(" + this.diameter / 2 + "," + this.diameter / 2 + ")");
}

CircleTree.prototype.update = function() {
  var nodes = this.tree.nodes(this.root),
      links = this.tree.links(nodes);

  var link = this.svg.selectAll(".link")
      .data(links)
    .enter().append("path")
      .attr("class", "link")
      .attr("d", this.diagonal);

  var node = this.svg.selectAll(".circle-node")
      .data(nodes)
    .enter().append("g")
      .attr("class", "circle-node")
      .attr("transform", function(d) { return "rotate(" + (d.x - 90) + ")translate(" + d.y + ")"; })

  node.append("circle")
      .attr("r", 4.5);

  node.append("text")
      .attr("dy", ".31em")
      .attr("text-anchor", function(d) { return d.x < 180 ? "start" : "end"; })
      .attr("transform", function(d) { return d.x < 180 ? "translate(8)" : "rotate(180)translate(-8)"; })
      .text(function(d) { return d.name; });

  d3.select(self.frameElement).style("height", this.diameter - 150 + "px");
}
