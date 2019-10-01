<?php
require("../config.php");
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Harbour picker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
    <style>
        /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
        #map {
            height: 100%;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Harbour picker</h1>
        <p>This tool fill the database that stores all harbours where players will be able to make deals.</p>
        <div class="col-xs-12 col-sm-6">
            <div id="map" style="height: 500px;"></div>
        </div>
        <div class="col-xs-12 col-sm-6">
            <h4>Add a new harbour</h4>
            <form action="handler.php" method="post">
                <input type="hidden" name="user_id">
                <p>
                    <label for="lat">Latitude (double click the map to fill in)</label>
                    <input type="number" name="lat">
                </p>
                <p>
                    <label for="lng">Longitude (double click the map to fill in)</label>
                    <input type="number" name="lng">
                </p>
                <p>
                    <label for="radius">Circular area radius</label>
                    <input type="number" name="radius" placeholder="m">
                </p>
                <p>
                    <label for="name">Harbour name</label>
                    <input type="text" name="name" placeholder="be concise">
                </p>
                <p>
                    <input type="button" value="Add this harbour to the database">
                </p>
            </form>
            <h4>Remove a wrong harbour</h4>
            <p>...</p>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $MAPS_API_KEY; ?>&callback=initMap" async defer></script>
</body>

</html>