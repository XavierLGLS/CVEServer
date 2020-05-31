<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'BoatsManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AppLogsManager.php';

$tempFilePath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'temp_v2.json';

$return;
$return["success"] = false;

if (!isRequestAuthenticated()) {
    $return["message"] = "You are not authorized to use this service. Please use a right key.";
} else {
    if (isset($_POST["action"])) {
        switch ($_POST["action"]) {
            case "get-settings":
                handleGetSettings();
                break;
            case "get-stats":
                handleGetStats();
                break;
            case "put-settings":
                handlePutSettings();
                break;
            default:
                $return["message"] = "The action is not correct";
                $return["success"] = false;
                break;
        }
    }
}

echo json_encode($return);

function handleGetSettings(): void
{
    global $return, $tempFilePath;
    
    $boatsManager = new BoatsManager();

    try {
        $json = json_decode(file_get_contents($tempFilePath));
        $json->boat_count = $boatsManager->getCount();
        $return["settings"] = $json;
        $return["success"] = true;
    } catch (Exception $e) {
        $return["success"] = false;
        $return["message"] = $e->getMessage();
    }
}

function handlePutSettings(): void
{
    global $return, $tempFilePath;

    if (isset($_POST['settings'])) {
        try {
            $json = json_decode(file_get_contents($tempFilePath));
            $json->location_update->enabled = $_POST['settings']["location_enabled"] == 1;
            $json->location_update->period = intval($_POST['settings']["location_period"]);
            $json->weather_update->enabled = $_POST['settings']["weather_enabled"] == 1;
            $json->weather_update->period = intval($_POST['settings']["weather_period"]);
            $json->damage_update->enabled = $_POST['settings']["damage_enabled"] == 1;
            $json->damage_update->period = intval($_POST['settings']["damage_period"]);
            $json->food_update->enabled = $_POST['settings']["food_enabled"] == 1;
            $json->food_update->period = intval($_POST['settings']["food_period"]);
            $json->sw_accounts_update->enabled = $_POST['settings']["sw_accounts_enabled"] == 1;
            $json->sw_accounts_update->period = intval($_POST['settings']["sw_accounts_period"]);
            $json->trajectories_update->enabled = $_POST['settings']["trajectories_enabled"] == 1;
            $json->trajectories_update->period = intval($_POST['settings']["trajectories_period"]);
            $json->logs_update->enabled = $_POST['settings']["logs_enabled"] == 1;
            $json->logs_update->period = intval($_POST['settings']["logs_period"]);
            file_put_contents($tempFilePath, json_encode($json));
            $return["success"] = true;
        } catch (Exception $e) {
            $return["success"] = false;
            $return["message"] = $e->getMessage();
        }
    } else {
        $return["success"] = false;
        $return["message"] = "Not enough information in the request";
    }
}

function handleGetStats(): void
{
    global $return;

    $appLogsManager = new AppLogsManager();

    try {
        $return["last_dates"] = $appLogsManager->getLastDates();
        $return["durations"] = $appLogsManager->getAverageDurations();
        $return["success"] = true;
    } catch (Exception $e) {
        $return["success"] = false;
        $return["message"] = $e->getMessage();
    }
}
