<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriSakay | Dispatcher</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dispatcher.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
        integrity="sha384-+63PiUjh04wBNnEGzr8tiq3EKdvxqDqv/XFux8h8V6C0jso9F8eZU+utlkoZzzmwZ" crossorigin="anonymous">
    <style>
        /* Add your custom styles here */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
        }

        .custom-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .btn-custom {
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <?php include('../php/navbar_dispatcher.php'); ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="custom-card">
                    <h3 id="current-time">Current Time</h3>
                    <h3>Toda: Piel</h3>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="custom-card">
                    <h3 id="current-time">Another Card</h3>
                    <p>Some content here</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="custom-card">
                    <h3 id="current-time">One More Card</h3>
                    <p>Some content here</p>
                </div>
            </div>
        </div>
        <div id="curve_chart" style="width: 100vw; height: 50vh"></div>
        <div class="row mt-4">
            <div class="col-md-4">
                <button class="btn btn-custom" onclick="redirectToMyLocation()">
                    Pending <span class="badge text-bg-warning" style="color: white !important;">10</span>
                </button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-custom" onclick="redirectToBooking()">
                    Cancelled <span class="badge text-bg-danger">3</span>
                </button>
            </div>
            <div class="col-md-4">
                <button class="btn btn-custom" onclick="redirectToBooking()">
                    Manual
                </button>
            </div>
        </div>
    </div>
    <script>
        function updateCurrentTime() {
            const currentTimeElement = document.getElementById("current-time");
            const now = new Date();
            const daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            const dayOfWeek = daysOfWeek[now.getDay()];
            const dateTimeString = dayOfWeek + ', ' + now.toLocaleString();
            currentTimeElement.textContent = dateTimeString;
        }
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);
    </script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          ['2004',  1000,      400],
          ['2005',  1170,      460],
          ['2006',  660,       1120],
          ['2007',  1030,      540]
        ]);

        var options = {
          title: 'Company Performance',
          curveType: 'function',
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

        chart.draw(data, options);
      }
    </script>
</body>

</html>