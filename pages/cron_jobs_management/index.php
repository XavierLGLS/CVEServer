<?php

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config_v2.php';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';

?>
<!-- <script src="http://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script> -->
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Cron jobs management</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
    <?php if (isset($_SESSION['auth'])) : ?>
        <?php if ($_SESSION['auth']->isAdmin()) : ?>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
            <style>
                .toggle-btn {
                    cursor: pointer;
                }

                .fa-toggle-off {
                    color: red;
                }

                .fa-toggle-on {
                    color: green;
                }
            </style>
            <table class="table">
                <tr>
                    <th class="w-40">Task</th>
                    <th class="w-25">Period</th>
                    <th class="w-10">Activation</th>
                    <th class="w-25">Description</th>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex;">
                            <div style="margin-right: 1em;">
                                <canvas id="location-progress-chart" style="display: none;" width="80px" height="80px"></canvas>
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="text-primary">Location updates</div>
                                <small><strong>last execution: </strong><span id="location-last-exec" class="text-muted"></span></small><br>
                                <small><strong>duration: </strong><span id="location-duration" class="text-muted"></span></small>
                            </div>
                        </div>
                    </td>
                    <td class="form-inline">
                        <input id="location-period" type="number" class="form-control  is-valid" style="width: 150px;">
                        <div id="location-label" class="valid-feedback"></div>
                    </td>
                    <td>
                        <i id="location-toggle" class="toggle-btn fas fa-toggle-off" style="font-size: 2em;"></i>
                    </td>
                    <td class="text-info">
                        <small class="info-container">
                            Updates the location of each boat tracked by the CVE app with data gathered from the sailaway API
                        </small>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex;">
                            <div style="margin-right: 1em;">
                                <canvas id="weather-progress-chart" style="display: none;" width="80px" height="80px"></canvas>
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="text-primary">Weather updates</div>
                                <small><strong>last execution: </strong><span id="weather-last-exec" class="text-muted"></span></small><br>
                                <small><strong>duration: </strong><span id="weather-duration" class="text-muted"></span></small>
                            </div>
                        </div>
                    </td>
                    <td class="form-inline">
                        <input id="weather-period" type="number" class="form-control is-valid" style="width: 150px;">
                        <div id="weather-label" class="valid-feedback"></div>
                    </td>
                    <td>
                        <i id="weather-toggle" class="toggle-btn fas fa-toggle-off" style="font-size: 2em;"></i>
                    </td>
                    <td class="text-info">
                        <small>
                            Computes a weather indicator for each boat tracked by the CVE app with data gathered from the sailaway API
                        </small>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex;">
                            <div style="margin-right: 1em;">
                                <canvas id="food-progress-chart" style="display: none;" width="80px" height="80px"></canvas>
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="text-primary">Food/water updates</div>
                                <small><strong>last execution: </strong><span id="food-last-exec" class="text-muted"></span></small><br>
                                <small><strong>duration: </strong><span id="food-duration" class="text-muted"></span></small>
                            </div>
                        </div>
                    </td>
                    <td class="form-inline">
                        <input id="food-period" type="number" class="form-control  is-valid" style="width: 150px;">
                        <div id="food-label" class="valid-feedback"></div>
                    </td>
                    <td>
                        <i id="food-toggle" class="toggle-btn fas fa-toggle-off" style="font-size: 2em;"></i>
                    </td>
                    <td class="text-info">
                        <small>
                            Updates the food and water quantities onboard
                        </small>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex;">
                            <div style="margin-right: 1em;">
                                <canvas id="damage-progress-chart" style="display: none;" width="80px" height="80px"></canvas>
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="text-primary">Damage updates</div>
                                <small><strong>last execution: </strong><span id="damage-last-exec" class="text-muted"></span></small><br>
                                <small><strong>duration: </strong><span id="damage-duration" class="text-muted"></span></small>
                            </div>
                        </div>
                    </td>
                    <td class="form-inline">
                        <input id="damage-period" type="number" class="form-control  is-valid" style="width: 150px;">
                        <div id="damage-label" class="valid-feedback"></div>
                    </td>
                    <td>
                        <i id="damage-toggle" class="toggle-btn fas fa-toggle-off" style="font-size: 2em;"></i>
                    </td>
                    <td class="text-info">
                        <small>
                            Updates hull, sails damage and the crew health
                        </small>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex;">
                            <div style="margin-right: 1em;">
                                <canvas id="sw-accounts-progress-chart" style="display: none;" width="80px" height="80px"></canvas>
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="text-primary">Sailaway accounts sync</div>
                                <small><strong>last execution: </strong><span id="sw-accounts-last-exec" class="text-muted"></span></small><br>
                                <small><strong>duration: </strong><span id="sw-accounts-duration" class="text-muted"></span></small>
                            </div>
                        </div>
                    </td>
                    <td class="form-inline">
                        <input id="sw-accounts-period" type="number" class="form-control  is-valid" style="width: 150px;">
                        <div id="sw-accounts-label" class="valid-feedback"></div>
                    </td>
                    <td>
                        <i id="sw-accounts-toggle" class="toggle-btn fas fa-toggle-off" style="font-size: 2em;"></i>
                    </td>
                    <td class="text-info">
                        <small>
                            Updates accounts and boats connected to sailaway during last 7 days.
                            <!-- Removes old values from the database (sailaway boats and user not active for <?= ceil($SW_DATA_DURATION / 30) ?> months, boat locations older than <?= $TRAJECTORY_DURATION ?> hours, CVE logs older than <?= $CVE_LOGS_DURATION; ?> days) -->
                        </small>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex;">
                            <div style="margin-right: 1em;">
                                <canvas id="trajectories-progress-chart" style="display: none;" width="80px" height="80px"></canvas>
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="text-primary">Old trajectories</div>
                                <small><strong>last execution: </strong><span id="trajectories-last-exec" class="text-muted"></span></small><br>
                                <small><strong>duration: </strong><span id="trajectories-duration" class="text-muted"></span></small>
                            </div>
                        </div>
                    </td>
                    <td class="form-inline">
                        <input id="trajectories-period" type="number" class="form-control  is-valid" style="width: 150px;">
                        <div id="trajectories-label" class="valid-feedback"></div>
                    </td>
                    <td>
                        <i id="trajectories-toggle" class="toggle-btn fas fa-toggle-off" style="font-size: 2em;"></i>
                    </td>
                    <td class="text-info">
                        <small>
                            Removes boat locations older than <?= $TRAJECTORY_DURATION ?> hours.
                        </small>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex;">
                            <div style="margin-right: 1em;">
                                <canvas id="logs-progress-chart" style="display: none;" width="80px" height="80px"></canvas>
                            </div>
                            <div style="flex-grow: 1;">
                                <div class="text-primary">Old logs</div>
                                <small><strong>last execution: </strong><span id="logs-last-exec" class="text-muted"></span></small><br>
                                <small><strong>duration: </strong><span id="logs-duration" class="text-muted"></span></small>
                            </div>
                        </div>
                    </td>
                    <td class="form-inline">
                        <input id="logs-period" type="number" class="form-control  is-valid" style="width: 150px;">
                        <div id="logs-label" class="valid-feedback"></div>
                    </td>
                    <td>
                        <i id="logs-toggle" class="toggle-btn fas fa-toggle-off" style="font-size: 2em;"></i>
                    </td>
                    <td class="text-info">
                        <small>
                            Removes CVE logs older than <?= $CVE_LOGS_DURATION; ?> days.
                        </small>
                    </td>
                </tr>
            </table>
            <table class="table table-borderless">
                <tr class="text-danger">
                    <td>Queries to sailaway: </td>
                    <td id="sw-queries-label"></td>
                </tr>
            </table>
            <div class="row">
                <div class="col"></div>
                <div class="col"><canvas id="sw-chart"></canvas></div>
                <div class="col"></div>
            </div>
            <button id="submit-btn" type="button" class="btn btn-warning">Submit modifications</button>
            <script src="main.js"></script>
        <?php else : ?>
            <p class="text-danger text-center">You must be administrator to access this page</p>
        <?php endif; ?>
    <?php else : ?>
        <p class="text-info text-center">You must be connected to access this page</p>
    <?php endif; ?>
</div>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>