<?php
session_start();
?>
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

    <style>
        .custom-btn {
            display: none;
        }
    </style>
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
        <p id="pickup-address">Add a pick-up point by double-clicking</p>
        <p id="dropoff-address" style="display:none;">Add a drop-off point by double-clicking.</p>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-default custom-btn" id="pickup-confirm-btn">
                    Confirm Pickup
                </button>
            </div>
            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-default custom-btn" id="pickup-undo-btn">
                    Undo Pickup
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-default custom-btn" id="dropoff-confirm-btn">
                    Confirm Drop-off
                </button>
            </div>
            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-default custom-btn" id="dropoff-undo-btn">
                    Undo Drop-off
                </button>
            </div>
            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-default custom-btn" id="confirm-booking-btn">
                    Confirm Booking
                </button>
            </div>
        </div>
    </div>

    <div class="passenger"></div>
    <?php
    include '../db/dbconn.php';

    $query = "SELECT toda, borders FROM route";
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




    <script>
        var map = L.map('map', {
            zoomControl: false,
            doubleClickZoom: false
        }).setView([0, 0], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var blueMarkerIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        var greenMarkerIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        var redMarkerIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        var blueMarker = L.marker([0, 0], {
            icon: blueMarkerIcon
        }).addTo(map).bindPopup('You are here').openPopup();

        var pickupMarker = null;
        var dropoffMarker = null;

        function updateLocation(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;

            blueMarker.setLatLng([latitude, longitude]).update();
            map.setView([latitude, longitude], 14);
        }

        function handleError(error) {
        }

        navigator.geolocation.getCurrentPosition(updateLocation, handleError);



        const dbPolygons = <?php echo $jsonData; ?>;
        let polygonLayers = [];

        function displayPolygons() {
            dbPolygons.forEach((polygonData, index) => {
                const latlngs = polygonData.borders.latlngs[0];
                const polygon = L.polygon(latlngs).addTo(map);
                polygon.toda = polygonData.toda;
                polygonLayers.push(polygon);
            });
        }

        function findPolygonContainingPoint(latlng) {
            let selectedPolygon = null;
            polygonLayers.forEach((polygon) => {
                if (isLatLngInPolygon(latlng, polygon.getLatLngs()[0])) {
                    selectedPolygon = polygon;
                }
            });
            return selectedPolygon;
        }

        function isLatLngInPolygon(latlng, polygon) {
            let x = latlng.lat, y = latlng.lng;
            let inside = false;
            for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
                let xi = polygon[i].lat, yi = polygon[i].lng;
                let xj = polygon[j].lat, yj = polygon[j].lng;
                let intersect = ((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
                if (intersect) inside = !inside;
            }
            return inside;
        }



        const searchInput = document.getElementById("search-input");
        const searchButton = document.getElementById("search-button");
        const pickupAddress = document.getElementById("pickup-address");
        const dropoffAddress = document.getElementById("dropoff-address");

        searchInput.addEventListener("keyup", function (event) {
            if (event.keyCode === 13) {
                searchButton.click();
            }
        });

        searchButton.addEventListener("click", function () {
            var searchValue = searchInput.value;
            axios.get("https://nominatim.openstreetmap.org/search?q=" + searchValue + "&format=json&limit=1")
                .then(function (response) {
                    var result = response.data[0];
                    map.setView([result.lat, result.lon], 16);
                })
                .catch(function (error) {
                });
        });

        var isPickupConfirmed = false;
        var isDropoffConfirmed = false;
        var isFirstDoubleClick = true;

        document.getElementById("pickup-confirm-btn").addEventListener("click", function () {
            isPickupConfirmed = true;
            document.querySelectorAll(".custom-btn").forEach(btn => btn.style.display = "none");
            document.getElementById("dropoff-address").style.display = "block";
            document.getElementById("dropoff-confirm-btn").style.display = "block";
            document.getElementById("dropoff-undo-btn").style.display = "block";
        });

        document.getElementById("pickup-undo-btn").addEventListener("click", function () {
            if (pickupMarker) {
                map.removeLayer(pickupMarker);
                pickupMarker = null;
                pickupAddress.textContent = "Add a Pickup Point";
            }
            isPickupConfirmed = false;
            document.querySelectorAll(".custom-btn").forEach(btn => btn.style.display = "none");
            isFirstDoubleClick = true;
        });

        document.getElementById("dropoff-confirm-btn").addEventListener("click", function () {
            isDropoffConfirmed = true;
            document.querySelectorAll(".custom-btn").forEach(btn => btn.style.display = "none");
            document.getElementById("pickup-address").style.display = "block";
            isFirstDoubleClick = true;

            if (isPickupConfirmed && isDropoffConfirmed) {
                getShortestPath(pickupMarker.getLatLng(), dropoffMarker.getLatLng());
            }
        });

        document.getElementById("dropoff-undo-btn").addEventListener("click", function () {
            if (dropoffMarker) {
                map.removeLayer(dropoffMarker);
                dropoffMarker = null;
                dropoffAddress.textContent = "Add a Drop-off Point";
            }
            isDropoffConfirmed = false;
            document.querySelectorAll(".custom-btn").forEach(btn => btn.style.display = "none");
            isFirstDoubleClick = true;
        });

        function handleDoubleClick(event) {
            if (isPickupConfirmed && isDropoffConfirmed) {
                return;
            }

            let selectedPolygon = findPolygonContainingPoint(event.latlng);

            if (!selectedPolygon) return;

            if (!isPickupConfirmed) {
                currentToda = selectedPolygon.toda;
            }

            if (!isDropoffConfirmed && isPickupConfirmed) {
                if (currentToda !== selectedPolygon.toda) {
                    return;
                }
            }

            if (isFirstDoubleClick) {
                document.querySelectorAll(".custom-btn").forEach(btn => btn.style.display = "none");
                document.getElementById("pickup-confirm-btn").style.display = "block";
                document.getElementById("pickup-undo-btn").style.display = "block";
                document.getElementById("dropoff-confirm-btn").style.display = "none";
                document.getElementById("dropoff-undo-btn").style.display = "none";
                isFirstDoubleClick = false;
            }

            var latlng = event.latlng;

            if (!isPickupConfirmed) {
                if (pickupMarker) {
                    map.removeLayer(pickupMarker);
                }
                pickupMarker = L.marker(latlng, {
                    icon: greenMarkerIcon
                }).addTo(map).bindPopup('Pickup point').openPopup();

                axios.get("https://nominatim.openstreetmap.org/reverse?lat=" + latlng.lat + "&lon=" + latlng.lng + "&format=json")
                    .then(function (response) {
                        var address = response.data.display_name;
                        pickupAddress.textContent = "Pickup to: " + address;
                    })
                    .catch(function (error) {
                    });
            }

            if (!isDropoffConfirmed && isPickupConfirmed) {
                if (dropoffMarker) {
                    map.removeLayer(dropoffMarker);
                }
                dropoffMarker = L.marker(latlng, {
                    icon: redMarkerIcon
                }).addTo(map).bindPopup('Drop-off point').openPopup();

                axios.get("https://nominatim.openstreetmap.org/reverse?lat=" + latlng.lat + "&lon=" + latlng.lng + "&format=json")
                    .then(function (response) {
                        var address = response.data.display_name;
                        dropoffAddress.textContent = "Drop-off to: " + address;
                    })
                    .catch(function (error) {
                    });
            }
        }


        let calculatedDistanceKm = 0; // Declare a global variable to store the distance in km

        function getShortestPath(pickup, dropoff) {
            const pickupCoord = `${pickup.lng},${pickup.lat}`;
            const dropoffCoord = `${dropoff.lng},${dropoff.lat}`;
            const url = `https://router.project-osrm.org/route/v1/driving/${pickupCoord};${dropoffCoord}?overview=full&geometries=geojson`;

            axios.get(url)
                .then(response => {
                    const route = response.data.routes[0].geometry.coordinates;
                    const distanceMeters = response.data.routes[0].distance;
                    calculatedDistanceKm = distanceMeters / 1000;  // Convert to kilometers and update the global variable
                    console.log("Distance:", calculatedDistanceKm, "km");
                    const geojsonRoute = {
                        type: "Feature",
                        properties: {},
                        geometry: {
                            type: "LineString",
                            coordinates: route
                        }
                    };
                    L.geoJSON(geojsonRoute).addTo(map);
                })
                .catch(error => {
                    console.log(error);
                });
        }

        document.getElementById("dropoff-confirm-btn").addEventListener("click", function () {
            // ... existing code ...

            if (isPickupConfirmed && isDropoffConfirmed) {
                document.getElementById("confirm-booking-btn").style.display = "block";
                getShortestPath(pickupMarker.getLatLng(), dropoffMarker.getLatLng());
            }
        });

        document.getElementById("confirm-booking-btn").addEventListener("click", async function () {
            let commuterId;
            await axios.post("../php/get_commuter_id.php")
                .then(response => {
                    commuterId = response.data;
                });

            // Fetch todaTerminalCoord from PHP
            let todaTerminalCoord;
            await axios.get("../php/get_toda_terminal_coord.php")
                .then(response => {
                    todaTerminalCoord = response.data; // Assuming it returns a string "lat,lng"
                });

            let pickup = pickupMarker.getLatLng();
            let dropoff = dropoffMarker.getLatLng();

            await getShortestPath(pickup, dropoff);

            let fare = calculatedDistanceKm <= 2 ? 30 : 30 + (calculatedDistanceKm - 2) * 10;
            fare = Math.round(fare);

            let osrmUrl = `https://router.project-osrm.org/route/v1/driving/${todaTerminalCoord.lng},${todaTerminalCoord.lat};${pickup.lng},${pickup.lat}`;

            let convenienceFee;
            await axios.get(osrmUrl)
                .then(response => {
                    const distanceMeters = response.data.routes[0].distance;
                    convenienceFee = distanceMeters / 1000 * 10;
                });

            let booking = {
                commuterid: commuterId,
                status: "pending",
                pickupPoint: `${pickup.lat},${pickup.lng}`,
                dropoffPoint: `${dropoff.lat},${dropoff.lng}`,
                pickupAddress: pickupAddress.textContent,
                dropoffAddress: dropoffAddress.textContent,
                fare: fare,
                convenienceFee: convenienceFee,
                distance: calculatedDistanceKm
            };

            axios.post("../php/insert_booking.php", booking)
                .then(response => {
                    console.log(response.data);
                });
        });




        map.on("dblclick", handleDoubleClick);
        displayPolygons();
    </script>
</body>

</html>