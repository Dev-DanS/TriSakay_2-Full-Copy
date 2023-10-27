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
    <button type="submit" class="btn btn-default custom-btn" id="saveBorderButton" onclick="redirectToShape()">
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

    <?php
    include '../db/dbconn.php';
    $query = "SELECT border FROM baliuag WHERE status = 'active'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $boundaryData = mysqli_fetch_assoc($result)['border'];
    } else {

        echo "No active boundary data found in the database.";
    }
    ?>

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


        var drawControl = new L.Control.Draw({
            draw: {
                marker: true,
                circle: false,
                rectangle: false,
                polygon: true,
                polyline: false,
                circlemarker: false
            },
            edit: {
                featureGroup: new L.FeatureGroup(),
            },
        });

        // const boundaryPolygon = L.polygon([
        //     [14.975631667703944, 120.8485794067383],
        //     [14.953409156404991, 120.87347030639648],
        //     [14.967008280154081, 120.89887619018556],
        //     [14.986742059051503, 120.87827682495119]
        // ], {
        //     color: 'red',
        //     fillOpacity: 0, 
        // }).addTo(map);

        //Simplify Baliuag Border w Turf.js 
        //Fetching for slow pc
        //Tolerance = 0.001;
        const boundaryData = <?php echo $boundaryData; ?>;

        const boundaryCoordinates = boundaryData.latlngs[0].map(coord => [coord.lat, coord.lng]);
        const boundaryPolygon = L.polygon(boundaryCoordinates, {
            color: 'red',
            fillOpacity: 0,
        }).addTo(map);

        function isShapeInsideExistingShape(newLayer) {
            let overlaps = false;
            polygonsLayer.eachLayer(function (layer) {
                if (overlaps) return;
                if (newLayer instanceof L.Polygon && layer instanceof L.Polygon) {
                    const newLatLngs = newLayer.getLatLngs()[0];
                    for (let i = 0; i < newLatLngs.length; i++) {
                        if (isPointInPolygon(newLatLngs[i], layer)) {
                            overlaps = true;
                            return;
                        }
                    }
                }

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
                    return;
                }
            });
            return overlaps;
        }


        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, function (event) {
            var layer = event.layer;

            if (layer instanceof L.Polygon) {
                if (!isPolygonWithinBoundary(layer, boundaryPolygon)) {
                    alert("Polygon must be inside the specified boundary.");
                    map.removeLayer(layer);
                    return;
                }

                if (doesPolygonOverlap(layer)) {
                    alert("A route border cannot be created on top of an existing route border");
                    map.removeLayer(layer);
                    return;
                }
            }

            if (layer instanceof L.Marker) {
                let isInPolygon = false;
                polygonsLayer.eachLayer(function (polygon) {
                    if (isPointInPolygon(layer.getLatLng(), polygon)) {
                        isInPolygon = true;
                        return;
                    }
                });
                if (!isInPolygon) {
                    alert("You can only place a marker inside the Route Borders.");
                    return;
                }
            } else if (layer instanceof L.Polygon) {

            }
            coordinatesArray = [];
            map.addLayer(layer);
            updateCoordinates(); // Update the display

            if (layer instanceof L.Marker || layer instanceof L.CircleMarker) {

                var latlng = layer.getLatLng();
                var coordinates = { type: "marker", latlng: latlng };
                coordinatesArray.push(coordinates);
            } else if (layer instanceof L.Circle) {

                var latlng = layer.getLatLng();
                var radius = layer.getRadius();
                var coordinates = { type: "circle", latlng: latlng, radius: radius };
                coordinatesArray.push(coordinates);
            } else if (layer instanceof L.Rectangle) {

                var bounds = layer.getBounds();
                var coordinates = { type: "rectangle", bounds: bounds };
                coordinatesArray.push(coordinates);
            } else if (layer instanceof L.Polygon) {

                var latlngs = layer.getLatLngs();
                var coordinates = { type: "polygon", latlngs: latlngs };
                coordinatesArray.push(coordinates);
            }

            updateCoordinates();
        });

        function updateCoordinates() {
            var coordinatesDiv = document.getElementById('coordinates');
            coordinatesDiv.innerHTML = '<h2>Coordinates:</h2>';
            if (coordinatesArray.length > 0) {
                var latestCoordinates = coordinatesArray[coordinatesArray.length - 1];
                var coordinatesInfo = JSON.stringify(latestCoordinates);
                coordinatesDiv.innerHTML += '<p>' + coordinatesInfo + '</p>';
            }
        }


        map.on('editable:editing', function (event) {
            var layers = event.layers;
            layers.eachLayer(function (layer) {
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

        document.getElementById('saveBorderButton').addEventListener('click', function () {
            saveCoordinatesToDatabase();
        });

        function saveCoordinatesToDatabase() {
            const coordinatesDiv = document.getElementById('coordinates');
            const coordinates = coordinatesDiv.querySelector('p').textContent;

            const coordinatesData = { coordinates: coordinates };

            fetch('save_coordinates.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(coordinatesData),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Coordinates saved successfully!');
                    } else {
                        alert('Error saving coordinates.');
                    }
                })
                .catch(error => {
                    console.error('Error saving coordinates:', error);
                });
        }

        function isPolygonWithinBoundary(polygon, boundary) {
            // Check if any point of the polygon is outside the boundary
            const polygonLatLngs = polygon.getLatLngs()[0];
            for (const point of polygonLatLngs) {
                if (!boundary.getBounds().contains(point)) {
                    return false;
                }
            }
            return true;
        }


        displayPolygons();

    </script>
</body>

</html>