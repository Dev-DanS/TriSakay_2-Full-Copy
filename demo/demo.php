<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Custom Div on Leaflet Map</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
  <style>
    #map {
      height: 400px;
    }
    .custom-div {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: #FF5733;
      color: #FFF;
      padding: 10px;
      border: 1px solid #000;
    }
  </style>
</head>
<body>
  <div id="map"></div>
  <div class="custom-div">This is a custom div</div>

  <script>
    // Create a Leaflet map
    var map = L.map('map').setView([51.505, -0.09], 13);

    // Add a tile layer to the map (you can use any tile provider)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
  </script>
</body>
</html>
