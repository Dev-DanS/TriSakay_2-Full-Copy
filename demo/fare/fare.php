<!DOCTYPE html>
<html>

<head>
    <title>Fare Calculator</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <h1>Fare Calculator</h1>
    <form id="fareCalculator">
        <label for="dayOrNight">Time of Travel:</label>
        <select id="dayOrNight" name="dayOrNight" required>
            <option value="day">Day</option>
            <option value="night">Night</option>
        </select>
        <br>

        <label for="passengers">Number of Passengers (Max: 4):</label>
        <input type="number" id="passengers" name="passengers" value="1" min="1" max="4" required>
        <br>

        <label for="distance">Distance (in km):</label>
        <input type="number" id="distance" name="distance" required>
        <br>

        <input type="button" value="Calculate Fare" onclick="calculateFare()">
    </form>

    <p id="result"></p>

    <script>
        function calculateFare() {
            const dayOrNight = document.getElementById("dayOrNight").value;
            const passengers = parseInt(document.getElementById("passengers").value);
            const distance = parseFloat(document.getElementById("distance").value);

            $.ajax({
                type: "GET",
                url: "calculate_fare.php",
                data: {
                    dayOrNight: dayOrNight,
                    passengers: passengers,
                    distance: distance
                },
                success: function (data) {
                    document.getElementById("result").textContent = "Fare: ₱" + data;
                },
                error: function (error) {
                    console.error('Error:', error);
                }
            });
        }
    </script>
</body>

</html>