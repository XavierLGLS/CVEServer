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
        <script src="assets/icons.js"></script>
        <script src="script.js"></script>
        <style>
            /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
            #map {
                height: 100%;
                margin: 1em;
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
        <link rel="stylesheet" href="style.css">

    </head>

    <body>
        <div class="container">
            <h1>Harbour picker</h1>
            <?php if ($_SESSION["auth"]) { ?>
                <p class="explanation text-info">Hello <?php echo $_SESSION["auth"]->name; ?>, thank you for your contribution !</p>
            <?php } ?>
            <p>This tool manages the database where all harbours are stored. This harbours are locations where players will be able to make deals. Currenlty <strong><span id="harbour-nbre"><?php echo $db->getHarboursNumber(); ?></span> harbours</strong> ar stored !</p>
            <!-- CAPTION -->
            <?php if ($_SESSION["auth"]) { ?>
                <div class="col-sm-12">
                    <h4>Chose the map interaction mod:</h4>
                    <div class="custom-control custom-radio">
                        <input id="harbour-creation" type="radio" class="custom-control-input" name="dbleclick-mode" checked>
                        <label class="custom-control-label" for="harbour-creation">Harbour creation</label>
                        <input id="polygon-selection" type="radio" class="custom-control-input" name="dbleclick-mode">
                        <label class="custom-control-label" for="polygon-selection">Polygon selection</label>
                    </div>
                    <button type="button" onclick="selectHarboursInPolygon();" class="btn btn-success visible-when-polygon-selection">Select all markers inside the polygon</button>
                    <button type="button" onclick="unselectHarboursInPolygon();" class="btn btn-primary visible-when-polygon-selection">Unselect all markers inside the polygon</button>
                    <button type="button" onclick="resetPolygon();" class="btn btn-danger visible-when-polygon-selection">Reset the polygon</button>
                </div>
            <?php } ?>
        </div>
        <!-- MAP -->
        <div class="col-xs-12 col-sm-9">
            <div id="map" style="height: 500px;"></div>
            <div id="select-count" class="hidden">
                <button type="button" class="btn btn-success disable">Selected harbours <span class="badge">0</span></button>
                <button type="button" class="btn btn-danger" onclick="unselectAllHarbours();">Unselect all harbours</button>
            </div>
        </div>
        <div class="col-xs-12 col-sm-3">
            <?php if ($_SESSION["auth"]) { ?>
                <!-- IF REGISTERED -->
                <h3>Add a new harbour</h3>
                <form id="add-form" action="handler.php" method="post" autocomplete="off">
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
                <div id="send-progress" class="progress hidden">
                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:70%">
                        0%
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
        <div class="container">
            <?php if ($_SESSION["auth"]) { ?>
                <div class="col-sm-12">
                    <h3>How to use this map</h3>
                    <ul>
                        <li><strong>red marker</strong> Current harbour: not yet stored in the database</li>
                        <li><strong>green marker</strong> Selected harbour</li>
                        <li><strong>blue marker</strong> Harbours stored in the database</li>
                        <li>
                            <strong>Double click </strong>
                            <span class="visible-when-harbour-creation">
                                Creates a temporary marker (red) on the map. Its location automatically fill out the form. It is not yet stored in the database. Double click this marker to remove it.
                            </span>
                            <span class="visible-when-polygon-selection hidden">
                                Creates a polygon corner. All markers inside this polygon are selected. Double click a corner (green marker) to remove it.
                            </span>
                        </li>
                        <li><strong>Click on a blue marker </strong> Selects or unselects it. A selected marker turns green.</li>
                    </ul>
                </div>
            <?php } ?>
        </div>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $MAPS_API_KEY; ?>&callback=initMap" async defer></script>
    </body>

    </html>