<?php

// job executed every minute
chdir(dirname(__FILE__));
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config_v2.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SailawayDataManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'BoatsManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AppLogsManager.php';
$swDataManager = new SailawayDataManager();
$boatsManager = new BoatsManager();
$appLogsManager = new AppLogsManager();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$tempFilePath = '../../../temp_v2.json';

$configJson = json_decode(file_get_contents($tempFilePath));

// ==============================================================================================
//
//                                          LOCATIONS
//
// ==============================================================================================

if ($configJson->location_update->enabled) {
    $period = intval($configJson->location_update->period);
    $timeElapsed =  intval((time() - intval($configJson->location_update->last_date)) / (60));

    if ($timeElapsed >= $period) {
        $configJson->location_update->last_date = intval(time());
        file_put_contents($tempFilePath, json_encode($configJson));

        $appLogsManager->storeEvent('start-location');

        try {
            //request all boat positions to the SW api
            $url = 'https://backend.sailaway.world/cgi-bin/sailaway/TrackAllBoats.pl?key=' . $SW_API_KEY;
            $json = json_decode(file_get_contents($url));


            foreach ($json->boats as $boat) {
                // update the location of all cve_boats
                $cveBoats = $boatsManager->getAllFromSWBoat(intval($boat->ubtnr));
                if (sizeof($cveBoats) > 0) // if this boat is tracked in the CVE server
                {
                    foreach ($cveBoats as $cveBoat) {
                        if (!$cveBoat->isInDryDock()) {
                            $boatsManager->updateLocation($cveBoat, $boat->ubtlat, $boat->ubtlon, $boat->ubtheading);
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            $line = $th->getLine();
            $appLogsManager->addStringLog("[" . $th->getFile() . " " . "line $line] " . $th->getMessage());
        }

        $appLogsManager->storeEvent('end-location');
        exit();
    }
}

// ==============================================================================================
//
//                                          WEATHER
//
// ==============================================================================================

if ($configJson->weather_update->enabled) {
    $period = intval($configJson->weather_update->period);
    $timeElapsed =  intval((time() - intval($configJson->weather_update->last_date)) / (60));

    if ($timeElapsed >= $period) {
        $configJson->weather_update->last_date = intval(time());
        file_put_contents($tempFilePath, json_encode($configJson));

        $appLogsManager->storeEvent('start-weather');

        try {
            $boats = $boatsManager->getAll();
            foreach ($boats as $boat) {
                if (!$boat->isInDryDock()) {
                    $pos = $boatsManager->getLastPos($boat);
                    if ($pos["lat"] !== NULL && $pos["lng"] !== NULL) {
                        $lat = $pos["lat"];
                        $lng = $pos["lng"];
                        $weatherUrl = "https://backend.sailaway.world/cgi-bin/sailaway/getenvironment.pl?lat=$lat&lon=$lng";
                        $jsonWeather = json_decode(file_get_contents($weatherUrl));
                        $weatherCoeff = ($jsonWeather->environment->windspeed - 5) ** 3 / 539 + 1;
                        if ($weatherCoeff < 1) {
                            $weatherCoeff = 1;
                        }
                        $boatsManager->setWeatherCoeff($boat, $weatherCoeff);
                    }
                }
            }
        } catch (\Throwable $th) {
            $line = $th->getLine();
            $appLogsManager->addStringLog("[" . $th->getFile() . " " . "line $line] " . $th->getMessage());
        }

        $appLogsManager->storeEvent('end-weather');
        exit();
    }
}

// ==============================================================================================
//
//                                          DAMAGE
//
// ==============================================================================================

if ($configJson->damage_update->enabled) {
    $period = intval($configJson->damage_update->period);
    $timeElapsed =  intval((time() - intval($configJson->damage_update->last_date)) / (60));

    if ($timeElapsed >= $period) {
        $configJson->damage_update->last_date = intval(time());
        file_put_contents($tempFilePath, json_encode($configJson));

        $appLogsManager->storeEvent('start-damage');

        try {
            foreach ($boatsManager->getAll() as $boat) {
                if (!$boat->isInDryDock()) {
                    $boatsManager->updateBoatState($boat);
                } else {
                    $boatsManager->repairInDryDock($boat);
                }
            }
        } catch (\Throwable $th) {
            $line = $th->getLine();
            $appLogsManager->addStringLog("[" . $th->getFile() . " " . "line $line] " . $th->getMessage());
        }

        $appLogsManager->storeEvent('end-damage');
        exit();
    }
}

// ==============================================================================================
//
//                                          FOOD / WATER
//
// ==============================================================================================

if ($configJson->food_update->enabled) {
    $period = intval($configJson->food_update->period);
    $timeElapsed =  intval((time() - intval($configJson->food_update->last_date)) / (60));

    if ($timeElapsed >= $period) {
        $configJson->food_update->last_date = intval(time());
        file_put_contents($tempFilePath, json_encode($configJson));

        $appLogsManager->storeEvent('start-food');

        try {
            // update the food/water
            foreach ($boatsManager->getAll() as $boat) {
                if (!$boat->isInDryDock()) {
                    $boatsManager->consumeFood($boat);
                    $boatsManager->consumeWater($boat);
                }
            }
        } catch (\Throwable $th) {
            $line = $th->getLine();
            $appLogsManager->addStringLog("[" . $th->getFile() . " " . "line $line] " . $th->getMessage());
        }

        $appLogsManager->storeEvent('end-food');
        exit();
    }
}

// ==============================================================================================
//
//                                          SAILAWAY DATA
//
// ==============================================================================================

if ($configJson->sw_accounts_update->enabled) {
    $period = intval($configJson->sw_accounts_update->period);
    $timeElapsed =  intval((time() - intval($configJson->sw_accounts_update->last_date)) / (60));

    if ($timeElapsed >= $period) {
        $configJson->sw_accounts_update->last_date = intval(time());
        file_put_contents($tempFilePath, json_encode($configJson));

        $appLogsManager->storeEvent('start-sw-data');

        try {
            //request all boats to the SW api
            $url = 'https://backend.sailaway.world/cgi-bin/sailaway/TrackAllBoats.pl?key=' . $SW_API_KEY;
            $json = json_decode(file_get_contents($url));


            foreach ($json->boats as $boat) {
                // automatically add all new users and boats in the cve database
                if ($boat->ubtnr and $boat->ubtbtpnr and $boat->usrnr and $boat->ubtname) {
                    $swDataManager->addSailawayBoat(intval($boat->ubtnr), intval($boat->ubtbtpnr), intval($boat->usrnr), rawurlencode($boat->ubtname));
                }
                if ($boat->usrnr and $boat->usrname) {
                    $swDataManager->addSailawayUser(intval($boat->usrnr), rawurlencode($boat->usrname));
                }
            }
        } catch (\Throwable $th) {
            $line = $th->getLine();
            $appLogsManager->addStringLog("[" . $th->getFile() . " " . "line $line] " . $th->getMessage());
        }

        $appLogsManager->storeEvent('end-sw-data');
        exit();
    }
}

// ==============================================================================================
//
//                                          TRAJECTORIES
//
// ==============================================================================================

if ($configJson->trajectories_update->enabled) {
    $period = intval($configJson->trajectories_update->period);
    $timeElapsed =  intval((time() - intval($configJson->trajectories_update->last_date)) / (60));

    if ($timeElapsed >= $period) {
        $configJson->trajectories_update->last_date = intval(time());
        file_put_contents($tempFilePath, json_encode($configJson));

        $appLogsManager->storeEvent('start-trajectories');

        try {
            $boatsManager->removeOldPositions();
        } catch (\Throwable $th) {
            $line = $th->getLine();
            $appLogsManager->addStringLog("[" . $th->getFile() . " " . "line $line] " . $th->getMessage());
        }

        $appLogsManager->storeEvent('end-trajectories');
        exit();
    }
}

// ==============================================================================================
//
//                                          LOGS
//
// ==============================================================================================

if ($configJson->logs_update->enabled) {
    $period = intval($configJson->logs_update->period);
    $timeElapsed =  intval((time() - intval($configJson->logs_update->last_date)) / (60));

    if ($timeElapsed >= $period) {
        $configJson->logs_update->last_date = intval(time());
        file_put_contents($tempFilePath, json_encode($configJson));

        $appLogsManager->storeEvent('start-logs');

        try {
            // remove old cve app activity logs
            $appLogsManager->removeOldActivityLogs();
            $appLogsManager->removeOldStringLogs();
        } catch (\Throwable $th) {
            $line = $th->getLine();
            $appLogsManager->addStringLog("[" . $th->getFile() . " " . "line $line] " . $th->getMessage());
        }

        $appLogsManager->storeEvent('end-logs');
        exit();
    }
}

//TODO: new task: $swDataManager->removeOldSailwayData();
