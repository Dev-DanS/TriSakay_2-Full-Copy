var map = L.map("map").setView([14.954269223586323, 120.90079720821329], 17);

L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution:
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
}).addTo(map);

var marker;

map.on("dblclick", function (e) {
  if (!marker) {
    marker = L.marker(e.latlng).addTo(map);
    document.getElementById("addMarkerButton").style.display = "block";
    document.getElementById("undoButton").style.display = "block";
  }
});

document.getElementById("undoButton").addEventListener("click", function () {
  if (marker) {
    map.removeLayer(marker);
    marker = null;
    document.getElementById("addMarkerButton").style.display = "none";
    document.getElementById("undoButton").style.display = "none";
  }
});

document
  .getElementById("addMarkerButton")
  .addEventListener("click", function () {
    if (marker) {
      alert("Add marker is clicked");
    }
  });
