<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Missions editor <small class="text-secondary">new mission</small></h3>
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

            .accordion-elt {
                overflow-y: scroll;
                max-height: 65vh;
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

        <div class="row" style="margin: 0;">
            <div class="col">
                <div id="map"></div>
            </div>
            <div class="col">

                <form id="add-form" action="" method="post">

                    <div id="accordion">

                        <div class="card">
                            <div class="card-header">
                                <a class="card-link" data-toggle="collapse" href="#name-container">
                                    Overview
                                </a>
                                <small class="form-text text-muted">
                                    The name and the description of the mission the player will see before enabling it
                                </small>
                            </div>
                            <div id="name-container" class="collapse accordion-elt show container" data-parent="#accordion">
                                <label for="mission-name">Mission title</label>
                                <input type="text" name="mission-name" class="form-control">
                                <label for="mission-passengers">Passengers</label>
                                <input type="number" name="mission-passengers" class="form-control" placeholder="when the skipper is alone, passengers = 0">
                                <label for="mission-caption">Short description of the mission <small>(optional)</small></label>
                                <textarea name="mission-caption" rows="2" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <a class="card-link" data-toggle="collapse" href="#steps-container">
                                    Steps
                                </a>
                                <small class="form-text text-muted">
                                    The player has to reach several steps in a defined order to complete the mission
                                </small>
                            </div>
                            <div id="steps-container" class="accordion-elt collapse" data-parent="#accordion">
                                <div id="new-step" class="text-center"><i class="fas fa-plus-circle"></i> Add a new step</div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <a class="collapsed card-link" data-toggle="collapse" href="#inputs-container">
                                    Places of availability
                                </a>
                                <small class="form-text text-muted">
                                    POIs where the mission can be enabled
                                </small>
                            </div>
                            <div id="inputs-container" class="collapse accordion-elt container" data-parent="#accordion">
                                <label for="poi-selection">Enable POI selection</label>
                                <i id="poi-selection" class="fas fa-toggle-off"></i>
                                <small class="form-text text-muted">click on a POI to add it to the list of places of availability</small>
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary btn-block new-location-button"><i class="fas fa-plus-circle"></i> Add this mission</button>

                </form>

            </div>
        </div>

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