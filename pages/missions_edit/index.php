<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Missions editor <small class="text-secondary">edit existing missions</small></h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<?php if (isset($_SESSION['auth'])) : ?>
    <?php if ($_SESSION['auth']->hasMissionEditionPerm()) : ?>
        <!-- leaflet lib -->
        <link rel="stylesheet" href="../../libs/leaflet/leaflet.css" />
        <script src="../../libs/leaflet/leaflet.js"></script>
        <!-- marker cluster lib -->
        <link rel="stylesheet" href="../../libs/marker-cluster/MarkerCluster.css" />
        <link rel="stylesheet" href="../../libs/marker-cluster/MarkerCluster.Default.css" />
        <script src="../../libs/marker-cluster/leaflet.markercluster-src.js"></script>
        <!-- custom arrows -->
        <script src="../../libs/leaflet-arrow/main.js"></script>
        <style>
            i {
                cursor: pointer;
                margin-left: 10px;
            }

            .mission-title:hover {
                cursor: pointer;
                color: #007bff;
            }

            #missions-container {
                overflow-y: scroll;
                max-height: 100vh;
            }

            #map {
                height: 100vh;
            }

            .legend {
                padding: 6px 8px;
                font: 14px/16px Arial, Helvetica, sans-serif;
                background: white;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
                border-radius: 5px;
            }

            .legend-item {
                padding: 2px 0px;
            }

            /* delete icon */
            .fas.fa-trash-alt {
                color: red;
            }

            i:hover {
                cursor: pointer;
                color: #138496;
            }

            i:active {
                color: white;
            }

            i.locating {
                color: white;
            }

            .step-container {
                margin: 10px;
                padding: 10px;
                border-radius: 5px;
                background-color: #EEEEEE;
            }

            #new-step {
                margin: 10px;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
                color: #fff;
                background-color: #17a2b8;
                border-color: #17a2b8;
            }

            #new-step:hover {
                color: #fff;
                background-color: #138496;
                border-color: #117a8b;
            }

            .icons-container * {
                padding: 15px;
            }
        </style>
        <script src="main.js"></script>
        <div class="form-group input-group container">
            <input type="text" name="search-field" class="form-control form-block" placeholder="type a mission name">
            <button type="button" id="name-search-button" class="btn btn-primary" style="margin-left: 1em;" disabled><i class="fas fa-search"></i> Find a mission by name</button>
            <button type="button" id="poi-search-button" class="btn btn-primary" style="margin-left: 1em;" disabled><i class="fas fa-search"></i> Find a mission in a POI</button>
        </div>
        <div class="row" style="margin: 0;">
            <div class="col">
                <div id="map" style="height: 100vh;">
                </div>
            </div>
            <div id="missions-container" class="col">
                <p class="text-info">please wait while pois are loading...</p>
            </div>
        </div>
    <?php else : ?>
        <div class="text-center text-danger">
            <i>you dont have the permission to access this page</i>
        </div>
    <?php endif ?>
<?php else : ?>
    <div class="text-center text-danger">
        <i>you dont have the permission to access this page</i>
    </div>
<?php endif ?>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>