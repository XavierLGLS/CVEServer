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
            $_SESSION["auth"] = $db->harbourPickerLogin(htmlspecialchars($_POST['username']), htmlspecialchars($_POST['password']));
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-csv/0.71/jquery.csv-0.71.min.js"></script>
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

            .btn-file {
                position: relative;
                overflow: hidden;
            }

            .btn-file input[type=file] {
                position: absolute;
                top: 0;
                right: 0;
                min-width: 100%;
                min-height: 100%;
                font-size: 100px;
                text-align: right;
                filter: alpha(opacity=0);
                opacity: 0;
                outline: none;
                background: white;
                cursor: inherit;
                display: block;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>Harbour picker</h1>
            <p>This tool fills the database that stores all harbours where players will be able to make deals.</p>
            <div class="col-xs-12 col-sm-6">
                <div id="map" style="height: 500px;"></div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <?php if ($_SESSION["auth"]) { ?>
                    <!-- IF REGISTERED -->
                    <p class="explanation text-info">Hello <?php echo $_SESSION["auth"]->name; ?>, thank you for your contribution !</p>
                    <h3>Add a new harbour</h3>
                    <form id="add-form" action="handler.php" method="post">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION["auth"]->user_id; ?>">
                        <div class="form-group">
                            <label for="lat">Latitude</label>
                            <input type="number" class="form-control" name="lat" step="0.001" placeholder="double click the map to fill in" required>
                        </div>
                        <label for="lng">Longitude</label>
                        <div class="form-group">
                            <input type="number" class="form-control" name="lng" step="0.001" placeholder="double click the map to fill in" required>
                        </div>
                        <label for="name">Harbour name</label>
                        <div class="form-group">
                            <input type="text" class="form-control" name="name" placeholder="be concise" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add this harbour</button>
                    </form>
                    <h3>Import harbours from a csv file</h3>
                    <div class="custom-file">
                        <div class="input-group">
                            <label class="input-group-btn">
                                <span class="btn btn-primary">
                                    Browse&hellip; <input type="file" id="file-input" style="display: none;">
                                </span>
                            </label>
                            <input type="text" id="file-name-display" class="form-control" readonly>
                        </div>
                        <p class="help-block">the <strong>.csv</strong> file structure must be lat;lng;name (without header)</p>
                    </div>
                    <div id="send-progress" class="progress" style="display: none;">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:70%">
                            70%
                        </div>
                    </div>
                    <h3>Remove harbours</h3>
                    <button id="remove" class="btn btn-danger">Remove all selected harbours</button>
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
                    <?php if (!empty($_POST)) {
                            if ($_POST['form'] == "login") { ?>
                            <div class="alert alert-danger fade in">The authentication failed</div>
                <?php }
                    }
                } ?>
            </div>
        </div>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $MAPS_API_KEY; ?>&callback=initMap" async defer></script>
    </body>

    </html>