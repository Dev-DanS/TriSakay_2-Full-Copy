<!DOCTYPE html>
<html>

<head>
    <title>Leaflet Polygons</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>

<body>

    <h1 id="todaHeader">Toda: </h1>
    <div id="map" style="height: 600px;"></div>

    <script>
  var map = L.map('map').setView([14.9529, 120.8995], 15);
  var polygons = [];
  var pickupPoint = null;
  var dropoffPoint = null;
  var confirmedToda = null;

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
  }).addTo(map);

  function isInsidePolygon(point, polygon) {
    var vs = polygon.getLatLngs()[0];
    var x = point.lat, y = point.lng;
    var inside = false;

    for (var i = 0, j = vs.length - 1; i < vs.length; j = i++) {
      var xi = vs[i].lat, yi = vs[i].lng;
      var xj = vs[j].lat, yj = vs[j].lng;
      var intersect = ((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
      if (intersect) inside = !inside;
    }
  
    return inside;
  }

  <?php
  include("../db/dbconn.php");
  $sql = "SELECT toda, borders FROM route";
  $result = $conn->query($sql);

  while ($row = $result->fetch_assoc()) {
      $toda = $row["toda"];
      $borders = json_decode($row["borders"], true);
      $latlngs = json_encode($borders['latlngs'][0]);
      ?>
        var latlngs = <?php echo $latlngs; ?>;
        var polygon = L.polygon(latlngs).addTo(map);
        polygon.toda = "<?php echo $toda; ?>";
        polygons.push(polygon);
        polygon.on('click', function() {
          document.getElementById("todaHeader").innerHTML = "Toda: " + this.toda;
        });
      <?php
  }
  $conn->close();
  ?>

  map.on('dblclick', function(e) {
    if (pickupPoint === null) {
      for (var i = 0; i < polygons.length; i++) {
        if (isInsidePolygon(e.latlng, polygons[i])) {
          pickupPoint = L.marker(e.latlng).addTo(map);
          pickupPoint.bindPopup('<button onclick="confirmPickup()">Confirm Pickup Point</button>').openPopup();
          confirmedToda = polygons[i].toda;
          return;
        }
      }
    } else if (pickupPoint !== null && dropoffPoint === null && confirmedToda !== null) {
      for (var i = 0; i < polygons.length; i++) {
        if (polygons[i].toda === confirmedToda && isInsidePolygon(e.latlng, polygons[i])) {
          dropoffPoint = L.marker(e.latlng).addTo(map);
          dropoffPoint.bindPopup('<button onclick="confirmDropoff()">Confirm Dropoff Point</button>').openPopup();
          return;
        }
      }
    }
  });

  function confirmPickup() {
    if (pickupPoint) {
      pickupPoint.unbindPopup();
      pickupPoint = null; // Lock the pickup point
    }
  }

  function confirmDropoff() {
    if (dropoffPoint) {
      dropoffPoint.unbindPopup();
      dropoffPoint = null; // Lock the dropoff point
    }
  }
    </script>

</body>

</html>