<?php
include('../php/session_commuter.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriSakay | Commuter</title>
    <?php
    include '../dependencies/dependencies.php';
    $imagePath = "../img/Logo_Nobg.png";
    ?>
    <style>
        .dropdown-center .btn-secondary {
            background-color: #3c8c74;
            border-color: #3c8c74;
        }

        .dropdown-center .btn-secondary:focus,
        .dropdown-center .btn-secondary:active {
            background-color: #3c8c74;
            border-color: #3c8c74;
        }

        .dropdown-center {
            display: flex;
            justify-content: center;
        }
    </style>

    <link rel="icon" href="<?php echo $imagePath; ?>" type="image/png" />
    <link rel="stylesheet" href="../css/booking.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/@mapbox/leaflet-pip@latest/leaflet-pip.js"></script>

</head>

<body>
    <?php
    include('../php/navbar_commuter.php');
    ?>
    <div class="search">
        <input id="search-input" type="text" placeholder="Where are you heading to?">
        <button id="search-button"><i class="fa-solid fa-magnifying-glass-location fa-lg" style="color: #ffffff;"></i>
            Search</button>
    </div>
    <div id="map" style="width: 100%; height: 50vh;"></div>
    <div class="locations">
        <p id="pickup-address">Locating your current address...</p>
        <p id="dropoff-address">To add a drop-off point, double-click inside one of the available drop-off locations.
        </p>
    </div>
    <div class="dropdown-center">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Number of passenger(s): <span id="passenger-display">1</span>
        </button>
        <ul class="dropdown-menu" id="passenger-dropdown">
            <li><a class="dropdown-item" href="#" data-value="1">1</a></li>
            <li><a class="dropdown-item" href="#" data-value="2">2</a></li>
            <li><a class="dropdown-item" href="#" data-value="3">3</a></li>
            <li><a class="dropdown-item" href="#" data-value="4">4</a></li>
        </ul>
    </div>
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
    $query = "SELECT BaseFare, PerKM, NightDiff, FarePerPassenger FROM farematrix WHERE status = 'active'";
    $result = mysqli_query($conn, $query);
    $fareData = mysqli_fetch_assoc($result);

    ?>
    <script src="search.js"></script>
    <!-- <script src="pickup.js"></script> -->
    <script>
        var map = L.map('map', {
            zoomControl: false,
            doubleClickZoom: false
        }).setView([14.954283534502583, 120.90080909502916], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var passengerCount = 1; // default value

        document.getElementById("passenger-dropdown").addEventListener("click", function (e) {
            if (e.target && e.target.nodeName == "A") {
                passengerCount = e.target.getAttribute("data-value");
                document.getElementById("passenger-display").innerText = passengerCount;
            }
        });

        var greenMarkerIcon = L.icon({
            iconUrl:
                "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
        });

        var pickupPoint;
        if (pickupPoint) {
            var currentLatLng = pickupPoint.getLatLng();
            console.log("Latitude: ", currentLatLng.lat);
            console.log("Longitude: ", currentLatLng.lng);
        }


        function handleLocationError(error) {
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    alert("User denied the request for Geolocation. " + error.message);
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Location information is unavailable. " + error.message);
                    break;
                case error.TIMEOUT:
                    alert("The request to get user location timed out. " + error.message);
                    break;
                case error.UNKNOWN_ERROR:
                    alert("An unknown error occurred. " + error.message);
                    break;
            }
        }

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    var latlng = new L.LatLng(
                        position.coords.latitude,
                        position.coords.longitude
                    );

                    if (!pickupPoint) {
                        pickupPoint = L.marker(latlng, { icon: greenMarkerIcon })
                            .addTo(map)
                            .bindPopup("You are here")
                            .openPopup();
                        map.setView(latlng, 15);
                    } else {
                        pickupPoint.setLatLng(latlng);
                    }

                    document.getElementById("pickup-address").textContent =
                        "Locating your address...";

                    axios
                        .get(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`
                        )
                        .then((response) => {
                            var address = response.data.display_name;
                            var addressWords = address.split(",");
                            addressWords.splice(-4);
                            addressWords = addressWords.filter(
                                (word) => word.trim() !== "Doña Enriquieta Subdivision"
                            );
                            var shortenedAddress = addressWords.join(",");
                            document.getElementById("pickup-address").textContent =
                                "Pickup to: " + shortenedAddress;
                        })
                        .catch((error) => {
                            console.error(error);
                            document.getElementById("pickup-address").textContent =
                                "Unable to locate your address.";
                        });
                },
                handleLocationError,
                { enableHighAccuracy: true }
            );
        } else {
            alert("Geolocation is not supported by your browser.");
        }

        const dbPolygons = <?php echo $jsonData; ?>;
        let polygonsLayer = L.layerGroup().addTo(map);
        let dropoffPoint; // Global variable to hold the single drop-off marker

        var redMarkerIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

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

        const fareMatrix = <?= json_encode($fareData); ?>;
        let routeLayer = L.layerGroup().addTo(map); // Add this after polygonsLayer
        function calculateFare(distanceKm, passengerCount) {
            let fare;
            const currentHour = new Date().getHours();

            if (currentHour >= 22 || currentHour <= 4) {
                fare = (distanceKm - 2) * (parseFloat(fareMatrix.PerKM) + parseFloat(fareMatrix.NightDiff));
            } else {
                fare = (distanceKm - 2) * parseFloat(fareMatrix.PerKM);
            }

            fare = distanceKm <= 2 ?
                parseFloat(fareMatrix.BaseFare) :
                parseFloat(fareMatrix.BaseFare) + fare;

            // Add the fare for each passenger beyond the first one
            if (passengerCount > 1) {
                fare += (passengerCount - 1) * parseFloat(fareMatrix.FarePerPassenger);
            }

            return fare.toFixed(2);
        }



        function getShortestPath(pickup, dropoff) {
            const pickupCoord = `${pickup.lng},${pickup.lat}`;
            const dropoffCoord = `${dropoff.lng},${dropoff.lat}`;
            const url = `https://router.project-osrm.org/route/v1/driving/${pickupCoord};${dropoffCoord}?overview=full&geometries=geojson`;

            axios.get(url)
                .then(response => {
                    const route = response.data.routes[0].geometry.coordinates;
                    const distanceMeters = response.data.routes[0].distance;
                    const geojsonRoute = {
                        type: "Feature",
                        properties: {},
                        geometry: {
                            type: "LineString",
                            coordinates: route
                        }
                    };

                    routeLayer.clearLayers(); // Clear the previous route
                    L.geoJSON(geojsonRoute).addTo(routeLayer); // Add the new route to the routeLayer

                    // Calculate distance in kilometers and ETA
                    const distanceKm = (distanceMeters / 1000).toFixed(2);  // Convert meters to km
                    const etaHours = distanceMeters / (10 * 1000);  // Time = Distance / Speed. The result is in hours since speed is in km/h.
                    const etaMinutes = Math.round(etaHours * 60);  // Convert hours to minutes

                    const fare = calculateFare(distanceKm, passengerCount);

                    // Update the drop-off popup with the distance, ETA and fare
                    if (dropoffPoint) {
                        dropoffPoint
                            .bindPopup(`Drop-off Point<br>Distance: ${distanceKm} km<br>ETA: ${etaMinutes} minutes<br>Fare: ₱${fare}`)
                            .openPopup();
                    }
                })
                .catch(error => {
                    console.log(error);
                });
        }

        function addDropoffPoint(e) {
            let isInPolygon = false;
            polygonsLayer.eachLayer(function (layer) {
                if (isPointInPolygon(e.latlng, layer)) {
                    isInPolygon = true;
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
                dropoffPoint.bindPopup("Drop-off Point").openPopup();

                // Fetching address for drop-off point
                fetchAddressForLatLng(e.latlng.lat, e.latlng.lng);
                getShortestPath(pickupPoint.getLatLng(), e.latlng);
            } else {
                alert("Please click inside an available drop-off location.");
            }
        }

        function fetchAddressForLatLng(lat, lng) {
            axios
                .get(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then((response) => {
                    var address = response.data.display_name;
                    var addressWords = address.split(",");
                    addressWords.splice(-4); // Retaining this line to remove last 4 words
                    addressWords = addressWords.filter(word => word.trim() !== 'Doña Enriquieta Subdivision');
                    var shortenedAddress = addressWords.join(",");
                    document.getElementById("dropoff-address").textContent = "Drop-off to: " + shortenedAddress;
                })
                .catch((error) => {
                    console.error(error);
                    document.getElementById("dropoff-address").textContent = "Unable to locate the drop-off address.";
                });
        }

        displayPolygons();
        map.on('dblclick', addDropoffPoint);
    </script>




</body>

</html>