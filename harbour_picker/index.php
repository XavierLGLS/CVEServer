<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require("../config.php");
require("../db.php");
$db = new DB();
$_SESSION["auth"] = NULL;

if (!empty($_POST)) {
    switch ($_POST['form']) {
        case "login":
            if ($db->isHarbourPickerRegistered(htmlspecialchars($_POST['username']), htmlspecialchars($_POST['password']))) {
                $_SESSION["auth"] = htmlspecialchars($_POST['username']);
            } else {
                $_SESSION["auth"] = NULL;
            }
            break;
    }
}

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
            <?php if ($_SESSION["auth"]) { ?>
                <!-- IF REGISTERED -->
                <p>Hello <?php echo $_SESSION["auth"]; ?> !</p>
                <h3>Add a new harbour</h3>
                <form action="handler.php" method="post">
                    <input type="hidden" name="user_id">
                    <div class="form-group">
                        <label for="lat">Latitude</label>
                        <input type="number" class="form-control" name="lat" step="0.001" placeholder="dble click the map to fill in" required>
                    </div>
                    <label for="lng">Longitude</label>
                    <div class="form-group">
                        <input type="number" class="form-control" name="lng" step="0.001" placeholder="dble click the map to fill in" required>
                    </div>
                    <label for="radius">Circular area radius</label>
                    <div class="form-group">
                        <input type="number" class="form-control" name="radius" placeholder="m" required>
                    </div>
                    <label for="name">Harbour name</label>
                    <div class="form-group">
                        <input type="text" class="form-control" name="name" placeholder="be concise" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add this harbour to the database</button>
                </form>
                <h3>Remove an existing harbour</h3>
                <p>... click on the map to select an existing harbour ...</p>
                <button type="submit" class="btn btn-danger">Remove the selected harbour from the database</button>
            <?php } else { ?>
                <!-- IF NOT REGISTERED YET -->
                <form action="" method="post">
                    <input type="hidden" name="form" value="login">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">login</button>
                </form>
                <?php if (!empty($_POST)) { ?>
                    <div class="alert alert-danger">The authentication failed</div>
                <?php }
        } ?>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $MAPS_API_KEY; ?>&callback=initMap" async defer></script>
</body>

</html>