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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>



</head>

<body>
    <div id="map" style="width: 100%; height: 50vh;"></div>

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
    // 1. Fetch the todalocation data from the database
    $todalocationQuery = "SELECT toda, terminal FROM todalocation";
    $todalocationResult = mysqli_query($conn, $todalocationQuery);
    $todalocations = array();

    while ($tl = mysqli_fetch_assoc($todalocationResult)) {
        $todalocations[] = array(
            'toda' => $tl['toda'],
            'terminal' => json_decode($tl['terminal'], true)
        );
    }

    $todalocationData = json_encode($todalocations);
    ?>

    <script>
        var map = L.map('map', {
            zoomControl: false,
            doubleClickZoom: false
        }).setView([14.954283534502583, 120.90080909502916], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const dbPolygons = <?php echo $jsonData; ?>;
        const todalocations = <?php echo $todalocationData; ?>;
        let polygonsLayer = L.layerGroup().addTo(map);
        let markersLayer = L.layerGroup().addTo(map);

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

                // 2. Parse and match the toda value
                const matchingToda = todalocations.find(tl => tl.toda === polygonData.toda);

                if (matchingToda) {
                    // 3. Create a marker on its corresponding terminal coordinates
                    const marker = L.marker([matchingToda.terminal.latlng.lat, matchingToda.terminal.latlng.lng]).addTo(markersLayer);

                    // 4. Attach a popup to the marker displaying the toda name
                    marker.bindPopup(`${polygonData.toda} Terminal`)
                }
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

        var greenMarkerIcon = L.icon({
            iconUrl:
                "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
        });

        var pickuppoint;

        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var userLat = position.coords.latitude;
                var userLng = position.coords.longitude;
                pickuppoint = [userLat, userLng];
                var userMarker = L.marker([userLat, userLng], { icon: greenMarkerIcon }).addTo(map);
                userMarker.bindPopup('You are here').openPopup();
            });
        }

        var redMarkerIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        let dropoffPoint;
        

        // Inside the addDropoffPoint function
        function addDropoffPoint(e) {
            let isInPolygon = false;
            let selectedToda = null;
            polygonsLayer.eachLayer(function (layer) {
                if (isPointInPolygon(e.latlng, layer)) {
                    isInPolygon = true;
                    selectedToda = layer.toda;
                    return false;
                }
            });

            if (isInPolygon) {
                // If a drop-off marker already exists, remove it before adding a new one
                if (dropoffPoint) {
                    map.removeLayer(dropoffPoint);
                }
                // Adding a drop-off marker using the red icon
                dropoffPoint = L.marker(e.latlng, { icon: redMarkerIcon }).addTo(map);

                // Calculate distance to the terminal of the selected TODA
                if (pickuppoint && selectedToda) {
                    const pickupLat = pickuppoint[0];
                    const pickupLng = pickuppoint[1];

                    // Find the corresponding TODA terminal location
                    const matchingToda = todalocations.find(tl => tl.toda === selectedToda);

                    if (matchingToda) {
                        const terminalLat = matchingToda.terminal.latlng.lat;
                        const terminalLng = matchingToda.terminal.latlng.lng;

                        // Make a request to OSRM to calculate the route to the terminal
                        axios.get(`https://router.project-osrm.org/route/v1/driving/${pickupLng},${pickupLat};${terminalLng},${terminalLat}?overview=false`)
                            .then(responseToTerminal => {
                                const distanceToTerminal = (responseToTerminal.data.routes[0].distance / 1000).toFixed(2); // Distance in kilometers

                                // Calculate distance to the drop-off point
                                const dropoffLat = e.latlng.lat;
                                const dropoffLng = e.latlng.lng;
                                // Make a request to OSRM to calculate the route to the drop-off point
                                axios.get(`https://router.project-osrm.org/route/v1/driving/${pickupLng},${pickupLat};${dropoffLng},${dropoffLat}?overview=false`)
                                    .then(responseToDropoff => {
                                        const distanceToDropoff = (responseToDropoff.data.routes[0].distance / 1000).toFixed(2); // Distance in kilometers
                                        const etaToDropoff = (responseToDropoff.data.routes[0].duration / 60).toFixed(0); // ETA in minutes

                                        // Create a customized popup
                                        const popupContent = `
                                    <strong>Dropoff Point</strong><br>
                                    Distance in km: ${distanceToDropoff} km<br>
                                    Distance in km (Terminal): ${distanceToTerminal} km<br>
                                    ETA: ${etaToDropoff} mins
                                `;

                                        // Display the customized popup
                                        dropoffPoint.bindPopup(popupContent).openPopup();
                                    })
                                    .catch(error => {
                                        console.error("Error calculating drop-off distance:", error);
                                        dropoffPoint.bindPopup(`Error calculating drop-off distance`).openPopup();
                                    });
                            })
                            .catch(error => {
                                console.error("Error calculating distance to terminal:", error);
                                dropoffPoint.bindPopup(`Error calculating distance to terminal`).openPopup();
                            });
                    } else {
                        dropoffPoint.bindPopup(`Terminal not found`).openPopup();
                    }
                } else {
                    dropoffPoint.bindPopup(`Error calculating distances`).openPopup();
                }
            } else {
                alert("Please click inside an available drop-off location.");
            }
        }




        displayPolygons();
        map.on('dblclick', addDropoffPoint);
    </script>
</body>

</html>