<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriSakay | Commuter</title>
    <?php
    include '../dependencies/dependencies.php';
    ?>
    <link rel="stylesheet" href="../css/booking.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6.5.0/turf.min.js"></script>

    <style>
        body {
            color: white;
        }
    </style>
</head>

<body>
    <div id="map" style="width: 100%; height: 50vh;"></div>
    <div id="coordinates"></div>
    <button type="submit" class="btn btn-default custom-btn">
        Save Border
    </button>
    <button type="submit" class="btn btn-default custom-btn" onclick="redirectToDrawing()">
        Save Marker
    </button>
    <script src="../js/button.js"></script>
    <?php
    include '../db/dbconn.php';

    $query = "SELECT toda, borders FROM route WHERE status = 'active'";
    $result = mysqli_query($conn, $query);
    $rows = array();

    while ($r = mysqli_fetch_assoc($result)) {
        $rows[] = array(
            'toda' => $r['toda'],
            'borders' => json_decode($r['borders'], true)
        );
    }

    $jsonData = json_encode($rows);
    ?>

    <php ?>
        <script>
            var map = L.map('map', {
                zoomControl: false,
                doubleClickZoom: false
            }).setView([14.954283534502583, 120.90080909502916], 15);
            var coordinatesArray = [];

            let dropoffPoint;
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);


            // Initialize the draw control and add it to the map
            var drawControl = new L.Control.Draw({
                draw: {
                    marker: true,
                    circle: false,      // Set this to false
                    rectangle: false,
                    polygon: true,
                    polyline: false,    // Set this to false (for lines)
                    circlemarker: false // Set this to false (for circle markers)
                },
                edit: {
                    featureGroup: new L.FeatureGroup(),
                },
            });




            function isShapeInsideExistingShape(newLayer) {
                let overlaps = false;
                polygonsLayer.eachLayer(function (layer) {
                    if (overlaps) return; // Exit if an overlap was already detected

                    // Check for polygon vs. polygon overlap
                    if (newLayer instanceof L.Polygon && layer instanceof L.Polygon) {
                        const newLatLngs = newLayer.getLatLngs()[0];
                        for (let i = 0; i < newLatLngs.length; i++) {
                            if (isPointInPolygon(newLatLngs[i], layer)) {
                                overlaps = true;
                                return;
                            }
                        }
                    }

                    // Extend with other shape types (e.g., rectangle vs. polygon) as necessary
                });
                return overlaps;
            }

            function doesPolygonOverlap(newPolygon) {
                let overlaps = false;
                const newPolygonGeoJSON = newPolygon.toGeoJSON();
                polygonsLayer.eachLayer(function (existingPolygon) {
                    const existingPolygonGeoJSON = existingPolygon.toGeoJSON();
                    if (turf.intersect(newPolygonGeoJSON, existingPolygonGeoJSON)) {
                        overlaps = true;
                        return;  // Exit the loop early if an overlap is detected
                    }
                });
                return overlaps;
            }


            map.addControl(drawControl);

            // Handle drawing events
            map.on(L.Draw.Event.CREATED, function (event) {
                var layer = event.layer;

                // Check if the layer is a polygon
                if (layer instanceof L.Polygon) {
                    // Check if the new polygon overlaps with any existing polygons
                    if (doesPolygonOverlap(layer)) {
                        alert("A border cannot be created on top of an existing route border");
                        return;  // Exit without adding the polygon to the map
                    }
                }

                if (layer instanceof L.Marker) {
                    // Check if the marker is inside a polygon
                    let isInPolygon = false;
                    polygonsLayer.eachLayer(function (polygon) {
                        if (isPointInPolygon(layer.getLatLng(), polygon)) {
                            isInPolygon = true;
                            return;
                        }
                    });
                    if (!isInPolygon) {
                        alert("You can only place a marker inside the Route Borders.");
                        return;  // Exit without adding the marker to the map
                    }
                } else if (layer instanceof L.Polygon) {
                    // Existing code to handle polygons
                }
                map.addLayer(layer);

                if (layer instanceof L.Marker || layer instanceof L.CircleMarker) {
                    // Handle markers
                    var latlng = layer.getLatLng();
                    var coordinates = { type: "marker", latlng: latlng };
                    coordinatesArray.push(coordinates);
                } else if (layer instanceof L.Circle) {
                    // Handle circles
                    var latlng = layer.getLatLng();
                    var radius = layer.getRadius();
                    var coordinates = { type: "circle", latlng: latlng, radius: radius };
                    coordinatesArray.push(coordinates);
                } else if (layer instanceof L.Rectangle) {
                    // Handle rectangles
                    var bounds = layer.getBounds();
                    var coordinates = { type: "rectangle", bounds: bounds };
                    coordinatesArray.push(coordinates);
                } else if (layer instanceof L.Polygon) {
                    // Handle polygons
                    var latlngs = layer.getLatLngs();
                    var coordinates = { type: "polygon", latlngs: latlngs };
                    coordinatesArray.push(coordinates);
                }

                updateCoordinates();
            });

            // Function to update the displayed coordinates
            function updateCoordinates() {
                var coordinatesDiv = document.getElementById('coordinates');
                coordinatesDiv.innerHTML = '<h2>Coordinates:</h2>';
                coordinatesArray.forEach(function (coordinates, index) {
                    var coordinatesInfo = JSON.stringify(coordinates);
                    coordinatesDiv.innerHTML += '<p>' + coordinatesInfo + '</p>';
                });
            }

            // Handle edit events
            map.on('editable:editing', function (event) {
                var layers = event.layers;
                layers.eachLayer(function (layer) {
                    // Handle edited layers here
                    // You can update the coordinatesArray when a shape is edited
                });
            });

            const dbPolygons = <?php echo $jsonData; ?>;
            let polygonsLayer = L.layerGroup().addTo(map);

            function displayPolygons() {
                dbPolygons.forEach((polygonData, index) => {
                    const latlngs = polygonData.borders.latlngs[0].map(coord => [coord.lat, coord.lng]);
                    const polygon = L.polygon(latlngs, {
                        color: 'transparent',
                        fillColor: 'green',
                        fillOpacity: 0.3,
                        weight: 0
                    }).addTo(polygonsLayer);
                    polygon.toda = polygonData.toda;
                });
            }

            function isPointInPolygon(point, polygon) {
                let polyPoints = polygon.getLatLngs()[0];
                let x = point.lat, y = point.lng;

                let inside = false;
                for (let i = 0, j = polyPoints.length - 1; i < polyPoints.length; j = i++) {
                    let xi = polyPoints[i].lat, yi = polyPoints[i].lng;
                    let xj = polyPoints[j].lat, yj = polyPoints[j].lng;

                    let intersect = ((yi > y) !== (yj > y))
                        && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
                    if (intersect) inside = !inside;
                }
                return inside;
            }

            document.querySelector(".save-border-button").addEventListener("click", function () {
                // Send the coordinatesArray to the PHP script for saving.
                axios.post('saveborder.php', {
                    coordinatesArray: JSON.stringify(coordinatesArray)
                })
                    .then(function (response) {
                        // Handle the response from the server.
                        console.log(response.data);
                    })
                    .catch(function (error) {
                        // Handle errors.
                        console.error(error);
                    });
            });


            displayPolygons();

        </script>
</body>

</html>