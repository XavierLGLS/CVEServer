<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>POIs editor</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<?php if (isset($_SESSION['auth'])) : ?>
    <?php if ($_SESSION['auth']->hasPOIEditionPerm()) : ?>
        <!-- leaflet lib -->
        <link rel="stylesheet" href="../../libs/leaflet/leaflet.css" />
        <script src="../../libs/leaflet/leaflet.js"></script>
        <!-- marker cluster -->
        <link rel="stylesheet" href="../../libs/marker-cluster/MarkerCluster.css" />
        <link rel="stylesheet" href="../../libs/marker-cluster/MarkerCluster.Default.css" />
        <script src="../../libs/marker-cluster/leaflet.markercluster-src.js"></script>
        <!-- custom spinner -->
        <script src="../../libs/leaflet-spin/spin.min.js"></script>
        <script src="../../libs/leaflet-spin/leaflet.spin.min.js"></script>
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
        </style>
        <div class="text-center">
            <i>click on a POI to edit it and double click anywhere to create a new one</i>
        </div>
        <div id="map" style="height: 650px;"></div>
        <script src="main.js"></script>
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