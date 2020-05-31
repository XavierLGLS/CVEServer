<?php
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config_v2.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PoisManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'BoatsManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SailawayBoatsManager.php';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';

$poisManager = new PoisManager();
$boatsManager = new BoatsManager();
$sailawayBoatsManager = new SailawayBoatsManager();
?>

<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Dashboard</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="content-wrapper">
    <?php if (isset($_SESSION['auth'])) : ?>
        <?php $boatId = $boatsManager->getBoatFromCVEUser($_SESSION['auth'])->getId(); ?>
        <?php if (count($boatsManager->getTrajectoryFromBoatId($boatId)) > 0) : ?>
            <!-- leaflet lib -->
            <link rel="stylesheet" href="../../libs/leaflet/leaflet.css" />
            <script src="../../libs/leaflet/leaflet.js"></script>
            <!-- marker rotate -->
            <script src="../../libs/marker-rotate/leaflet.marker.rotate.js"></script>
            <!-- custom arrows -->
            <script src="../../libs/leaflet-arrow/main.js"></script>
            <!-- custom spinner -->
            <script src="../../libs/leaflet-spin/spin.min.js"></script>
            <script src="../../libs/leaflet-spin/leaflet.spin.min.js"></script>
            <style>
                #map {
                    height: 95vh;
                }

                .legend {
                    padding: 6px 8px;
                    font: 14px/16px Arial, Helvetica, sans-serif;
                    background: white;
                    background: rgba(255, 255, 255, 0.8);
                    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
                    border-radius: 5px;
                }

                .dashboard {
                    min-width: 150px;
                    max-width: 25vw;
                }

                .poi-control {
                    min-width: 150px;
                    max-width: 25vw;
                }

                .mission-control {
                    min-width: 150px;
                    max-width: 25vw;
                }

                .mission-overview {
                    min-width: 250px;
                    max-width: 30vw;
                }

                .legend-item {
                    padding: 2px 0px;
                }

                .test-legend-container {
                    border: 1px solid black;
                    border-radius: 5px;
                    background: rgba(250, 246, 217, 0.8);
                }

                .fontawesome {
                    background-color: transparent;
                }

                .progress {
                    margin: 0 5px;
                }

                .progress-bar-title {
                    position: absolute;
                    text-align: center;
                    line-height: 1rem;
                    overflow: hidden;
                    right: 0;
                    left: 0;
                    top: 0;
                }

                .mission-header {
                    margin-bottom: 0.5em;
                    padding: 0.2em;
                    border-radius: 5px;
                    cursor: pointer;
                }

                .auto-mission-header {
                    background-color: #d2b4de;
                }

                .manual-mission-header {
                    background-color: #ffc107;
                }

                .mission-header:hover {
                    opacity: 0.8;
                }

                .clickable {
                    cursor: pointer;
                }

                .clickable:hover {
                    color: #ffc107;
                }
            </style>
            <div id="map"></div>

            <div id="dashboard" style="display: none;">
                <h5 id="dashboard-boat-name" class="text-center">
                    boat name
                </h5>
                <label><u>Hold</u></label>
                <!-- water -->
                <div style="display: flex; margin-bottom: 5px;">
                    <i class="fas fa-tint" style="vertical-align: top;" title="water onboard"></i>
                    <div class="progress position-relative" style="flex-grow: 1;">
                        <div id="water-progress" class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        <div id="water-progress-add" class="progress-bar bg-success" style="width:0%"></div>
                        <small id="water-label" class="progress-bar-title"></small>
                    </div>
                </div>
                <!-- food -->
                <div style="display: flex; margin-bottom: 5px;">
                    <i class="fas fa-drumstick-bite" style="vertical-align: top;" title="food onboard"></i>
                    <div class="progress position-relative" style="flex-grow: 1;">
                        <div id="food-progress" class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        <div id="food-progress-add" class="progress-bar bg-success" style="width:0%"></div>
                        <small id="food-label" class="progress-bar-title"></small>
                    </div>
                </div>
                <!-- spare parts -->
                <div style="display: flex; margin-bottom: 5px;">
                    <i class="fas fa-toolbox" style="vertical-align: top;" title="spare parts onboard"></i>
                    <div class="progress position-relative" style="flex-grow: 1;">
                        <div id="spare-parts-progress" class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        <div id="spare-parts-progress-add" class="progress-bar bg-success" style="width:0%"></div>
                        <div id="spare-parts-progress-remove" class="progress-bar bg-danger" style="width:0%"></div>
                        <small id="spare-parts-label" class="progress-bar-title"></small>
                    </div>
                </div>
                <label><u>Status</u></label>
                <!-- hull dammage -->
                <div style="display: flex; margin-bottom: 5px;">
                    <i class="fas fa-question-circle" style="vertical-align: top;" title="hull damage level"></i>
                    <div class="progress position-relative" style="flex-grow: 1;">
                        <div id="hull-damage-progress" class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        <div id="hull-damage-progress-add" class="progress-bar bg-success" style="width:0%"></div>
                        <small id="hull-damage-label" class="progress-bar-title"></small>
                    </div>
                    <i id="repair-hull" class="fas fa-tools clickable" style="vertical-align: top;" title="repair"></i>
                </div>
                <!-- sails damage -->
                <div style="display: flex; margin-bottom: 5px;">
                    <i class="fas fa-question-circle" style="vertical-align: top;" title="sails damage level"></i>
                    <div class="progress position-relative" style="flex-grow: 1;">
                        <div id="sails-damage-progress" class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        <div id="sails-damage-progress-add" class="progress-bar bg-success" style="width:0%"></div>
                        <small id="sails-damage-label" class="progress-bar-title"></small>
                    </div>
                    <i id="repair-sails" class="fas fa-tools clickable" style="vertical-align: top;" title="repair"></i>
                </div>
                <!-- crew health -->
                <div style="display: flex; margin-bottom: 5px;">
                    <i class="fas fa-heartbeat" style="vertical-align: top;" title="crew health"></i>
                    <div class="progress position-relative" style="flex-grow: 1;">
                        <div id="crew-health-progress" class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        <small id="crew-health-label" class="progress-bar-title"></small>
                    </div>
                </div>
                <div style="margin-top: 15px;">
                    <p class="text-info text-center" style="margin: 0px;">passengers: <span id="passengers"></span></p>
                    <p class="text-muted text-center" style="margin: 0px;">money: <span id="money"></span></p>
                </div>
                <div id="dry-dock-container" style="display: none;">
                    <button class="btn btn-sm btn-warning" style="padding: 0.1em; margin: 5px 0;" onclick="requestLeaveDryDock();">Leave the dry dock</button>
                </div>
            </div>


            <div id="poi-panel" style="display: none;">
                <h5 class="text-center">
                    Welcome in <span id="poi-name"></span>
                </h5>
                <label><u>Facilities</u></label>
                <!-- water -->
                <div id="poi-panel-water-container" style="display: flex; margin-bottom: 5px;">
                    <i class="fas fa-tint" style="vertical-align: top;" title="get water"></i>
                    <div class="progress position-relative" style="flex-grow: 1;">
                        <div id="poi-panel-water-progress" class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        <div id="poi-panel-water-progress-add" class="progress-bar bg-success" style="width:0%" amount="0"></div>
                        <small id="poi-panel-water-label" class="progress-bar-title"></small>
                    </div>
                    <i id="poi-panel-water-remove" class="far fa-minus-square clickable"></i>
                    <i id="poi-panel-water-add" class="far fa-plus-square clickable" style="margin: 0 5px;"></i>
                </div>
                <!-- food -->
                <div id="poi-panel-food-container" style="display: flex; margin-bottom: 5px;">
                    <i class="fas fa-drumstick-bite" style="vertical-align: top;" title="buy food"></i>
                    <div class="progress position-relative" style="flex-grow: 1;">
                        <div id="poi-panel-food-progress" class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        <div id="poi-panel-food-progress-add" class="progress-bar bg-success" style="width:0%" amount="0"></div>
                        <small id="poi-panel-food-label" class="progress-bar-title"></small>
                    </div>
                    <i id="poi-panel-food-remove" class="far fa-minus-square clickable"></i>
                    <i id="poi-panel-food-add" class="far fa-plus-square clickable" style="margin: 0 5px;"></i>
                </div>
                <!-- spare parts -->
                <div id="poi-panel-spare-parts-container" style="display: flex; margin-bottom: 5px;">
                    <i class="fas fa-toolbox" style="vertical-align: top;" title="buy spare parts"></i>
                    <div class="progress position-relative" style="flex-grow: 1;">
                        <div id="poi-panel-spare-parts-progress" class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        <div id="poi-panel-spare-parts-progress-add" class="progress-bar bg-success" style="width:0%" amount="0"></div>
                        <small id="poi-panel-spare-parts-label" class="progress-bar-title"></small>
                    </div>
                    <i id="poi-panel-spare-parts-remove" class="far fa-minus-square clickable"></i>
                    <i id="poi-panel-spare-parts-add" class="far fa-plus-square clickable" style="margin: 0 5px;"></i>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <button id="poi-panel-buy-btn" class="btn btn-warning btn-sm" style="padding: 0.1em; margin: 5px 0;">Buy items</button>
                    <span>cost: <span id="poi-panel-price">0</span></span>
                </div>
                <div id="poi-panel-dry-dock-container" style="display: flex; justify-content: space-between;">
                    <button id="poi-panel-dry-dock-btn" class="btn btn-warning btn-sm" style="padding: 0.1em; margin: 5px 0;">Use the dry dock</button>
                    <span>cost: <?= $PRICE_DRY_DOCK ?></span>
                </div>
                <label id="poi-panel-missions-label"><u>Missions</u></label>
                <div id="poi-panel-missions-container" style="overflow-y: scroll; max-height: 100px;"></div>
            </div>

            <div id="mission-panel" style="display: none;">
                <h5>Mission <i id="mission-panel-title"></i></h5>
                <label><u>Next destination:</u></label><span id="mission-panel-poi-name" style="margin-left: 1em;"></span>
                <div class="text-muted">
                    step <span id="mission-panel-rank"></span>, dist: <span id="mission-panel-dist">12</span> nm
                </div>
                <div id="mission-panel-caption" class="text-info" style="margin: 0.5em 0;"></div>
                <button class="btn btn-sm btn-danger" onclick="requestCancelMission();">cancel the mission</button>
                <button id="mission-panel-validation-btn" class="btn btn-sm btn-primary" onclick="requestValidateStep();">validate the step</button>
            </div>
            <script>
                function getBoatId() {
                    return <?= ($boatsManager->getBoatFromCVEUser($_SESSION['auth']))->getId() ?>;
                }
            </script>
            <script src="main.js"></script>
        <?php else : ?>
            <p class="text-info text-center">your boat is not yet synchronized with CVE</p>
            <?php
            $now = (new DateTime())->getTimestamp();
            $configJson = json_decode(file_get_contents('../../../temp_v2.json'));
            $lastSyncDate = intval($configJson->location_update->last_date);
            $timeToNextUpdate = $configJson->location_update->period - floor(($now - $lastSyncDate) / 60);
            ?>
            <p class="text-info text-center"><i>next synchronization in <?= $timeToNextUpdate ?> min</i></p>
        <?php endif; ?>
    <?php else : ?>
        <p class="text-info text-center">you need to log in to access this page</p>
    <?php endif; ?>
</div>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>