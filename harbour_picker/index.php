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
                <div class="form-group">
                    <label for="lat">Latitude</label>
                    <input type="number" class="form-control" name="lat" placeholder="dble click the map to fill in">
                </div>
                <label for="lng">Longitude</label>
                <div class="form-group">
                    <input type="number" class="form-control" name="lng" placeholder="dble click the map to fill in">
                </div>
                <label for="radius">Circular area radius</label>
                <div class="form-group">
                    <input type="number" class="form-control" name="radius" placeholder="m">
                </div>
                <label for="name">Harbour name</label>
                <div class="form-group">
                    <input type="text" class="form-control" name="name" placeholder="be concise">
                </div>
                <button type="submit" class="btn btn-primary">Add this harbour to the database</button>
            </form>
            <h4>Remove a wrong harbour</h4>
            <p>...</p>
            <button type="submit" class="btn btn-danger">Remove the selected harbour from the database</button>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $MAPS_API_KEY; ?>&callback=initMap" async defer></script>
</body>

</html>