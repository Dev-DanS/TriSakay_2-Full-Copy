<?php
include('../php/session_dispatcher.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriSakay | Dispatcher</title>
    <script src="https://kit.fontawesome.com/e96c3f3ee3.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dispatcher.css">


</head>

<body>
    <?php
    include('../php/navbar_dispatcher.php');
    ?>
    <div class="col-md-4">
        <div class="custom-card">
            <h3 id="current-time">Current Time</h3>
            <h3>Toda: Piel</h3>
        </div>
    </div>

    
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-default custom-btn" onclick="redirectToPending()">
                    Pending <span class="badge text-bg-warning" style="color: white !important;">10</span>
                </button>

            </div>
            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-default custom-btn" onclick="redirectToBooking()">
                    Cancelled <span class="badge text-bg-danger">3</span>
                </button>
            </div>




            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-default custom-btn" onclick="redirectToBooking()">
                    Manual
                </button>
            </div>
        </div>
    </div>
    <script src="../js/button.js"></script>
    <script>
        function updateCurrentTime() {
            const currentTimeElement = document.getElementById("current-time");
            const now = new Date();
            const daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            const dayOfWeek = daysOfWeek[now.getDay()]; // Get the day of the week
            const dateTimeString = dayOfWeek + ', ' + now.toLocaleString(); // Combine day of the week with date and time
            currentTimeElement.textContent = dateTimeString;
        }

        // Update the time initially
        updateCurrentTime();

        // Update the time every second (1000 milliseconds)
        setInterval(updateCurrentTime, 1000);
    </script>

</body>

</html>